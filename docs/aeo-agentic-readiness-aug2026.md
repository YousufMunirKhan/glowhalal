# Glow Halal — AEO & Agentic Readiness Audit

**Date:** 15 August 2026 · **Site:** https://glowhalal.com · **Repo:** `C:\laragon\www\glowhalal`
**Auditor scope:** answer-engine optimization (wave 2: getting *cited*) and agentic task
completion (wave 3: agents *doing things*). No application code was modified.

> **Method.** Every claim below is backed by a live fetch of production on 15 Aug 2026
> (raw `curl`, not a rendering browser) or by a file+line reference in the repo.
> Where something could not be verified, it says so explicitly. Quoted HTML/JSON is verbatim.
>
> **Note on the previous report.** `docs/seo-aeo-status-report.md` (11 Aug) is still
> broadly accurate. Two things have changed since: the sitemap grew 31 → **36 URLs**,
> and the site now emits **`aggregateRating`**, which that report said it did not. See §1.C.

---

## 0. Headline

The site is in the **top few percent of Pakistani small e-commerce for wave-2 (citation)
readiness** and is **structurally unable to be transacted on by an agent that does not run
JavaScript** — but that second sentence matters far less than it sounds, and §3 explains why.

| Wave | What it means | Grade |
|---|---|---|
| 1 — Search rankings | Classic SEO: crawl, index, rank | **A−** (already audited, holding) |
| 2 — AI citation | ChatGPT/Perplexity/Gemini *naming and quoting* the site | **B+** content, **D** entity |
| 3 — Agentic completion | An AI agent finishing a purchase | **C** (browsing agents), **F** (no-JS clients) |

**The single biggest constraint is not technical.** The site's crawlable, quotable substance
is genuinely strong. What is missing is **off-site corroboration** — an AI assistant will not
name a brand it can only find on that brand's own domain. That is §4, and it is where the
next 30 days of effort belong.

**Three findings in §5 will be unwelcome, and are load-bearing:**

1. The WebMCP syntax circulating online — `data-mcp-action`, `navigator.mcpActions.register()` —
   is **fabricated**. The real API is `document.modelContext.registerTool()` with bare
   `toolname`/`tooldescription` attributes. Nothing consumes it yet. **Do not implement.** (§5.2)
2. **97% of published `llms.txt` files receive zero requests** (Ahrefs, 137,210 domains), and no
   provider has confirmed reading one. Keep the file — it is free — but it is not an AEO win. (§5.3)
3. Adding schema markup produced **no measurable AI-citation lift** in the only controlled test.
   Keep the existing schema for Google merchant listings, but stop treating it as an AEO lever. (§5.4)

**And size the whole bet honestly: only ~15% of Pakistani adults have ever used an AI chatbot,
and ChatGPT Shopping is still US-only.** AEO here is a cheap forward position for 2027–28, not
a 2026 revenue channel. Classic SEO and WhatsApp/COD conversion remain the engine this year.
Fortunately almost everything recommended below serves both. (§5.7)

---

## 1. Scorecard

Rated **A–F** with live evidence.

### A. Crawlability & indexation — **A**

`robots.txt` (verbatim, live):

```
User-agent: *
Disallow: /cart
Disallow: /checkout
Disallow: /account
Disallow: /login
Disallow: /search
Disallow: /feed
Allow: /feed/google.xml
Allow: /feed/meta.csv

Sitemap: https://glowhalal.com/sitemap.xml
```

- Master switch is ON, only private paths blocked. Correct.
- `sitemap.xml` → **36 `<loc>` entries** (was 31 on 11 Aug — the daily drip is working).
- **hreflang reciprocity verified live** on real EN↔UR pairs:

  `/blog/sidr-leaves-benefits-skin-hair` emits
  ```html
  <link rel="alternate" hreflang="en" href="https://glowhalal.com/blog/sidr-leaves-benefits-skin-hair">
  <link rel="alternate" hreflang="ur-Latn" href="https://glowhalal.com/ur-roman/blog/beri-ke-patte-ke-fayde">
  <link rel="alternate" hreflang="x-default" href="https://glowhalal.com/blog/sidr-leaves-benefits-skin-hair">
  ```
  and `/ur-roman/blog/beri-ke-patte-ke-fayde` mirrors it exactly. Reciprocal. ✅
  EN posts with no Urdu twin correctly emit **no** alternates rather than a broken cluster.
- `<html lang="ur-Latn" dir="ltr">` on the Roman-Urdu tree — correct (Latin script is LTR).
- **IndexNow verified live.** Key derived from `app/Console/Commands/IndexNowPing.php:29` and
  fetched: `https://glowhalal.com/b5c6859719cee7ed4120d2111026e270.txt` → **HTTP 200**,
  body matches the key. Daily ping scheduled at `routes/console.php:45`. ✅

**Gap:** `robots.txt` does not advertise `/llms.txt`, and no `rel="alternate"` RSS/Atom feed
exists (`/feed.xml`, `/rss.xml`, `/blog/feed` all → **404**). See §3.

### B. No-JS content rendering (citation readiness) — **A**

This was the biggest open question in the brief, and the answer is **better than expected**.
Livewire server-renders its initial HTML, so a crawler that executes zero JavaScript still
receives the full document. Verified by stripping `<script>`/`<style>` from raw `curl` output:

| Fact | PDP | /shop | Home |
|---|---|---|---|
| `Rs 1,200` (50 ml price) | ✅ | ✅ | ✅ |
| `Rs 2,200` (100 ml price) | ✅ | ✅ | ✅ |
| `In stock` | ✅ | — | — |
| `Sesamum` / `Commiphora` (INCI) | ✅ | — | — |
| `guggul` | ✅ | ✅ | ✅ |
| `Cash on Delivery` | ✅ | ✅ | ✅ |
| `Rs 300` shipping | ✅ | — | ✅ |

Extracted PDP text is **11,548 characters** of real prose with no JS executed. Product name,
both prices, stock state, full INCI list, shipping cost and the reseller disclosure
("By M.U. Amrelia (India)") are all present. **This is the thing most Livewire/React stores
get wrong, and Glow Halal gets right.** No action needed.

### C. Structured data — **B+** (one live risk)

PDP `@graph` emits 22 typed nodes: `OnlineStore`, `WebSite`, `WebPage`, `BreadcrumbList`,
`Product`, `Offer`, `OfferShippingDetails`, `ShippingDeliveryTime`, `MerchantReturnPolicy`,
2× `UnitPriceSpecification`, `FAQPage` with **11** `Question`/`Answer` pairs.

The `Offer` node is genuinely excellent — this is the verbatim live JSON:

```json
"shippingDetails": {
  "@type": "OfferShippingDetails",
  "shippingRate": {"@type": "MonetaryAmount", "value": "300.00", "currency": "PKR"},
  "shippingDestination": {"@type": "DefinedRegion", "addressCountry": "PK"},
  "deliveryTime": {
    "@type": "ShippingDeliveryTime",
    "handlingTime": {"@type": "QuantitativeValue", "minValue": 0, "maxValue": 1, "unitCode": "DAY"},
    "transitTime": {"@type": "QuantitativeValue", "minValue": 2, "maxValue": 7, "unitCode": "DAY"}
  }
}
```

Honesty rules are **held in code**, not just in policy — `SchemaGraph.php:135-139` refuses to
emit `manufacturer`/`countryOfOrigin`, and `:150-159` refuses to invent a GTIN. Good.

#### ⚠️ Live finding: `aggregateRating` is now being emitted

```json
"aggregateRating": {
  "@type": "AggregateRating",
  "ratingValue": "4.80",
  "reviewCount": 5,
  "bestRating": "5",
  "worstRating": "1"
}
```

The audit brief states "**no aggregateRating / no fabricated reviews — there are none yet**".
That constraint is now **out of date**, not violated. Five reviews were added after the
11 Aug report via `database/seeders/OwnerReviewSeeder.php`, documented there as
"*Real customer reviews … collected by the owner over WhatsApp after Cash-on-Delivery orders
and provided verbatim*". The gate at `SchemaGraph.php:252` only fires when
`reviews_count > 0`, and the reviews **are visibly rendered on the PDP** (verified: the
author name "Maira Yousuf" appears in the live HTML). There is also a `/reviews` policy page
explaining the collection rule. So the markup is defensible.

**But two real risks remain, and the seeder's own author flagged the first one:**

1. **Efficacy claims inside review bodies.** Three of the five say a burn mark disappeared
   (e.g. *"jalne ke daag chale gaye"*). These are customers' words, but they sit on a
   Merchant-Center-connected landing page. `OwnerReviewSeeder.php:21-25` already warns this
   is a catalogue-suspension risk. **This is the highest-severity compliance item in the
   audit** and it is a business decision, not a code fix.
2. **No provenance trail.** `order_id` is `null` for all five (`:59`), so nothing links them
   to an order row. That is *honest* (no "Verified purchase" badge is shown), but it means
   the rating cannot be substantiated if challenged.

**Recommendation: keep the reviews visible, but consider removing `aggregateRating` from the
schema until reviews are order-linked** — the rich-result upside on a 5-review base is
negligible, and the downside is a catalogue suspension. This is a judgement call for the owner.

**Schema gaps:** no `Review` nodes (only the aggregate), no `FAQPage` on blog posts (they
carry `BlogPosting` only), no `sameAs` (see §1.E), no `Organization.founder`.

### D. Answer-engine assets — **B**

- `/llms.txt` → **HTTP 200**, `text/plain; charset=utf-8`, **3,854 bytes**, dynamically built
  from live catalogue + blog (`app/Http/Controllers/LlmsTxtController.php`). Prices are read
  from the same rows the pages render, so it cannot drift. Opening line is a genuinely good
  entity summary and includes the honest disclaimers:
  > "*It is a cosmetic, not a medicine, and Glow Halal does not claim third-party halal
  > certification.*"
- **Answer boxes are real.** Blog posts open with a direct 40–55-word answer before the first
  H2 — verified on `/blog/lookman-e-hayat-oil-uses-benefits-price`:
  > "*Lookman-e-Hayat oil (also spelled Luqman-e-Hayat) is a traditional herbal oil made from
  > a sesame (til) oil base infused with guggul resin… It is a herbal comfort oil for
  > external use — not a medicine.*"

  That is exactly the shape an answer engine lifts.
- `/faq` and `/shipping-returns` are clean, server-rendered, and answer the transactional
  questions in plain sentences.

**Gaps:** `/llms.txt` omits delivery *time* (it has the Rs 300 charge but not the 7-day
window), omits the return policy, omits the two ingredient-index and exclusion pages'
actual content, and is not linked from `robots.txt` or `<head>`. See §3.

### E. Entity signals — **D** ← *the real bottleneck*

The `OnlineStore` node has these keys, live:

```
['@type','@id','name','url','description','areaServed','currenciesAccepted',
 'email','telephone','address','alternateName','contactPoint','knowsAbout']
```

**`sameAs` is absent.** It is *implemented* — `app/Support/JsonLd.php:85-89` builds it from
`instagram_url`, `facebook_url`, `tiktok_url`, `youtube_url` — but every one of those
`StoreSettings` fields is empty, so `array_filter` drops the key entirely.

`sameAs` is the single most important machine-readable "this entity is real and exists
elsewhere" signal. Right now Glow Halal exists, to a language model, **only on its own
domain**. There is no Wikidata item, no social profile, no directory listing, no third-party
mention. An assistant asked "best halal face oil in Pakistan" has no corroborating source
that would let it name this brand with confidence. **This is why §4 is the most valuable
section of this document.**

`knowsAbout` is good and specific:
`['halal cosmetics','herbal oils','Lookman-e-Hayat oil','champi (hair oiling)','sesame (til) oil skincare','cosmetic ingredient sourcing','INCI nomenclature','cash on delivery shopping in Pakistan']`

`contactPoint` is correct: `{"contactType":"sales","telephone":"+923012973886","availableLanguage":["en","ur"],"areaServed":"PK"}`

### F. Agentic task completion — **C / F** (see §2 and §3)

### G. Agentic declaration (WebMCP / `agents.json`) — **N/A**

Verified 404 on all three: `/agents.json`, `/mcp-actions.json`, `/.well-known/agents.json`.
**This is currently the correct state.** See §3.7 — I am recommending *against* implementing
these right now, and the reasoning is in §5.

### H. Feeds & syndication — **B**

`/feed/google.xml` → **200**. `/feed/meta.csv` → **200** (per prior report). Both correctly
`Disallow`ed-but-`Allow`ed in robots. No blog RSS/Atom feed exists (404 on all common paths).

### I. Cross-surface fact consistency — **B−**

| Fact | Where stated | Value |
|---|---|---|
| Shipping charge | schema, `/faq`, `/shipping-returns`, home, PDP, llms.txt | **Rs 300 flat, free > Rs 5,000** — consistent everywhere ✅ |
| Delivery time | `/shipping-returns`, `/faq` | "within 7 working days" |
| Delivery time | PDP `Offer` schema | handling 0–1 d **+** transit 2–7 d = up to 8 d |
| Delivery time | **PDP visible copy** | **absent** — PDP states cost but never a timeframe |
| Delivery time | **`/llms.txt`** | **absent** |
| Return window | `/shipping-returns`, `/faq`, schema | **7 days** — consistent ✅ |
| Payment | `/faq`, `/shipping-returns`, drivers | **COD + bank transfer** — consistent ✅ |

The 7-vs-8-day drift is trivial. The real gap is that **the two surfaces an AI is most likely
to read (the product page and `/llms.txt`) are the two that never state a delivery time at
all.** Cheap, high-value fix in §3.

---

## 2. Task-completion table

Rated for two distinct client types, because conflating them is the most common error in this
kind of audit:

- **Crawler / no-JS client** — `OAI-SearchBot`, `PerplexityBot`, `ClaudeBot`, plain HTTP
  fetchers. These *do not execute JavaScript*. They read, they never click.
- **Browsing agent** — Claude in Chrome, ChatGPT agent mode, Perplexity Comet. These drive a
  **real browser and do execute JavaScript**, so `wire:click` works for them.

| # | Task | No-JS client | Browsing agent | Breaks at |
|---|---|---|---|---|
| 1 | Find product for a need; read real price + sizes | ✅ **Works** | ✅ **Works** | — |
| 2 | Add to cart / reach checkout | ⚠️ **Partial** | ✅ **Works** | `resources/views/livewire/cart/add-to-cart.blade.php:113` |
| 3 | Complete checkout | ❌ **Broken** | ✅ **Works** | `resources/views/livewire/checkout.blade.php:62` |
| 4 | Look up shipping cost & delivery time | ⚠️ **Partial** | ⚠️ **Partial** | cost ✅ everywhere; time absent from PDP + llms.txt |
| 5 | Look up return policy | ✅ **Works** | ✅ **Works** | — |
| 6 | Contact the business | ⚠️ **Partial** | ⚠️ **Partial** | `app/Http/Controllers/ContactController.php:95` and `:103` |

### Task 1 — Find product, read price and sizes ✅

`/shop` and both PDPs are fully server-rendered. The two sizes are **separate products with
separate URLs** (`herbal-skin-oil-50ml`, `herbal-skin-oil-100ml`), each with its own
`Offer` and SKU (`GH-OIL-50`, `GH-OIL-100`) — which is *better* for agents than a JS variant
switcher, because each size has a stable, linkable, individually-priced URL. Nothing to fix.

### Task 2 — Add to cart ⚠️

There are **three different add-to-cart paths and they do not have equal agent support**:

| Surface | Mechanism | Works without JS? |
|---|---|---|
| **Homepage** | `<form method="POST" action="/products/{slug}/buy-now">` | ✅ **Yes** |
| **`/shop`** | no purchase control at all in HTML | ❌ No |
| **PDP** | `<button type="button" wire:click="add">` | ❌ No |

Live HTML from the homepage — a genuine, agent-completable POST form:
```html
<form method="POST" action="https://glowhalal.com/products/herbal-skin-oil-50ml/buy-now">
```
It is backed by `app/Http/Controllers/BuyNowController.php`, a plain controller that creates a
detached cart and redirects to `/checkout?direct=1`. **This is the pattern that should exist
on the PDP and the shop cards too, and the code to serve it already exists.**

The PDP, by contrast, offers only:
```html
<button type="button" wire:click="add"  …>Add to bag</button>
<button type="button" wire:click="buyNow" …>Buy now</button>
```
`add-to-cart.blade.php:111-112` documents the choice deliberately ("*Add to bag is a
`<button>`, never an `<a href>`. An anchor that mutates…*") — the reasoning is sound for
accessibility, but a `<form method="POST">` satisfies both concerns and is what the homepage
already does.

### Task 3 — Complete checkout ❌ (no-JS)

`resources/views/livewire/checkout.blade.php:62`:
```html
<form wire:submit="placeOrder" class="mt-10 grid gap-12 …">
```

Two independent reasons this cannot be completed without JavaScript:

1. **No `action` and no `method`.** Without Livewire, a submit is a GET to the current URL.
2. **The inputs carry no `name` attributes.** They bind via `wire:model` only —
   `:69 customerName`, `:77 phone`, `:86 email`, `:106 province`, `:118 city`. Even if the
   form did POST, **zero field data would be transmitted.** (The one exception is the payment
   radio at `:143`, which does have `name="paymentMethod"`.)

For completeness, the checkout itself is well-designed for humans: six fields, no account
required, no CNIC, no postal code, COD pre-selected (`app/Livewire/Checkout.php:25-37`).
Payment drivers available: `CashOnDeliveryDriver`, `BankTransferDriver`. `/checkout` with an
empty cart correctly `302`s to `/cart`.

**Severity judgement: LOW-to-MEDIUM, not critical.** No AI agent that can actually transact
today operates without JavaScript. Crawlers do not buy things. See §5 for why this is ranked
below the entity work.

### Task 4 — Shipping cost & delivery time ⚠️

Cost is answered everywhere and consistently. **Delivery time is absent from the product page
and absent from `/llms.txt`** — it exists only on `/shipping-returns`, `/faq`, and inside the
`Offer` schema. An assistant asked *"how fast does Glow Halal deliver to Karachi?"* has to
find and read a third page, or read JSON-LD it may not parse. Fixable in ten minutes (§3.3).

### Task 5 — Return policy ✅

`/shipping-returns` is server-rendered, plain-language, and mirrored in `MerchantReturnPolicy`
schema. `/faq` restates it. An agent can answer this from any of three surfaces. Good.

### Task 6 — Contact the business ⚠️

The contact form is, structurally, **the best agent-completable form on the site** — real
POST, real `name` attributes, real `<label for>`, a real `<select>` with enumerated values,
`type="submit"`:

```html
<form method="POST" action="https://glowhalal.com/contact" novalidate>
  <input type="hidden" name="_token" value="…">
  <input id="name" name="name" type="text" autocomplete="name" required>
  <input id="email" name="email" type="email" autocomplete="email" required>
  <select id="subject" name="subject" required> … </select>
  <textarea id="message" name="message" rows="5" required></textarea>
  <button type="submit">Send message</button>
```

**But it has two anti-bot traps that will also catch a legitimate agent acting for a user:**

1. **Honeypot, `ContactController.php:95`** — if the hidden `website` field is filled, the
   controller returns `303` to the thank-you page and **silently discards the message**. The
   user and the agent both see success; the business never receives it. Mitigations already
   in place are good: the wrapper is `aria-hidden="true"` and off-screen, so
   accessibility-tree agents (Claude in Chrome) and vision agents will not see it; and the
   visible label literally reads *"Do not fill this in"*, which an LLM will obey. **Risk is
   limited to naive raw-HTML form-fillers — but the failure mode is silent, which is the
   worst kind.**
2. **3-second form-age check, `ContactController.php:103`** — an agent that fills four fields
   programmatically and submits in under three seconds is rejected. This one **will** fire
   routinely. The agent does see a real error message and could retry, so it is recoverable.

WhatsApp is well covered: `wa.me/923012973886` appears on `/faq`, `/contact`, the header, the
footer and in `/llms.txt`. `+92 301 2973886` is in the `ContactPoint` schema. An agent can
always hand the user a working WhatsApp link. ✅

---

## 3. Gap list, ranked by impact

Each item: what, why, exact file, concrete sketch. **Nothing here has been applied.**

### 3.1 — Publish and fill the social/entity profiles → `sameAs` · **IMPACT: HIGHEST**

The code is already written and waiting. `app/Support/JsonLd.php:85-89`:
```php
$sameAs = array_values(array_filter([
    $store->instagram_url ?? null,
    $store->facebook_url ?? null,
    $store->tiktok_url ?? null,
    $store->youtube_url ?? null,
```
All four `StoreSettings` fields are empty, so the key vanishes from the graph.

**Fix: no code at all — create the profiles, then paste the URLs into Admin → Store settings.**
Suggest extending the settings to also accept a Google Business Profile URL and a Wikidata QID:

```php
// app/Settings/StoreSettings.php — additive
public ?string $google_business_url;
public ?string $wikidata_url;
```
```php
// app/Support/JsonLd.php — add to the same array_filter
$store->google_business_url ?? null,
$store->wikidata_url ?? null,
```

Do this **first**. Everything in §4 depends on these profiles existing.

### 3.2 — Give the PDP and shop cards a real POST purchase form · **IMPACT: HIGH**

Closes Task 2 for every client type, using a controller that **already exists**
(`BuyNowController`), and costs nothing in UX because the Livewire button stays primary.

`resources/views/livewire/cart/add-to-cart.blade.php` — wrap the existing buttons so the
no-JS path is a real form, and let Livewire intercept when it is available:

```blade
{{-- Progressive enhancement: real POST for agents and no-JS clients.
     Livewire's wire:click still wins when JS is running. --}}
<form method="POST"
      action="{{ route('buy-now', $product->slug) }}"
      class="flex flex-col gap-3">
    @csrf
    <input type="hidden" name="variant" value="{{ $selectedVariantId }}">
    <input type="hidden" name="quantity" value="{{ $quantity }}">

    <button type="submit" wire:click.prevent="add" wire:target="add" …>
        <span wire:loading.remove wire:target="add">Add to bag</span>
        <span wire:loading wire:target="add">Adding…</span>
    </button>

    <button type="submit" formaction="{{ route('buy-now', $product->slug) }}"
            wire:click.prevent="buyNow" wire:target="buyNow" …>Buy now</button>
</form>
```

`BuyNowController::__invoke()` would need to honour the posted `variant`/`quantity` rather
than always taking `defaultVariant` and `1` (`BuyNowController.php:21,40`). Add the same
control to the `/shop` product cards, which today expose no purchase path in HTML at all.

### 3.3 — Put delivery time on the PDP and in `/llms.txt` · **IMPACT: HIGH, COST: ~15 min**

The most valuable-per-minute fix in this document. `LlmsTxtController.php:56` — extend the
Key-facts block so the two facts an AI is asked for most often sit in the file it reads first:

```php
$lines[] = '- Delivery: Cash on Delivery nationwide (Pakistan); Rs 300 flat, '
    .'free on orders above Rs 5,000. Normally delivered within 7 working days; '
    .'major cities (Karachi, Lahore, Islamabad, Rawalpindi, Faisalabad) are usually faster.';
$lines[] = '- Payment: Cash on Delivery or bank transfer. No account needed to order.';
$lines[] = '- Returns: unopened items in sealed packaging within 7 days of delivery; '
    .'damaged, defective or incorrect items replaced or refunded — https://glowhalal.com/shipping-returns';
$lines[] = '- Halal position: no third-party halal certificate is held or claimed. '
    .'The full INCI list is published on every product page.';
```

And add one visible line to the PDP buy box beside the existing "Rs 300 nationwide" text:
> Delivered in about 2–7 working days. Cash on Delivery.

Keep the wording numerically aligned with the `transitTime` already in the `Offer` node.

### 3.4 — Make `/llms.txt` discoverable · **IMPACT: NEAR-ZERO, COST: ~5 min**

> **Downgraded after research (§5.3).** 97% of published `llms.txt` files receive zero requests,
> and AI bots do not probe for the file on domains that lack it. Do this only because it costs
> five minutes; expect nothing from it.

It is currently reachable only by guessing the path. Two one-line additions:

`public/robots.txt`:
```
# Machine-readable fact sheet for AI answer engines
Llms: https://glowhalal.com/llms.txt
```
`resources/views/components/layouts/app.blade.php`, in `<head>`:
```blade
<link rel="llms" type="text/plain" href="{{ route('llms') }}">
```

**Honest caveat:** neither `Llms:` in robots nor `rel="llms"` is a ratified standard, and no
crawler is documented as consuming them. They are zero-risk and zero-cost, which is the only
reason to do them. Do not expect measurable return. See §5.

### 3.5 — Add `FAQPage` to blog posts · **IMPACT: LOW (downgraded)**

> **Downgraded after research (§5.4).** Adding JSON-LD produced no measurable AI-citation lift
> in the only controlled experiment, and the widely-quoted "FAQ schema → 40% higher citation
> weighting" claim has no study behind it. This is still worth doing for Google rich-result
> eligibility, but it is **not** an AEO lever. If time is short, write the missing pages in §6
> instead — visible prose is what the engines actually read.

Blog posts are the AEO workhorses but emit only `BlogPosting`. The PDP already proves the
pattern works — `SchemaGraph::faqPage()` exists and the PDP ships 11 Q&A pairs through it.
Posts already contain H2-shaped questions ("What is Lookman-e-Hayat oil?", "What is it used
for?"), so the entities are visible on-page, which is what keeps this inside guidelines.

In `BlogController::show()`, where the graph is built:
```php
$graph->faqPage(
    route('blog.show', $post->slug),
    $post->faqs ?? [],   // add a repeater to the Filament BlogPost resource
);
```
Populate from a Filament repeater so the owner controls it, and only emit Q&As that are
actually rendered in the post body.

### 3.6 — Add a blog RSS/Atom feed · **IMPACT: MEDIUM-LOW**

`/feed.xml`, `/rss.xml`, `/blog/feed` all 404. RSS remains a real ingestion path for
aggregators and several AI-content pipelines, and it costs one controller.

```php
// routes/web.php — above the /{slug} catch-all
Route::get('/feed.xml', [\App\Http\Controllers\FeedController::class, 'rss'])->name('feed.rss');
```
```blade
{{-- layouts/app.blade.php <head> --}}
<link rel="alternate" type="application/rss+xml"
      title="Glow Halal — Journal" href="{{ route('feed.rss') }}">
```
Include both locales, each `<item>` carrying `<link>`, `<pubDate>`, `<description>` = the
post's answer-box paragraph.

### 3.7 — WebMCP and `agents.json`: **do not implement yet** · **IMPACT: NONE TODAY**

`/agents.json`, `/mcp-actions.json` and `/.well-known/agents.json` all return 404, and I am
**recommending that stays true for now.** Full reasoning in §5.2 and §5.5. The short version:

- WebMCP is a **Community Group draft, not a W3C standard**, live only in a Chrome origin trial
  (149→156, token required); Edge is flag-only; Firefox and Safari have no commitment.
- **No mainstream agent consumes WebMCP tools today** — Claude, ChatGPT Agent, Perplexity and
  Gemini all still drive pages via the DOM. Adoption outside demos is approximately zero.
- The API **renamed three times in eight months** (`window.agent` → `navigator.modelContext` →
  `document.modelContext`, the last on 21 July 2026). Anything hard-coded now would be stale by
  the time an agent could read it.
- The syntax most commonly published for this — `data-mcp-action`, `navigator.mcpActions` — is
  **fabricated and would have been silently inert**. See §5.2 for the real attribute names.
- Agentic *checkout* protocols (ACP, UCP, AP2) are unusable from Pakistan regardless: Stripe and
  PayPal do not serve Pakistani merchants, no local PSP has integrated any of them, and **none
  of the protocols model Cash on Delivery**. See §5.5.

**The correct preparation for WebMCP is not markup — it is §3.2.** A site whose actions are
real, named, semantic HTML forms with labelled inputs is a site that can be annotated later in
an afternoon. A site whose only purchase control is `wire:click` cannot be, regardless of
which spec wins. **Fix the forms; the attributes are a five-minute retrofit whenever the spec
settles.**

### 3.8 — Decide the `aggregateRating` question · **IMPACT: RISK REDUCTION**

See §1.C. Not a code gap — a business decision. Two sub-items:

1. Review the three burn-mark testimonials against Merchant Center policy. They can be hidden
   from Admin → Reviews without touching `OwnerReviewSeeder.php`, per its own note at `:25`.
2. Consider gating `SchemaGraph.php:252` on order-linked reviews:
   ```php
   if ((int) $product->verified_reviews_count > 0 && …) {
   ```
   so the aggregate only ships once reviews are substantiated.

### 3.9 — Harden article HTML against naive text extractors · **IMPACT: LOW**

The blog prose wrapper uses Tailwind arbitrary variants containing literal `>` characters
inside the `class` attribute (`[&>h2]:mt-10`, `[&>ul]:list-disc`, …). The HTML is
**well-formed** and any spec-compliant parser handles it correctly — Google and OpenAI will be
fine. But regex-based `<[^>]+>` strippers, which are still common in lightweight LLM ingestion
pipelines, terminate the tag early and inject ~700 characters of CSS into the extracted
article text, immediately after the answer box. I reproduced this.

Cheap defensive fix — move the variants into a component class:
```css
/* resources/css/app.css */
@utility prose-gh {
  & > * + * { margin-top: 1rem; }
  & > h2 { margin-top: 2.5rem; @apply text-title-lg text-text-primary; }
  /* … */
}
```
```blade
<div class="prose-gh max-w-[var(--container-read)] text-body text-text-secondary">
```

### 3.10 — Relax the contact-form traps for legitimate agents · **IMPACT: LOW**

`ContactController.php:103` — three seconds is aggressive for a form an agent fills
instantly. Dropping to one second retains nearly all the spam benefit:
```php
if ($renderedAt > 0 && (time() - $renderedAt) < 1) {
```
And `:95` — the silent-discard is the worse problem, because nobody learns the message was
lost. Consider logging it so the owner can spot false positives:
```php
if (filled($request->input('website'))) {
    \Log::info('contact.honeypot', ['ip' => $request->ip(), 'email' => $request->input('email')]);
    return redirect()->to(route('contact.thank-you'), 303);
}
```

---

## 4. "Get named by AI" plan

**This is the section that matters most.** Everything in §1–§3 makes the site *quotable*.
None of it makes an assistant *choose* to name Glow Halal, because assistants corroborate
across sources and Glow Halal currently has exactly one source: itself.

The strategic asset is already identified in `docs/keyword-research-aug2026.md`:

> "**Roman Urdu SERPs in Pakistan are structurally empty.** … No major PK herbal competitor
> (Saeed Ghani, ChiltanPure, Hemani, Marhaba, Qarshi) publishes Roman Urdu content."

That is a genuine moat, and it applies with **more** force to AI answers than to blue links —
when a user asks an assistant a Roman-Urdu question, the model needs *some* Roman-Urdu source,
and there may be only one. Lean into it hard.

### 4.1 Entity foundation (week 1) — prerequisite for everything else

| Action | Why it moves AI answers |
|---|---|
| **Google Business Profile** — Saddar, Karachi 74400 (the `postalCode` already in schema) | The strongest local-entity signal available in Pakistan; feeds Google's Knowledge Graph, which Gemini and AI Overviews read directly |
| **Instagram + Facebook + TikTok + YouTube**, name matched exactly to `alternateName` "GlowHalal" | Populates `sameAs` (§3.1); gives assistants an independent existence check |
| **Wikidata item** — "Glow Halal", instance of *online retailer*, country Pakistan, with the GBP + site as references | Wikidata is disproportionately trusted by LLMs as an entity spine. A small retailer *can* qualify if it has independent references; do not create it before there is at least one press or directory mention to cite, or it will be deleted |
| **NAP consistency** — `+92 301 2973886` / `support@glowhalal.com` / Saddar, Karachi 74400, identical everywhere | Contradictory contact details are a strong negative confidence signal |

### 4.2 Off-site corroboration (weeks 2–6) — the actual lever

Ranked by **strength of evidence**, not by how often the tactic is recommended. Sample sizes
and caveats are given so the owner can judge for themselves.

**Lever 1 — Get listed on a third-party review platform. Largest measured effect.**
Seer Interactive, March 2026: 804,491 AI responses across 1,926 brands, 15,783 prompts, four
platforms. Brands with **just 1–13 Trustpilot reviews** showed a **53.5% citation rate versus
1% for brands with none**. Sharp diminishing returns after that (+25pp for the next tier, +6pp
after). *Caveat, stated by Seer: this is correlational — 99.5% of those citations arrived via
organic ranking, so some of the effect may be "sites with reviews also rank better."*

Even discounted heavily, this is the highest-leverage item available, and it has a second
benefit specific to this audit: **it moves review collection off-site, which directly reduces
the `aggregateRating` exposure in §1.C.** Off-site reviews on a neutral platform carry more
weight with an assistant than five self-hosted testimonials *and* carry none of the Merchant
Center risk. Set up Trustpilot (or Google Business Profile reviews, which double as the §4.1
entity signal) and ask existing WhatsApp customers to post there instead.

**Lever 2 — Get *included in* third-party listicles. Do not just publish your own.**
AirOps/Digital Applied, 863,000 search results: **listicles are 21.9% of all AI citations**, the
highest of any content format (articles 16.7%, product pages 13.7%) — **and 80.9% of cited
listicles are third-party, not brand-published.**

This is an important nuance against the obvious move. Publishing "best halal skincare brands in
Pakistan" (§6 query 22) is still worth doing — it targets a real query and is the kind of page
that gets cited — but **being named inside someone else's round-up is roughly four times more
likely to be the cited source.** Budget effort accordingly: outreach to PK beauty bloggers and
listicle publishers with a free 50 ml unit and **no editorial conditions**, because a
conditioned review reads as promotional and is worth less.

**Lever 3 — Build branded search volume and unlinked brand mentions.**
Ahrefs, 75,000 brands — correlation with AI Overview brand mentions:

| Factor | r |
|---|---|
| Branded web mentions | **0.664** ← strongest single factor |
| Branded anchor text | 0.527 |
| Branded search volume | 0.392 |
| Domain Rating | 0.326 |
| Referring domains | 0.295 |
| Backlinks | 0.218 |

Note the shape: **mentions beat links.** The traditional backlink-chasing playbook is the
*weakest* factor in the table. Ahrefs' own caveat — correlation is not causation and all
factors are "moderate to very weak." But the ranking is consistent across independent studies,
and it points somewhere cheap: get the words "Glow Halal" written on other people's pages, with
or without a link.

**Lever 4 — Rank in classic search. Still the substrate.**
seoClarity, 432,000 keywords: **97% of AI Overviews cite at least one page from the organic
top-20.** Seer found Google page-1 ranking correlates ~0.65 with LLM mention, while backlinks
were "weak or neutral." **Rank in the top 20 or be invisible** — which means the existing SEO
work is not superseded by AEO, it is the precondition for it.

**Lever 5 — Reddit and YouTube, with a realistic expectation.**
Reddit's reported citation share ranges from **0.1% to 60% across credible studies** depending
on engine, date window and prompt set — anyone quoting a single number without those three
qualifiers is oversimplifying. Semrush (230,000 prompts) caught ChatGPT's Reddit citation rate
collapsing from ~60% to ~10% within six weeks in late 2025. It is volatile and not controllable.

Critically: Profound found **99% of ChatGPT's Reddit citations point to individual discussion
threads**, not subreddits or brand profiles. **You cannot build a Reddit presence and be cited
for it** — organic, unprompted discussion gets cited. So the honest play is to *participate*
where relevant (`r/pakistan`, `r/IndianSkincareAddicts`, `r/SkincareAddiction`), **disclose that
you are the seller**, and answer with the genuinely honest version: the fresh-burn safety
warning, the absence of a halal certificate, the real INCI. That honesty is the wedge against
ChiltanPure-style claim-stuffing, and it is the content shape a model quotes.

YouTube remains a heavily cited source type and transcripts are indexed — a 60–90 second
Roman-Urdu video per top guide doubles as the `sameAs` YouTube entry from §4.1.

**Lever 6 — A corroborating marketplace listing (PriceOye / Daraz).**
Not for the sales — for the independent page an assistant can cross-reference for price and
existence. A brand that appears only on its own domain is unverifiable.

> **One genuine counterweight, in fairness.** Grow & Convert (n=120 prompts) found **86% of
> citations came from within-industry sources** and only 14% from Reddit-class general domains —
> though for mass-market B2C brands in the same dataset, general sites were ~60%. **Niche
> verticals get niche citations.** For halal herbal cosmetics in Pakistan, that argues for
> weighting Levers 1, 2 and 6 (industry and commerce sources) above Lever 5.

### 4.3 Content formats that get lifted verbatim

The site already does the first of these well. The rest are gaps.

- **Answer box** — already live, keep it. ✅
- **"Honest guide" framing** — `shilajit-side-effects-honest-guide` is the best-shaped asset
  on the site. When a model is asked a risk question, a page that leads with the risk is a
  safer citation than one that buries it. **Make this a template**, not a one-off.
- **Explicit comparison tables** — models lift tables. Missing today: a 50 ml vs 100 ml table
  (price per ml, who each is for). See §5, query 18.
- **"Who this is not for"** sections — near-uniquely quotable, almost nobody writes them, and
  they are compliant by construction.
- **Price + availability stated in prose**, not only in schema — "Rs 1,200 for 50 ml, Rs 2,200
  for 100 ml, Cash on Delivery across Pakistan" as a literal sentence on the PDP.
- **A dated "last checked" line** on price pages — freshness is a citation tiebreaker.

### 4.4 What NOT to do

- ❌ Do not create a Wikipedia article. It will be deleted for notability and the deletion
  discussion becomes a durable negative signal.
- ❌ Do not seed reviews anywhere, on-site or off. `/reviews` publicly commits to this and
  the commitment is itself a trust asset.
- ❌ Do not chase "AI-optimized content" vendors selling `llms.txt` packages, WebMCP markup, or
  a "Pakistani AI visibility score". All three fail verification — §5.3, §5.2 and §5.9. If an
  agency quotes a statistic from the §5.9 list, that is a reliable signal about the agency.
- ❌ Do not buy backlinks for this. Backlinks were the **weakest** factor (r=0.218) in the
  75,000-brand study; unlinked brand mentions were the strongest (r=0.664). Mentions, not links.
- ❌ Do not add a halal certification claim, in any surface, under any wording. The `/contact`
  page's flat "**No.** Glow Halal holds no third-party halal accreditation and does not claim
  one" is genuinely more persuasive to a careful model than a vague badge would be.

---

## 5. Standards reality check

> **This section is intentionally skeptical**, and it contradicts a lot of what is currently
> sold as "AEO/GEO best practice". Roughly 80% of published material on these topics is vendor
> content recycling unsourced numbers. Everything below is either something I verified against
> this site directly, or a primary source (a spec, a browser status page, or a study with
> disclosed methodology and a stated sample size). Anything I could not verify says so.

### 5.1 What I verified on this site

| Item | Verified state |
|---|---|
| `/llms.txt` | **200**, well-formed, dynamic |
| `/agents.json` · `/mcp-actions.json` · `/.well-known/agents.json` | **404** (all three) |
| IndexNow key file | **200**, matches `IndexNowPing.php:29` |
| `/feed/google.xml` | **200** |
| RSS/Atom | **404** on all common paths |

### 5.2 ⚠️ Correction: the WebMCP syntax in circulation is fabricated

**This is the most important technical correction in this document.**

The attribute `data-mcp-action` and the API `navigator.mcpActions.register()` are **widely
repeated online and do not exist in any draft of the specification.** They appear in AI-generated
"WebMCP implementation guides" and in at least one agent playbook. Had they been written into a
Blade template, the result would have been markup that looks implemented, passes a checklist,
and does literally nothing forever.

The **real** surface, from the W3C Web Machine Learning CG draft (dated **14 August 2026** — one
day before this audit) and Chrome's declarative-API documentation:

**Imperative** — the object is `document.modelContext`, not `navigator`:
```js
await document.modelContext.registerTool({
  name, description, inputSchema, async execute({ ... }) { ... }
}, { signal });
```
Methods: `registerTool()`, `unregisterTool()`, `getTools()`, `executeTool()`, plus a
`toolchange` event.

**Declarative** — four **bare** attributes on ordinary forms, no `data-` prefix:

| Attribute | Element | Purpose |
|---|---|---|
| `toolname` | `<form>` | Tool name |
| `tooldescription` | `<form>` | What the tool does |
| `toolparamdescription` | `<input>` / `<select>` | Per-field description (falls back to `<label>`) |
| `toolautosubmit` | `<form>` | Auto-submit on agent invocation |

The naming churn explains the confusion — the API has moved from `window.agent` →
`navigator.modelContext.provideContext()` (removed March 2026) → `navigator.modelContext.registerTool()`
→ **`document.modelContext`, moved 21 July 2026**. So `navigator.modelContext` is stale-but-real;
`navigator.mcpActions` never existed.

**Status: draft, origin-trial, near-zero adoption.** It is a *Community Group Draft Report* —
explicitly **not a W3C Standard and not on the Standards Track**. Chrome runs a public origin
trial (149→156, token required); Edge is flag-only; Firefox and Safari have made no
implementation commitment. Most decisively: **no mainstream agent consumes WebMCP tools today.**
Claude, ChatGPT Agent, Perplexity and Gemini all still drive pages via the DOM and screenshots.
Adoption outside demos is described as approximately zero — WebMCP *checker tools* currently
outnumber real implementations.

**This vindicates the §3.7 recommendation to defer.** When it is worth doing, the declarative
form is four attributes on forms that §3.2 will already have created, and it degrades to
nothing in every browser that does not support it. That is a ten-minute retrofit — but only for
a site whose actions are real forms. **A `wire:click` button cannot be annotated by any of these
attributes, which is precisely why §3.2 outranks §3.7.**

### 5.3 ⚠️ Correction: `llms.txt` is close to inert

The site's `/llms.txt` is well-built, and §3.3 is still worth the fifteen minutes because it
doubles as an internal fact sheet. But the evidence against it as a *traffic or citation*
mechanism is now strong, and the owner should not believe otherwise:

- **Ahrefs, June 2026** — 137,210 domains with real traffic, measured via bot analytics:
  **97% of published `llms.txt` files received zero requests** in the measured month. Of the
  3% that saw any traffic, the **largest single requester category was SEO audit tools
  (21.7%)** — ahead of AI agents and infrastructure (10.5%). GPTBot 4.51%, PerplexityBot ~1.1%.
- AI bots do **not probe** for the file on domains that lack it — they are not looking for it.
- Domains publishing `llms.txt` were **cited no more often** than matched domains without it.
- **No provider has confirmed consuming it.** Google's Gary Illyes said Google does not support
  it and is not planning to; John Mueller compared it to the keywords meta tag. Claims that
  "Anthropic and Perplexity confirmed support" trace back to a vendor blog with **no linked
  official statement for either** — Anthropic *publishes* an `llms.txt` on its docs site, which
  is not the same as reading one. **Treat that claim as false.**

**Keep the file** (it is generated, so it costs nothing) and do the §3.3 content improvements
because they also improve the PDP. **Spend nothing further on it, and do not report it as an
AEO win.**

### 5.4 ⚠️ Correction: schema markup showed no measurable citation lift

Uncomfortable, but it is the only controlled experiment in the field. **Ahrefs, Aug 2025 –
Mar 2026**: 1,885 pages that newly added JSON-LD, matched against 4,000 controls (3 per treated
page, matched on pre-period citation volume), difference-in-differences. Result: **AI Overviews
−4.6%, AI Mode +2.4%, ChatGPT +2.2% — all statistically indistinguishable from zero.** A
separate test found the AI systems examined parsed only rendered HTML and ignored
JSON-LD/Microdata/RDFa. Google's Robby Stein: "no special markup, no secret schema."

**Important caveat that keeps this from being a reason to rip anything out:** every page in that
test already had 100+ AI Overview citations, so it measures *"does schema help pages already
being cited"* — not cold-start discovery.

**What this means for Glow Halal, concretely:**
- **Do not remove any existing schema.** It earns its keep through a *different* channel —
  Google merchant listing rich results, price and shipping annotations in classic search, and
  Merchant Center eligibility. Those are real and unaffected by this finding.
- **Do down-weight schema as an AEO lever.** The `FAQPage`-on-blog-posts item (§3.5) should drop
  from "Medium" to a nice-to-have. The claim that FAQ schema yields "40% higher citation
  weighting" circulates widely and has **no study behind it.**
- **Re-weight toward what the AI engines actually read: rendered HTML prose.** This is why §3.3
  (put the delivery time in *visible text*, not only in the `Offer` node) matters more than
  another schema node.

### 5.5 Agentic commerce: not adoptable from Pakistan

- **OpenAI's Instant Checkout / ACP launched 29 Sept 2025 and was pulled around 4 March 2026** —
  roughly five months. At retreat, about a dozen Shopify merchants were actually using it.
  Causes: merchant onboarding, product-data accuracy, no multi-item carts, no US sales-tax
  remittance. The model shifted to **discovery in ChatGPT, transaction on the merchant's own
  site** — which is exactly the model this site already fits.
- **Google + Shopify UCP** (Jan 2026) has the real traction — 8,000+ verified stores by June
  2026 — but **~99% of those are Shopify**, auto-enabled by a platform switch, not independent
  integrations.
- **Google AP2** is a real spec donated to the FIDO Alliance, but deployed essentially only by
  Google.

**The blocker for Glow Halal is not technical, it is payment rails.** Stripe does not support
Pakistan; PayPal does not offer Pakistani merchant accounts; ACP's checkout runs on Stripe. No
Pakistani PSP — JazzCash, Easypaisa, PayFast, PayPro, Swich — has announced any integration with
ACP, UCP or AP2. And **no agentic-checkout protocol currently models Cash on Delivery**, which
is ~70% of Pakistani e-commerce and this store's primary method.

**Recommendation: skip agentic checkout protocols entirely.** The adoptable surface for this
merchant is product **discovery**, not agentic **transaction**. That is §4.

### 5.6 ⚠️ Correction: IndexNow's payoff in Pakistan is small

IndexNow is live and working here, it took half an hour, and it is harmless — keep it. But the
framing that it is "required for AI visibility" is vendor copy:

- **No OpenAI documentation states that Bing indexation or IndexNow submission affects ChatGPT
  Search retrieval.** The widely-quoted "87% of ChatGPT citations match Bing's top results" is
  from a **February 2025** study of 100 queries and is eighteen months stale.
- Profound's 240M-citation dataset shows **ChatGPT–Bing alignment falling from 26% to 8%** while
  ChatGPT–Google alignment rose from 12% to 33%. The Bing dependency is decaying.
- **StatCounter Pakistan, July 2026: Google 97.14%, Bing 1.89%.** Even if the coupling were
  strong, the Pakistani payoff would be marginal.

Where Bing genuinely still matters is **Microsoft Copilot**, which is a direct index dependency.
The one claim that holds unconditionally: **if robots.txt blocked `OAI-SearchBot` you would be
out of ChatGPT Search entirely.** It does not block it. Good.

### 5.7 Market reality: size the whole bet honestly

Two verified facts the owner should hold alongside everything in this document:

- **Gallup/Gilani Pakistan (nationally representative): only ~15% of Pakistani adults have ever
  used an AI chatbot.** Among those who have: ChatGPT 66%, Copilot 23%, Gemini 17%. Usage skews
  hard by education (52% of educated adults vs 8% of less-educated).
- **ChatGPT Shopping and product carousels remain US-only.** Google AI Mode is live in Pakistan
  (since Aug 2025) but **English only**, with no confirmed Urdu support.

**So AEO for this brand is a bet on 2027–2028, not a 2026 traffic channel.** That is a
legitimate bet — the Roman-Urdu moat in `docs/keyword-research-aug2026.md` is real and gets
harder to take later — but it should be funded as a *cheap forward position*, not as the primary
acquisition strategy. **Classic SEO and WhatsApp/COD conversion remain the revenue engine this
year.** The good news is that almost every recommendation in §3 and §4 helps both.

### 5.8 On no-JS checkout

Worth fixing for robustness and for a crawler-visible purchase path, but honestly: **no agent
that can currently complete a purchase is blocked by it**, because browsing agents run
JavaScript. Ranked below the entity work for that reason.

### 5.9 Explicitly flagged as unusable

Circulating claims that did not survive verification. **Do not let any agency quote these:**

- `data-mcp-action`, `navigator.mcpActions.register()` — fabricated syntax (§5.2)
- "Anthropic and Perplexity confirmed `llms.txt` support" — no official statement exists
- "87% of ChatGPT citations match Bing" — Feb 2025, n=100, superseded
- "FAQ schema → 40% higher citation weighting", "r=0.87 semantic completeness" — no methodology
- All NAP-consistency-for-AI statistics ("16% ranking boost", "2.4× visibility lift") — no study
- "Fewer than 12% of companies under $10M have Wikidata entries" — untraceable
- **Any Pakistan-specific "AI visibility score"** — no public dataset exists to check it against.
  There is *no* published research on whether AI engines cite Daraz.pk, PriceOye or any
  Pakistani domain, and none on Roman-Urdu shopping-intent queries. Anyone selling you a
  Pakistani AI-visibility benchmark cannot substantiate it.

---

## 6. Query → answer coverage matrix

32 questions a real Pakistani buyer would put to an assistant. **Covered** = a page exists
that directly answers it. **Partial** = the fact is on the site but not as a findable answer.

### English

| # | Query | Covered? | URL / what is needed |
|---|---|---|---|
| 1 | best halal face oil in Pakistan | ✅ | `/blog/best-halal-herbal-oils-in-pakistan` |
| 2 | what is Lookman-e-Hayat oil | ✅ | `/blog/lookman-e-hayat-oil-uses-benefits-price` |
| 3 | Lookman e Hayat oil price in Pakistan | ✅ | `/blog/lookman-e-hayat-oil-price-in-pakistan` + both PDPs |
| 4 | Lookman e Hayat oil ingredients / what is it made of | ✅ | PDP — full INCI in raw HTML |
| 5 | Lookman e Hayat oil for joint pain | ✅ | `/blog/lookman-e-hayat-oil-for-joint-pain` |
| 6 | oil for old burn marks Pakistan | ✅ | `/blog/lookman-e-hayat-oil-for-cuts-and-burns` |
| 7 | how to use herbal oil for hair / champi | ✅ | `/blog/how-to-use-herbal-oil-for-hair-champi` |
| 8 | shilajit side effects | ✅ | `/blog/shilajit-side-effects-honest-guide` |
| 9 | sidr / lote leaves benefits for skin and hair | ✅ | `/blog/sidr-leaves-benefits-skin-hair` |
| 10 | is carmine halal | ✅ | `/halal-ingredients/carmine` |
| 11 | is glycerin halal in cosmetics | ✅ | `/halal-ingredients/glycerin` |
| 12 | is denatured alcohol halal | ✅ | `/halal-ingredients/denatured-alcohol` |
| 13 | does Glow Halal do Cash on Delivery | ✅ | `/faq` |
| 14 | Glow Halal return policy | ✅ | `/shipping-returns` |
| 15 | does Glow Halal have a halal certificate | ✅ | `/contact` — answers honestly "No" |
| 16 | how long does Glow Halal delivery take | ⚠️ Partial | On `/shipping-returns` + `/faq` only. **Add to PDP + `/llms.txt`** (§3.3) |
| 17 | is Glow Halal legit / genuine | ⚠️ Partial | `/about` + `/reviews` exist but no page answers the question in those words. **Needs a "Why trust us" page** |
| 18 | should I buy the 50 ml or the 100 ml | ❌ | **Needs a size-comparison table** — price per ml, who each suits. High commercial intent, trivial to write |
| 19 | is Lookman e Hayat oil safe for oily / acne-prone skin | ❌ | **Needs a "who this is for / not for" section** on the PDP + a skin-type guide. Cosmetic-suitability language only |
| 20 | sesame (til) oil benefits for skin | ❌ | **Needs an ingredient deep-dive** — feeds the flagship and is a clean informational win |
| 21 | what is guggul / Commiphora mukul | ❌ | **Needs an ingredient deep-dive.** Almost zero PK competition |
| 22 | halal skincare brands in Pakistan | ❌ | **Biggest missing page.** A category-defining listicle that names competitors honestly is the classic route into an AI round-up answer |
| 23 | best herbal hair oil in Pakistan | ⚠️ Partial | Champi guide covers usage, not selection. **Needs a selection listicle** |
| 24 | ashwagandha benefits for men / women | ❌ | Scheduled in the drip per the 11 Aug report — **verify it published** |
| 25 | patch test / side effects of herbal oil | ⚠️ Partial | In PDP FAQ; **should be its own H2** so it can be lifted standalone |

### Roman Urdu (`ur-Latn`)

| # | Query | Covered? | URL / what is needed |
|---|---|---|---|
| 26 | asli lookman e hayat tel kahan se lein | ✅ | `/ur-roman/blog/asli-lookman-e-hayat-tel-kahan-se-lein` |
| 27 | behtareen halal herbal tel pakistan | ✅ | `/ur-roman/blog/behtareen-halal-herbal-tel-pakistan` |
| 28 | balon mein tel lagane ka tarika | ✅ | `/ur-roman/blog/baalon-mein-tel-lagane-ka-tarika` |
| 29 | jodon ke dard ka tel | ✅ | `/ur-roman/blog/jodon-ke-dard-ka-tel` |
| 30 | jalne kaatne par lagane wala tel | ✅ | `/ur-roman/blog/jalne-kaatne-par-lagane-wala-tel` |
| 31 | beri ke patte ke fayde | ✅ | `/ur-roman/blog/beri-ke-patte-ke-fayde` |
| 32 | asli salajeet ki pehchan | ✅ | `/ur-roman/blog/asli-salajeet-ki-pehchan` |
| 33 | lookman e hayat tel kya hai | ❌ | **Gap.** The Urdu tree has "where to buy" but no "what is it". Per the no-cannibalization rule this must target a *different* primary keyword from EN #2 — `lookman e hayat tel kya hai` vs EN `what is lookman-e-hayat oil` qualifies |
| 34 | salajeet ke fayde aur istemal | ❌ | P1 in the keyword sheet, not yet published |
| 35 | chehray ke liye behtareen cream | ❌ | P1 in the keyword sheet; blocked on the face-cream SKU |
| 36 | sabse acha balon ka tel | ❌ | P1 in the keyword sheet, "Hindi-only SERP" — **wide open** |

**Coverage: 21 / 36 fully answered (58%), 5 partial, 10 missing.**

The missing English pages cluster into three cheap, high-value writes — **#18 size comparison,
#20+#21 ingredient deep-dives, #22 the "halal skincare brands in Pakistan" listicle** — and
#22 is the one most likely to earn a named mention in an AI round-up answer, because
round-up questions are answered from round-up pages.

---

## 7. Recommended order of work

Re-ordered after the §5 research. The top three are all **off-site or content** work, because
that is where the evidence points — not at markup.

| # | Action | Effort | Section |
|---|---|---|---|
| 1 | **Google Business Profile + Trustpilot**, then redirect WhatsApp customers to review there | 3 h + ongoing | §4.1, §4.2 L1 |
| 2 | Create social profiles; fill `StoreSettings` → `sameAs` goes live | 2 h | §3.1, §4.1 |
| 3 | Decide the `aggregateRating` / burn-mark-testimonial question | 30 min | §1.C, §3.8 |
| 4 | Add delivery time + returns + payment to the PDP **and** `/llms.txt` | 15 min | §3.3 |
| 5 | Write the missing EN pages — #22 listicle, #18 size table, #20–21 ingredients | 1 week drip | §6 |
| 6 | Outreach to be **included in** third-party PK listicles | ongoing | §4.2 L2 |
| 7 | Real POST purchase form on PDP + shop cards | 3 h | §3.2 |
| 8 | Roman-Urdu gaps #33, #34, #36 | drip | §6 |
| 9 | RSS feed; `FAQPage` on posts; `llms.txt` discovery links | 3 h | §3.4–3.6 |
| 10 | WebMCP · `agents.json` · agentic checkout protocols | **do not** | §3.7, §5.2, §5.5 |

**What changed from the pre-research draft:** review platforms and listicle inclusion moved to
the top; `llms.txt` discovery, `FAQPage` and the no-JS purchase form all moved down. The
purchase form is still worth building — it is the only real WebMCP preparation, and it closes a
genuine robustness gap — but no agent that can transact today is blocked by its absence.

---

*Compiled 15 August 2026. Live evidence collected by direct HTTP fetch without JavaScript
execution; code references are file+line against the working tree at commit `55d2eb4`.
No application code was modified in producing this audit.*
