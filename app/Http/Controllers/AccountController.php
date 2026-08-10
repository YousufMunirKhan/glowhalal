<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The signed-in customer's account area. Reached after "Sign in with Google";
 * guests are bounced to /login by the `auth` middleware on the route.
 */
class AccountController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $orders = $user->orders()
            ->latest()
            ->take(20)
            ->get();

        return view('account.index', [
            'user' => $user,
            'orders' => $orders,
        ]);
    }

    /**
     * Marketing opt-in — the CONSENTED path to the mailing list. Sets
     * accepts_marketing only when the customer ticks it themselves.
     */
    public function updateMarketing(Request $request): RedirectResponse
    {
        $opted = $request->boolean('accepts_marketing');

        $request->user()->forceFill([
            'accepts_marketing' => $opted,
            'accepts_marketing_at' => $opted ? now() : null,
        ])->save();

        return back()->with('account_status', $opted
            ? 'Thanks — you will hear about new products and offers.'
            : 'You have been unsubscribed from marketing emails.');
    }
}
