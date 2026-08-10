<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

/**
 * "Sign in with Google" — OPTIONAL customer auth (Socialite redirect flow) plus
 * Google One Tap (the auto-prompt). Both are user-CHOSEN, never a forced wall,
 * and NEITHER auto-subscribes anyone to marketing — the `accepts_marketing`
 * consent flag is only set when the person ticks the opt-in box (Google's API
 * Services User Data Policy requires exactly this).
 */
class GoogleAuthController extends Controller
{
    // ---- Redirect ("Sign in with Google" button) ----------------------------

    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('login')->with('auth_error',
                'Google sign-in did not complete. Please try again.');
        }

        if (! $googleUser->getEmail()) {
            return redirect()->route('login')->with('auth_error',
                'Google did not return an email address, so we could not sign you in.');
        }

        $this->upsertAndLogin(
            $googleUser->getEmail(),
            $googleUser->getId(),
            $googleUser->getName() ?: $googleUser->getNickname(),
            $googleUser->getAvatar(),
        );

        return redirect()->intended(route('account'));
    }

    // ---- Google One Tap (the auto-prompt) -----------------------------------

    /**
     * Receives the One Tap credential (a Google ID token / JWT) from the browser
     * and signs the user in after verifying the token WITH Google — verifying the
     * audience is what stops a token minted for another site being replayed here.
     */
    public function oneTap(Request $request): JsonResponse
    {
        $credential = (string) $request->input('credential');

        if ($credential === '') {
            return response()->json(['ok' => false], 422);
        }

        // Verify the ID token with Google (validates signature + expiry server-side).
        $resp = Http::acceptJson()->get('https://oauth2.googleapis.com/tokeninfo', [
            'id_token' => $credential,
        ]);

        if ($resp->failed()) {
            return response()->json(['ok' => false], 401);
        }

        $p = $resp->json();

        // Audience MUST be our client id, issuer must be Google, email present+verified.
        $audOk = ($p['aud'] ?? null) === config('services.google.client_id');
        $issOk = in_array($p['iss'] ?? null, ['accounts.google.com', 'https://accounts.google.com'], true);
        $emailOk = ! empty($p['email']) && ($p['email_verified'] ?? 'false') === 'true';

        if (! $audOk || ! $issOk || ! $emailOk) {
            return response()->json(['ok' => false], 401);
        }

        $this->upsertAndLogin($p['email'], $p['sub'] ?? null, $p['name'] ?? null, $p['picture'] ?? null);

        return response()->json(['ok' => true]);
    }

    // ---- Logout -------------------------------------------------------------

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    // ---- Shared: create-or-update the user and log them in ------------------

    private function upsertAndLogin(string $email, ?string $googleId, ?string $name, ?string $avatar): void
    {
        // Match by email; include soft-deleted so a returning customer is reused.
        // forceFill bypasses mass-assignment guards.
        $user = User::withTrashed()->firstOrNew(['email' => $email]);

        $user->forceFill([
            'google_id' => $googleId,
            'name' => $name ?: ($user->name ?: 'Customer'),
            'avatar_path' => $avatar ?: $user->avatar_path,
            'email_verified_at' => $user->email_verified_at ?? now(),
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
        ]);

        // password is NOT NULL in the schema; OAuth users get an unusable random one.
        if (! $user->password) {
            $user->password = Hash::make(Str::random(48));
        }

        if ($user->trashed()) {
            $user->deleted_at = null;   // reactivate a returning customer
        }

        $user->save();

        // NOTE: deliberately does NOT set accepts_marketing — consent is separate.

        Auth::login($user, remember: true);      // fires Login → MergeGuestCartOnLogin
        request()->session()->regenerate();
    }
}
