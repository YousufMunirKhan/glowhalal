<?php

namespace App\Services\Cart;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Support\Facades\Cookie;

/**
 * Architecture §5.3 — cart identity for guests and logged-in users.
 *
 * The cart lives in the database; the cookie holds only an opaque ULID token.
 * A session-carried cart evaporates with SESSION_LIFETIME (120 minutes); a
 * 30-day cookie holding a token survives browser restarts and makes
 * abandoned-cart recovery possible.
 */
final class CartManager
{
    public const COOKIE = 'gh_cart';

    public const COOKIE_DAYS = 30;

    /** Set once per request so a cart created mid-request is visible to later reads. */
    private ?Cart $resolved = null;

    private ?string $queuedToken = null;

    public function current(bool $createIfMissing = true): ?Cart
    {
        if ($this->resolved !== null) {
            return $this->resolved;
        }

        $user = auth()->user();

        if ($user) {
            $cart = Cart::active()->where('user_id', $user->id)->latest('id')->first();

            if ($cart) {
                return $this->resolved = $cart;
            }
        }

        if ($token = $this->token()) {
            $cart = Cart::active()->where('token', $token)->first();

            if ($cart) {
                // Claim an anonymous cart for a user who logged in mid-session.
                if ($user && $cart->user_id === null) {
                    $cart->update(['user_id' => $user->id]);
                }

                return $this->resolved = $cart;
            }
        }

        return $createIfMissing ? $this->resolved = $this->create($user) : null;
    }

    /**
     * A cart that exists outside the shopper's session basket — used by Buy Now.
     * Deliberately does NOT touch the gh_cart cookie, so the existing basket is
     * neither merged into nor cleared.
     */
    public function createDetached(): Cart
    {
        return Cart::create([
            'user_id' => auth()->id(),
            'currency' => 'PKR',
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 500),
        ]);
    }

    /** Point the cookie at a different cart (merge on login). */
    public function remember(Cart $cart): void
    {
        $this->resolved = $cart;
        $this->queueCookie($cart->token);
    }

    /** Forget the current cart entirely — called once a cart converts to an order. */
    public function forget(): void
    {
        $this->resolved = null;
        $this->queuedToken = null;
        Cookie::queue(Cookie::forget(self::COOKIE));
    }

    private function token(): ?string
    {
        return $this->queuedToken ?? request()->cookie(self::COOKIE);
    }

    private function create(?User $user): Cart
    {
        $cart = Cart::create([
            'user_id' => $user?->id,
            'currency' => 'PKR',
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 500),
        ]);

        $this->queueCookie($cart->token);

        return $cart;
    }

    private function queueCookie(string $token): void
    {
        $this->queuedToken = $token;

        Cookie::queue(Cookie::make(
            self::COOKIE,
            $token,
            self::COOKIE_DAYS * 24 * 60,
            path: '/',
            domain: null,
            secure: app()->isProduction(),
            httpOnly: true,
            raw: false,
            sameSite: 'lax',
        ));
    }
}
