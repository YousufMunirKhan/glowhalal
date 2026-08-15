<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Storefront
|--------------------------------------------------------------------------
|
| PLACEHOLDER DATA — read this before wiring the real models.
|
| Every array below is shaped the way the Blade components consume it, and
| nothing in the views reaches for an Eloquent attribute directly. Swapping in
| real data is therefore a change to this file only:
|
|   $products      -> Product::published()->with('exclusions')->get()
|                     keys: slug, name, volume, descriptor, price (int, PKR),
|                           ingredient_count, free_from[], image, image_alt
|   $neverUse      -> Exclusion::forHomepage()->take(6)  (full list is 8)
|   $journal       -> Post::published()->forLocale('en')->latest()->take(6)
|   $journalUr     -> Post::published()->forLocale('ur-Latn')->latest()->take(3)
|   $company       -> config('company') or a Settings singleton
|   $founder       -> Settings / About page content
|
| Prices are integers in whole rupees; the views format them with
| number_format() and render them as `PKR 4,850` with tabular figures.
|
| The layout picks between the editorial feature-block format and the real
| product grid on catalogue count, never on a config flag. Below 4 published
| SKUs the homepage MUST NOT render a product grid — three tiles in a grid is
| the single visual that says "dead store".
|
| ---------------------------------------------------------------------------
| NO THIRD-PARTY HALAL CLAIMS. Read this before adding anything to this file.
| ---------------------------------------------------------------------------
|
| Glow Halal holds NO halal accreditation from any body and does not claim one.
| Do not add an issuing organisation, reference number, standard number, seal,
| badge, lab name, audit reference, "approval pending" status, or anything else
| that implies third-party verification. A halal claim is a religious
| compliance claim; inventing one is not a placeholder, it is a false
| statement.
|
| The only trust claims this site is permitted to make are the two that are
| true: we publish the full INCI list for every product, and we publish the
| list of ingredients we will not formulate with ($neverUse).
|
| Company registration details (registered legal name, tax identifiers,
| incorporation number) are also deliberately absent at the owner's
| instruction. Ordinary contact details only.
|
*/

Route::get('/', function () {
    // REAL products from the database — no longer a hardcoded array.
    // The previous placeholder array listed three products that do not exist
    // (nourishing-face-cream, rose-tint-lip-balm, cleansing-oil); every one of
    // their links 404'd. Anything shown here must be a row the owner can edit
    // in the admin.
    $products = \App\Models\Product::query()
        ->where('status', 'active')
        ->whereNotNull('published_at')
        ->with(['defaultVariant', 'primaryImage', 'verifiedFreeFromAttributes'])
        ->withCount('ingredients')
        ->orderBy('position')
        ->orderByDesc('is_featured')
        ->take(6)
        ->get()
        ->map(fn ($p) => [
            'slug' => $p->slug,
            'name' => $p->name,
            'volume' => $p->defaultVariant?->name ?? '',
            'descriptor' => $p->short_description ?? '',
            // price_min_amount is cast to App\Support\Money, not an int — it
            // cannot be divided. toRupees() is the supported conversion; the
            // views then format with number_format(), so pass whole rupees.
            'price' => (int) round($p->price_min_amount?->toRupees() ?? 0),
            // Old price for the strikethrough — only when it genuinely beats
            // the selling price, same rule as the shop cards and buy box.
            'compare_at' => ($p->defaultVariant?->compare_at_amount
                && $p->price_min_amount
                && $p->defaultVariant->compare_at_amount->minorUnits > $p->price_min_amount->minorUnits)
                    ? (int) round($p->defaultVariant->compare_at_amount->toRupees())
                    : null,
            'ingredient_count' => $p->ingredients_count,
            'free_from' => $p->verifiedFreeFromAttributes->pluck('name')->take(3)->all(),
            // ->url() resolves to /storage/<path>; the raw ->path is relative and
            // would 404 on the homepage (shop/PDP already use the storage URL).
            // Fallback rotates by slug so a grid of image-less products never
            // renders the same illustration four times (a "dead store" signal).
            'image' => $p->primaryImage?->url() ?? '/images/placeholder/'.[
                'product-face-cream.svg',
                'product-cleansing-oil.svg',
                'product-lip-balm.svg',
            ][crc32($p->slug) % 3],
            'image_alt' => $p->primaryImage?->alt_text ?? $p->name,
        ])
        ->all();

    // Six of eight. The full table lives at /what-we-never-use — no accordion,
    // no "read more", no truncation to "+3 more".
    $neverUse = [
        [
            'ingredient' => 'Denatured alcohol',
            'inci' => ['Alcohol Denat.', 'SD Alcohol 40'],
        ],
        [
            'ingredient' => 'Carmine',
            'inci' => ['CI 75470', 'Cochineal Extract', 'Carminic Acid'],
        ],
        [
            'ingredient' => 'Gelatin',
            'inci' => ['Gelatin', 'Hydrolyzed Collagen'],
        ],
        [
            'ingredient' => 'Lanolin',
            'inci' => ['Lanolin', 'Lanolin Alcohol', 'PEG-75 Lanolin'],
        ],
        [
            'ingredient' => 'Animal-derived glycerin',
            'inci' => ['Glycerin (tallow-derived)'],
        ],
        [
            'ingredient' => 'Keratin',
            'inci' => ['Keratin', 'Hydrolyzed Keratin'],
        ],
    ];

    // NOTE: a hardcoded "$inDevelopment" roadmap (Hydrating Serum, Mineral
    // Sunscreen, Cream Blush with 2026–27 dates) used to be assembled here and
    // passed to the view. No template ever rendered it — verified against the
    // live HTML — and it described products that are not in the actual
    // expansion plan (docs/keyword-research-aug2026.md: sidr soap, shilajit,
    // ashwagandha, hair oil, face cream) with formulation-stage claims a
    // reseller cannot make. Deleted rather than corrected: fictional dead data
    // is a liability waiting for someone to render it.

    // NOTE: a "$delivery" how-it-works array also used to live here claiming
    // "2–3 working days in Lahore, Karachi and Islamabad. 3–5 elsewhere" —
    // contradicting the PDP copy and the Offer schema (2–4 / 4–7). Like the
    // roadmap above, no template ever rendered it (verified live), so it is
    // deleted rather than corrected. If a delivery-steps section is ever added
    // to the homepage, its timings must quote the PDP/schema truth: 2–4
    // working days to the big five cities, 4–7 elsewhere.

    // REAL posts from the database. The previous hardcoded array advertised two
    // articles that were never written; both links 404'd from the homepage.
    // With no posts published this is simply empty and the section renders its
    // empty state — which is honest, and fixes itself the moment the owner
    // publishes a post in the admin.
    //
    // WHY SIX + THREE, NOT TWO: the homepage is the most-crawled URL on the
    // site and this band is the documented discovery path for new posts
    // (seo-aeo-status-report §5). take(2) meant 13 of 15 live posts — including
    // every Roman-Urdu one — had no homepage link at all, which slows their
    // indexation for no reason. Six English cards plus a three-link Roman-Urdu
    // strip keeps the section compact while giving BOTH locales a crawl path;
    // Roman Urdu carries most of the modelled search opportunity
    // (docs/seo-growth-to-10k-aug2026.md §5.2), so hiding it from the homepage
    // was hiding the growth plan from Google.
    $mapJournalPost = fn ($post) => [
        'slug' => $post->slug,
        'title' => $post->title,
        'excerpt' => $post->excerpt ?? '',
        'date' => $post->published_at?->format('j F Y') ?? '',
        'date_iso' => $post->published_at?->toDateString() ?? '',
        'read_time' => $post->reading_time_minutes
            ? $post->reading_time_minutes.' min read'
            : '',
    ];

    $journal = \App\Models\BlogPost::query()
        ->published()
        // English cards link to /blog/{slug}; the Roman-Urdu mirror renders in
        // its own strip below and must never leak into this list.
        ->forLocale('en')
        ->orderByDesc('published_at')
        ->take(6)
        ->get()
        ->map($mapJournalPost)
        ->all();

    $journalUr = \App\Models\BlogPost::query()
        ->published()
        ->forLocale('ur-Latn')
        ->orderByDesc('published_at')
        ->take(3)
        ->get()
        ->map($mapJournalPost)
        ->all();

    // Contact details only, from Store settings (single source of truth).
    // Company registration identifiers are deliberately not published — see the
    // header note. NO invented fallbacks: an unset field stays null so the
    // structured data (built by App\Support\JsonLd) OMITS it rather than faking
    // a phone number or an address the business does not have. The display
    // chrome (footer, announcement bar) carries its own last-resort fallback.
    $storeSettings = rescue(fn () => app(\App\Settings\StoreSettings::class), null, false);
    $company = [
        'name' => 'Glow Halal',
        'address' => $storeSettings?->formattedAddress(),
        'mobile' => $storeSettings?->contact_phone,
        'whatsapp' => $storeSettings?->whatsappLink('Hi, I have a question about Glow Halal'),
        'email' => $storeSettings?->contact_email,
    ];

    // No founder persona: a fabricated person would breach the honesty rules.
    // If the owner ever wants a real founder section, it comes from Store
    // settings, not from hardcoded fiction.
    $founder = [];

    $meta = [
        'title' => 'Glow Halal — Halal Beauty & Cosmetics in Pakistan',
        'description' => 'Glow Halal is the halal beauty & cosmetics store in Pakistan — herbal, clean and honest skincare, delivered nationwide with Cash on Delivery.',
    ];

    return view('home', [
        'products' => $products,
        'neverUse' => $neverUse,
        'neverUseTotal' => 8,
        'journal' => $journal,
        'journalUr' => $journalUr,
        'company' => $company,
        'founder' => $founder,
        'meta' => $meta,
    ]);
})->name('home');

/*
|--------------------------------------------------------------------------
| Commerce — catalogue, cart, checkout
|--------------------------------------------------------------------------
|
| Appended by the commerce agent. `routes/web.php` is shared; append only.
|
| URL decisions and where they come from:
|   /shop, /shop/{category}     seo.md §2.2
|   /products/{slug}            architecture §7.1 (flat — a product belongs to
|                               several categories over its life, and nesting
|                               the category forces a 301 on every
|                               recategorisation). `/product/{slug}` from
|                               seo.md §2.2 is honoured as a 301 alias so the
|                               two documents cannot disagree in production.
|   /cart, /checkout            noindex + robots-Disallow (seo.md §2.2)
|
| Category slugs are flat (architecture §7.1): `/shop/serums`, never
| `/shop/skincare/serums`. Merchandisers reorganise trees constantly and a
| nested path turns a routine catalogue edit into a mass redirect event.
|
*/

Route::get('/shop', [\App\Http\Controllers\ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{category}', [\App\Http\Controllers\ShopController::class, 'category'])->name('shop.category');

Route::get('/products/{product}', [\App\Http\Controllers\ProductController::class, 'show'])->name('products.show');
// "Buy now" from a listing/homepage card → detached cart → checkout.
Route::post('/products/{product}/buy-now', \App\Http\Controllers\BuyNowController::class)->name('buy-now');
Route::permanentRedirect('/product/{slug}', '/products/{slug}');

Route::get('/cart', \App\Http\Controllers\CartController::class)->name('cart.index');
Route::get('/checkout', \App\Http\Controllers\CheckoutController::class)->name('checkout.index');
// The no-JS fallback for the checkout form. With JS on, Livewire intercepts
// the submit and this route never fires (see CheckoutController::store).
Route::post('/checkout', [\App\Http\Controllers\CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/order/{token}', \App\Http\Controllers\OrderConfirmationController::class)->name('orders.confirmation');

// Architecture §6.8 — one webhook route, forever. Adding Easypaisa means a
// driver class and a config entry; this line never changes.
Route::post('/payments/{driver}/callback', \App\Http\Controllers\PaymentCallbackController::class)
    ->name('payments.callback')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);

/*
|--------------------------------------------------------------------------
| Content pages
|--------------------------------------------------------------------------
|
| The editorial half of the site: the exclusion list, the ingredient index, the
| Journal, and the two pages a cautious buyer checks before ordering anything.
|
| URL decisions, and why they differ from the older labels still used in the
| header component:
|
|   /halal-ingredients   canonical, per SEO §2.2. A separate tree from /blog
|                        because these are evergreen reference documents, not
|                        dated posts — different template, different schema,
|                        its own sitemap. `/ingredient-index` 301s here.
|   /blog                canonical, per SEO §2.2. `/journal` 301s here; the
|                        word "Journal" stays as the visible label everywhere.
|
| Both aliases are permanent redirects rather than duplicate routes, so there is
| exactly one indexable URL per document and no chain (§2.1).
|
| Everything below is a plain server-rendered controller. Nothing here may be
| converted to a lazy island, a `wire:text` binding or an append-based infinite
| scroll: this content is the site's entire organic acquisition strategy and it
| has to exist in the initial HTML (SEO §8).
|
*/

Route::get('/what-we-never-use', \App\Http\Controllers\ExclusionController::class)
    ->name('exclusions');

Route::get('/halal-ingredients', [\App\Http\Controllers\IngredientController::class, 'index'])
    ->name('ingredients.index');
Route::get('/halal-ingredients/{slug}', [\App\Http\Controllers\IngredientController::class, 'show'])
    ->name('ingredients.show');

Route::get('/blog', [\App\Http\Controllers\BlogController::class, 'index'])
    ->name('blog.index');
// `category` is a reserved first segment so a post slug can never collide with
// a category slug (SEO §2.2). It must stay declared BEFORE /blog/{slug}.
Route::get('/blog/category/{slug}', [\App\Http\Controllers\BlogController::class, 'category'])
    ->name('blog.category');
Route::get('/blog/{slug}', [\App\Http\Controllers\BlogController::class, 'show'])
    ->name('blog.show');

Route::get('/about', \App\Http\Controllers\AboutController::class)->name('about');

// Newsletter subscribe — posted from the homepage newsletter band. Throttled
// like the contact form; writes to the existing newsletter_subscribers table.
Route::post('/newsletter', [\App\Http\Controllers\NewsletterController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('newsletter.subscribe');

/*
| Customer authentication — OPTIONAL "Sign in with Google" (Socialite).
| Never forced; guest Cash-on-Delivery checkout still works with no account.
| Declared above the /{slug} catch-all so /login resolves to a real page.
*/
Route::get('/login', fn () => view('auth.login'))->name('login');
Route::get('/auth/google/redirect', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'redirect'])
    ->name('auth.google');
Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'callback'])
    ->name('auth.google.callback');
// Google One Tap posts the ID token here (verified server-side). Throttled.
Route::post('/auth/google/one-tap', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'oneTap'])
    ->middleware('throttle:10,1')
    ->name('auth.google.onetap');
Route::post('/logout', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'logout'])->name('logout');

// Signed-in customer account area (order history + email preferences).
Route::middleware('auth')->group(function () {
    Route::get('/account', [\App\Http\Controllers\AccountController::class, 'index'])->name('account');
    Route::post('/account/marketing', [\App\Http\Controllers\AccountController::class, 'updateMarketing'])
        ->name('account.marketing');
});

Route::get('/contact', [\App\Http\Controllers\ContactController::class, 'show'])->name('contact');
// Throttled rather than CAPTCHA'd: a CAPTCHA costs INP and conversions on the
// low-end Android devices this store actually sells to (§6.2).
Route::post('/contact', [\App\Http\Controllers\ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.submit');
Route::get('/contact/thank-you', [\App\Http\Controllers\ContactController::class, 'thankYou'])
    ->name('contact.thank-you');

// Legacy / navigation aliases. One hop each, never chained.
Route::permanentRedirect('/ingredient-index', '/halal-ingredients');
Route::permanentRedirect('/ingredient-index/{slug}', '/halal-ingredients/{slug}');
Route::permanentRedirect('/journal', '/blog');
Route::permanentRedirect('/journal/{slug}', '/blog/{slug}');

/*
|--------------------------------------------------------------------------
| Roman-Urdu Journal — /ur-roman/*  (bilingual, anti-cannibalization)
|--------------------------------------------------------------------------
|
| English lives at the root and is UNTOUCHED above. This mirror serves the
| SAME BlogController against the 'ur-Latn' content locale, pinned by the
| SetLocale middleware from the /ur-roman prefix.
|
| These are ALTERNATES of the English pages, never duplicates: each version
| carries its own self-referencing canonical and the two reference each other
| through a reciprocal hreflang cluster (see BlogController::show). Roman Urdu
| is Latin script, so these pages are LTR.
|
| Declared ABOVE the /{slug} catch-all. `blog.category.ur` is declared BEFORE
| `blog.show.ur` for the same reason as the English routes: a post slug must
| never be captured by the category route. The catch-all is NOT moved.
*/
Route::prefix('ur-roman')
    ->middleware(\App\Http\Middleware\SetLocale::class)
    ->group(function () {
        Route::get('/blog', [\App\Http\Controllers\BlogController::class, 'index'])
            ->name('blog.index.ur');
        Route::get('/blog/category/{slug}', [\App\Http\Controllers\BlogController::class, 'category'])
            ->name('blog.category.ur');
        Route::get('/blog/{slug}', [\App\Http\Controllers\BlogController::class, 'show'])
            ->name('blog.show.ur');
    });

/*
|--------------------------------------------------------------------------
| XML sitemap — indexation prerequisite (SEO §7.5)
|--------------------------------------------------------------------------
|
| One sitemap for the whole storefront. Blog posts appear in BOTH locales,
| each carrying xhtml:link alternates that mirror the on-page hreflang cluster.
| Declared ABOVE the /{slug} catch-all so /sitemap.xml resolves to real XML and
| is never swallowed as a CMS page slug.
*/
Route::get('/sitemap.xml', \App\Http\Controllers\SitemapController::class)->name('sitemap');

// Machine-readable fact sheet for AI answer engines — generated dynamically
// from the live catalogue + blog, so every new product/post appears here
// automatically. Same reason as the sitemap for sitting above the catch-all.
Route::get('/llms.txt', \App\Http\Controllers\LlmsTxtController::class)->name('llms');

/*
|--------------------------------------------------------------------------
| Product feeds — Shopping / catalogue / carousel ads
|--------------------------------------------------------------------------
|
| Fetched DIRECTLY by Google Merchant Center and Meta Commerce (not crawled —
| they are Disallow'd in robots.txt). One offer per active variant of every
| published product, the variant SKU as the stable id. Declared ABOVE the
| /{slug} catch-all so `/feed/*` resolves to real feeds, never a CMS page slug.
*/
Route::get('/feed/google.xml', [\App\Http\Controllers\FeedController::class, 'google'])->name('feed.google');
Route::get('/feed/meta.csv', [\App\Http\Controllers\FeedController::class, 'meta'])->name('feed.meta');

/*
|--------------------------------------------------------------------------
| CMS pages — MUST REMAIN LAST
|--------------------------------------------------------------------------
|
| Catch-all for admin-authored pages: /privacy, /terms, /shipping-returns,
| /faq and anything else created in the Pages resource. It matches a single
| unclaimed segment, so every route declared above wins first. Adding routes
| BELOW this line makes them unreachable.
|
| Unpublished pages 404 — see PageController.
*/
Route::get('/{slug}', \App\Http\Controllers\PageController::class)
    ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')
    ->name('page.show');
