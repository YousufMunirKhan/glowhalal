# Glow Halal — Organic Clicks Plan

**Date:** 4 September 2026 · **Data window:** 7 Aug – 3 Sep 2026 (28 days), GA4 + GSC export
**Companion to:** `docs/llm-visibility-plan-sep2026.md` (strategy) — this doc is execution.

---

## 1. What the data actually says

### 1.1 The headline number

```
Google Search impressions   1,998   (across 50 landing pages)
Organic clicks                  4
CTR                         0.20%   ← normal is 2–5%
```

**This is a position problem, not an indexing problem, and not primarily a snippet problem.**
A 0.2% CTR is what average position ~18–35 produces. The site is being *shown* 2,000 times a
month; it is simply too deep to be clicked. That is good news — the hard part (getting crawled,
indexed and served) is done and working.

The four clicks, in full:

| Query | Clicks |
|---|---|
| lookman e hayat oil | 1 |
| lukman e hayat oil | 1 |
| luqman hayat | 1 |
| herbal cream for face | 1 |

Three are the same branded entity. **Not one non-brand commercial keyword has ever produced a click.**

### 1.2 Traffic is not coming from search at all

New users by first channel: **Direct 36 · Organic Social 24 · Paid Social 14 · Paid Search 6.**
**Organic Search: zero.** Every visitor this month arrived from Facebook, from an ad, or already
knowing the URL.

### 1.3 A third of the "audience" is not human

GA4 reports 83 active users. The city breakdown includes Prineville, Forest City, Lulea, Altoona,
Gallatin, Fort Worth and Dublin — **Facebook and Google datacenter locations**, i.e. ad-crawler
traffic, not people. Roughly **25 of 83**.

**Real audience ≈ 46**, of which **Karachi 43**, Lahore 1, Sargodha 1. Read every other GA4
number with that discount applied — and treat Meta ads reporting with the same suspicion.

### 1.4 One page is 37% of the business

| Page | Impressions | Share |
|---|---|---|
| `/blog/lookman-e-hayat-oil-uses-benefits-price` | **732** | **37%** |
| `/blog/lookman-e-hayat-oil-for-joint-pain` | 130 | 6.5% |
| `/blog/lookman-e-hayat-oil-for-cuts-and-burns` | 105 | 5.3% |
| `/blog/sidr-leaves-benefits-skin-hair` | 103 | 5.2% |
| `/` | 101 | 5.1% |
| `/halal-ingredients` | 96 | 4.8% |
| `/product/nourishing-halal-face-cream/` *(dead WP URL)* | 83 | 4.2% |

Moving that one page from ~position 15 to ~position 5 is worth more than twenty new posts.

### 1.5 Conversion is fine. Do not touch it.

```
46 real users → 6 reached cart → 5 reached checkout → 2 orders (Rs 1,500 + Rs 2,500)
```

Checkout→purchase is **40%**, which is strong for Pakistani COD. The funnel is not leaking; it is
starved. Every hour spent on checkout UX this quarter is an hour wasted.

### 1.6 Honest ceiling

Holding impressions constant, everything in this document is worth roughly **30–90 clicks/month**.
The Day-30 gate in `seo-impact-log.md` (≥3,000 impressions, 100–250 clicks) will be missed on the
**impressions** line. Growing impressions is the entity-ladder work in the companion doc, not this one.

---

## 2. Shipped 4 Sep 2026

| # | Fix | Commit |
|---|---|---|
| 1 | **Meta text no longer cut mid-word.** `Str::limit` takes `preserveWords`; `PageController:44` used it, the other 13 call sites did not. Live before: `…5 Checks Before You` ("Buy" amputated), `…Ingredient list being confirmed — stated o` ("openly" amputated). | `ff12c11` |
| 2 | **Face-cream 301s repointed.** Four dead WooCommerce URLs pulled **116 impressions, zero clicks** while redirecting to `/shop/oils` — a page selling no cream. Google treats a redirect to an unrelated page as a soft 404: the destination inherits nothing and the old URL keeps floating, which is exactly what happened. Now → `/blog/best-herbal-face-cream-pakistan`, and *"herbal cream for face"* is one of the four queries that has ever converted. | `ff12c11` |
| 3 | **Legacy soap redirect** → `/blog/pimples-in-pakistan-heat-humidity` instead of the blog index (closed a TODO in the seeder's own docblock). | `ff12c11` |
| 4 | **New PDP titles rewritten** to lead with demand instead of a coined name, and to fit once `\| Glow Halal` is appended. | `9523ef0` |
| 5 | **Lookman prices corrected** to Rs 1,200 / Rs 2,200 and `OwnerProductSeeder` made non-destructive. | `682f81b` |

---

## 3. Not yet done — ranked by impact per effort

### P0 — no code, do these first

**3.1 `/shop/oils` has a null title. It is the only commercial category page.**

Live: `<title>Oils | Glow Halal</title>` · description *"Herbal skin & massage oils. Every
ingredient published in full."* · H1 `Oils`.

`ShopController::render()` already has the correct fallback — an **admin-set `meta_title` on the
Oils category is overriding it**. Fix in Admin → Catalogue → Categories → Oils → SEO.

- Title: `Herbal Oils in Pakistan – Hair, Skin & Body, COD`
- Description: `Herbal hair, skin and body oils — Lookman-e-Hayat, Roghan-e-Jarain and Roghan-e-Sukoon. Full ingredient list on every one. Cash on Delivery in Pakistan.`
- H1: `Herbal Oils in Pakistan`

This page currently targets nothing, while `massage oil for body in pakistan` and
`herbal soap price in pakistan` sit in the keyword sheet as P1 category terms.

**3.2 The 732-impression page has a Roman Urdu meta description on an English page.**

Live: *"Lookman-e-Hayat oil ke uses for skin, hair & massage, its til + guggul benefits, aur
Pakistan mein asal price (50ml Rs 1,200). Aik honest, poori guide."*

The page body and title are English; the three clicking queries are English-mode SERPs. The
snippet reads as broken English to an English searcher, **does not match the visible copy** (the
condition under which Google discards a description and writes its own), and violates the
EN/UR separation rule in the one place a searcher actually reads.

Fix in Admin → Content → Blog → that post → Meta description:

`Lookman-e-Hayat oil explained: what til and guggul actually do, the traditional uses for skin, hair and massage, how to apply it, and who should avoid it.`

**3.3 Attach the pillar to both Lookman products.**

`ProductController` pulls `$guides` from posts *attached to the product*, falling back to the
three newest posts sitewide. Live, **neither Lookman PDP links to the pillar** — they link to
face/pimples/how-to-identify instead. Admin → Products → Related posts. No code change.

**3.4 GSC housekeeping.** Confirm the three removal requests show **Approved**, then request
re-crawl of the four face-cream URLs so the new 301 targets are seen. This is also what finally
clears the *"halal-certified skincare"* description discussed in the companion doc §9.2.

### P1 — needs a decision or an export

**3.5 Four URLs are competing for "lookman e hayat oil price".**

The pillar's title carries *"Price"*, `/blog/lookman-e-hayat-oil-price-in-pakistan` exists, and
both PDP titles carry the price. Intended ownership:

| Query | Owner |
|---|---|
| lookman e hayat oil *(head entity)* | `/blog/lookman-e-hayat-oil-uses-benefits-price` |
| lookman e hayat oil price in pakistan | `/blog/lookman-e-hayat-oil-price-in-pakistan` |
| buy lookman e hayat oil 50 / 100 ml | the two PDPs |

**Do not act on this yet.** Pull GSC → Performance, dimensions *page + query*, 28 days, regex
`lookman|luqman|lukman` first. If the pillar is winning price queries outright and the dedicated
price post has near-zero impressions, the correct fix is the opposite — 301 the price post into
the 100 ml PDP and keep "Price" on the pillar. Guessing here costs the site's best asset.

**3.6 The pillar's title and H1 disagree.**
Title `Lookman e Hayat Oil: Uses, Benefits & Price (2026)` vs H1 `Lookman-e-Hayat Oil: Uses,
Benefits & How to Apply`. When they diverge Google often rewrites the SERP link from the H1, so
the "(2026)" freshness hook may never render. Make them identical — after 3.5 is settled.

**3.7 The pillar is nearly orphaned.**
`/blog` is paginated and the pillar is not on page 1. The homepage journal band
(`routes/web.php:173`) is `orderByDesc('published_at')->take(6)` — pure recency — so the pillar
fell off and the most-crawled URL on the site no longer links to its most valuable page. Add an
`is_featured` flag on `blog_posts`, unioned ahead of the recency query. Also add contextual links
from the pillar's body to its five cluster siblings; it currently links to zero of them.

### P2 — snippet rewrites, marginal

- **`/`** — *"Halal Beauty & Cosmetics"* describes a catalogue that does not exist (four herbal
  oils). Someone clicking for makeup bounces.
  Title: `Glow Halal — Herbal Oils & Halal Skincare in Pakistan`
  Description: `Herbal hair, skin and massage oils from Karachi. Every ingredient published in full, nothing hidden. Cash on Delivery across Pakistan, 2–7 days.`
- **`/halal-ingredients`** — *"A-Z"* over-promises; only three entries exist. The real fix is more
  entries, not the snippet. 96 impressions against 3 pages means Google is showing an index to
  people who wanted an answer.
- **`/blog/lookman-e-hayat-oil-for-cuts-and-burns`** — description should lead with the yes/no:
  `Should you put Lookman-e-Hayat oil on a cut or a burn? The honest answer, the 10-minute cool-water rule that comes first, and when to see a doctor.`
- **Both Lookman PDP titles** are 61 chars and truncate on mobile. Drop `| Glow Halal`.

**Deliberately not doing:** FAQ schema on blog posts (Google restricted FAQ rich results to
government/health sites in 2023 — buys nothing); chasing star snippets off one review; renaming
`/products/herbal-skin-oil-50ml` to a Lookman slug (correct eventually, but these are the only two
ranking commercial URLs and a slug change resets consolidation — revisit once positions stabilise).

---

## 4. SEO plan for the two new products

Both went live 3 Sep with **zero impressions, zero photos and zero inbound internal links.**

### 4.1 Keyword ownership

| | Roghan-e-Jarain | Roghan-e-Sukoon |
|---|---|---|
| **Primary EN** | `herbal hair oil in pakistan` | `body massage oil in pakistan` |
| **Primary UR** | `balon ki jarain ka tel` | `maalish ka tel` |
| **Secondary** | `kalonji badam ka tel balon ke liye`, `champi ka tel` | `kamar dard ka tel`, `kandhe ke dard ka tel` |
| **Never target** | — | `jodon ke dard ka tel` — belongs to Lookman |

EN and UR pages must never share a primary keyword. The Roman Urdu titles already do this correctly.

### 4.2 The three gates, in order

1. **Photos.** No `ProductImage` row means `SchemaGraph` emits no `image` on the Product node.
   `image` is required for Google product rich results, so **both PDPs are currently ineligible for
   price/availability enhancement and will be rejected by `/feed/google.xml`**. Worse, `og:image`
   falls back to the Lookman 100 ml bottle — a different product — and the on-page placeholder is a
   **face cream** on an oil page. This is the only blocker requiring something outside the codebase.
2. **Internal links.** Attach `/blog/how-to-use-herbal-oil-for-hair-champi` and
   `/blog/baalon-mein-tel-lagane-ka-tarika` → Jarain; `/blog/best-massage-oil-in-pakistan`,
   `/ur-roman/blog/jism-ki-maalish-ka-behtareen-tel` and `/ur-roman/blog/kamar-dard-ke-liye-tel`
   → Sukoon. Those last three were published 31 Aug and are purpose-built feeders sitting unused.
3. **Ingredient list for Sukoon.** It currently ships with the gap stated honestly. That is the
   right call, but it is a weaker page than it should be, and it cannot compete on
   `massage oil` queries against pages that list what is in the bottle.

### 4.3 Realistic timeline

| Target | When |
|---|---|
| Ranking for their own names | 2–6 weeks (near-certain — zero competition) |
| `balon ki jarain ka tel`, `maalish ka tel price` | 2–4 months |
| `herbal hair oil`, `pain relief oil` *(head terms)* | **6–12 months**, and only with the off-site work in the companion doc §4 |

For Karachi / Lahore / Islamabad queries specifically: Google shows a **local pack**, and the only
way into it is a **verified Google Business Profile**, which does not exist. No amount of on-page
work substitutes. Do **not** build `/karachi`, `/lahore`, `/islamabad` doorway pages.

---

## 5. Order of operations

1. **Today, no code:** 3.1 (`/shop/oils`), 3.2 (pillar description), 3.3 (attach pillar to PDPs).
2. **Today, external:** request the GSC re-crawls (3.4); start the product photography (4.2).
3. **Get the export** in 3.5 before touching the pillar's title.
4. **This week, code:** homepage meta (P2), pinned-post slot (3.7), internal links (4.2).
5. **Then stop optimising snippets.** The problem is 1,998 impressions.
   Go back to `docs/llm-visibility-plan-sep2026.md` §2 — that is the plan that grows the number
   this document can only convert.
