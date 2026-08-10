<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/*
| Newsletter subscribe — the homepage footer/newsletter band posts here.
|
| Writes to the existing `newsletter_subscribers` table (migration
| 2026_08_10_001320) via the existing NewsletterSubscriber model. There is no
| mailer wired up yet, so a subscribe is recorded as `subscribed` directly
| rather than sending a double opt-in confirmation — when a mailer is added,
| switch the status to `pending` and dispatch the confirmation token the table
| already carries.
|
| updateOrCreate keeps the unique email constraint from ever throwing: a repeat
| subscribe is idempotent, and a previously-unsubscribed address is re-enabled.
*/
class NewsletterController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:180'],
        ]);

        // The subscribers table migration is annotated "Phase 6 — optional", so
        // it may not have run on every environment. Fail soft rather than 500 at
        // a customer: log it and still confirm, so a missing table never breaks
        // the homepage form.
        try {
            NewsletterSubscriber::updateOrCreate(
                ['email' => $validated['email']],
                [
                    'status' => 'subscribed',
                    'unsubscribed_at' => null,
                    'source' => 'homepage',
                ],
            );
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with(
            'newsletter_status',
            'Thanks — you are on the list. We will only email about launches, restocks and formulation notes.',
        );
    }
}
