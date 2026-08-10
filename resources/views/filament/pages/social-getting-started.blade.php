{{-- Onboarding for the Social planner. Styling uses inline styles + this scoped
     block only (never purgeable Tailwind utilities) so it renders identically
     regardless of the panel's CSS build. Theme-aware via the `.dark` selector. --}}
<x-filament-panels::page>
    <style>
        .gs-card { border:1px solid #e5e7eb; border-radius:12px; padding:20px 22px; background:#ffffff; }
        .dark .gs-card { border-color:#374151; background:#111827; }
        .gs-card + .gs-card { margin-top:20px; }

        .gs-h2 { font-size:16px; font-weight:700; margin:0 0 4px; color:#111827; }
        .dark .gs-h2 { color:#f9fafb; }
        .gs-sub { font-size:13px; color:#6b7280; margin:0 0 16px; }
        .dark .gs-sub { color:#9ca3af; }

        .gs-steps { list-style:none; margin:0; padding:0; counter-reset:step; }
        .gs-steps li { position:relative; padding:0 0 14px 44px; font-size:14px; line-height:1.55; color:#374151; }
        .dark .gs-steps li { color:#d1d5db; }
        .gs-steps li:last-child { padding-bottom:0; }
        .gs-steps li::before {
            counter-increment:step; content:counter(step);
            position:absolute; left:0; top:0; width:28px; height:28px; border-radius:9999px;
            display:flex; align-items:center; justify-content:center;
            font-size:13px; font-weight:700; background:#059669; color:#ffffff;
        }
        .gs-steps li strong { color:#111827; }
        .dark .gs-steps li strong { color:#f9fafb; }

        .gs-links { list-style:none; margin:0; padding:0; }
        .gs-links li { padding:14px 0; border-bottom:1px solid #f3f4f6; }
        .dark .gs-links li { border-bottom-color:#1f2937; }
        .gs-links li:last-child { border-bottom:none; padding-bottom:0; }
        .gs-links a { font-weight:600; font-size:14px; color:#047857; text-decoration:none; }
        .gs-links a:hover { text-decoration:underline; }
        .dark .gs-links a { color:#34d399; }
        .gs-links .gs-note { display:block; margin-top:3px; font-size:13px; color:#6b7280; }
        .dark .gs-links .gs-note { color:#9ca3af; }
        .gs-links .gs-url { display:block; margin-top:2px; font-size:12px; font-family:ui-monospace, SFMono-Regular, Menlo, monospace; color:#9ca3af; word-break:break-all; }

        .gs-callout { border:1px solid #fcd34d; border-radius:12px; padding:16px 18px; background:#fffbeb; }
        .dark .gs-callout { border-color:#78350f; background:#78350f33; }
        .gs-callout .gs-callout-title { font-weight:700; font-size:14px; color:#92400e; margin:0 0 4px; }
        .dark .gs-callout .gs-callout-title { color:#fde68a; }
        .gs-callout p { margin:0; font-size:13.5px; line-height:1.55; color:#78350f; }
        .dark .gs-callout p { color:#fef3c7; }

        .gs-pill { display:inline-block; padding:1px 8px; border-radius:9999px; font-size:12px; font-weight:600;
                   background:#d1fae5; color:#065f46; }
        .dark .gs-pill { background:#064e3b; color:#a7f3d0; }
    </style>

    {{-- (a) The Phase-0 flow --}}
    <div class="gs-card">
        <h2 class="gs-h2">How the planner works <span class="gs-pill">Phase 0 · manual publishing</span></h2>
        <p class="gs-sub">You plan and stay compliant here; the actual posting happens by hand in each app. Follow these steps for every post.</p>
        <ol class="gs-steps">
            <li><strong>Compose a post.</strong> Open <em>Post Composer</em> and give it an internal title (never shown publicly).</li>
            <li><strong>Add the details.</strong> Pick the platforms it goes to, write the caption (override per-platform if you like), attach media with alt text, and add hashtags (or pull in a saved hashtag set).</li>
            <li><strong>Tick the compliance checklist.</strong> The honesty boxes must all be ticked before the post can leave <em>Draft</em> — no medical/cure claims, no “halal certified” claim, a safety note if it’s skin-safety content, and consent for any testimonial.</li>
            <li><strong>Set the schedule.</strong> Choose the date &amp; time (PKT). Only once the checklist is complete can you set the status to <em>Scheduled</em>.</li>
            <li><strong>It shows on the Calendar.</strong> The post appears on its scheduled day in the month grid, colour-coded by status.</li>
            <li><strong>Each morning you get a reminder.</strong> A bell notification lists what’s due today (and anything overdue) — see the 🔔 bell at the top of the admin.</li>
            <li><strong>Copy caption / Open app.</strong> On the post row use <em>Copy / Open app</em> to copy the caption + hashtags and jump straight to the platform.</li>
            <li><strong>Publish in the native app.</strong> Paste and post manually in Instagram / Facebook / TikTok, etc.</li>
            <li><strong>Mark as posted.</strong> Back here, click <em>Mark as posted</em> and pick the platform. When every platform is done, the post flips to <em>Posted</em> automatically.</li>
        </ol>
    </div>

    {{-- (b) Connect / set up accounts --}}
    <div class="gs-card">
        <h2 class="gs-h2">Connect / set up your accounts</h2>
        <p class="gs-sub">The official places to create and prepare each account. Open in a new tab and follow their own instructions.</p>
        <ul class="gs-links">
            <li>
                <a href="https://www.facebook.com/pages/create" target="_blank" rel="noopener noreferrer">Create a Facebook Page</a>
                <span class="gs-note">Your business needs a Facebook <em>Page</em> (not just a personal profile) — everything else connects to it.</span>
                <span class="gs-url">https://www.facebook.com/pages/create</span>
            </li>
            <li>
                <a href="https://business.facebook.com" target="_blank" rel="noopener noreferrer">Meta Business Suite</a>
                <span class="gs-note">Free scheduling + a shared inbox for Facebook and Instagram. This is the practical way to actually schedule posts <strong>today</strong>.</span>
                <span class="gs-url">https://business.facebook.com</span>
            </li>
            <li>
                <a href="https://help.instagram.com/502981923235522" target="_blank" rel="noopener noreferrer">Convert Instagram to Business/Creator + link your Facebook Page</a>
                <span class="gs-note">Switch your Instagram to a Business or Creator account and link it to the Page so Business Suite can manage both.</span>
                <span class="gs-url">https://help.instagram.com/502981923235522</span>
            </li>
            <li>
                <a href="https://www.tiktok.com/business/en" target="_blank" rel="noopener noreferrer">TikTok for Business</a>
                <span class="gs-note">Set up a TikTok business account for your brand’s videos.</span>
                <span class="gs-url">https://www.tiktok.com/business/en</span>
            </li>
            <li>
                <a href="https://developers.facebook.com" target="_blank" rel="noopener noreferrer">Meta for Developers + Business Verification</a>
                <span class="gs-note">Only needed later, for in-admin auto-publishing. Requires Business Verification before any API posting is possible.</span>
                <span class="gs-url">https://developers.facebook.com</span>
            </li>
        </ul>
    </div>

    {{-- (c) Honest callout --}}
    <div class="gs-callout" style="margin-top:20px;">
        <p class="gs-callout-title">Publishing is manual in this version</p>
        <p>You plan, stay compliant, and get reminders here — then copy the caption and post in the app. One-click auto-publish needs Meta Business Verification and will be added later.</p>
    </div>
</x-filament-panels::page>
