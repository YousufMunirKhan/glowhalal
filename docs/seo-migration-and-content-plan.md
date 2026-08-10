# Glow Halal — SEO Migration + Content Campaign

_Replacing the live WordPress/WooCommerce site at `glowhalal.com` with the new Laravel site, without losing search rankings — plus an organic blog/content engine to grow traffic._

> **Scope note.** This plan follows the **code**, not the stale parts of `docs/seo.md`. `seo.md` §5 still references a `/halal-certification` page and "name the certifier"; that contradicts the hard rule in `routes/web.php` (no accreditation, no "Halal Certified" claim) and the recent decision to withhold unsourced halal/haram rulings. Everywhere they conflict, the **no-certification / disclosure-first** rule wins. (Recommend cleaning up `seo.md` §5 separately — it's misleading as written.)

---

## 0. Executive summary

**The live site is thin, so migration risk is low — but not zero.** `glowhalal.com` today is a small WooCommerce store: 5 near-duplicate products at placeholder prices (₨30–50), **3 blog posts**, 2 categories. Its only genuine organic assets are the **brand term "Glow Halal"** and **three Pakistan-focused blog posts** (neem soap; dangers of market/store-bought soap; pimples in Pakistan).

**The single biggest risk:** those 3 blog posts sit at **root URLs** (`glowhalal.com/{post-slug}/`). On the new site they fall into the `/{slug}` CMS catch-all → **404 on launch day** unless we add explicit 301s *above* the catch-all **and recreate the content** on `/blog`.

**The opportunity:** the new site has a real content moat the live site lacks — the **Halal Ingredient Index** ("Is X halal?"), full **INCI disclosure**, and **"what we never use."** The content campaign turns that moat into compounding organic traffic.

**Two limitations to close for precision:** WebSearch is US-only, and there's no access to Google Search Console / GA4 / Ahrefs. Export **GSC → Queries + Pages (12 mo)** and share it to tune keyword targets and confirm which live URLs actually have traffic/backlinks.

---

# PART A — SEO-safe migration

Same domain (`https://glowhalal.com`), same brand, new codebase. No domain change ⇒ **no Google "Change of Address" needed**; keep the existing GSC + GA4 properties for history.

## A1. Complete old → new 301 redirect map

`{…}` = confirm the real new slug at launch; fall back to the listed default.

| # | Live URL | New target | Status | Note |
|---|---|---|---|---|
| 1 | `/` | `/` | keep | Both homepages |
| 2 | `/shop/` | `/shop` | 301 | trailing-slash strip |
| 3 | `/about/` | `/about` | 301 | trailing-slash strip |
| 4 | `/blog/` | `/blog` | 301 | trailing-slash strip |
| 5 | `/cart/` | `/cart` | 301 | noindex, UX only |
| 6 | `/checkout/` | `/checkout` | 301 | noindex, UX only |
| 7 | `/my-account/` | `/` | 301 | no account route exists |
| 8 | `/category/skincare/` | `/shop/skincare` _(else `/shop`)_ | 301 | only if slug exists |
| 9 | `/category/health-beauty/` | `/shop` | 301 | no 1:1 — don't fabricate empty category |
| 10 | `/product/nourishing-halal-face-cream/` | `/products/{real-cream}` _(else `/shop`)_ | 301 **override** | must sit **above** the generic `/product/{slug}` redirect |
| 11 | `/product/glow-halal-nourishing-face-cream/` | `/products/{real-cream}` _(else `/shop`)_ | 301 **override** | duplicate placeholder |
| 12 | `/product/glow-halal-nourishing-face-cream-2/` | `/products/{real-cream}` _(else `/shop`)_ | 301 **override** | duplicate placeholder |
| 13 | `/product/glow-halal-nourishing-cream/` | `/products/{real-cream}` _(else `/shop`)_ | 301 **override** | duplicate placeholder |
| 14 | `/product/natural-glow-skincare-set/` | `/products/{real-set}` _(else `/shop`)_ | 301 **override** | bundle |
| 15 | `/embrace-natural-care-the-benefits-of-neem-soap/` | `/blog/neem-soap-benefits-skin` | 301 **above catch-all** + recreate | **CRITICAL** |
| 16 | `/the-hidden-dangers-of-market-soaps-understanding-the-causes-of-pimples-in-pakistan/` | `/blog/pimples-in-pakistan-heat-humidity` | 301 **above catch-all** + recreate | **CRITICAL** — owns "pimples in Pakistan" |
| 17 | `/the-hidden-dangers-of-store-bought-soaps-for-your-skin/` | `/blog/whats-really-in-your-bar-soap` | 301 **above catch-all** | **CRITICAL** + de-duplicate (see below) |
| 18 | `wp-sitemap.xml` | `/sitemap.xml` | 301 | so GSC/links resolve |
| 19 | `/wp-admin/*`, `/wp-login.php`, `/wp-json/*`, `/feed/`, `?s=`, `?add-to-cart=` | — | **410 Gone** | de-index WP cruft |

### The critical collision (rows 15–17)
On the new site, `/embrace-natural-care-...` matches the `/{slug}` catch-all → PageController → no page → **404**. **Unless explicit 301s are declared above the catch-all, all three posts 404 on launch and you lose the only pages with real rankings/backlinks.** A 301 to an empty/404 page is worthless, so the **content must be recreated first**.

**Cannibalization fix:** rows 16 & 17 ("market soaps" and "store-bought soaps") are near-duplicates competing for the same intent. Consolidate the *soap-dangers* angle into **one** post and keep the *pimples-in-Pakistan* angle as its own post. Net: two clean owners instead of three thin, overlapping ones. (The neem-soap post stays separate — distinct keyword.)

## A2. Laravel implementation

**Preferred — one-hop legacy middleware** (avoids the trailing-slash double-hop that route redirects create, since the canonical middleware strips the slash before routing):

1. `redirects` table: `from_path` (unique, lowercased, no trailing slash), `to_path`, `status` (default 301).
2. `App\Http\Middleware\LegacyRedirectMiddleware` — normalises the raw path (lowercase, trim trailing slash), looks up `redirects`, and issues a **single 301 to the final target**.
3. Register it **first** (prepend) in `bootstrap/app.php`, before the canonical/trailing-slash middleware.
4. Seed rows 2–17.

**Simpler alternative — route redirects** (two hops on trailing-slash URLs, tolerable for a legacy migration). Placement is non-negotiable:
- Product overrides (rows 10–14) go **above** `Route::permanentRedirect('/product/{slug}', '/products/{slug}')`, or they soft-404.
- Blog/category 301s (rows 15–17, 8–9, 7) go in the existing **"Legacy / navigation aliases"** block, which is safely **above** the `/{slug}` catch-all.

**Trailing slashes:** enforce in ONE place (the app's canonical middleware — apex host + HTTPS + lowercase + no trailing slash, single 301). Do **not** also normalise at nginx/Apache — two normalizers = a chain.

## A3. Keyword-parity check

| Live term | New-site coverage | Risk | Action |
|---|---|---|---|
| halal skincare | Covered (home title, `/shop`) | Low | keep titles + "Glow Halal" brand term |
| **"halal certified"** | **Deliberately dropped** (no accreditation) | Accepted | do NOT build/redirect toward it; reposition to transparency |
| neem soap benefits | None yet | **High** | recreate `/blog/neem-soap-benefits-skin` |
| market/store-bought soap dangers | None yet | **High** | recreate `/blog/whats-really-in-your-bar-soap` |
| causes of pimples in Pakistan | None yet | **Med–High** | recreate `/blog/pimples-in-pakistan-heat-humidity` |
| natural / ethical skincare | Partial (`/what-we-never-use`, `/about`) | Low–Med | add explicit "natural/ethical" framing to those intros |

**Net:** the only parity gaps that risk a live ranking are the 3 blog posts. Redirects are the plumbing; recreating the posts is the content — neither works alone.

## A4. Launch / rankings-preservation checklist

**Pre-launch (blocking):**
- [ ] All redirects live + tested — each old URL returns a **single 301 → 200** (no redirect-to-404, no chains). Explicitly test rows 10–17.
- [ ] The **3 blog posts recreated & published** at their `/blog/{slug}` targets *before* the 301s go live.
- [ ] `<title>` keeps the brand term **"Glow Halal"** on home/about/shop.
- [ ] Canonicals absolute, self-referencing, apex HTTPS; exactly one per page.
- [ ] `www` → apex, HTTP → HTTPS, one hop.
- [ ] `robots.txt` doesn't block the site (only `/cart`, `/checkout`, `/account`, `/search`). No leftover `Disallow: /` from staging.
- [ ] New `sitemap.xml` generated; `wp-sitemap.xml` → 301 → `/sitemap.xml`.
- [ ] WordPress infra (`/wp-*`, feeds, `?s=`) returns **410**.

**Launch day:**
- [ ] Deploy on the **same domain** (keep GSC + GA4 property; annotate launch date in GA4).
- [ ] Submit new `sitemap.xml` in GSC + Bing; **URL-inspect → Request indexing** the 3 posts + homepage.

**First 30 days:**
- [ ] Watch GSC Pages for `Not found (404)` / `Redirect error` spikes.
- [ ] Confirm impressions for the 3 blog queries + "glow halal" transfer to new URLs within 2–4 weeks.
- [ ] Crawl (Screaming Frog) filtered to the old URL list → 0 chains, 0 redirect-to-404.
- [ ] Keep 301s **permanent**.

## A5. Risk ranking

| Rank | Risk | Severity | Why |
|---|---|---|---|
| **1** | 3 root blog slugs 404 via catch-all / content not recreated | **High×High** | only pages with real rankings/backlinks |
| 2 | Old product URLs soft-404 via generic `/product/{slug}` redirect | High×Med | wastes crawl budget; needs row 10–14 overrides |
| 3 | Redirect chains from trailing-slash + host/scheme stacking | Med×Med | dilutes/slows signal; solved by middleware ordering |
| 4 | `/category/*` → 404 if slugs differ | Med×Low | map to `/shop` or real slug |
| 5 | Loss of "halal certified" rankings | Deliberate | reposition to "halal skincare" + transparency |

**Single most important action:** before launch, declare explicit one-hop 301s for the 3 root blog URLs — above the `/{slug}` catch-all — pointing to freshly recreated `/blog/{slug}` posts on the same topics.

---

# PART B — Organic content / blog campaign

Follows the code's disclosure-first moat. Product links use `/products/{slug}` (plural). No unsourced halal/haram ruling anywhere; contested points cite a source or use brand-preference framing.

## B1. Six content pillars

Difficulty scored for a **zero-authority new domain**. Volumes are directional for Pakistan (en-PK + Roman-Urdu) — **validate in Keyword Planner set to Pakistan** before locking.

**Pillar 1 — Halal Ingredient Index** (reference pages `/halal-ingredients/{slug}`, NOT blog; the flagship moat, near-zero PK competition, AI-quotable):
`is glycerin halal` · `is carmine halal` · `is collagen halal` · `is alcohol in skincare halal` · `is hyaluronic acid halal` · `is stearic acid halal`. Blog posts **link out** to these; never target "is X halal" in a blog post.

**Pillar 2 — Pakistani Skin Concerns** (blog → `/shop/skincare` + products; the old site's proven lane):
`pimples in Pakistan / daanay ka ilaj` · `oily skin remedy Pakistan` · `melasma / jhaiyan` · `dry skin winter Pakistan` · `sun tan removal / dhoop se kala pan`.

**Pillar 3 — Ingredient Safety & "What We Never Use"** (blog → `/what-we-never-use` + INCI pages; disclosure as link-bait):
`skin whitening cream side effects (mercury/steroid)` · `dangers of chemical/harsh soap` · `how to read an INCI ingredient list` · `harmful ingredients in skincare`.

**Pillar 4 — Halal Beauty Education** (blog + `/faq`; must cite sources / present scholarly split — YMYL):
`what is halal skincare` · `does skincare/makeup break wudu` · `is Korean skincare halal / is Cerave halal` · `halal vs vegan skincare` · `how to check if skincare is halal`.

**Pillar 5 — Local / Desi Natural Ingredient Guides** (blog → natural range; huge Roman-Urdu overlap, low competition):
`neem soap benefits` · `multani mitti benefits for face` · `rose water / gulab jal benefits` · `besan for skin / ubtan` · `aloe vera for face`.

**Pillar 6 — Buying Guides & Routines** (blog → shop/product; commercial, needs some authority):
`best face wash/soap for acne in Pakistan` · `halal skincare routine Pakistan` · `best halal skincare brand Pakistan` · `mineral sunscreen Pakistan (no white cast)` · `bridal / dulhan skincare routine`.

## B2. 3-month editorial calendar (2 posts/week — 24 pieces)

Old-site reworks are front-loaded in Weeks 1–2 so equity transfers via 301. Ingredient pages (glycerin, carmine, collagen, alcohol-denat) ship in parallel Weeks 1–2 as reference pages.

| Wk | Post title | Primary keyword | Intent | Words | Links to |
|---|---|---|---|---|---|
| 1 | Why You Get Pimples in Pakistan's Heat & Humidity | pimples in pakistan | Info→Comm | 1.8–2.2k | `/shop/skincare`, cleansing-oil, `/halal-ingredients/glycerin`, `/what-we-never-use` |
| 1 | Neem Soap Benefits for Skin: Does It Clear Pimples? | neem soap benefits | Info | 1.4–1.8k | pimples post, multani post, `/shop/skincare` |
| 2 | What's Really in Your Bar Soap (And Why It Dries You Out) | dangers of chemical soap | Info | 1.6–2.0k | `/what-we-never-use`, INCI-reading post, cleansing-oil |
| 2 | Skin Whitening Cream Side Effects: What PK Studies Show | skin whitening cream side effects | Info | 2.2–2.8k | `/what-we-never-use`, ingredients-to-avoid, melasma post |
| 3 | Oily Skin in a Pakistani Summer: A Simple Routine | oily skin pakistan | Info→Comm | 1.6–2.0k | pimples post, `/shop/skincare`, serum |
| 3 | How to Read a Skincare Ingredient List (INCI Guide) | how to read inci ingredient list | Info | 1.6–2.0k | `/what-we-never-use`, `/halal-ingredients`, whitening post |
| 4 | Multani Mitti Benefits for Oily & Acne-Prone Skin | multani mitti benefits for face | Info | 1.3–1.7k | neem/oily posts, `/shop/skincare` |
| 4 | What Is Halal Skincare? A Plain-Language Guide | what is halal skincare | Info | 1.8–2.2k | `/what-we-never-use`, `/halal-ingredients`, halal-vs-vegan, `/shop` |
| 5 | Melasma & Pigmentation in Pakistan: Causes & Care | melasma pigmentation pakistan | Info | 1.8–2.2k | sunscreen, tan post, whitening post |
| 5 | Ingredients to Avoid in Skincare (and Safer Swaps) | harmful ingredients in skincare | Info | 1.6–2.0k | `/what-we-never-use`, INCI post, ingredient pages |
| 6 | Rose Water for Skin: Real Benefits vs Marketing | rose water benefits for skin | Info | 1.3–1.6k | neem/multani posts, `/shop/skincare` |
| 6 | Halal vs Vegan Skincare: What's the Difference? | halal vs vegan skincare | Info | 1.4–1.7k | what-is-halal post, `/halal-ingredients/carmine`, `/what-we-never-use` |
| 7 | Sun Tan Removal in Pakistan: What Actually Works | sun tan removal pakistan | Info→Comm | 1.6–2.0k | sunscreen, melasma & besan posts |
| 7 | Does Skincare Break Wudu? What Scholars Say | does skincare break wudu | Info (sourced) | 1.4–1.8k | `/faq`, what-is-halal, `/halal-ingredients/alcohol-denat` |
| 8 | Besan for Skin: Tan, Oil & Brightening (Honest Take) | besan for skin | Info | 1.3–1.6k | multani/rose posts, `/shop/skincare` |
| 8 | Is Korean Skincare Halal? How to Check Any Product | is korean skincare halal | Info | 1.6–2.0k | how-to-check post, ingredient pages |
| 9 | Dry Skin in Lahore Winters: Fixing the Flakiness | dry skin winter pakistan | Info→Comm | 1.4–1.8k | face-cream, `/shop/skincare` |
| 9 | How to Check If Your Skincare Is Halal (5 Steps) | how to check if skincare is halal | Info | 1.6–2.0k | `/halal-ingredients`, INCI post, `/what-we-never-use` |
| 10 | Aloe Vera for Face: Uses for Oily & Sensitive Skin | aloe vera for face | Info | 1.3–1.6k | rose/neem posts, serum |
| 10 | Best Mineral Sunscreen in Pakistan (No White Cast) | mineral sunscreen pakistan | Comm | 1.6–2.0k | `/products/mineral-sunscreen-spf40`, melasma & tan posts |
| 11 | Best Soap & Face Wash for Acne in Pakistan (2026) | best face wash acne pakistan | Comm | 1.8–2.2k | `/shop/skincare`, cleansing-oil, pimples post |
| 11 | A Halal Skincare Routine for Pakistani Skin | halal skincare routine pakistan | Comm | 1.8–2.2k | all products, `/shop`, cluster posts |
| 12 | Halal Skincare Brands in Pakistan: An Honest Guide | halal skincare brands pakistan | Comm | 2.0–2.5k | `/shop`, `/what-we-never-use`, what-is-halal |
| 12 | Bridal Skincare Routine: 3 Months Before the Wedding | bridal skincare routine pakistan | Comm (seasonal) | 1.8–2.2k | routine post, all products, `/shop` |

## B3. Interlinking model (funnel to COD)

```
   HUB blog post  →  links DOWN ("Full analysis →", ~120 words each)
        ▼
   /halal-ingredients/{slug}  (reference moat; links UP to hub, SIDEWAYS to siblings, DOWN to commerce)
        ▼
   /what-we-never-use  ◄── every safety/disclosure post links here (trust hub)
        ▼
   /shop/skincare  →  /products/{slug}  →  COD checkout / WhatsApp
```

Rules that prevent cannibalization: a hub post gives each ingredient ~120 words then links out (never the full ruling); every ingredient page carries a dynamic *"Glow Halal products made without {ingredient}"* block (the key commercial hop); every product INCI entry links to its ingredient page; every skin-concern post links ≥1 product + `/what-we-never-use`; add a COD/WhatsApp CTA **mid-article** (not only the footer — 4G readers bounce early); descriptive varied anchors, ≤1 internal link per 100 words, no orphan pages.

**Cannibalization guard:** one `primary_keyword` per URL; CI fails if a primary keyword appears on two URLs. From Week 4, monthly GSC page+query audit — if two URLs both rank top-20 for one query, assign the owner (most clicks), de-optimise the loser, add loser→owner link.

## B4. On-page + technical rules

- **Titles/H1:** one H1, primary keyword left, `<title>` ≤60 chars (append ` | Glow Halal` only if it fits). No heading skips.
- **Answer box (all informational posts):** open with a **40–55 word direct answer** under the H1, before preamble, in `.article-answer-box` (paragraph-snippet + AI-overview + `speakable` target). Tables for comparisons, `<ol>` for routines, `<ul>` for lists. Turn live PAA questions into H2s / FAQ.
- **Schema:** per post — `BlogPosting` (real dates, wordCount, real-person `author`, `publisher` → `#organization`, `citation[]` for sourced claims, `speakable`) + `WebPage` + `BreadcrumbList`. `FAQPage` only for Q&As visibly on the page. **No `HowTo`**. Ingredient pages: `Article` + `DefinedTerm` (`sameAs` → Wikipedia) + `FAQPage`. **Never assert an unsourced ruling in schema; no certifier/certificate node.**
- **E-E-A-T (YMYL):** real named author + credentials (no "Admin"/"Team" bylines); religious/health claims reviewed by a named reviewer shown on-page; dofollow outbound citations; real "Last updated"; state scholarly disagreement. **Get legal review before the whitening-cream and "ingredients to avoid" posts.**
- **Images:** `<picture>` AVIF→WebP→JPEG, `srcset`, inline ≤100KB / hero ≤120KB, `loading="lazy" decoding="async"` below the fold (never on the LCP image), descriptive hyphenated filenames, 60–125 char alt. **Original photography** (real INCI panels, real products) = the Experience signal + image-search traffic.
- **CWV budget:** LCP <2.0s mobile, INP <150ms, CLS <0.05; article JS ≤150KB, CSS ≤40KB. Content in the initial server HTML (no lazy islands / `wire:text` for blog/ingredient). No intrusive interstitial (delay ≥15s / exit-intent, ≤30% viewport).
- **Urdu:** fold Roman-Urdu terms (`daanay ka ilaj`, `jhaiyan`, `gora cream nuksan`) in as **secondary keywords within the English post**. A real `/ur/…` tree is a Phase-2 project with hreflang.

## B5. Measurement

**Tools:** GSC (per-sitemap coverage), GA4 (organic segment + `whatsapp_click` + COD `purchase` events), a rank tracker set to **Pakistan/mobile**, CrUX/PageSpeed, and a `seo_keywords` registry for cannibalization CI.

| Stage | KPI | 3-month target (new domain) |
|---|---|---|
| Indexation | % published URLs indexed | ≥90% of blog + ingredient URLs |
| Visibility | Impressions on non-brand + "…pakistan/is X halal" | rising WoW; first page-1s by wk 8–10 |
| Rankings | keywords in positions 4–20 | 15–25 target keywords top-20 |
| Micro-conversion | `whatsapp_click` + blog→product clicks | establish baseline CTR |
| Conversion | COD orders attributed to organic | first organic-attributed orders |
| Technical | CWV "Good" p75 mobile, 0 critical crawl errors | LCP<2.0s, INP<150ms, CLS<0.05 |

**Monthly 30-min loop:** (1) coverage sweep → fix unindexed; (2) cannibalization audit; (3) **striking-distance harvest** (refresh posts ranking 5–20 — fastest ROI on a young site); (4) snippet check; (5) conversion check → double down on the cluster that converts; (6) CWV field-data check.

## B6. First 5 posts to write (impact ÷ effort)

1. **Why You Get Pimples in Pakistan's Heat & Humidity** — inherits the old "pimples in Pakistan" equity, high intent, funnels to shop. Best ratio.
2. **Neem Soap Benefits for Skin** — lowest effort (equity transfer + near-zero competition), high Roman-Urdu intent.
3. **What's Really in Your Bar Soap** — inherits soap-dangers equity; first post that activates the disclosure moat.
4. **Skin Whitening Cream Side Effects: What PK Studies Show** — biggest volume + link-bait, real citable PK research; higher effort (precision + legal review).
5. **A Halal Skincare Routine for Pakistani Skin** — the commercial spine that interlinks the cluster and converts to COD.

In parallel (reference, not blog): ship `/halal-ingredients/glycerin` and `/halal-ingredients/carmine` in Weeks 1–2 — highest-achievability assets on the domain.

---

## Sources (Pakistan keyword/market research)
- Neem soap benefits (Smytten); best acne soap Pakistan (Stiflex, Dango.pk)
- Mercury/steroid in whitening creams — JPAD, Tribune, PubMed
- Melasma treatment in Pakistan — Derma.pk, The Ordinary PK
- Halal beauty market context — Grazia; Iba Cosmetics (halal-certified competitor)
