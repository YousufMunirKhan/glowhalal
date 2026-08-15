<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsStorefrontLayout;
use App\Models\Page;
use App\Settings\StoreSettings;
use App\Support\JsonLd;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

/**
 * `/contact` — GET the page, POST the form, GET the thank-you.
 *
 * The form is a plain `<form method="POST" action="/contact">` with a real
 * server-side handler. It works with JavaScript switched off, which is both an
 * accessibility requirement and the reason a crawler can see the whole page
 * (SEO §6.2). Livewire may be layered over it later; it must not replace it.
 *
 * POST → 303 → /contact/thank-you (noindex,follow). The success state is never
 * rendered at the same URL: it would break measurement and create a second
 * document at a canonical that is supposed to be the form.
 *
 * Delivery is `Mail::raw` to the address in StoreSettings. Locally
 * MAIL_MAILER=log, so a submission lands in storage/logs/laravel.log. There is
 * no contact_messages table and this controller does not create one — writing a
 * migration is another workstream's job.
 *
 * Spam control is a honeypot plus a form-age check plus route throttling. No
 * CAPTCHA: it costs INP and conversions on exactly the low-end Android devices
 * this store sells to.
 */
class ContactController extends Controller
{
    use BuildsStorefrontLayout;

    /** Subjects double as the routing key in the notification email. */
    private const SUBJECTS = [
        'ingredient' => 'A question about an ingredient',
        'order' => 'An existing order',
        'product' => 'A product or a shade',
        'wholesale' => 'Wholesale or stockist enquiry',
        'press' => 'Press or collaboration',
        'other' => 'Something else',
    ];

    public function show(): View
    {
        $page = Page::query()->published()->where('slug', 'contact')->first();
        $store = app(StoreSettings::class);

        $canonical = $this->canonical();
        $title = $page?->seoMeta?->meta_title ?: 'Contact Glow Halal - WhatsApp, Phone & Ingredient Questions';
        $description = $page?->resolvedMetaDescription()
            ?: 'Reach Glow Halal by WhatsApp, phone or email — or ask us about a specific ingredient '
              .'on a pack you already own and we will publish the answer.';

        $faqs = $this->faqs($store);

        $crumbs = [
            ['name' => 'Home', 'url' => url('/')],
            ['name' => 'Contact'],
        ];

        return view('pages.contact', [
            ...$this->layoutData(),
            'title' => str($title)->limit(65, '')->trim()->toString(),
            'description' => str($description)->limit(158)->toString(),
            'canonical' => $canonical,
            'page' => $page,
            'store' => $store,
            'subjects' => self::SUBJECTS,
            'faqs' => $faqs,
            'crumbs' => $crumbs,
            'schema' => $this->schema([
                JsonLd::webPage($canonical, $title, $description, 'ContactPage'),
                JsonLd::breadcrumbs($canonical, $crumbs),
                JsonLd::faqPage($canonical, $faqs),
                // No LocalBusiness node. That requires a genuine walk-in
                // location, and there is no confirmed address at all yet
                // (SEO §4.12). Emitting one would fabricate a storefront.
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // Honeypot. A real browser never fills a field it cannot see; a bot
        // fills every input it finds. Fail silently — telling a spammer which
        // check caught them is how the next attempt gets past it.
        //
        // BUT: silent means silent to the SENDER, never to us. This trap used
        // to discard the message entirely, which is the worst failure mode a
        // contact form can have — a false positive (browser autofill, a
        // legitimate assistant filling the form for a user) looked like
        // success to the sender while the business never received a word. The
        // tripped submission is now logged in full, so a real message caught
        // here is recoverable from the log instead of gone.
        //
        // `website` is still checked alongside the new field name: any page
        // cached before the rename still posts the old field.
        if (filled($request->input('gh_extra')) || filled($request->input('website'))) {
            Log::warning('Contact form honeypot tripped — message NOT delivered', [
                'name' => (string) $request->input('name'),
                'email' => (string) $request->input('email'),
                'subject' => (string) $request->input('subject'),
                'message' => (string) $request->input('message'),
                'ip' => $request->ip(),
            ]);

            return redirect()->to(route('contact.thank-you'), 303);
        }

        // Form age. Anything submitted within three seconds of the page being
        // rendered was not typed by a person.
        $renderedAt = (int) $request->input('rendered_at', 0);

        if ($renderedAt > 0 && (time() - $renderedAt) < 3) {
            throw ValidationException::withMessages([
                'message' => 'That was submitted a little too quickly. Please try again.',
            ]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'subject' => ['required', 'string', 'in:'.implode(',', array_keys(self::SUBJECTS))],
            'message' => ['required', 'string', 'min:10', 'max:4000'],
        ], [
            'name.required' => 'Please tell us your name.',
            'email.required' => 'We need an email address to reply to.',
            'email.email' => 'That does not look like an email address — check for a typo.',
            'subject.required' => 'Please choose what this is about.',
            'message.required' => 'Please write your message.',
            'message.min' => 'Please add a little more detail so we can answer properly.',
        ]);

        $store = app(StoreSettings::class);
        $to = $store->contact_email ?: config('mail.from.address');

        $subjectLabel = self::SUBJECTS[$data['subject']];

        $body = implode("\n", [
            'New enquiry from the Glow Halal contact form.',
            '',
            'Subject: '.$subjectLabel,
            'Name:    '.$data['name'],
            'Email:   '.$data['email'],
            'Sent:    '.now()->toDayDateTimeString().' (server time)',
            '',
            '---',
            $data['message'],
        ]);

        Mail::raw($body, function ($mail) use ($to, $subjectLabel, $data) {
            $mail->to($to)
                ->replyTo($data['email'], $data['name'])
                ->subject('[Glow Halal] '.$subjectLabel);
        });

        Log::info('Contact form submission', [
            'subject' => $data['subject'],
            'email' => $data['email'],
        ]);

        return redirect()->to(route('contact.thank-you'), 303)
            ->with('contact.sent', $data['email']);
    }

    public function thankYou(): View
    {
        $store = app(StoreSettings::class);
        $canonical = url('/contact/thank-you');

        return view('pages.contact-thank-you', [
            ...$this->layoutData(),
            'title' => 'Message sent | Glow Halal',
            'description' => null,
            'canonical' => $canonical,
            // A post-conversion page. Indexing it would put a dead-end
            // confirmation screen in the search results (SEO §7.2).
            'robots' => 'noindex,follow',
            'store' => $store,
            'sentTo' => session('contact.sent'),
            'crumbs' => [
                ['name' => 'Home', 'url' => url('/')],
                ['name' => 'Contact', 'url' => url('/contact')],
                ['name' => 'Message sent'],
            ],
            'schema' => $this->schema([
                JsonLd::webPage($canonical, 'Message sent', null),
            ]),
        ]);
    }

    /**
     * Support FAQs. Every answer is rendered visibly on the page, and every one
     * of them is a statement we can stand behind — nothing here promises a
     * response time, a courier, or a returns window that has not been
     * configured, because those are business facts the owner owns.
     *
     * @return array<int, array{question: string, answer: string}>
     */
    private function faqs(StoreSettings $store): array
    {
        $faqs = [
            [
                'question' => 'Can you tell me what is in a product before I order?',
                'answer' => 'Yes, and you should not have to ask — the full INCI list is published on every '
                    .'product page, in the order it appears on the pack. If something on it is unclear, send us '
                    .'the name and we will explain what it is and where it comes from.',
            ],
            [
                'question' => 'Can I ask about an ingredient in something you do not sell?',
                'answer' => 'Yes. Send the name exactly as it is printed on the pack. We publish what we find in '
                    .'the Ingredient Index, including the answers that are inconvenient for us.',
            ],
            [
                'question' => 'Do you have a halal certificate you can send me?',
                'answer' => 'No. Glow Halal holds no third-party halal accreditation and does not claim one. '
                    .'What we can send you is the complete ingredient list for any product, and the reasoning '
                    .'behind every exclusion on our list.',
            ],
        ];

        if (filled($store->opening_hours)) {
            $faqs[] = [
                'question' => 'When can I reach you?',
                'answer' => $store->opening_hours,
            ];
        }

        return $faqs;
    }
}
