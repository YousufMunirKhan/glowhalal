# Glow Halal — SEO Specification

> ## ⚠️ SUPERSEDING NOTICE (2026-08-10) — read before using this document
>
> **This spec's "Halal Certified" strategy is VOID.** Glow Halal holds **no third-party
> halal accreditation and claims none** (authoritative rule: the header comment in
> `routes/web.php`). Wherever this document says otherwise, the code and the newer plan win.
>
> **Do NOT implement any of the following from this doc** — every one is a false or
> unsourced claim and has been deliberately removed from the build:
> - the `/halal-certification` page, and any "Certification hub / trust hub" (there is no such route)
> - homepage/product titles or copy using **"Halal Certified"** (e.g. "Halal Certified Makeup & Skincare in Pakistan")
> - the `hasCertification` / `Certification` schema node, `certificationIdentification` (certificate number), `"Halal Certified": "Yes"`, or naming a certifier (SANHA/PSQCA "certified by")
> - targeting keywords that assert certification (`halal certified lipstick/makeup`, `halal certification for cosmetics in pakistan`) as a **claim we make** — you may still write *informational* content that explains what halal certification is, citing PSQCA PS 5319-2014 / SANHA as external references, without claiming Glow Halal is certified
> - any halal/**haram** verdict presented without a cited fatwa or recognised standard (see the "verdicts withheld" decision in the ingredient views)
>
> **What replaces it — the two true trust claims only:** (1) the **full INCI list** published on every
> product, and (2) the public **`/what-we-never-use`** list. Reposition all "certification" equity onto
> "**halal skincare**" + **ingredient transparency**.
>
> **Current, authoritative documents:** `routes/web.php` (the no-claim rule) and
> `docs/seo-migration-and-content-plan.md` (live→new migration + the real content campaign).
> This file still has valid, reusable material (URL architecture §2, on-page mechanics §3,
> technical/CWV §7, the ingredient-index moat), but the certification framing throughout is stale.

**Domain:** https://glowhalal.com
**Primary market:** Pakistan (en-PK), secondary: global English-language halal-beauty audience
**Stack:** Laravel 13.24 · PHP 8.3 · **Livewire 4.3.5** · Tailwind 4 · Filament 5.7.6 (admin) · server-rendered (Blade)
**Status of codebase at time of writing:** the storefront is greenfield (`routes/web.php` contains only the `/` closure). Everything below is a build-it-right-first-time specification.
**Livewire version note:** this document is written against **Livewire 4**, not 3. Section 8 covers v4-specific behaviour — Islands, single-file components, `Route::livewire()` page components, and the v4 optimistic-UI directives — each of which introduces SEO footguns that did not exist in v3. Livewire 4 is strongly backwards-compatible with v3, so all v3-era risks still apply *in addition to* the v4-specific ones.
**Mandate:** "0 SEO compromise."
**Document owner:** SEO. **Primary consumer:** frontend developer.
**Version:** 1.0 — 2026-08-09

---

## 0. How to read this document

- **MUST** = non-negotiable, blocks launch.
- **SHOULD** = strongly recommended, ship within 30 days of launch.
- **LATER** = designed for now, built when the catalog justifies it.
- Anything in `{braces}` is a template variable.
- Do **not** implement application code from this document into the repo as part of this task — this is the specification. The frontend developer implements it.

**Reality check on timelines.** A brand-new domain with zero backlinks does not rank for competitive commercial terms in month 1. Expect:
- Weeks 0–6: indexation, brand queries, ultra-long-tail informational.
- Months 2–5: informational long-tail ("is carmine halal", "haram ingredients in makeup") starts landing page 1–3.
- Months 6–12: mid-tail commercial ("halal lipstick Pakistan", "alcohol free foundation Pakistan") becomes winnable.
- Months 12+: head terms ("halal makeup Pakistan", "halal cosmetics") realistically contestable **only** with sustained link acquisition.

The entire strategy below is built around that curve: **win informational first, convert commercial second.**

---

## 1. Keyword research

### 1.1 Data honesty statement

I could not pull Ahrefs/Semrush/Keyword Planner numbers inside this environment. Every volume figure below is an **estimate** derived from three verifiable inputs, and each is labelled as such:

1. **SERP composition analysis** — who actually ranks today, and how weak they are (this is the strongest available signal for a zero-authority site).
2. **Published market/trend research** — cited below.
3. **Query-pattern inference** from the live SERPs and related-search surfaces.

**MUST before locking the content calendar:** validate every keyword in this section against Google Keyword Planner with location = Pakistan, language = English + Urdu, and again with location = Worldwide/English. Replace the estimate columns with real numbers and re-sort by priority. Treat this section as a *hypothesis set*, not gospel.

### 1.2 What the research actually confirms

| Finding | Source |
|---|---|
| Google searches for "halal makeup" have risen steadily since 2013; interest now extends beyond Muslim-majority markets (Japan, Slovakia cited) | [Mordor Intelligence — Halal Cosmetic Products Market](https://www.mordorintelligence.com/industry-reports/halal-cosmetic-products-market), [IJHI search-engine-based market analysis](https://journal.uii.ac.id/IJHI/article/view/39574) |
| Halal beauty demand splits ~38% skincare / 29% haircare / 21% colour cosmetics | [IJHI / market analysis](https://journal.uii.ac.id/IJHI/article/view/39574) |
| Pakistan colour cosmetics ≈ USD 81.56M; 52% of sales driven by wedding/occasion cycles in the top five cities | [Halal cosmetics market research](https://www.businessresearchinsights.com/market-reports/halal-cosmetics-and-personal-care-products-market-109988) |
| Demand for **ingredient transparency + certification** is the dominant purchase driver, and consumers will switch to smaller brands that demonstrate accountability | [Mordor Intelligence](https://www.mordorintelligence.com/industry-reports/halal-cosmetic-products-market) |
| Pakistan has a formal halal cosmetics standard — **PSQCA PS 5319-2014**, "General Guidelines for Halal Cosmetics and Personal Care Products", plus PS 3733; **SANHA Pakistan** is an active certification body | [PSQCA Halaal Division](http://updated.psqca.com.pk/standardization/division/halaal-division/), [SANHA Pakistan](https://www.sanha.org.pk/) |
| Carmine (cochineal) is treated as haram; gelatin is source-dependent (fish halal, porcine haram); topical alcohol is scholarly-contested, not settled | [Halal Code Check ingredients guide](https://halalcodecheck.com/blog/halal-cosmetics-ingredients-guide/), [Claudia Nour](https://claudianour.com/blogs/modest-makeup/what-is-halal-makeup-more-than-just-alcohol-pork-and-carmine) |
| Wudu-friendly / breathable (oxygen-permeable) nail polish is an established, searched product category | [Eluxe Magazine — best halal nail polish brands](https://eluxemagazine.com/beauty/best-halal-nail-polish/) |

**The single most important strategic read:** the confirmed #1 purchase driver is *ingredient transparency and certification* — which is an **informational search behaviour**. That is exactly what a zero-authority site can win. The commercial SERP is defended by brands with retail footprints; the ingredient SERP is defended by nobody.

### 1.3 Competitive landscape (verified live SERP occupants)

| Competitor | Type | What they own | Weakness we exploit |
|---|---|---|---|
| [masarratmakeup.com](https://masarratmakeup.com/) | Brand D2C | "first Halal Certified Makeup Brand in Pakistan" — owns brand + strong commercial terms | Brand-first; thin editorial/ingredient content |
| [haya-beauty.com](https://www.haya-beauty.com/) | Brand D2C | "halal certified, alcohol-free makeup" positioning | Small content footprint |
| [khubsurti.pk](https://khubsurti.pk/blogs/news/the-complete-guide-to-halal-makeup-in-pakistan-best-brands-products) | Multi-brand retailer + blog | "Complete Guide to Halal Makeup in Pakistan" | Single listicle, not a topic cluster |
| [gharpar.co](https://gharpar.co/list-of-halal-makeup-brands-in-pakistan/) | Salon services blog | "List of Halal Makeup Brands in Pakistan" | Off-topic domain (salon booking), no topical authority in ingredients |
| [dermagin.com](https://dermagin.com/top-halal-skincare-products-in-pakistan/) | Clinic blog | "Top Halal Skincare Products in Pakistan" | Same — borrowed authority, no cluster |
| [daraz.pk/tag/halal-cosmetics](https://www.daraz.pk/tag/halal-cosmetics/) | Marketplace | Generic tag page | Auto-generated, zero editorial value |
| [halalcodecheck.com](https://halalcodecheck.com/blog/halal-cosmetics-ingredients-guide/), [halalseeker.com](https://halalseeker.com/tools/cosmetics-checker), [youremma.com](https://youremma.com/blogs/news/haram-ingredients-in-makeup) | Global ingredient content | Ingredient-level queries | Not Pakistan-localised; no PSQCA/SANHA context; no product tie-in |

**Conclusion:** the Pakistan informational SERP is held by *low-authority, off-topic listicles from salon and clinic blogs*. That is a beatable SERP. A dedicated halal-cosmetics domain publishing a genuine ingredient cluster with PSQCA/SANHA citations out-ranks a salon booking site on topical relevance within 3–6 months.

### 1.4 Commercial-intent keywords (Pakistan)

Difficulty scored **for a zero-authority domain**: Easy = winnable in ≤6 months, Medium = 6–12 months, Hard = 12+ months / needs links.

| Keyword | Est. PK monthly volume | Intent | Difficulty (0-authority) | Target URL | Priority |
|---|---|---|---|---|---|
| halal makeup pakistan | 400–900 | Transactional | **Hard** | `/shop` | P2 |
| halal cosmetics pakistan | 300–700 | Transactional | **Hard** | `/shop` | P2 |
| halal makeup brands in pakistan | 300–700 | Commercial investigation | **Medium** | `/blog/halal-makeup-brands-pakistan` | **P0** |
| halal skincare pakistan | 200–500 | Transactional | **Medium** | `/shop/skincare` | P1 |
| halal lipstick pakistan | 100–300 | Transactional | **Easy–Medium** | `/shop/lipstick` | **P0** |
| wudu friendly nail polish pakistan | 50–150 | Transactional | **Easy** | `/shop/nail-polish` | **P0** |
| breathable nail polish pakistan | 50–200 | Transactional | **Easy** | `/shop/nail-polish` | **P0** |
| alcohol free makeup pakistan | 100–250 | Transactional | **Easy–Medium** | `/shop/alcohol-free` | **P0** |
| alcohol free skincare pakistan | 100–300 | Transactional | **Easy–Medium** | `/shop/skincare/alcohol-free` | P1 |
| halal certified lipstick | 50–150 | Transactional | **Easy** | `/shop/lipstick` | **P0** |
| halal foundation pakistan | 80–200 | Transactional | **Medium** | `/shop/foundation` | P1 |
| halal nail polish price in pakistan | 50–150 | Transactional | **Easy** | `/shop/nail-polish` | **P0** |
| buy halal cosmetics online pakistan | 50–150 | Transactional | **Easy–Medium** | `/shop` | P1 |
| vegan cruelty free makeup pakistan | 100–300 | Commercial | **Medium** | `/shop` + blog | P2 |
| halal perfume pakistan (alcohol free attar) | 200–600 | Transactional | **Hard** (attar incumbents) | LATER | P3 |

**Global English commercial (secondary, low effort, real upside):**

| Keyword | Est. global volume | Difficulty | Target |
|---|---|---|---|
| halal nail polish | 3,000–8,000 | Hard | `/shop/nail-polish` (long-game) |
| wudu friendly nail polish | 800–2,500 | **Medium** | `/shop/nail-polish` |
| halal certified makeup | 1,000–3,000 | Hard | `/shop` |
| halal lipstick | 1,000–3,000 | Hard | `/shop/lipstick` |

Do **not** chase global commercial head terms in year 1. Ship them as by-products of Pakistan pages, with correct `hreflang`/geo signals, and harvest whatever comes.

### 1.5 Informational keywords — this is where we win

These are the near-term achievable wins. Every one of them is a blog or ingredient-glossary page.

**Tier A — ingredient queries (highest achievability, near-zero competition from PK sites):**

| Keyword | Est. volume (PK + global EN) | Difficulty | Target URL |
|---|---|---|---|
| is carmine halal | 500–2,000 | **Easy** | `/halal-ingredients/carmine` |
| is glycerin halal | 800–3,000 | **Easy** | `/halal-ingredients/glycerin` |
| is gelatin in cosmetics halal | 300–1,000 | **Easy** | `/halal-ingredients/gelatin` |
| is collagen halal | 500–2,000 | **Easy** | `/halal-ingredients/collagen` |
| is alcohol in skincare halal | 400–1,500 | **Easy** | `/halal-ingredients/alcohol-denat` |
| is squalene halal / shark squalene | 200–800 | **Easy** | `/halal-ingredients/squalene` |
| is lanolin halal | 150–600 | **Easy** | `/halal-ingredients/lanolin` |
| is stearic acid halal | 200–800 | **Easy** | `/halal-ingredients/stearic-acid` |
| is keratin halal | 200–700 | **Easy** | `/halal-ingredients/keratin` |
| is shellac halal | 150–500 | **Easy** | `/halal-ingredients/shellac` |
| is hyaluronic acid halal | 150–600 | **Easy** | `/halal-ingredients/hyaluronic-acid` |
| is cochineal halal | 100–400 | **Easy** | canonical → `/halal-ingredients/carmine` |
| is retinol halal | 100–400 | **Easy** | `/halal-ingredients/retinol` |
| is beeswax halal | 200–800 | **Easy** | `/halal-ingredients/beeswax` |
| is cetyl alcohol halal | 100–400 | **Easy** | `/halal-ingredients/cetyl-alcohol` |
| is guanine halal | 50–200 | **Easy** | `/halal-ingredients/guanine` |
| is L-cysteine halal | 100–400 | **Easy** | `/halal-ingredients/l-cysteine` |
| is tallow halal | 100–400 | **Easy** | `/halal-ingredients/tallow` |

**Tier B — decision/education queries:**

| Keyword | Est. volume | Difficulty | Target URL |
|---|---|---|---|
| haram ingredients in makeup | 500–2,000 | **Easy–Medium** | `/blog/haram-ingredients-in-makeup` |
| which cosmetic brands use haram ingredients | 200–800 | **Easy** | `/blog/cosmetic-brands-haram-ingredients` |
| what is halal makeup | 800–2,500 | **Medium** | `/blog/what-is-halal-makeup` |
| is nail polish halal | 1,000–4,000 | **Medium** | `/blog/is-nail-polish-halal` |
| can you pray with nail polish on | 500–2,000 | **Medium** | `/blog/is-nail-polish-halal` (section + FAQ) |
| does makeup break wudu | 300–1,200 | **Easy–Medium** | `/blog/does-makeup-break-wudu` |
| halal certification for cosmetics in pakistan | 100–400 | **Easy** | `/halal-certification` |
| how to check if makeup is halal | 300–1,000 | **Easy** | `/blog/how-to-check-if-makeup-is-halal` |
| difference between halal and vegan cosmetics | 200–800 | **Easy** | `/blog/halal-vs-vegan-cosmetics` |
| is korean skincare halal | 300–1,200 | **Easy–Medium** | `/blog/is-korean-skincare-halal` |
| halal skincare routine | 300–1,000 | **Medium** | `/blog/halal-skincare-routine` |
| e numbers in cosmetics halal | 100–400 | **Easy** | `/blog/e-numbers-cosmetics-halal` |

**Tier C — Urdu/Roman-Urdu (do not ignore; huge in PK):**
`halal makeup kya hai`, `nail polish halal hai ya haram`, `carmine halal hai ya haram`, `wudu me nail polish`. These currently return near-zero optimised results. **Plan:** Phase 2 — Urdu subdirectory (§7.4). Do **not** create Roman-Urdu duplicate English pages; that creates cannibalisation with no upside.

### 1.6 Near-term achievable wins (the 90-day list)

Ranked by (achievability × commercial pull-through):

1. `/halal-ingredients/carmine` + `/halal-ingredients/glycerin` + `/halal-ingredients/collagen`
2. `/blog/haram-ingredients-in-makeup` (hub for the whole cluster)
3. `/blog/is-nail-polish-halal` → converts directly into nail polish product
4. `/blog/halal-makeup-brands-pakistan` (out-ranks a salon blog and a clinic blog — very achievable)
5. `/shop/nail-polish` for "wudu friendly nail polish pakistan" (tiny SERP, near-zero competition)
6. `/halal-certification` for "halal certification cosmetics pakistan" (PSQCA/SANHA citations = instant topical authority)
7. Brand queries: "glow halal", "glowhalal", "glow halal pakistan" — must be locked by week 2.

### 1.7 Cannibalisation control (mandatory, pre-emptive)

There is no Search Console data yet, so the standard GSC page+query audit is impossible. Use the **pre-GSC method**: a keyword-ownership registry, enforced in code.

**MUST:** create `config/seo_keywords.php` (or a `seo_keyword_map` DB table) that stores, for every URL, a single `primary_keyword` and an array of `secondary_keywords`. Add a CI test that fails the build if any `primary_keyword` appears on two URLs.

Ownership boundaries that are already at risk — resolve now, not later:

| Query | Owner | Explicitly NOT allowed to target it |
|---|---|---|
| halal makeup pakistan | `/shop` | homepage `/`, `/about`, any blog post |
| what is halal makeup | `/blog/what-is-halal-makeup` | `/about`, `/`, `/shop` |
| is nail polish halal | `/blog/is-nail-polish-halal` | `/shop/nail-polish`, `/halal-ingredients/shellac` |
| wudu friendly nail polish pakistan | `/shop/nail-polish` | `/blog/is-nail-polish-halal` |
| is carmine halal | `/halal-ingredients/carmine` | `/blog/haram-ingredients-in-makeup` (must link out, not compete) |
| haram ingredients in makeup | `/blog/haram-ingredients-in-makeup` | every individual ingredient page |
| halal certification pakistan | `/halal-certification` | `/about` |
| glow halal (brand) | `/` | `/about` |

**The two structural cannibalisation traps in this build:**

- **Homepage vs `/shop`.** With 3 products, the temptation is to make `/` the product listing. Do not. Homepage `<title>`/H1 owns the **brand + category positioning** ("Halal Certified Makeup & Skincare in Pakistan"); `/shop` owns **"buy/shop halal makeup Pakistan"**. Different primary keywords, enforced in the registry.
- **Ingredient hub vs ingredient pages.** `/blog/haram-ingredients-in-makeup` must be a *router*: 100–150 words per ingredient, then "Full analysis →" linking to `/halal-ingredients/{slug}`. It must never contain the full analysis, or it eats its own children.

**Post-launch (from week 4):** run the standard GSC page+query audit monthly (dimensions `page` + `query`). Any query where two URLs both sit in the top 20 with split clicks = active cannibalisation → assign the owner (most clicks wins), de-optimise the loser's title/H1, and add an internal link from loser → owner using the query as anchor.

---

## 2. URL architecture

### 2.1 Global rules (MUST)

| Rule | Value |
|---|---|
| Scheme | HTTPS only. HSTS `max-age=31536000; includeSubDomains; preload` |
| Host | Apex: `glowhalal.com`. `www.glowhalal.com` → **301** → apex. Pick one and never change it. |
| Trailing slash | **No trailing slash** on every URL except `/`. `/shop/` → **301** → `/shop` |
| Case | Lowercase only. Any uppercase path → **301** → lowercase |
| Separator | Hyphens. Never underscores, never `%20` |
| Slug charset | `[a-z0-9-]` only. Transliterate Urdu/Arabic to ASCII in slugs |
| IDs in URLs | Never. Slug-only, unique-indexed, with a `redirects` table for slug history |
| Locale prefix | None at launch (implicit `en-PK` at root). Urdu later at `/ur/…` — see §7.4 |
| Index document | `/` only. No `/home`, no `/index.php` (301 if hit) |

**MUST:** implement one global middleware (`CanonicalUrlMiddleware`) that enforces host, scheme, lowercase, and trailing-slash rules with a single 301 — never chained. Redirect chains > 1 hop fail launch review.

### 2.2 URL map

```
/                                      Homepage
/shop                                  All products (paginated)
/shop/{category}                       Category            e.g. /shop/lipstick
/shop/{category}/{subcategory}         Sub-category        e.g. /shop/skincare/serums
/shop/{curated-facet}                  Curated facet page  e.g. /shop/alcohol-free  (LATER, editorially approved only)
/product/{product-slug}                Product detail      e.g. /product/glow-halal-matte-lipstick-rosewater-nude

/blog                                  Blog index (paginated)
/blog/{post-slug}                      Blog post           e.g. /blog/haram-ingredients-in-makeup
/blog/category/{category-slug}          Blog category       e.g. /blog/category/ingredients
/blog/tag/{tag-slug}                   Blog tag            (noindex,follow)
/blog/author/{author-slug}             Author archive      (index — E-E-A-T asset, see §6.4)

/halal-ingredients                     Ingredient glossary index
/halal-ingredients/{ingredient-slug}   Ingredient page     e.g. /halal-ingredients/carmine

/about                                 About Us
/contact                               Contact Us
/halal-certification                   Certification & sourcing (trust hub)
/faq                                   FAQ
/shipping-and-returns                  Policy
/returns-policy                        Policy (or merge into above)
/privacy-policy                        Policy
/terms-and-conditions                  Policy
/editorial-policy                      Editorial standards (E-E-A-T, links from Article schema)

/search?q={query}                      Site search        (noindex + Disallow)
/cart                                  Cart               (noindex + Disallow)
/checkout, /checkout/*                 Checkout           (noindex + Disallow)
/account, /account/*                   Customer account   (noindex + Disallow, auth)
/login /register /password/*           Auth               (noindex + Disallow)
```

**Design decisions and why:**

- **`/product/{slug}` is flat — no category in the path.** A product will belong to multiple categories over time ("lipstick" + "alcohol-free" + "vegan"). Nesting the category forces a 301 every recategorisation and creates duplicate paths to one product. Flat product URLs are the only architecture that survives catalog growth. Breadcrumbs (schema + UI) carry the hierarchy instead.
- **`/blog/category/{slug}` uses a reserved `category` segment.** Without it, `/blog/{slug}` and `/blog/{category}` collide the moment a post slug matches a category slug. Register `category`, `tag`, `author`, `page` as reserved slugs and block them at model-validation level.
- **`/halal-ingredients/*` is a separate tree from `/blog/*`.** These are evergreen reference documents, not dated posts. Separating them (a) gives a clean, semantically obvious path, (b) makes a dedicated sitemap trivial, (c) lets them use a different schema type and template, (d) signals a *reference resource* — which is what earns links and AI-overview citations.
- **`/shop/{category}` max depth is 2.** Every product is ≤3 clicks from the homepage. No deeper nesting, ever.

### 2.3 Pagination

**Pattern:** `?page={n}`. Page 1 MUST be the bare URL — `/shop?page=1` **301 → /shop**.

| Aspect | Rule |
|---|---|
| Canonical | **Self-referencing.** `/shop?page=3` canonicals to `/shop?page=3`. Never canonical page N → page 1 (Google treats that as a soft-404 signal and drops deep products from the index) |
| Indexability | `index,follow` on all pages — this is how deep products get discovered |
| `rel="next"/"prev"` | Emit them. Google ignores them; Bing does not. Zero cost |
| Titles | Page 1: `{Base Title}`. Page ≥2: `{Base Title} - Page {n}` |
| Meta description | Page ≥2: append ` Page {n} of {total}.` — prevents duplicate-description warnings |
| H1 | **Identical on every page.** Do not append "Page 2" to the H1 |
| Intro copy | Category intro copy renders on **page 1 only**. On page ≥2, suppress it |
| Links | Pagination links MUST be real `<a href>` elements. Never `wire:click`-only. See §8 |
| "Load more" | Allowed as *progressive enhancement only*, layered on top of real paginated `<a href>` links |

### 2.4 Filtering and faceted navigation — the crawl trap, killed before it exists

With 3 products this looks like a non-problem. It becomes a catastrophic one at 300 products. Build the guard rails now.

**Parameter policy:**

| Parameter | Purpose | Robots | Canonical target |
|---|---|---|---|
| `page` | Pagination | `index,follow` | self |
| *(none)* | Clean category | `index,follow` | self |
| `sort` | Ordering | **`noindex,follow`** | clean category URL |
| `price_min`, `price_max` | Price range | **`noindex,follow`** | clean category URL |
| `color`, `shade`, `finish`, `size` | Attribute facets | **`noindex,follow`** | clean category URL |
| `brand` | Brand facet | **`noindex,follow`** at launch; promote to a curated URL when a brand earns it | clean category URL |
| `view`, `per_page`, `layout` | Display prefs | **`noindex,follow`** | clean category URL |
| `q` | Search | **`noindex,follow`** + robots.txt Disallow | none (noindex is enough) |
| `utm_*`, `gclid`, `fbclid`, `ref` | Tracking | inherit page's robots | clean URL without tracking params |

**Implementation rules (MUST):**

1. **One helper decides everything.** A single `SeoMeta` service computes `robots` + `canonical` from the current route + query bag. No per-view ad-hoc logic. One place to audit, one place to fix.
2. **Canonical strips all non-canonical params.** Whitelist approach: only `page` survives into a canonical URL. Everything else is dropped.
3. **Facet links get `rel="nofollow"` plus the `noindex` on the destination.** Belt and braces — `nofollow` conserves crawl budget, `noindex` handles the case where Google finds the URL anyway.
4. **Never allow multi-facet combination URLs to be linked.** If `?color=red&finish=matte` is reachable by clicking two checkboxes, the crawlable combination space is `2^n`. Rule: **facet state lives in the query string for users, but facet *links* in the DOM are single-dimension only**, and any URL with **≥2 facet params** gets `noindex,nofollow` (not `follow` — cut the crawl path entirely).
5. **Parameters MUST be alphabetically sorted and deduplicated** before rendering, so `?a=1&b=2` and `?b=2&a=1` never both exist.
6. **`robots.txt` `Disallow` is a backstop, not the primary control** — a Disallowed URL can still be indexed URL-only. `noindex` + canonical is the primary control (see §7.2 for the exact interplay).
7. **Curated facet pages (LATER).** When a facet has genuine, distinct search demand (`alcohol free makeup pakistan`, `wudu friendly nail polish`), promote it to a **static, editorially-owned URL** — `/shop/alcohol-free` — with unique H1, unique 200+ word intro, unique title/meta, its own entry in the keyword registry, and its own sitemap entry. Promotion requires: (a) validated search volume, (b) ≥8 products in the facet, (c) SEO sign-off. Never auto-generate these.

### 2.5 Canonical rules — complete table

| Page type | Canonical |
|---|---|
| `/` | `https://glowhalal.com/` (absolute, with trailing slash on root only) |
| `/shop` | self |
| `/shop?page=n` (n≥2) | self, **including** `?page=n` |
| `/shop?sort=…` or any facet | `https://glowhalal.com/shop` (params stripped) |
| `/shop/{category}` | self |
| `/shop/{category}?page=n` | self incl. `?page=n` |
| `/product/{slug}` | self |
| `/product/{slug}?variant=…` | `/product/{slug}` — **unless** variants have distinct URLs (§2.6) |
| Out-of-stock product | self, still `index,follow` (see §2.6) |
| Discontinued product | 301 → closest category, or 410 if no equivalent |
| `/blog`, `/blog?page=n` | self |
| `/blog/{post}` | self |
| `/blog/category/{slug}` | self |
| `/blog/tag/{slug}` | self + `noindex,follow` |
| `/halal-ingredients/{slug}` | self |
| Static pages | self |
| Any URL with tracking params | clean URL, params stripped |
| `/search?q=…` | omit canonical entirely; rely on `noindex` |

**MUST:** every canonical is **absolute** (`https://glowhalal.com/...`), rendered server-side in the initial HTML `<head>`, exactly one `<link rel="canonical">` per document. Add an automated test asserting `count(canonical tags) === 1` on every route.

### 2.6 Product variant and lifecycle rules

- **Shade/colour variants of one product:** one canonical product URL, variants selectable in-page. Emit a single `Product` with `hasVariant` / `offers` as an `AggregateOffer`. Do **not** create a URL per shade at 3 products — revisit only when a shade has independent search demand.
- **Out of stock:** keep the URL live, `index,follow`, `availability: OutOfStock`, show restock-notify form. Never 404 or noindex an out-of-stock product — you lose accumulated ranking and re-earn it from scratch.
- **Permanently discontinued:** 301 to the nearest live equivalent product; if none, 301 to the parent category; if the category is gone too, return **410 Gone** (not 404 — 410 de-indexes faster).
- **Slug changes:** write the old slug to a `redirects` table and 301. Never let a slug change produce a 404.

---

## 3. On-page templates

### 3.1 Global constraints

- **Title:** target 50–60 characters (~575px). Hard cap 60. Brand suffix ` | Glow Halal` **only if** it fits inside 60 — otherwise drop it (except on `/`, `/about`, `/contact` where brand matters).
- **Meta description:** 140–160 characters. Every page unique. Never auto-generated from the first N characters of body copy — write them, or leave blank and let Google compose (a blank description beats a truncated duplicate).
- **Primary keyword** appears in: title (as far left as reads naturally), H1, first 100 words, and at least one H2.
- **No keyword stuffing.** One primary keyword per page, 2–4 secondary.
- **Every title and description is stored on the model** (`meta_title`, `meta_description` nullable columns) with the formula as fallback. Editors override; formula covers the gap.

### 3.2 Title tag formulas

| Page | Formula | Example (≤60 chars) |
|---|---|---|
| Homepage | `Halal Certified Makeup & Skincare in Pakistan \| Glow Halal` | as written (57) |
| `/shop` | `Shop Halal Makeup & Skincare Online in Pakistan` | as written (46) |
| `/shop` page ≥2 | `Shop Halal Makeup & Skincare in Pakistan - Page {n}` | — |
| Category | `{Category} - Halal & Alcohol-Free \| Glow Halal` | `Lipstick - Halal & Alcohol-Free \| Glow Halal` (44) |
| Category (alt, when volume supports it) | `Halal {Category} in Pakistan \| Glow Halal` | `Halal Nail Polish in Pakistan \| Glow Halal` (42) |
| Sub-category | `{Subcategory} - Halal {Parent} \| Glow Halal` | — |
| Curated facet | `{Facet} {Category} in Pakistan \| Glow Halal` | `Alcohol-Free Foundation in Pakistan \| Glow Halal` |
| Product | `{Product Name} - {Key Attribute} \| Glow Halal` | `Matte Lipstick Rosewater Nude - Halal Certified \| Glow Halal` → trim to `Matte Lipstick Rosewater Nude - Halal \| Glow Halal` |
| Product (price-intent variant) | `{Product Name} Price in Pakistan \| Glow Halal` | use when `{product} price in pakistan` has validated volume |
| Blog index | `Halal Beauty Blog - Ingredients, Guides & Reviews` | (48) |
| Blog post | `{Post Title}` (write titles ≤60 natively; append ` \| Glow Halal` only if it fits) | `Haram Ingredients in Makeup: The Full List (2026)` (49) |
| Blog category | `{Category} Articles - Halal Beauty \| Glow Halal` | — |
| Author archive | `{Author Name} - Halal Beauty Writer \| Glow Halal` | — |
| Ingredient index | `Halal Cosmetic Ingredients A-Z: Is It Halal?` | (44) |
| Ingredient page | `Is {Ingredient} Halal? Sources, Status & Alternatives` | `Is Carmine Halal? Sources, Status & Alternatives` (48) |
| About | `About Glow Halal - Pakistan's Halal Beauty Store` | (48) |
| Contact | `Contact Glow Halal - Support, WhatsApp & Orders` | (47) |
| Certification | `Our Halal Certification & Ingredient Standards` | (46) |
| FAQ | `Halal Beauty FAQs - Certification, Wudu & Shipping` | (50) |
| 404 | `Page Not Found \| Glow Halal` |  |

**Title anti-patterns (MUST NOT):** `Home | Glow Halal`, `Products`, `Blog`, any title used on two URLs, the brand name first on non-brand pages, `»` / `–` inconsistency (standardise on ` | ` for brand and ` - ` for modifiers).

### 3.3 Meta description formulas

| Page | Formula |
|---|---|
| Homepage | `Shop halal certified, alcohol-free makeup and skincare in Pakistan. Verified ingredients, wudu-friendly formulas, nationwide delivery. Free shipping over Rs {threshold}.` |
| `/shop` | `Browse every halal certified cosmetic at Glow Halal — alcohol-free, carmine-free, cruelty-free. Full ingredient disclosure on every product. Delivered across Pakistan.` |
| Category | `Shop halal {category} in Pakistan — alcohol-free and certified, with every ingredient listed. {count} products, cash on delivery, {city}-wide dispatch.` |
| Product | `{Product Name} — halal certified, {key attributes}. Rs {price}. Full ingredient list, {certifier} certified, delivered across Pakistan. {stock_phrase}` |
| Blog post | Hand-written per post. Must contain the primary keyword and a specific promise ("the full list of 14 ingredients", "what 3 scholars actually say"). |
| Ingredient page | `Is {ingredient} halal? {Ingredient} is derived from {sources}. Here's the ruling, how to spot it on an INCI list, and the halal alternatives.` |
| About | `Glow Halal is a Pakistani halal beauty brand built on ingredient transparency. Meet the founder, see our certification process, and read our sourcing standards.` |
| Contact | `Contact Glow Halal — WhatsApp {number}, email {email}, or use the form. Order support, ingredient questions and wholesale enquiries answered within {sla}.` |

### 3.4 Heading hierarchy rules

**MUST:**
- Exactly **one `<h1>` per page.** Enforce with an automated test.
- The logo in the header is **never** an `<h1>` — use `<a><img alt="Glow Halal"></a>`.
- No heading level skipping (`h2` → `h4` is a fail).
- Headings are for structure, not styling. Tailwind classes handle appearance; never pick `<h3>` because it "looks right."
- Product card titles in a grid are `<h2>` or `<h3>` — never `<h1>`.

**Per page type:**

| Page | H1 | H2s |
|---|---|---|
| `/` | `Halal Certified Makeup & Skincare, Made for Pakistan` | Shop by category · Why halal certified matters · Our ingredient promise · Latest from the blog · What our customers say |
| `/shop` | `Shop All Halal Makeup & Skincare` | Filter/sort (visually hidden `h2`) · Categories · About our halal standard |
| Category | `Halal {Category} in Pakistan` | Our {category} range · What makes a {category} halal · How to choose · {Category} FAQs |
| Product | `{Product Name}` (exact product name — **not** keyword-stuffed) | Description · Full ingredient list (INCI) · Halal certification · How to use · Shipping & returns · Reviews · Related products |
| `/blog` | `Halal Beauty Blog` | Latest articles · Browse by topic · Ingredient guides |
| Blog post | The article's headline (matches, or nearly matches, the `<title>`) | Structured to answer the query + its People Also Ask set |
| Ingredient page | `Is {Ingredient} Halal?` | Quick answer · What is {ingredient}? · Where it comes from · The halal ruling · How to spot it on a label · Halal alternatives · Products at Glow Halal without {ingredient} · FAQs |
| `/about` | `About Glow Halal` | see §6.1 |
| `/contact` | `Contact Glow Halal` | see §6.2 |

**Featured-snippet formatting rules (MUST for all informational pages):**
- Open with a **40–55 word direct answer** immediately under the H1, before any preamble. This is the paragraph-snippet target.
- Use `<table>` for comparisons (table snippets), `<ol>` for processes, `<ul>` for lists (list snippets).
- Every H2 that is phrased as a question gets a **≤300-character** direct answer in the very next paragraph.
- Mine People Also Ask on the live SERP for each target keyword and make each PAA question an H2 or an FAQ item.

### 3.5 Internal linking

The hardest constraint: this must work with 3 products **and** with 3,000.

**Fixed structural links (always present):**

| From | To | Anchor |
|---|---|---|
| Header nav | `/shop`, top 5 categories, `/blog`, `/halal-ingredients`, `/about`, `/contact` | Descriptive, keyword-aligned. **Never "Products" — use "Shop Halal Makeup"** |
| Footer | All static pages, `/halal-certification`, `/editorial-policy`, top categories, top 5 ingredient pages | Exact-match-ish but natural |
| Breadcrumbs | Every non-homepage page | Category/parent names |
| Homepage body | Every category, top 3 blog posts, `/halal-certification` | Descriptive |

**Cluster links (the engine):**

```
                 /blog/haram-ingredients-in-makeup   ← PILLAR (hub)
                    ↓ links down to each             ↑ every child links back up
   /halal-ingredients/carmine ─┬─ /halal-ingredients/glycerin ─┬─ /halal-ingredients/collagen …
                               │                              │
                               └──── sibling links (2–3) ─────┘
                               ↓
                    /shop/lipstick  (commercial handoff)
                               ↓
                    /product/{carmine-free-lipstick}
```

Rules:
1. **Every ingredient page links UP to the pillar** with anchor "the full list of haram ingredients in makeup".
2. **Every ingredient page links SIDEWAYS to 2–3 related ingredients** ("Also check: is glycerin halal?").
3. **Every ingredient page links DOWN to commerce**, in a dedicated section: *"Glow Halal products made without {ingredient}"* — with a live product listing. **This is the conversion mechanism for the whole strategy.** With 3 products, that section shows all 3. With 300, it shows a filtered set. Same component, both scales.
4. **Every product page links to the ingredient pages for its own key ingredients** from within the INCI list — each INCI entry that has a glossary page becomes a link. This is a huge, automatic, scale-free internal link source, and it is genuinely useful to the shopper.
5. **Every blog post links to ≥1 category page and ≥1 product** contextually, in body copy, not just a sidebar.
6. **Every category page links to ≥2 relevant blog posts** in a "Learn about halal {category}" block.

**Anchor text rules (MUST):**
- Descriptive and varied. Never "click here", "read more", "this article" as the sole anchor.
- Never use the exact same anchor text pointing to two different URLs sitewide.
- Never use a keyword owned by page A as the anchor for a link to page B (that is cannibalisation by internal link).
- Cap in-body internal links at ~1 per 100 words.

**Thin-catalog specifics (launch state, 3 products):**
- **MUST NOT** create empty category pages. A category with 0 products returns **404**, not an empty listing. A category with 1–2 products is allowed **only if** it carries ≥300 words of unique editorial ("What makes a lipstick halal", "How we source our pigments"). Otherwise, don't create it.
- Launch with **at most 2–3 categories.** Three products spread across six categories = six thin pages = a Helpful Content liability.
- **`/halal-ingredients` and `/blog` carry the site at launch.** Give them primary header nav positions, not buried footer links.
- **Orphan check:** an automated test asserts every URL in `sitemap.xml` has ≥1 internal inbound link from another indexable page. Zero orphans, ever.

**Scale-proofing:** internal links from ingredient → product must be **query-driven, not hardcoded** (`Product::whereDoesntHave('ingredients', fn($q) => $q->where('slug', $ingredient->slug))->limit(4)`). At 3 products it returns 3; at 3,000 it returns the 4 best. Never hardcode product IDs into content.

---

## 4. Structured data

### 4.1 Implementation approach (MUST)

- **JSON-LD only.** No Microdata, no RDFa.
- **One `<script type="application/ld+json">` per page containing a single `@graph` array.** Multiple competing scripts cause node-duplication and validation noise.
- Nodes are cross-referenced by `@id` so Google resolves one coherent entity graph.
- Render it in the **`<head>`** of the server response — but see §8.4 for the Livewire `wire:navigate` caveat and the body-placement escape hatch.
- **Escape everything**: use `json_encode($graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)`. Never string-concatenate JSON-LD in Blade.
- **Every structured-data claim must be visible on the page.** Price in JSON-LD must equal the price the user sees. Ratings in JSON-LD must correspond to reviews rendered on the page. Violating this is a manual-action risk.
- Validate every template against the [Rich Results Test](https://search.google.com/test/rich-results) and [Schema Markup Validator](https://validator.schema.org/) before launch, and add a CI smoke test that asserts each route's JSON-LD parses.

Build one `Schema` service with a method per node type; Blade never assembles JSON by hand.

### 4.2 Organization — sitewide (in `@graph` on every page)

```json
{
  "@type": "OnlineStore",
  "@id": "https://glowhalal.com/#organization",
  "name": "Glow Halal",
  "alternateName": ["GlowHalal", "Glow Halal Cosmetics"],
  "legalName": "{Registered Legal Entity Name}",
  "url": "https://glowhalal.com/",
  "logo": {
    "@type": "ImageObject",
    "@id": "https://glowhalal.com/#logo",
    "url": "https://glowhalal.com/images/brand/glow-halal-logo.png",
    "contentUrl": "https://glowhalal.com/images/brand/glow-halal-logo.png",
    "width": 512,
    "height": 512,
    "caption": "Glow Halal"
  },
  "image": { "@id": "https://glowhalal.com/#logo" },
  "description": "Glow Halal is a Pakistani halal beauty brand selling halal certified, alcohol-free makeup and skincare with full ingredient disclosure on every product.",
  "slogan": "Beauty you can verify.",
  "foundingDate": "2026",
  "founder": {
    "@type": "Person",
    "@id": "https://glowhalal.com/about#founder",
    "name": "{Founder Full Name}",
    "jobTitle": "Founder",
    "image": "https://glowhalal.com/images/team/{founder-slug}.jpg"
  },
  "email": "{support@glowhalal.com}",
  "telephone": "+92-{XXX}-{XXXXXXX}",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "{Street Address}",
    "addressLocality": "{City}",
    "addressRegion": "{Province}",
    "postalCode": "{Postal Code}",
    "addressCountry": "PK"
  },
  "areaServed": { "@type": "Country", "name": "Pakistan" },
  "currenciesAccepted": "PKR",
  "paymentAccepted": "Cash on Delivery, Debit Card, Credit Card, Bank Transfer, JazzCash, Easypaisa",
  "vatID": "{NTN if applicable}",
  "knowsAbout": [
    "Halal cosmetics",
    "Halal certification",
    "Alcohol-free skincare",
    "Wudu-friendly nail polish",
    "Cosmetic ingredient sourcing"
  ],
  "publishingPrinciples": "https://glowhalal.com/editorial-policy",
  "contactPoint": [
    {
      "@type": "ContactPoint",
      "@id": "https://glowhalal.com/contact#customer-support",
      "contactType": "customer support",
      "telephone": "+92-{XXX}-{XXXXXXX}",
      "email": "{support@glowhalal.com}",
      "areaServed": "PK",
      "availableLanguage": ["en", "ur"],
      "hoursAvailable": {
        "@type": "OpeningHoursSpecification",
        "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],
        "opens": "10:00",
        "closes": "19:00"
      }
    },
    {
      "@type": "ContactPoint",
      "@id": "https://glowhalal.com/contact#wholesale",
      "contactType": "sales",
      "email": "{wholesale@glowhalal.com}",
      "areaServed": "PK",
      "availableLanguage": ["en", "ur"]
    }
  ],
  "sameAs": [
    "https://www.instagram.com/{handle}",
    "https://www.facebook.com/{handle}",
    "https://www.tiktok.com/@{handle}",
    "https://www.youtube.com/@{handle}",
    "https://www.linkedin.com/company/{handle}",
    "https://www.pinterest.com/{handle}"
  ],
  "hasMerchantReturnPolicy": { "@id": "https://glowhalal.com/returns-policy#policy" }
}
```

Notes:
- `OnlineStore` is a valid `Organization` subtype and is the most accurate type for a pure e-commerce brand. If any validator complains, fall back to `"@type": ["Organization", "OnlineStore"]`.
- `sameAs` MUST only list profiles that actually exist and actually link back. Listing dead profiles is a trust negative.
- Fill `legalName`, `address`, `vatID` before launch — an incomplete Organization node is a weak entity signal for a new brand, and entity strength is the main lever a zero-authority domain has.

### 4.3 WebSite + SearchAction — sitewide

```json
{
  "@type": "WebSite",
  "@id": "https://glowhalal.com/#website",
  "url": "https://glowhalal.com/",
  "name": "Glow Halal",
  "description": "Halal certified, alcohol-free makeup and skincare in Pakistan, with full ingredient disclosure.",
  "publisher": { "@id": "https://glowhalal.com/#organization" },
  "inLanguage": "en-PK",
  "copyrightHolder": { "@id": "https://glowhalal.com/#organization" },
  "potentialAction": {
    "@type": "SearchAction",
    "target": {
      "@type": "EntryPoint",
      "urlTemplate": "https://glowhalal.com/search?q={search_term_string}"
    },
    "query-input": "required name=search_term_string"
  }
}
```

**MUST:** `/search?q=` has to be a real, working, server-rendered GET endpoint returning results, even though it's `noindex`. Google verifies the sitelinks searchbox works. A Livewire-only search with no GET route makes this markup a lie.

### 4.4 WebPage node — every page

```json
{
  "@type": "WebPage",
  "@id": "{CANONICAL_URL}#webpage",
  "url": "{CANONICAL_URL}",
  "name": "{TITLE_TAG}",
  "description": "{META_DESCRIPTION}",
  "isPartOf": { "@id": "https://glowhalal.com/#website" },
  "about": { "@id": "https://glowhalal.com/#organization" },
  "primaryImageOfPage": { "@id": "{CANONICAL_URL}#primaryimage" },
  "datePublished": "{ISO8601}",
  "dateModified": "{ISO8601}",
  "inLanguage": "en-PK",
  "breadcrumb": { "@id": "{CANONICAL_URL}#breadcrumb" },
  "potentialAction": {
    "@type": "ReadAction",
    "target": ["{CANONICAL_URL}"]
  }
}
```

Swap `@type` per page: `CollectionPage` for `/shop` and categories, `ItemPage` for products, `AboutPage` for `/about`, `ContactPage` for `/contact`, `FAQPage` for `/faq`, `Blog` for `/blog`.

### 4.5 BreadcrumbList — every page except `/`

```json
{
  "@type": "BreadcrumbList",
  "@id": "{CANONICAL_URL}#breadcrumb",
  "itemListElement": [
    { "@type": "ListItem", "position": 1, "name": "Home",     "item": "https://glowhalal.com/" },
    { "@type": "ListItem", "position": 2, "name": "Shop",     "item": "https://glowhalal.com/shop" },
    { "@type": "ListItem", "position": 3, "name": "Lipstick", "item": "https://glowhalal.com/shop/lipstick" },
    { "@type": "ListItem", "position": 4, "name": "Matte Lipstick Rosewater Nude" }
  ]
}
```

Rules: `position` starts at 1 with Home. The **final item omits `item`** (it is the current page). Breadcrumbs MUST also be rendered as visible `<nav aria-label="Breadcrumb">` HTML with real `<a href>` — schema without visible breadcrumbs is a mismatch. Blog posts use `Home > Blog > {Category} > {Post}`; ingredient pages use `Home > Halal Ingredients > {Ingredient}`.

### 4.6 Product — product detail pages

```json
{
  "@type": "Product",
  "@id": "https://glowhalal.com/product/{slug}#product",
  "name": "{Product Name}",
  "url": "https://glowhalal.com/product/{slug}",
  "description": "{120-300 char product description, same as visible copy}",
  "sku": "{SKU}",
  "mpn": "{MPN}",
  "gtin13": "{GTIN if barcoded}",
  "image": [
    "https://glowhalal.com/storage/products/{slug}-1x1.jpg",
    "https://glowhalal.com/storage/products/{slug}-4x3.jpg",
    "https://glowhalal.com/storage/products/{slug}-16x9.jpg"
  ],
  "brand": { "@type": "Brand", "name": "Glow Halal" },
  "manufacturer": { "@id": "https://glowhalal.com/#organization" },
  "category": "{Category Name}",
  "color": "{Shade}",
  "material": "{Base, e.g. mineral pigment}",
  "countryOfOrigin": { "@type": "Country", "name": "Pakistan" },
  "hasCertification": {
    "@type": "Certification",
    "name": "Halal Certification",
    "issuedBy": {
      "@type": "Organization",
      "name": "{SANHA Pakistan | PSQCA-recognised body}",
      "url": "{certifier URL}"
    },
    "certificationIdentification": "{Certificate No.}",
    "certificationStatus": "CertificationActive",
    "datePublished": "{ISO8601}",
    "expires": "{ISO8601}"
  },
  "additionalProperty": [
    { "@type": "PropertyValue", "name": "Halal Certified",   "value": "Yes" },
    { "@type": "PropertyValue", "name": "Alcohol Free",      "value": "Yes" },
    { "@type": "PropertyValue", "name": "Carmine Free",      "value": "Yes" },
    { "@type": "PropertyValue", "name": "Wudu Friendly",     "value": "{Yes|Not applicable}" },
    { "@type": "PropertyValue", "name": "Cruelty Free",      "value": "Yes" },
    { "@type": "PropertyValue", "name": "Shelf Life",        "value": "{24 months}" },
    { "@type": "PropertyValue", "name": "Net Weight",        "value": "{3.5 g}" }
  ],
  "offers": {
    "@type": "Offer",
    "@id": "https://glowhalal.com/product/{slug}#offer",
    "url": "https://glowhalal.com/product/{slug}",
    "price": "{1499.00}",
    "priceCurrency": "PKR",
    "priceValidUntil": "{YYYY-12-31}",
    "availability": "https://schema.org/InStock",
    "itemCondition": "https://schema.org/NewCondition",
    "seller": { "@id": "https://glowhalal.com/#organization" },
    "hasMerchantReturnPolicy": { "@id": "https://glowhalal.com/returns-policy#policy" },
    "shippingDetails": { "@id": "https://glowhalal.com/shipping-and-returns#shipping" }
  }
}
```

**Supporting nodes (emit once in the graph, referenced by `@id`):**

```json
{
  "@type": "MerchantReturnPolicy",
  "@id": "https://glowhalal.com/returns-policy#policy",
  "applicableCountry": "PK",
  "returnPolicyCategory": "https://schema.org/MerchantReturnFiniteReturnWindow",
  "merchantReturnDays": 7,
  "returnMethod": "https://schema.org/ReturnByMail",
  "returnFees": "https://schema.org/FreeReturn",
  "refundType": "https://schema.org/FullRefund",
  "merchantReturnLink": "https://glowhalal.com/returns-policy"
}
```

```json
{
  "@type": "OfferShippingDetails",
  "@id": "https://glowhalal.com/shipping-and-returns#shipping",
  "shippingRate": {
    "@type": "MonetaryAmount",
    "value": "{200.00}",
    "currency": "PKR"
  },
  "shippingDestination": {
    "@type": "DefinedRegion",
    "addressCountry": "PK"
  },
  "deliveryTime": {
    "@type": "ShippingDeliveryTime",
    "handlingTime": { "@type": "QuantitativeValue", "minValue": 0, "maxValue": 1, "unitCode": "DAY" },
    "transitTime":  { "@type": "QuantitativeValue", "minValue": 2, "maxValue": 5, "unitCode": "DAY" }
  }
}
```

**`availability` mapping:** `InStock` · `OutOfStock` · `PreOrder` · `BackOrder` · `Discontinued`. Drive this from the live stock value — never hardcode `InStock`.

### 4.7 AggregateRating and Review — READ THIS BEFORE IMPLEMENTING

**MUST NOT emit `aggregateRating` until real, verifiable customer reviews exist and are rendered on the page.**

Emitting `aggregateRating` with seeded, self-authored, or placeholder values on a 3-product launch store is:
1. A direct violation of Google's structured data guidelines (self-serving reviews are ineligible for rich results),
2. A realistic **manual action** risk for a new domain with no trust buffer,
3. Actively harmful — a fake 5.0/5 with 3 reviews reads as fake to buyers too.

**The rule:** conditional rendering. Emit `aggregateRating` **only when `reviewCount >= 1`** *and* those reviews are visible on the page.

```json
{
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "{4.7}",
    "reviewCount": "{23}",
    "ratingCount": "{31}",
    "bestRating": "5",
    "worstRating": "1"
  },
  "review": [
    {
      "@type": "Review",
      "@id": "https://glowhalal.com/product/{slug}#review-{id}",
      "author": { "@type": "Person", "name": "{Reviewer Name}" },
      "datePublished": "{ISO8601}",
      "reviewRating": {
        "@type": "Rating",
        "ratingValue": "{5}",
        "bestRating": "5",
        "worstRating": "1"
      },
      "reviewBody": "{Verbatim review text}",
      "publisher": { "@id": "https://glowhalal.com/#organization" }
    }
  ]
}
```

**Launch plan for reviews:** ship the review capability from day one (schema + UI + a post-delivery review-request email at delivery + 7 days). Reviews will accumulate. Do not fake the head start — a Product node with no `aggregateRating` still earns a valid merchant listing rich result via `offers` alone.

### 4.8 ItemList — category and shop pages

```json
{
  "@type": "ItemList",
  "@id": "https://glowhalal.com/shop/{category}#itemlist",
  "name": "Halal {Category} in Pakistan",
  "numberOfItems": "{count_on_this_page}",
  "itemListOrder": "https://schema.org/ItemListOrderAscending",
  "itemListElement": [
    {
      "@type": "ListItem",
      "position": 1,
      "url": "https://glowhalal.com/product/{slug}"
    }
  ]
}
```

Use the **URL-only** form (not full nested Product objects) — it is Google's preferred summary-page pattern and keeps page weight down. `position` must be **absolute across pagination** (page 2 starts at position 13, not 1).

### 4.9 Article / BlogPosting — blog posts

```json
{
  "@type": "BlogPosting",
  "@id": "https://glowhalal.com/blog/{slug}#article",
  "isPartOf": { "@id": "https://glowhalal.com/blog/{slug}#webpage" },
  "mainEntityOfPage": { "@id": "https://glowhalal.com/blog/{slug}#webpage" },
  "headline": "{Post headline, max 110 chars}",
  "alternativeHeadline": "{Optional subhead}",
  "description": "{Meta description}",
  "articleSection": "{Blog Category Name}",
  "keywords": ["halal makeup", "haram ingredients", "carmine"],
  "wordCount": "{1850}",
  "datePublished": "{ISO8601 with timezone, e.g. 2026-08-09T10:00:00+05:00}",
  "dateModified": "{ISO8601 with timezone}",
  "inLanguage": "en-PK",
  "image": {
    "@type": "ImageObject",
    "@id": "https://glowhalal.com/blog/{slug}#primaryimage",
    "url": "https://glowhalal.com/storage/blog/{slug}-1200x630.jpg",
    "width": 1200,
    "height": 630,
    "caption": "{Descriptive caption}"
  },
  "author": {
    "@type": "Person",
    "@id": "https://glowhalal.com/blog/author/{author-slug}#person",
    "name": "{Author Full Name}",
    "url": "https://glowhalal.com/blog/author/{author-slug}",
    "jobTitle": "{Cosmetic Chemist | Halal Compliance Researcher | Beauty Editor}",
    "description": "{One-sentence credential statement}",
    "image": "https://glowhalal.com/images/authors/{author-slug}.jpg",
    "knowsAbout": ["Halal cosmetics", "Cosmetic ingredient sourcing", "INCI nomenclature"],
    "alumniOf": { "@type": "EducationalOrganization", "name": "{Institution}" },
    "sameAs": ["https://www.linkedin.com/in/{handle}"]
  },
  "reviewedBy": {
    "@type": "Person",
    "name": "{Reviewer Name}",
    "jobTitle": "{Islamic Scholar | Halal Auditor}",
    "description": "{Credentials}"
  },
  "publisher": { "@id": "https://glowhalal.com/#organization" },
  "copyrightHolder": { "@id": "https://glowhalal.com/#organization" },
  "citation": [
    {
      "@type": "CreativeWork",
      "name": "PS 5319-2014 General Guidelines for Halal Cosmetics and Personal Care Products",
      "publisher": { "@type": "Organization", "name": "Pakistan Standards and Quality Control Authority (PSQCA)" },
      "url": "https://www.psqca.com.pk/standardization/division/halaal-division/"
    }
  ],
  "speakable": {
    "@type": "SpeakableSpecification",
    "cssSelector": [".article-answer-box", "h1"]
  }
}
```

`headline` MUST be ≤110 characters — Google truncates and may drop the rich result above that. `dateModified` MUST be genuine (actual content edit), never bumped by a cron job — that is a spam pattern.

### 4.10 Ingredient pages — hybrid Article + DefinedTerm

Ingredient pages are reference documents, not news. Use `Article` for eligibility **plus** a `DefinedTerm` node so the ingredient is understood as an entity.

```json
[
  {
    "@type": "Article",
    "@id": "https://glowhalal.com/halal-ingredients/{slug}#article",
    "headline": "Is {Ingredient} Halal? Sources, Status & Alternatives",
    "mainEntityOfPage": { "@id": "https://glowhalal.com/halal-ingredients/{slug}#webpage" },
    "about": { "@id": "https://glowhalal.com/halal-ingredients/{slug}#term" },
    "author": { "@id": "https://glowhalal.com/blog/author/{author-slug}#person" },
    "publisher": { "@id": "https://glowhalal.com/#organization" },
    "datePublished": "{ISO8601}",
    "dateModified": "{ISO8601}",
    "inLanguage": "en-PK"
  },
  {
    "@type": "DefinedTerm",
    "@id": "https://glowhalal.com/halal-ingredients/{slug}#term",
    "name": "{Ingredient Name}",
    "alternateName": ["{INCI name}", "{CI number}", "{Common name}"],
    "description": "{One-paragraph definition, matching visible copy}",
    "termCode": "{CI 75470 / E120}",
    "inDefinedTermSet": {
      "@type": "DefinedTermSet",
      "@id": "https://glowhalal.com/halal-ingredients#termset",
      "name": "Glow Halal Cosmetic Ingredient Glossary",
      "url": "https://glowhalal.com/halal-ingredients"
    },
    "sameAs": ["https://en.wikipedia.org/wiki/{Ingredient}"]
  }
]
```

The `sameAs` → Wikipedia link is deliberate: it anchors the page to a known entity in Google's Knowledge Graph, which materially helps a zero-authority domain get understood.

### 4.11 FAQPage

**Honest expectation:** since Google's August 2023 change, FAQ rich results are largely restricted to well-known authoritative government and health sites. **Glow Halal will most likely not get FAQ rich snippets.** Implement it anyway, because it still (a) helps Google parse the Q&A structure for PAA eligibility, (b) is a strong input to AI Overviews and AI-assistant citation, (c) costs nothing.

```json
{
  "@type": "FAQPage",
  "@id": "{CANONICAL_URL}#faq",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "Is carmine halal?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Carmine (CI 75470, also listed as cochineal or E120) is a red pigment extracted from the cochineal insect. The majority scholarly position treats insects other than locusts as impermissible, so carmine is generally classified as haram in cosmetics. Halal alternatives include iron oxides, synthetic organic pigments and beetroot-derived colourants."
      }
    }
  ]
}
```

Rules: only mark up Q&As that are **visibly rendered** on that page. Never put the same Q&A block on multiple URLs. Only one `FAQPage` node per document. `text` may contain limited HTML (`<p>`, `<br>`, `<ol>`, `<ul>`, `<li>`, `<a>`, `<b>`, `<em>`) — escape it correctly.

**Where to apply:** `/faq` (sitewide), each ingredient page (3–5 ingredient-specific questions), each blog post with a real FAQ section, each category page (3–4 category questions), `/contact` (see §6.2).

**Do not implement `HowTo`** — Google deprecated HowTo rich results. Write how-to content as good ordered lists instead; it will still win list snippets.

### 4.12 LocalBusiness — conditional

**Only emit `LocalBusiness` if there is a genuine physical location customers can visit** (a shop, a studio, a walk-in collection counter). A pure online store with a warehouse address must **not** emit `LocalBusiness` — Google treats fabricated local entities as spam, and it will block Business Profile verification later.

**Decision:**
- Pure e-commerce, no walk-in → **do not emit.** `OnlineStore` (§4.2) already carries the address and area served. Skip Google Business Profile.
- Physical retail/pickup location exists → emit the node below **on `/contact` only**, and create a Google Business Profile with **NAP identical to the character** to the schema and the visible page (name, address, phone).

```json
{
  "@type": ["Store", "HealthAndBeautyBusiness"],
  "@id": "https://glowhalal.com/contact#localbusiness",
  "name": "Glow Halal",
  "parentOrganization": { "@id": "https://glowhalal.com/#organization" },
  "url": "https://glowhalal.com/contact",
  "image": "https://glowhalal.com/images/store/storefront-1200x800.jpg",
  "logo": { "@id": "https://glowhalal.com/#logo" },
  "telephone": "+92-{XXX}-{XXXXXXX}",
  "email": "{support@glowhalal.com}",
  "priceRange": "Rs 500 - Rs 5,000",
  "currenciesAccepted": "PKR",
  "paymentAccepted": "Cash, Cash on Delivery, Debit Card, Credit Card, JazzCash, Easypaisa",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "{Street Address}",
    "addressLocality": "{City}",
    "addressRegion": "{Province}",
    "postalCode": "{Postal Code}",
    "addressCountry": "PK"
  },
  "geo": { "@type": "GeoCoordinates", "latitude": "{00.000000}", "longitude": "{00.000000}" },
  "hasMap": "https://www.google.com/maps/place/?q=place_id:{PLACE_ID}",
  "areaServed": [
    { "@type": "Country", "name": "Pakistan" },
    { "@type": "City", "name": "Karachi" },
    { "@type": "City", "name": "Lahore" },
    { "@type": "City", "name": "Islamabad" }
  ],
  "openingHoursSpecification": [
    { "@type": "OpeningHoursSpecification", "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday","Saturday"], "opens": "11:00", "closes": "20:00" },
    { "@type": "OpeningHoursSpecification", "dayOfWeek": "Friday", "opens": "14:30", "closes": "20:00" }
  ]
}
```

(Friday hours reflect Jumu'ah — a small localisation detail that signals genuine local operation.)

### 4.13 Node matrix

| Page | Nodes in `@graph` |
|---|---|
| `/` | Organization, WebSite, WebPage, ItemList (featured products) |
| `/shop` | Organization, WebSite, CollectionPage, BreadcrumbList, ItemList |
| Category | Organization, WebSite, CollectionPage, BreadcrumbList, ItemList, FAQPage |
| Product | Organization, WebSite, ItemPage, BreadcrumbList, Product (+Offer, +ReturnPolicy, +ShippingDetails, +AggregateRating/Review *if real*) |
| `/blog` | Organization, WebSite, Blog, BreadcrumbList, ItemList |
| Blog post | Organization, WebSite, WebPage, BreadcrumbList, BlogPosting, FAQPage (if present) |
| Blog category | Organization, WebSite, CollectionPage, BreadcrumbList, ItemList |
| Author archive | Organization, WebSite, ProfilePage, BreadcrumbList, Person, ItemList |
| `/halal-ingredients` | Organization, WebSite, CollectionPage, BreadcrumbList, DefinedTermSet, ItemList |
| Ingredient page | Organization, WebSite, WebPage, BreadcrumbList, Article, DefinedTerm, FAQPage |
| `/about` | Organization, WebSite, AboutPage, BreadcrumbList |
| `/contact` | Organization, WebSite, ContactPage, BreadcrumbList, FAQPage, LocalBusiness *(conditional)* |
| `/faq` | Organization, WebSite, FAQPage, BreadcrumbList |
| `/halal-certification` | Organization, WebSite, WebPage, BreadcrumbList, FAQPage |

---

## 5. The thin-catalog problem — editorial strategy

### 5.1 Diagnosis

Three products = at most 3 product pages + 2 category pages + `/shop` = **6 commercial URLs**, all targeting keywords defended by brands with retail distribution and existing links. Left alone, this site is invisible for 12 months.

**Solution:** treat Glow Halal as a *halal ingredient authority that happens to sell products*. Content is not marketing support here — it **is** the acquisition channel, and the product pages are the conversion layer beneath it. Build 30–40 genuinely useful informational URLs in months 1–4. The confirmed #1 purchase driver in this category is ingredient transparency; the content strategy and the product proposition are therefore the same thing.

### 5.2 The three content assets

**Asset 1 — The Ingredient Glossary (`/halal-ingredients/*`).** The flagship. 18–25 pages, one per ingredient, each 900–1,400 words. Consistent template. This is the linkable, citable, AI-quotable asset, and it targets queries with near-zero competition from Pakistani sites.

**Asset 2 — The Pillar + Cluster blog (`/blog/*`).** 12–18 posts across four clusters: Ingredients, Rulings & Practice (wudu/prayer), Buying Guides, Routines.

**Asset 3 — The Halal Standard page (`/halal-certification`).** The E-E-A-T anchor. Cites PSQCA PS 5319-2014 and PS 3733, names the certifying body, shows the certificate. Every product and blog post links to it. This single page is what makes the rest of the content credible.

### 5.3 Publishing plan — priority order

Publish in this order. Do **not** publish more than 2–3 pieces per week — a brand-new domain dumping 40 pages in week one is a quality-classifier risk.

#### Phase 0 — Launch week (must be live on day 1)

| # | URL | Primary keyword | Words | Notes |
|---|---|---|---|---|
| 0.1 | `/halal-certification` | halal certification for cosmetics in pakistan | 1,200–1,500 | Cite PSQCA PS 5319-2014, PS 3733, name certifier, embed certificate image, explain the audit process. FAQPage schema. |
| 0.2 | `/about` | glow halal / halal beauty brand pakistan | 1,000–1,400 | See §6.1 |
| 0.3 | `/contact` | contact glow halal | 400–600 | See §6.2 |
| 0.4 | `/editorial-policy` | — | 500–700 | Who writes, who reviews, how we verify halal status, correction policy. Linked from every Article's `publishingPrinciples`. |
| 0.5 | `/faq` | halal beauty faq | 800–1,200 | 12–18 questions. FAQPage schema. |

#### Phase 1 — Weeks 1–4: the money content

| # | URL | Primary keyword | Words | Why first |
|---|---|---|---|---|
| 1.1 | `/blog/haram-ingredients-in-makeup` | haram ingredients in makeup | 2,200–2,800 | **The pillar.** Table of 15–18 ingredients: name, INCI/CI code, source, ruling, halal alternative. 100–150 words each, then "Full analysis →". This one page is the hub for the entire cluster. |
| 1.2 | `/halal-ingredients/carmine` | is carmine halal | 1,100–1,400 | Highest-intent, clearest ruling, most searched ingredient |
| 1.3 | `/halal-ingredients/glycerin` | is glycerin halal | 1,100–1,400 | Highest volume; nuanced (plant vs animal vs synthetic) = genuinely useful |
| 1.4 | `/halal-ingredients/collagen` | is collagen halal | 1,000–1,300 | Bovine/porcine/marine distinction |
| 1.5 | `/halal-ingredients/alcohol-denat` | is alcohol in skincare halal | 1,200–1,500 | Present the scholarly split honestly (khamr-derived vs synthetic; topical vs ingested) — nuance is the differentiator vs the listicles |
| 1.6 | `/blog/is-nail-polish-halal` | is nail polish halal / can you pray with nail polish on | 1,800–2,200 | Huge query, direct product tie-in to nail polish, and the wudu angle is emotionally central |
| 1.7 | `/halal-ingredients/gelatin` | is gelatin in cosmetics halal | 1,000–1,300 | Source-dependent; fish halal, porcine haram |
| 1.8 | `/blog/halal-makeup-brands-pakistan` | halal makeup brands in pakistan | 2,000–2,500 | **Highest commercial-adjacent achievability.** Incumbents are a salon booking site and a dermatology clinic blog. Be genuinely comprehensive — include competitors. Honesty out-ranks self-promotion here, and it earns links. |

#### Phase 2 — Weeks 5–10: cluster depth

| # | URL | Primary keyword | Words |
|---|---|---|---|
| 2.1 | `/halal-ingredients/squalene` | is squalene halal (shark liver vs olive) | 900–1,200 |
| 2.2 | `/halal-ingredients/stearic-acid` | is stearic acid halal | 900–1,200 |
| 2.3 | `/halal-ingredients/lanolin` | is lanolin halal | 900–1,100 |
| 2.4 | `/halal-ingredients/keratin` | is keratin halal | 900–1,100 |
| 2.5 | `/halal-ingredients/shellac` | is shellac halal | 900–1,100 |
| 2.6 | `/halal-ingredients/beeswax` | is beeswax halal | 900–1,100 |
| 2.7 | `/halal-ingredients/hyaluronic-acid` | is hyaluronic acid halal | 900–1,100 |
| 2.8 | `/blog/does-makeup-break-wudu` | does makeup break wudu | 1,400–1,800 |
| 2.9 | `/blog/what-is-halal-makeup` | what is halal makeup | 1,800–2,200 |
| 2.10 | `/blog/how-to-check-if-makeup-is-halal` | how to check if makeup is halal | 1,600–2,000 (include a step-by-step INCI-reading walkthrough with photos of real labels — first-hand **Experience** signal) |
| 2.11 | `/blog/halal-vs-vegan-cosmetics` | difference between halal and vegan cosmetics | 1,400–1,700 (comparison table = table snippet target) |
| 2.12 | `/halal-ingredients` (index) | halal cosmetic ingredients list | 1,000–1,300 + A–Z table |

#### Phase 3 — Weeks 11–20: reach and conversion

| # | URL | Primary keyword | Words |
|---|---|---|---|
| 3.1 | `/blog/cosmetic-brands-haram-ingredients` | which cosmetic brands use haram ingredients | 2,200–2,800 |
| 3.2 | `/blog/is-korean-skincare-halal` | is korean skincare halal | 1,600–2,000 |
| 3.3 | `/blog/halal-skincare-routine` | halal skincare routine | 1,800–2,200 |
| 3.4 | `/blog/wudu-friendly-nail-polish-guide` | wudu friendly nail polish / breathable nail polish | 1,600–2,000 |
| 3.5 | `/blog/e-numbers-cosmetics-halal` | e numbers in cosmetics halal | 1,400–1,700 |
| 3.6 | `/blog/halal-makeup-for-brides-pakistan` | halal bridal makeup pakistan | 1,800–2,200 — **seasonal gold**: 52% of PK colour-cosmetics sales are wedding-driven |
| 3.7 | `/blog/best-halal-lipstick-pakistan` | best halal lipstick in pakistan | 1,600–2,000 |
| 3.8 | `/blog/alcohol-free-skincare-benefits` | alcohol free skincare benefits | 1,400–1,700 |
| 3.9 | `/blog/halal-certification-bodies-pakistan` | halal certification bodies in pakistan (PSQCA, SANHA) | 1,600–2,000 — **strong link-bait for B2B and journalists** |
| 3.10 | `/halal-ingredients/{remaining 6–8}` | l-cysteine, tallow, guanine, retinol, cetyl-alcohol, placenta, cochineal-extract, allantoin | 800–1,100 each |

**Caution on 3.1.** "Which brands use haram ingredients" is the highest-traffic idea here and the highest-risk. **MUST** rules: state only verifiable facts sourced from each brand's own published INCI list; link to the source; date-stamp every claim ("as listed on {brand}'s official ingredient page, checked {date}"); never say a *brand* is haram — say *this specific product lists carmine*; publish a visible correction/right-of-reply policy; get legal review before publishing. Written correctly it is the single strongest link-earning asset on the site. Written carelessly it is a defamation exposure.

### 5.4 The ingredient page template (reusable, MUST)

Every ingredient page follows this exact structure — consistency is what makes 25 pages readable as one authoritative resource rather than 25 thin posts.

```
H1: Is {Ingredient} Halal?

[ANSWER BOX — .article-answer-box, 40-55 words, direct verdict]
  "{Ingredient} is {halal / haram / depends on source}. It is derived from
   {source(s)}. {One-sentence key nuance}. On an INCI list it appears as
   {INCI name}{, CI xxxxx}{, E-number}."

[STATUS BADGE — visible: Halal / Haram / Source-dependent — mirrored in DefinedTerm]

H2: What is {Ingredient}?          (150-200w — chemistry, function, product types)
H2: Where does {Ingredient} come from?  (200-250w — every source, animal/plant/synthetic, TABLE)
H2: Is {Ingredient} halal or haram? (250-350w — the ruling, scholarly nuance, cite PSQCA PS 5319-2014 where relevant, cite named scholars/bodies)
H2: How to spot {Ingredient} on a label (150-200w — INCI name, CI/E codes, aliases, ambiguous names)
H2: Halal alternatives to {Ingredient} (150-250w — bullet list with what they do)
H2: Glow Halal products made without {Ingredient}  [DYNAMIC PRODUCT BLOCK — the conversion layer]
H2: Frequently asked questions      (3-5 Q&As → FAQPage schema)

[AUTHOR BOX: photo, name, credentials, link to /blog/author/{slug}]
[REVIEWED BY: name + credential + date — MUST be real]
[LAST UPDATED: {date} — real dateModified]
[SOURCES: numbered external citations with outbound links]
[RELATED: 3 sibling ingredient pages + link up to the pillar]
```

**E-E-A-T requirements (non-negotiable, this is YMYL-adjacent religious guidance):**
- Real named author with real credentials. **No "Admin", no "Glow Halal Team" bylines.**
- Religious rulings reviewed by a named, credentialed reviewer, shown on the page.
- Outbound citations to PSQCA, SANHA, published fatawa, and cosmetic-chemistry sources. Outbound links to authorities are a **positive** signal — do not nofollow them.
- Where scholars disagree, say so and present both positions. Manufactured certainty on a contested religious question destroys trust with exactly the audience being targeted.
- Visible "Last updated" + a real review cadence (every 12 months).

### 5.5 Content quality guard rails

- **No AI-generated content published without substantive human expertise added.** Google's guidance is about *value*, not authorship — but a 25-page ingredient glossary of generic AI prose on a new domain is exactly the pattern the helpful-content systems demote. Every page must contain something not obtainable from the top 3 results: a real label photo, a named scholar's position, a PSQCA clause reference, an original comparison table.
- **No thin pages.** Minimum 800 words for any indexable content page. Below that, merge it.
- **No publishing without an internal link plan.** Each new page ships with its inbound links from existing pages already added.
- **Original imagery.** Photograph real product labels, real INCI panels, the real halal certificate. Stock photos of generic makeup add zero differentiation, and original imagery earns image-search traffic and links.

---

## 6. About Us and Contact Us

These are 2 of the 5 pages that will exist at launch. On a zero-authority domain they are disproportionately important, because they carry the **entity and trust signals** that let Google understand who this brand is.

### 6.1 About Us — `/about`

**Targets:** `glow halal` (brand), `halal beauty brand pakistan`, `pakistani halal cosmetics brand`, `halal makeup company pakistan`.
**NOT allowed to target:** `halal makeup pakistan` (owned by `/shop`), `what is halal makeup` (owned by the blog post).

**Title:** `About Glow Halal - Pakistan's Halal Beauty Store`
**H1:** `About Glow Halal`
**Schema:** `AboutPage` + `Organization` (full node) + `BreadcrumbList`. `Person` node for the founder, `@id`-linked as `Organization.founder`.

**Required structure (1,000–1,400 words):**

| Section | H2 | Content requirement |
|---|---|---|
| Answer box | — | 40–55 words directly under H1: who we are, what we sell, where, what makes us different. |
| Origin | `Why we started Glow Halal` | Real, specific founding story with dates, places, the actual problem encountered. Specificity is the trust signal. Generic mission-speak is worthless. |
| Founder | `Meet {Founder Name}` | Real name, real photo (not stock), credentials, LinkedIn link. This is the strongest **Experience** signal available. `Person` schema. |
| Standard | `Our halal standard` | What "halal" means at Glow Halal in operational terms. Name the certifier. Reference PSQCA PS 5319-2014. Link to `/halal-certification`. |
| Ingredients | `Ingredients we will never use` | A named, specific list (carmine, porcine gelatin, tallow, shark squalene, alcohol denat…) with each item linking to its `/halal-ingredients/{slug}` page. **This section alone justifies the page's existence and feeds internal link equity into the whole cluster.** |
| Sourcing | `How we source and verify` | Supplier audits, certificates of analysis, batch testing. Photos of real documents. |
| People | `The team` | Real names, roles, photos. Even if it's 2 people — say 2 people. |
| Proof | `Certifications and memberships` | Certificate images with issuer, number, validity dates. |
| Location | `Where we are` | City, province, service area. NAP consistent with `/contact` and (if applicable) Google Business Profile. |
| CTA | `Shop the range` | Links to `/shop` and top categories. |

**Trust signals (MUST all be present):** real founder photo · real company name and registration/NTN · physical address · working phone with country code · working email on the brand domain (never Gmail) · verifiable certificate images · social profiles that link back to the site · press/partnership logos only if real.

**MUST NOT:** stock photos of models presented as the team · "Founded by a team of experts" with no names · "We are passionate about beauty" filler · duplicating the `/halal-certification` content (link to it instead — that is cannibalisation).

### 6.2 Contact Us — `/contact`

Contact pages are usually SEO dead weight because they are 40 words and a form. Make it a genuine support-intent landing page.

**Targets:** `contact glow halal`, `glow halal customer service`, `glow halal whatsapp`, `halal cosmetics wholesale pakistan` (secondary).
**Title:** `Contact Glow Halal - Support, WhatsApp & Orders`
**H1:** `Contact Glow Halal`
**Schema:** `ContactPage` + `Organization` with the full `contactPoint` array + `FAQPage` + `BreadcrumbList` + `LocalBusiness` **only if** a physical location exists (§4.12).

**Required structure (400–700 words — long enough to be substantive, short enough to convert):**

1. **Answer box (40–55 words)** — every way to reach us and the response SLA, above the fold. Support intent is impatient; put the answer first.
2. **H2: Contact details** — as real, crawlable, machine-readable markup:
   - `<a href="tel:+92XXXXXXXXXX">` — international format
   - `<a href="mailto:support@glowhalal.com">`
   - `<a href="https://wa.me/92XXXXXXXXXX">` — WhatsApp is the dominant PK support channel; it is a conversion feature, not a nicety
   - Postal address in `<address>` with `PostalAddress` schema
   - Hours, stated in PKT, with Friday Jumu'ah hours noted
3. **H2: Order support** — order-status, tracking, returns links. Route commercial intent immediately.
4. **H2: Ask about an ingredient** — a dedicated enquiry type. **This is the differentiator:** it converts the site's core value proposition into a support channel, feeds real user questions into the editorial pipeline, and gives the page unique topical relevance no competitor's contact page has. Link to `/halal-ingredients`.
5. **H2: Wholesale and stockist enquiries** — captures B2B intent, a separate `contactPoint`.
6. **H2: Press and collaborations** — a named PR contact. **This is a link-acquisition surface** — journalists need someone to email.
7. **H2: Send us a message** — the form.
8. **H2: Frequently asked questions** — 4–6 support questions (delivery time, COD availability, return window, halal certificate request, damaged item). FAQPage schema. This is what turns a thin page into a ranking page.
9. **Map embed** — only if a physical location exists. Lazy-load it (see §7.5); an eager Google Maps iframe will destroy LCP and INP.

**Form MUST-haves:**
- The form is `<form method="POST" action="/contact">` with a real server-side handler and a real POST route, progressively enhanced by Livewire. It must work with JavaScript disabled. This is both an accessibility requirement and a crawl-safety requirement (§8).
- Every field has a real `<label>` (not placeholder-only).
- Honeypot + timestamp check + rate limiting instead of a visible CAPTCHA where possible. A CAPTCHA on the contact page hurts INP and conversion; if one is required, load it on first field interaction only.
- POST → **303 redirect** to `/contact/thank-you`, which is **`noindex,follow`**. Never render the success state at the same URL (breaks measurement and can create a duplicate).
- Fire a GA4 `generate_lead` event on the thank-you page (§9).

**MUST NOT:** contact info rendered as an image · email obfuscated with JavaScript · phone number without country code · a page with fewer than 200 words · `noindex` on the contact page (it is a trust and entity signal Google explicitly looks for on commerce sites).

---

## 7. Technical SEO checklist

### 7.1 XML sitemaps

**Structure — a sitemap index at `/sitemap.xml`:**

```
/sitemap.xml                    ← index, references all below
  /sitemap-pages.xml            ← /, /about, /contact, /halal-certification, /faq, /editorial-policy, policies
  /sitemap-categories.xml       ← /shop + all /shop/{category} + curated facets
  /sitemap-products.xml         ← all /product/{slug}
  /sitemap-blog.xml             ← /blog + all /blog/{post}
  /sitemap-blog-taxonomies.xml  ← /blog/category/*, /blog/author/*   (NOT tags — tags are noindex)
  /sitemap-ingredients.xml      ← /halal-ingredients + all /halal-ingredients/{slug}
  /sitemap-images.xml           ← image sitemap (SHOULD, after launch)
```

**Rules (MUST):**
- Generate **dynamically from the database** via a Laravel route, cached (`Cache::remember`, 1h TTL), invalidated on model save. Never a static committed file.
- Only **canonical, indexable, 200-status** URLs. A URL in a sitemap that is `noindex`, redirected, 404, or canonicalised elsewhere is a quality signal against the whole sitemap. **Add a CI test that fetches every sitemap URL and asserts 200 + self-canonical + no `noindex`.**
- Absolute URLs, matching the canonical exactly (scheme, host, no trailing slash, lowercase).
- `<lastmod>` = the real `updated_at`, W3C datetime with timezone (`2026-08-09T10:00:00+05:00`). **Never** `now()`. Google uses `lastmod` for recrawl scheduling and distrusts sitemaps where everything updates daily.
- **Omit `<changefreq>` and `<priority>`.** Google ignores both. They add bytes and invite misuse.
- Max 50,000 URLs / 50MB uncompressed per file. Split by type first, then by chunk (`sitemap-products-1.xml`).
- Reference `/sitemap.xml` from `robots.txt` and submit it in Search Console at launch.
- Splitting by type is deliberate: Search Console's **Index Coverage report is per-sitemap**, so indexation problems become instantly attributable to products vs blog vs ingredients.

**Do NOT include:** `/cart`, `/checkout`, `/account/*`, `/search`, `/blog/tag/*`, any faceted or paginated-beyond-page-1 URL, `/contact/thank-you`, or auth routes.

### 7.2 robots.txt

Served dynamically from a Laravel route so staging can serve a different file.

```
# https://glowhalal.com/robots.txt

User-agent: *
Allow: /

# Commerce funnel — no crawl value
Disallow: /cart
Disallow: /checkout
Disallow: /account
Disallow: /login
Disallow: /register
Disallow: /password
Disallow: /logout

# Site search — infinite space
Disallow: /search
Disallow: /*?q=

# Faceted navigation — crawl-budget protection
Disallow: /*?*sort=
Disallow: /*?*price_min=
Disallow: /*?*price_max=
Disallow: /*?*color=
Disallow: /*?*shade=
Disallow: /*?*finish=
Disallow: /*?*size=
Disallow: /*?*view=
Disallow: /*?*per_page=

# Filament admin panel (Filament 5) — never index
Disallow: /admin
Disallow: /filament
Disallow: /livewire/upload-file
Disallow: /livewire/preview-file

# Framework internals — NOTE: do NOT blanket-block /livewire/ (see clarifications)
Disallow: /livewire/update
Disallow: /storage/framework/
Disallow: /vendor/

# Post-conversion pages
Disallow: /contact/thank-you
Disallow: /checkout/success

# Allow assets — blocking these breaks rendering and Core Web Vitals assessment
Allow: /build/
Allow: /images/
Allow: /storage/
Allow: /livewire/livewire.js
Allow: /livewire/livewire.min.js
Allow: /*.css$
Allow: /*.js$
Allow: /*.webp$
Allow: /*.avif$
Allow: /*.svg$

Sitemap: https://glowhalal.com/sitemap.xml
```

**Critical clarifications:**
- **`Disallow` ≠ de-index.** A blocked URL with external links can still appear as a URL-only listing. For anything that must never appear in the index, use `<meta name="robots" content="noindex">` and **leave it crawlable** — Google must be able to fetch the page to see the `noindex`. Blocking it in robots.txt *prevents* de-indexing.
- Consequently: cart/checkout/account are `Disallow`ed **and** `noindex` (they are also auth/session-gated, so URL-only listings are harmless).
- **Facets are `noindex` first**, robots.txt-blocked second as a crawl-budget measure — accepted trade-off since these URLs have no external links.
- **LIVEWIRE 4 — do NOT blanket-`Disallow: /livewire/`.** Livewire 4 introduces **scoped component `<style>` and `<script>` blocks that are extracted and served as real, cached asset files**, and the core `livewire.js` runtime is also served from under `/livewire/`. A blanket block on `/livewire/` can therefore prevent Googlebot from fetching CSS/JS it needs to render the page — a rendering failure that suppresses rankings and breaks CWV attribution. Block only the RPC endpoint (`/livewire/update`, a POST endpoint that is never crawled anyway) and the Filament upload endpoints.
  **MUST verify at build time:** load a product page, list every request under `/livewire/*` in DevTools, and confirm every asset URL is `Allow`ed. Then confirm with Search Console → URL Inspection → *Test live URL* → *More info* → *Page resources* that **zero** resources are reported as blocked by robots.txt. Repeat after any Livewire upgrade — the asset path is an implementation detail that can change between versions.
- **Filament 5 admin at `/admin` MUST also send `X-Robots-Tag: noindex, nofollow`** at the middleware level, not just a robots.txt `Disallow` (see §7.2 first bullet — `Disallow` alone does not de-index). Filament's own CSS/JS bundle must never be loaded on storefront pages; verify the storefront layout does not include Filament asset directives.
- **Never block CSS/JS.** Google renders the page; blocked assets produce a broken render and can suppress rankings and CWV attribution.
- **Staging MUST serve `User-agent: * / Disallow: /` plus `X-Robots-Tag: noindex` at the server level, and be HTTP-Basic-Auth protected.** A leaked staging index on a new domain is a duplicate-content disaster.

### 7.3 Meta robots directives

| Page type | Directive |
|---|---|
| Homepage, shop, categories, products, blog, posts, ingredients, about, contact, certification, FAQ | `index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1` |
| Blog tags | `noindex, follow` |
| Faceted / sorted URLs | `noindex, follow` (single facet) · `noindex, nofollow` (≥2 facets) |
| Search results | `noindex, follow` |
| Cart, checkout, account, auth | `noindex, nofollow` |
| Filament admin (`/admin/*`) | `noindex, nofollow` via **`X-Robots-Tag` response header** in middleware (Filament renders its own layout — a Blade meta tag in the storefront layout will not apply there) |
| Thank-you / confirmation | `noindex, follow` |
| Policy pages | `index, follow` |
| Paginated pages 2+ | `index, follow` |
| 404 page | `noindex, follow` |

`max-image-preview:large` is required for large image thumbnails in mobile SERPs and Discover — meaningful for a visual beauty brand. Set it globally.

### 7.4 hreflang and internationalisation (Urdu, Phase 2)

Not needed at launch (single language). Design for it now so it is a config change, not a rebuild.

**Decision: subdirectory.** `glowhalal.com/ur/...` — a subdirectory inherits the domain's authority; a subdomain or ccTLD starts from zero. For a brand-new domain that difference is decisive.

```
English (default): https://glowhalal.com/blog/is-nail-polish-halal
Urdu:              https://glowhalal.com/ur/blog/nail-polish-halal-hai-ya-haram
```

On **both** URLs, emit the **complete, reciprocal** set:

```html
<link rel="alternate" hreflang="en-pk" href="https://glowhalal.com/blog/is-nail-polish-halal" />
<link rel="alternate" hreflang="ur-pk" href="https://glowhalal.com/ur/blog/nail-polish-halal-hai-ya-haram" />
<link rel="alternate" hreflang="x-default" href="https://glowhalal.com/blog/is-nail-polish-halal" />
```

**MUST rules:**
- **Reciprocity is mandatory.** If A declares B but B does not declare A, Google discards the entire set.
- Every page in a set **self-references** its own hreflang.
- Exactly one `x-default`.
- `<html lang="en" dir="ltr">` on English pages, `<html lang="ur" dir="rtl">` on Urdu. The `lang` attribute is an **independent signal** — set it even when hreflang is present.
- Urdu pages are **genuine translations with localised keyword research**, never machine output. A machine-translated Urdu tree is a thin-content liability, not an asset.
- hreflang URLs must be canonical, indexable, 200-status URLs.
- **Never build a bilingual single page.** A URL mixing English and Urdu with no `lang`/hreflang is treated as one ambiguous document, serves cleanly to neither audience, and dilutes topical authority for both.
- Add `hreflang` entries to the XML sitemaps (`xhtml:link`) as well as the `<head>` — belt and braces at scale.

**Design-now requirements:** locale-aware routing from day 1 (an optional `{locale?}` prefix), translatable slugs on models (`slug_en`, `slug_ur`), and a `Translation` relationship linking a post to its counterpart. Retrofitting this after 40 published pages is expensive.

### 7.5 Core Web Vitals

Pakistan is a mobile-first, mid-range-Android, variable-4G market. Budget aggressively — the global thresholds are the *floor*, not the goal.

| Metric | Google "Good" | **Glow Halal target (p75, mobile)** |
|---|---|---|
| LCP | < 2.5s | **< 2.0s** |
| INP | < 200ms | **< 150ms** |
| CLS | < 0.1 | **< 0.05** |
| TTFB | < 800ms | **< 400ms** |
| FCP | < 1.8s | **< 1.5s** |

**Performance budgets (MUST, enforced in CI with Lighthouse CI):**

| Resource | Budget (per page, compressed) |
|---|---|
| Total JS | **≤ 150 KB** |
| Total CSS | **≤ 40 KB** (Tailwind 4 purged) |
| LCP image | **≤ 120 KB** |
| Total page weight (product page) | **≤ 900 KB** |
| Font files | **≤ 2 families, ≤ 4 weights, ≤ 100 KB total** |
| Third-party scripts | **≤ 3**, all deferred |

**Implementation requirements:**
- **Server:** PHP 8.3 + OPcache + JIT; Redis for cache/session/queue; hosting in-region or a CDN with a Pakistan PoP. TTFB from Karachi/Lahore is the single biggest lever available.
- **HTTP caching:** `Cache-Control: public, max-age=31536000, immutable` on hashed Vite assets; `s-maxage` + `stale-while-revalidate` at the CDN for HTML on anonymous requests.
- **Full-page cache for anonymous visitors** on `/`, `/shop`, categories, blog, ingredients. The cart badge is hydrated *after* paint via a deferred island (see §8.8) so full-page caching stays safe.
- **LCP:** the LCP element is the hero/product image. `fetchpriority="high"`, **no** `loading="lazy"`, preloaded via `<link rel="preload" as="image" imagesrcset="…">`. Never let the LCP element sit inside a Livewire lazy component, a `lazy`/`defer`/`skip` island, or a carousel that initialises in JS — in all four cases the LCP candidate does not exist at first paint and LCP collapses.
- **CLS:** every `<img>` and `<video>` has explicit `width`/`height` (or `aspect-ratio` CSS). Reserve fixed space for banners, cart badges, review widgets, and cookie notices. `font-display: swap` **plus** a `size-adjust`-matched fallback in `@font-face` to eliminate the swap reflow.
- **INP:** this is where Livewire is a genuine risk (§8.9). Debounce all `wire:model.live`, wrap the product grid in a default `@island` so a filter change re-renders one region instead of the whole tree, and avoid re-rendering large trees on every keystroke. Budget for the extra CSS/JS requests that Livewire 4's scoped single-file component assets introduce (§8.14).
- **Fonts:** self-host WOFF2. `<link rel="preload" as="font" type="font/woff2" crossorigin>` for the one font used above the fold. No Google Fonts CDN (extra DNS + connection + a privacy issue in some jurisdictions).
- **Third-party:** GA4 loaded with `defer`. Meta Pixel / TikTok Pixel loaded **after** the `load` event or on first interaction. Chat widgets on interaction only. Maps iframes with `loading="lazy"` behind a click-to-load facade.
- **Monitoring:** Lighthouse CI on every PR against the budgets above; CrUX/PageSpeed field data reviewed monthly; the `web-vitals` JS library reporting real-user LCP/INP/CLS into GA4 (§9.3).

### 7.6 Images

**Format and delivery (MUST):**
- **AVIF → WebP → JPEG** via `<picture>` with `<source type>` fallbacks.
- Responsive `srcset` at 320 / 480 / 640 / 768 / 1024 / 1280 / 1600 px with an accurate `sizes` attribute.
- Product images: square 1:1 master at 1600×1600, plus 4:3 and 16:9 crops for schema and OG.
- Compress to visual quality ~80. Product images ≤ 150 KB, hero ≤ 120 KB, blog inline ≤ 100 KB, thumbnails ≤ 40 KB.
- Strip EXIF except copyright.
- `loading="lazy" decoding="async"` on everything **below** the fold; **never** on the LCP element.
- Serve from a CDN with on-the-fly resizing; store originals outside the web root.

**Filenames (MUST):** descriptive, lowercase, hyphenated, keyword-relevant, no camera codes.
- Good: `glow-halal-matte-lipstick-rosewater-nude-swatch.webp`
- Bad: `IMG_2947.jpg`, `product1.jpg`, `halal-makeup-halal-lipstick-halal-cosmetics-pakistan.jpg` (stuffing)

**Alt text rules (MUST):**
1. Describe what is **in** the image, for someone who cannot see it. Accessibility first; SEO is the by-product.
2. 60–125 characters. Never a keyword list.
3. Include the primary keyword **only when it genuinely describes the image**.
4. Decorative images (icons, dividers, background flourishes): `alt=""` — empty, not missing.
5. Product images: `alt="{Product Name} in shade {Shade}, {what the shot shows}"` → `alt="Glow Halal matte lipstick in Rosewater Nude, swatched on medium skin tone"`.
6. Ingredient/label photos: `alt="INCI ingredient list on {Brand} {Product} showing {ingredient}"` — first-hand evidence, and a genuine image-search opportunity.
7. Never start with "image of" / "picture of".
8. Never reuse identical alt text across different images.
9. Text embedded in an image must be repeated in the alt (or better: don't put text in images).
10. **Enforce in CI:** a test that fails if any `<img>` in a Blade template lacks an `alt` attribute.

**Image sitemap + captions:** add `<figure><figcaption>` to editorial images — captions are among the most-read text on a page and add keyword-relevant context.

### 7.7 Mobile-first indexing

Google indexes the mobile rendering. Non-negotiables:
- `<meta name="viewport" content="width=device-width, initial-scale=1">`
- **Content parity:** the mobile DOM must contain **exactly the same content, links, headings and structured data** as desktop. Never `hidden md:block` a body of copy that only desktop users see — if it's hidden from mobile, it's effectively hidden from the index. Accordions/tabs are fine (content is in the DOM), `@media`-based omission is not.
- Tap targets ≥ 48×48 CSS px with ≥ 8px spacing.
- Base body font ≥ 16px; never disable zoom (`user-scalable=no` is banned).
- No horizontal scroll at 320px width.
- No interstitials covering content on entry (Google's intrusive-interstitial penalty). Newsletter pop-ups: delayed ≥ 15s or exit-intent, dismissible, ≤ 30% viewport on mobile. Cookie/consent banners must be small and must not cause CLS.
- Test every template in Search Console URL Inspection → "Test live URL" → "View crawled page" and diff against the desktop DOM.

### 7.8 404s, redirects, and error handling

| Situation | Response |
|---|---|
| Unknown URL | **404** with a useful custom page: search box, top categories, top blog posts, contact link. `noindex, follow`. **MUST return HTTP 404**, never 200 (soft 404). |
| Deleted product with an equivalent | **301** → equivalent product |
| Deleted product, no equivalent | **301** → parent category |
| Deleted category | **301** → `/shop` |
| Permanently removed, nothing relevant | **410 Gone** |
| Slug change | **301** old → new, recorded in a `redirects` table |
| Empty category (0 products) | **404** — never a 200 empty listing (soft-404 risk) |
| Out-of-stock product | **200**, indexable, `availability: OutOfStock` |
| Search with 0 results | **200**, `noindex`, with suggestions |
| Legacy/pre-launch URLs | **301** to the mapped new URL |
| Server error | **503** with `Retry-After` during maintenance — **never** a 200 maintenance page |

**Redirect rules:**
- Store redirects in a **database table** (`from_path`, `to_path`, `status_code`, `hits`, `last_hit_at`), resolved in middleware. Editable without a deploy.
- **Never chain.** A → B → C must be flattened to A → C. Add a CI test that walks the redirect table and fails on any chain or loop.
- 301 for permanent, 302 only for genuinely temporary (a seasonal campaign), 307/308 to preserve method on POST.
- Redirect to the **exact canonical target** — not a URL that then canonicalises elsewhere.
- Log 404 hits with referrer; review weekly and convert high-traffic 404s into redirects.
- **Cross-domain rule:** never mass-redirect unrelated old URLs to the homepage — Google treats that as a soft 404 and it wastes the equity.

### 7.9 Security, headers, and crawl hygiene

- HTTPS everywhere, valid cert, auto-renewal, no mixed content.
- HSTS with preload.
- `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy` minimal.
- CSP that **does not block Googlebot's rendering of your own JS/CSS**. Test with URL Inspection after any CSP change.
- `X-Robots-Tag: noindex` on PDFs, CSV exports, and any non-HTML asset that shouldn't rank.
- Return proper `Content-Type` and UTF-8 charset on every response.
- Configure GZIP/Brotli on HTML, CSS, JS, SVG, JSON.
- HTTP/2 or HTTP/3 enabled.
- Add `/.well-known/security.txt` and a `humans.txt` — small but real trust signals for a new brand.

---

## 8. Livewire 4 — SEO risks and safe patterns

**Verified against Livewire 4 (project runs 4.3.5).** Livewire is server-rendered by default, which is why it is safe *when used correctly*. Livewire 4 is the biggest release in the framework's history and it is **strongly backwards-compatible with v3** — which means every v3-era SEO footgun still exists, and v4 adds several new ones. Every risk below comes from a specific, easy-to-make mistake.

### 8.0 What changed in v4 that matters for SEO

| v4 feature | SEO impact | Verdict |
|---|---|---|
| **Islands** (`@island` / `wire:island`) — isolated regions that re-render independently | Islands **do render in the initial server HTML by default** — safe. But `lazy: true`, `defer: true`, and especially `skip: true` emit **placeholder-only HTML** on first render. This is the single biggest new footgun on this site. | ⚠️ **High risk** — §8.3 |
| **Island `append` / `prepend` modes** — purpose-built for infinite scroll and pagination | Makes JS-only infinite scroll trivially easy to build, which orphans every product past page 1 | ⚠️ **High risk** — §8.7 |
| **`Route::livewire()`** page components + `pages::` / `layouts::` namespaces | Pages *are* Livewire components now. The `<head>` is owned by the layout, so canonical/meta/JSON-LD must be plumbed from the page component to the layout deliberately, or they silently don't render | ⚠️ **High risk** — §8.2 |
| **Single-file components** with scoped `<style>` / `<script>`, served as separate cached asset files | Extra HTTP requests and potential render-blocking CSS; on Pakistani 4G this is a real LCP cost | ⚠️ Medium — §8.14 |
| **`wire:navigate`** (unchanged behaviour from v3) — replaces URL, `<title>`, `<body>` only | `<head>` meta/canonical/JSON-LD go stale between SPA navigations | ⚠️ Medium — §8.6 |
| **Islands reduce payload size** (headline perf claim for v4) | Genuinely **good** for INP — a filter change re-renders one island, not the whole component tree | ✅ Use it |
| **`wire:show`** — toggles visibility via CSS instead of removing from DOM | **Better than v3.** Content stays in the DOM, so it stays indexable | ✅ Use it |
| **`wire:text` / `wire:bind`** — optimistic client-side text and attribute updates | Can desynchronise visible content (e.g. price) from server-rendered content and JSON-LD | ⚠️ Medium — §8.12 |
| **`wire:transition`** using the View Transitions API | Can introduce layout shift and long tasks if applied to above-the-fold or large regions | ⚠️ Medium — §8.15 |
| **`wire:sort`, `$dirty`, `$js`, `wire:ref`, `#[Json]`** | No SEO impact | ✅ Neutral |

### 8.1 The one rule that governs everything

> **The initial HTML response for every indexable URL must contain the complete, final content — every heading, every paragraph, every internal link, every structured-data node — with JavaScript disabled.**

**Acceptance test (MUST, automated):** for every indexable route, `curl` the URL with no JS execution and assert the response body contains the H1, the primary copy, the product/blog links, the canonical, and the JSON-LD. Wire this into CI. If it fails, the page is broken for search, full stop.

In Livewire 4 this test is **more** important than it was in v3, because Islands make it trivially easy to move a region of content out of the initial render with a single keyword — and the page still looks perfect in a browser.

### 8.2 RISK (v4) — `Route::livewire()` page components and a missing `<head>`

Livewire 4 introduces `Route::livewire()`, which routes directly to a component by name, with `pages::` and `layouts::` namespaces. This means **storefront pages are Livewire components, and the `<head>` belongs to the layout component**, not to the page. If the page component doesn't explicitly pass its SEO data up to the layout, the layout renders a generic default — and every page ships with the same title, no canonical, and no JSON-LD. This is a silent, total failure that looks fine in a browser.

**MUST:** every routable page component exposes its SEO payload and the layout consumes it. Two acceptable patterns — pick one and use it everywhere:

**Pattern A (recommended) — layout data, one object, one source of truth:**
```blade
{{-- page component --}}
#[Layout('layouts::app')]
{{-- expose a single Seo value object built by the SeoMeta service --}}
public function seo(): Seo
{
    return SeoMeta::forProduct($this->product); // title, description, canonical, robots, og, schema graph
}
```
The layout renders `<title>`, `<meta name="description">`, `<link rel="canonical">`, `<meta name="robots">`, OG/Twitter tags and the JSON-LD `@graph` from that one object. Nothing is assembled ad hoc in a view.

**Pattern B — `@push`/`@stack` from the page component's template into the layout's `<head>`.** Works, but it scatters SEO across templates and makes the "exactly one canonical" test harder to guarantee. Only use if Pattern A is blocked.

**MUST NOT** rely on `#[Title]` alone. `#[Title]` sets the `<title>` tag and nothing else — no description, no canonical, no robots, no schema. A page with only `#[Title]` is not optimised, it is 20% optimised.

**MUST NOT** let any page fall back to a layout default for canonical or description. Add a CI test asserting every indexable route emits a non-empty, unique `<title>`, a non-empty `<meta name="description">`, and exactly one `<link rel="canonical">` whose value equals the request URL.

**Also verify:** Filament 5 registers its own panel layout. Confirm the storefront layout (`layouts::app`) and the Filament panel layout are entirely separate, and that no Filament head directives leak into storefront pages.

### 8.3 RISK (v4, HIGH) — Islands with `lazy`, `defer`, or `skip` remove content from the initial HTML

Islands are Livewire 4's headline feature and the biggest new SEO hazard on this project. Behaviour, per the Livewire 4 documentation:

| Island mode | Initial server HTML contains | Safe for indexable content? |
|---|---|---|
| `@island` (default) | **Full rendered content** | ✅ **Yes — safe** |
| `@island(lazy: true)` | Placeholder only; real content fetched when scrolled into view via IntersectionObserver | ❌ **No** |
| `@island(defer: true)` | Placeholder only; real content fetched immediately after page load | ❌ **No** |
| `@island(skip: true)` | Placeholder only; **never renders until explicitly triggered** | ❌ **Absolutely not** |

A `lazy` island is worse than a lazy component for SEO, because it is gated behind an **IntersectionObserver**. Googlebot's renderer does not scroll a real viewport the way a user does; content that only materialises on scroll-into-view is content you are gambling on. Do not gamble.

**MUST NOT** use `lazy`, `defer`, or `skip` on any island containing: product listings or product cards, product name/price/description, INCI ingredient lists, blog or ingredient body copy, headings, breadcrumbs, category intro copy, FAQ content, review content, or any internal link that is a page's only inbound link.

**MUST** use plain `@island` (default, eagerly rendered) wherever islands are used on indexable pages. You still get the entire performance benefit — isolated re-render on interaction — with zero indexability cost. **Default islands are the correct tool here and should be used liberally**; it is only the loading modifiers that are dangerous.

**Safe uses of `lazy` / `defer` islands:** "recently viewed", personalised recommendations, live stock counters, a review *submission* form (the reviews themselves must be eagerly rendered), account dashboards, order history, anything already `noindex`.

**Safe pattern for expensive queries:** cache server-side (`Cache::remember`) and render the island eagerly. Do not trade indexability for TTFB — cache instead.

**Computed-property caveat:** the docs note that computed properties inside an island are evaluated during the initial render but only re-execute when that island re-renders. Confirm that any computed property feeding indexable content (price, stock label, product count) actually produces its value in the initial HTML — check the `curl` output, not the browser.

**Nested-island caveat:** when an outer island re-renders, nested inner islands are **skipped by default**. If a nested island holds indexable content, verify it is present in the *initial* render (it will be) and understand that it simply won't refresh — which is fine for static content and wrong for anything derived from filters.

### 8.4 RISK — `lazy` components hide content from the initial HTML (v3 carryover, still live in v4)

Livewire's component-level lazy loading **skips the component during the initial render and inserts an empty placeholder** (`@placeholder` content if provided); the real content arrives in a subsequent network request.

**MUST NOT:** use `#[Lazy]`, `@lazy`, or `<livewire:xyz lazy />` on any component that renders indexable content — product listings, product details, descriptions, ingredient lists, blog bodies, breadcrumbs, category copy, headings, or internal links. The `@placeholder` directive makes this *look* better to a human and changes nothing for a crawler: a skeleton loader is not content.

**Safe uses of `lazy`:** identical to the island list in §8.3.

### 8.5 RISK — content that only exists after a user action

Copy revealed by `wire:click`, tabs whose panels are fetched on demand, "read more" that triggers a server round trip, reviews loaded by a "Load reviews" button, or an island triggered by `$wire.$island()` from JavaScript — none of that is in the initial HTML, and Google will not click.

**MUST:** all indexable content is in the DOM on first paint. Use CSS to hide/show (accordions, tabs) — the content stays in the DOM and stays indexed.

Livewire 4 makes the safe path easier: **`wire:show` toggles visibility with CSS rather than removing the element from the DOM.** Prefer it over `@if` for any show/hide of indexable content.

```blade
{{-- UNSAFE — content does not exist until clicked (server round trip) --}}
<button wire:click="loadDescription">Description</button>
@if($showDescription) <div>{!! $product->description !!}</div> @endif

{{-- UNSAFE — @if removes the node from the server-rendered DOM entirely --}}
@if($tab === 'ingredients') <div>{!! $product->inci !!}</div> @endif

{{-- SAFE (v4) — always in the DOM, visibility toggled instantly via CSS --}}
<div wire:show="tab === 'ingredients'">{!! $product->inci !!}</div>

{{-- SAFE (no JS required at all) — best option for the product page tabs --}}
<details open>
  <summary><h2>Description</h2></summary>
  <div>{!! $product->description !!}</div>
</details>
```

### 8.6 RISK — `wire:navigate` leaves the `<head>` stale

**Verified against the Livewire 4 documentation:** `wire:navigate` replaces **the URL, the `<title>` tag, and the `<body>` contents**. Head *scripts* are handled specially (scripts present on the initial page are not re-run; genuinely new head scripts are evaluated; `data-navigate-track` assets force a full reload when their version query string changes — Laravel's Vite integration adds this automatically). Nothing in the documented behaviour guarantees replacement of `<meta name="description">`, `<link rel="canonical">`, Open Graph tags, or a JSON-LD `<script>` sitting in the `<head>`.

Livewire 4 pushes `wire:navigate` harder than v3 did — with `Route::livewire()` page components, SPA-style navigation between single-file page components is the promoted architecture. So this will be used sitewide, and it must be handled deliberately rather than assumed away.

**Impact assessment — be precise about this:**
- **Googlebot: no ranking impact.** Googlebot does not click links to navigate; it fetches each URL independently and receives a correct, fresh `<head>` every time. This is *not* a ranking bug, and nobody should panic about it.
- **Real impact:** SEO auditing crawlers driven by a real browser session, in-page share buttons that read `og:` tags from the DOM, and any client-side analytics reading canonical/meta will see stale values. Debugging becomes confusing and false alarms get raised.

**Safe patterns (implement all five):**
1. **Render canonical, meta, robots and OG tags server-side in the `<head>` on every full page load**, from the §8.2 `Seo` object. This is what search engines actually consume — it is always correct at the fetch that matters.
2. **Place the JSON-LD `<script type="application/ld+json">` at the end of `<body>`, not in `<head>`.** Google explicitly supports JSON-LD in the body, and body placement means `wire:navigate` swaps it correctly along with the content. This is the single cleanest fix and it also sidesteps §8.13.
3. **Add a `livewire:navigated` listener** that rewrites `canonical`, `description`, `robots` and `og:*` from a per-page JSON data island in the body. ~20 lines, removes the whole class of confusion. Verify by navigating product → blog post → ingredient page and inspecting the DOM at each step.
4. **Keep `data-navigate-track` on Vite asset tags** (Laravel adds it automatically — do not strip it). Without it, users on an old JS bundle after a deploy get broken interactions, which shows up as INP regressions in field data.
5. **Prefetch awareness:** `wire:navigate` prefetches on mousedown by default (`.hover` prefetches after 60ms of hover). Prefetched HTML is fetched but not executed, so it does **not** inflate GA4 pageviews — but it **does** inflate server request volume and access logs. Account for this before drawing conclusions from log-file analysis or server-side analytics, and be conservative with `.hover` on link-dense pages like `/shop`.

Also: use `@persist` for elements that must survive navigation (cart drawer, audio) and keep them **outside** all Livewire components, in the layout. Livewire 4 preserves scroll position across back/forward navigation automatically; `wire:navigate:scroll` preserves scroll within a persisted element.

### 8.7 RISK (v4, HIGH) — island `append`/`prepend` infinite scroll orphans the catalog

Livewire 4 ships `wire:island.append` and `wire:island.prepend`, documented as ideal for pagination and infinite scroll. This makes JS-only infinite scroll a one-line feature — and a one-line way to make every product past the first screen uncrawlable and unlinkable.

**MUST NOT** replace paginated URLs with island-append infinite scroll. If products 13–200 only ever appear by appending into an island, they exist at **no crawlable URL**, appear in **no sitemap-consistent path**, receive **no internal links**, and will not be indexed.

**MUST:** implement §2.3 pagination first — real `?page=n` URLs with real `<a href>` links, self-canonical, `index,follow`. Only then layer island-append "Load more" on top as *progressive enhancement*, keeping the `<a href>` pagination links in the DOM (visually hidden is acceptable; removed is not).

**Verification:** disable JavaScript, load `/shop`, and confirm you can click through to page 2 and reach a product that is not on page 1.

### 8.8 RISK — reactive cart breaks full-page caching and injects variance

A cart-count badge rendered server-side makes every HTML response user-specific, which kills full-page caching, spikes TTFB, and makes anonymous-user output vary — a real CWV and crawl-efficiency problem.

**Safe pattern:**
- Render the cart badge as a **fixed-dimension placeholder** in the server HTML (`<span id="cart-count" class="min-w-[1.25rem] inline-block">0</span>`) — fixed dimensions prevent CLS.
- Hydrate the real count **after** paint. In Livewire 4 the clean way is a **`@island(defer: true)` in the layout header** — the cart badge is not indexable content, so a deferred island is exactly the right tool, and it keeps the rest of the page fully cacheable and fully server-rendered. (This is the one place on the storefront where `defer` is correct.) A `localStorage` value or a small `/api/cart/count` JSON call are equally acceptable.
- Use `wire:text` for the badge value if you want instant optimistic updates on add-to-cart — the badge is chrome, not content, so §8.12's warning does not apply here.
- **Never** vary the cacheable HTML body of `/`, `/shop`, categories, blog, or ingredient pages by cart state.
- "Add to cart" MUST NOT change the URL, the canonical, the `<title>`, or any indexable body content.
- The add-to-cart control is a `<button>`, never an `<a href>`. An `<a>` invites crawling into cart mutation URLs.
- Cart/checkout routes are `noindex, nofollow` **and** robots-Disallowed (§7.2).

### 8.9 RISK — `wire:model.live` destroys INP (and how v4 Islands fix it)

Every keystroke on a `wire:model.live` field fires a network round trip and a re-render. On a mid-range Android over Pakistani 4G, that is 300–800ms of interaction latency per keystroke. **INP is a ranking signal**, and our target is a strict 150ms (§7.5).

**MUST:**
- Default to `wire:model` (deferred) — no network on keystroke.
- Where live behaviour is genuinely needed (search-as-you-type, filters), use `wire:model.live.debounce.500ms` at minimum.
- Prefer `wire:model.blur` for form fields.
- **Use a default `@island` around the product grid.** This is the correct v4 answer: a filter change re-renders only the grid island rather than the whole page component, which is where v4's payload/latency improvement actually comes from. It costs nothing in indexability because default islands render fully server-side.
- Use `wire:key` on every item in every loop. Missing keys cause the morph to destroy and rebuild DOM subtrees, producing both layout shift (CLS) and long tasks (INP). This matters more with islands, because append/prepend modes rely on stable keys.
- Use `wire:loading` / `data-loading` skeletons with **reserved dimensions matching the loaded content** — an unreserved loading state is a guaranteed CLS hit.
- Use `wire:show` / `wire:text` / `wire:bind` for optimistic UI on interactions that don't need server state (tab switches, toggles) — they update instantly with no round trip, which is a direct INP win.

### 8.10 RISK — filter/sort state that never reaches the URL

If Livewire filters mutate component state without touching the URL, filtered views are unshareable, unlinkable, and untrackable in GA4/GSC. If they *do* touch the URL via `#[Url]`, you have just generated crawlable faceted URLs — the exact trap §2.4 prevents.

**Correct configuration:**
- Use `#[Url]` so state is shareable and measurable — **and** make the `SeoMeta` service read those same query params to emit `noindex,follow` + a params-stripped canonical (§2.4/§2.5).
- Use `#[Url(as: 'sort', except: 'default')]` so default values never appear in the URL — this alone eliminates a large share of duplicate URLs.
- Never `#[Url]` a parameter that isn't in the §2.4 parameter policy table.
- Sitemaps and internal links only ever reference clean URLs.

### 8.11 RISK — pagination as `wire:click` instead of links

Livewire's `WithPagination` can render `<button wire:click>` page controls. Buttons are not crawlable. If pagination isn't a real link, every product past page 1 is orphaned.

**MUST:** publish Livewire's pagination views (`php artisan livewire:publish --pagination`) and ensure every page link is `<a href="{{ $paginator->url($page) }}" wire:navigate>` — a real, crawlable `href` that also works with JS disabled, with SPA behaviour layered on top. Verify by disabling JS and clicking through to page 2.

The same rule applies to **every** navigational element: category tiles, product cards, "read more", breadcrumbs, footer links, and any island-triggering control that also navigates. **Navigation = `<a href>`. Actions = `<button>`.** No exceptions.

### 8.12 RISK — client-side-only content: Alpine, `@entangle`, and v4's `wire:text` / `wire:bind`

Content that only exists inside an Alpine `x-if` / `x-html` template is invisible to a crawler that doesn't execute (or fully render) the JS. Use Alpine for *behaviour*, never for producing indexable *content*.

**New in v4 — `wire:text` and `wire:bind`.** These update text content and attributes optimistically on the client. They are excellent for UI responsiveness and dangerous for two specific things:

- **`wire:text` MUST NOT be the only source of indexable text.** The element must be server-rendered with its correct value, with `wire:text` layered on for optimistic updates. `<span wire:text="price"></span>` renders as an empty element in the initial HTML — if that's the product price, the price does not exist for search engines, and the visible price no longer matches the `Product.offers.price` in the JSON-LD. **A price mismatch between JSON-LD and the visible page is a structured-data violation with manual-action risk (§4.1).**
- **`wire:bind` MUST NOT be the only source of `href`, `src`, or `alt`.** Server-render the real attribute value; bind on top.

**Rule:** for any element carrying indexable content, the server-rendered value and the bound value must be identical on first paint. If a variant selector changes the displayed price, it must also update the JSON-LD `offers.price` — or, simpler and preferred, variant price changes trigger a real server render of the price island so both stay in sync.

### 8.13 RISK — duplicate, dropped, or stale JSON-LD after a morph or island re-render

If JSON-LD is rendered inside a Livewire component root or inside an island, a re-render can duplicate the node, drop it, or leave it stale relative to the visible content. Islands make this more likely in v4, because partial re-renders are now the normal case rather than the exception.

**MUST:**
- Render JSON-LD from the **layout**, at the end of `<body>`, driven by the single `Seo` object from §8.2.
- **Never** place a JSON-LD script inside an `@island`.
- **Never** place a JSON-LD script inside a component that re-renders on interaction.
- Automated test: exactly **one** `<script type="application/ld+json">` per rendered document, and it parses as valid JSON.

### 8.14 RISK (v4) — single-file component scoped styles and scripts hurting LCP

Livewire 4 lets components declare `<style>` and `<script>` inline in a single file; Livewire extracts, scopes and serves these as separate cached asset files, deduplicated per component type. Convenient — but on a Pakistani mobile connection, **each additional render-blocking stylesheet is a direct LCP cost**, and this project has a hard 40 KB CSS / 2.0s LCP budget (§7.5).

**MUST:**
- Keep **all above-the-fold styling in the main Tailwind 4 bundle**. Do not let the header, hero, product gallery, or product-card components depend on a separately-fetched scoped stylesheet for their initial layout — that is a render-blocking request on the critical path and a CLS risk if it arrives late.
- Reserve scoped component styles for **below-the-fold, interaction-driven, or admin-only** components.
- Confirm the generated asset files are served with long-lived immutable cache headers and are **crawlable** (not blocked by robots.txt — see §7.2).
- Audit the network waterfall on `/`, `/shop` and a product page: count every CSS and JS request and check it against the §7.5 budget. If single-file scoped assets push the count up materially, move those styles into the Tailwind bundle.
- Component `<script>` blocks must be non-blocking and must never be required to render content (§8.1).

### 8.15 RISK (v4) — `wire:transition` and the View Transitions API causing CLS

Livewire 4's `wire:transition` uses the browser View Transitions API. Animated element entry/exit that changes layout is, by definition, layout shift — and CLS is measured against a strict 0.05 target here.

**MUST:**
- Never apply `wire:transition` to above-the-fold elements that affect layout on initial render.
- Transitions must animate `opacity` and `transform` only — never `height`, `width`, `margin`, or `top/left`. Transform and opacity do not trigger layout and do not count toward CLS.
- Reserve the final dimensions of any transitioning container before the transition starts.
- Respect `prefers-reduced-motion`.
- Measure: run Lighthouse and field CWV with transitions enabled, not just in a clean dev environment.

### 8.16 Livewire 4 pre-launch verification checklist

**Server-render integrity**
- [ ] Every indexable route renders complete content with JS disabled (`curl` + assert in CI).
- [ ] **No `@island(lazy:)`, `@island(defer:)`, or `@island(skip:)` on any island containing indexable content** — grep the codebase for `lazy:`, `defer:`, `skip:` and review every hit.
- [ ] No `#[Lazy]` / `@lazy` / `lazy` attribute on any component rendering indexable content.
- [ ] No `@if` gating of indexable content behind interaction state — `wire:show` or `<details>` used instead.
- [ ] No indexable text sourced only from `wire:text`; no `href`/`src`/`alt` sourced only from `wire:bind`.
- [ ] Visible product price === `Product.offers.price` in the JSON-LD, on initial render and after a variant change.

**Head and schema**
- [ ] Every routable page component supplies a full `Seo` object; no page relies on `#[Title]` alone.
- [ ] Exactly one `<title>`, one `<meta name="description">`, one `<link rel="canonical">`, one H1, one JSON-LD script per document (CI test).
- [ ] JSON-LD lives at the end of `<body>` and is not inside any island or interactive component.
- [ ] `livewire:navigated` listener updates canonical / description / og:* — verified by navigating product → blog → ingredient page.

**Links and crawl paths**
- [ ] All pagination, category, product, and breadcrumb links are `<a href>` with real URLs.
- [ ] Real `?page=n` pagination exists and works with JS disabled; any island-append "Load more" is layered on top, not instead.
- [ ] Filter/sort URLs emit `noindex,follow` + a params-stripped canonical.
- [ ] Clicking "add to cart" does not alter URL, title, canonical, or body content.
- [ ] `/livewire/update` returns no indexable content and is not linked with `<a href>`.
- [ ] Search Console → URL Inspection → *Test live URL* → **zero page resources blocked by robots.txt** (critical given v4's scoped asset files under `/livewire/`).
- [ ] Rendered HTML in URL Inspection matches the raw `curl` HTML for `/`, `/shop`, a category, a product, a blog post, and an ingredient page.

**Performance**
- [ ] Lighthouse INP < 150ms on the product page with a filter interaction and a variant change.
- [ ] CSS/JS request count and total bytes within the §7.5 budgets **with** single-file scoped assets included.
- [ ] `wire:transition` animates transform/opacity only; CLS < 0.05 measured with transitions enabled.
- [ ] `data-navigate-track` present on Vite asset tags.

**Admin isolation**
- [ ] `/admin` (Filament 5) returns `X-Robots-Tag: noindex, nofollow`.
- [ ] No Filament assets loaded on storefront pages.

**Forms**
- [ ] Contact form submits and succeeds with JavaScript disabled.

---

## 9. Measurement

### 9.1 Search Console (day 0)

- Verify **both** `https://glowhalal.com` (URL-prefix) **and** the `glowhalal.com` Domain property (DNS TXT). The Domain property captures every subdomain and protocol; the URL-prefix property is required for some legacy reports.
- Submit `/sitemap.xml`. Confirm each child sitemap reports independently.
- Set international targeting: country = Pakistan (URL-prefix property).
- Request indexing manually for the ~10 launch URLs.
- Enable email alerts for manual actions, security issues, and coverage spikes.
- **Enable the BigQuery bulk data export** on day 1. GSC's UI retains 16 months; the export is unsampled and unlimited. On a new domain, the first year of data is the most valuable dataset you will ever have — and you cannot retroactively create it.
- Weekly monitoring: Coverage (Indexed vs Discovered-not-indexed vs Crawled-not-indexed), Core Web Vitals, Enhancements (Products, Breadcrumbs, FAQ, Merchant listings), Manual actions.

**Launch-critical GSC checks (week 1–4):**
- "Discovered – currently not indexed" on a new domain is normal for 2–6 weeks. Do **not** panic-edit content.
- "Crawled – currently not indexed" is the real warning: it means Google fetched it and judged it not worth indexing. Fix = more depth, more uniqueness, more internal links, not more pages.
- Verify the `noindex` set is actually excluded and the `index` set is actually included. Any mismatch = a bug in `SeoMeta`.

### 9.2 Bing Webmaster Tools + IndexNow

- Verify the site in Bing Webmaster Tools (import from GSC), submit the sitemap.
- **Implement IndexNow** — Bing/Yandex index near-instantly from a ping. On publish/update of any indexable model, POST the URL to the IndexNow endpoint. Cheap, and it accelerates discovery on a domain with no crawl history. (It has no effect on Google; do it anyway.)

### 9.3 GA4

**Setup:** GA4 property, enhanced measurement on, PKR as currency, Pakistan as reporting time zone, **Google Signals off** unless a privacy policy covers it, internal-traffic IP filter for the team, cross-domain N/A, data retention set to **14 months** (the maximum) on day 1 — the default 2 months silently destroys year-one data.

**Ecommerce events (MUST, standard GA4 names — never custom names for standard actions):**

| Event | Trigger |
|---|---|
| `view_item_list` | Category / shop / ingredient-page product block impressions |
| `select_item` | Product card click |
| `view_item` | Product detail page view |
| `add_to_cart` | Add to cart |
| `remove_from_cart` | Remove from cart |
| `view_cart` | Cart page |
| `begin_checkout` | Checkout start |
| `add_shipping_info` | Shipping step |
| `add_payment_info` | Payment step |
| `purchase` | Order confirmation — with `transaction_id`, `value`, `currency: "PKR"`, `items[]` |
| `refund` | Refund processed |

Every `items[]` entry MUST carry `item_id`, `item_name`, `item_category` (and `item_category2` for sub-category), `price`, `quantity`, `item_brand`, plus custom dimensions `halal_certified`, `alcohol_free`, `carmine_free`.

**Content and lead events (custom, the ones that prove this strategy works):**

| Event | Trigger | Params |
|---|---|---|
| `generate_lead` | Contact form success | `form_type` (support / ingredient / wholesale / press) |
| `whatsapp_click` | WhatsApp link click | `page_location` |
| `ingredient_page_view` | Ingredient glossary page view | `ingredient_name`, `halal_status` |
| `ingredient_to_product_click` | Click from an ingredient page into the product block | `ingredient_name`, `item_id` — **this is the single most important custom event on the site**: it measures whether the whole thin-catalog content strategy actually converts |
| `blog_to_shop_click` | Blog → commerce click | `post_slug`, `destination` |
| `scroll_75` | 75% article scroll | `post_slug` |
| `read_complete` | 90% scroll + ≥60s dwell | `post_slug` |
| `faq_expand` | FAQ accordion open | `question` |
| `certificate_view` | Halal certificate image opened | `page_location` |
| `review_submitted` | Review submitted | `item_id`, `rating` |
| `newsletter_signup` | Email capture | `source_block` |
| `search_site` | Internal site search | `search_term` — mine weekly for content ideas |
| `web_vitals` | `web-vitals` JS library | `metric_name`, `metric_value`, `metric_rating`, `page_path` |

**Key conversions to mark:** `purchase`, `generate_lead`, `whatsapp_click`, `newsletter_signup`.

**Segmentation (MUST):** build an Explorations report splitting organic traffic into:
1. **Branded** — session source = google/organic AND landing page = `/`, or GSC query matching `glow ?halal`
2. **Non-branded commercial** — landing on `/shop*` or `/product/*`
3. **Non-branded informational** — landing on `/blog/*` or `/halal-ingredients/*`

Segment 3 is what this strategy is built to grow. Reporting blended "organic traffic up 40%" without this split is meaningless on a brand-new domain, where brand-search growth from paid social can mask total informational failure.

**Link GSC ↔ GA4** so query data appears alongside behaviour.

### 9.4 Rank tracking

Track weekly, mobile + desktop, geo = Pakistan (and a second global-English project for the ingredient set).

**Tier 1 — brand (must own by week 4):** glow halal · glowhalal · glow halal pakistan · glow halal cosmetics · glow halal reviews

**Tier 2 — near-term achievable (the 90-day scorecard):** is carmine halal · is glycerin halal · is collagen halal · is gelatin in cosmetics halal · haram ingredients in makeup · is nail polish halal · can you pray with nail polish on · halal makeup brands in pakistan · wudu friendly nail polish pakistan · halal certification for cosmetics in pakistan · how to check if makeup is halal

**Tier 3 — mid-term commercial (6–12 months):** halal lipstick pakistan · alcohol free makeup pakistan · halal skincare pakistan · breathable nail polish pakistan · halal foundation pakistan · buy halal cosmetics online pakistan · best halal lipstick in pakistan

**Tier 4 — long-term head (12+ months, report but do not judge performance on):** halal makeup pakistan · halal cosmetics pakistan · halal nail polish · halal certified makeup

**Also track:** SERP feature ownership (featured snippet / PAA presence / image pack / sitelinks) per Tier-2 keyword, and **AI Overview citation presence** — for a "is X halal" query set, being the cited source in an AI Overview is increasingly the whole prize.

### 9.5 Reporting cadence and honest KPIs

| Cadence | Report |
|---|---|
| Weekly | Indexation count, Tier 1+2 rankings, GSC impressions/clicks, new 404s, CWV field regressions |
| Monthly | Full performance vs targets, **cannibalisation audit (GSC page+query)**, content published vs plan, new referring domains, top gaining/losing queries |
| Quarterly | Strategy review, keyword-universe refresh (re-run Keyword Planner), competitor content/link movement, algorithm-update correlation |

**Realistic month-by-month targets — hold the founder to these, not to fantasy:**

| Month | Indexed URLs | Non-brand organic clicks/mo | Tier-2 keywords in top 10 | Referring domains |
|---|---|---|---|---|
| 1 | 10–15 | 0–50 | 0 | 0–2 |
| 3 | 25–35 | 150–500 | 2–4 | 3–8 |
| 6 | 40–55 | 800–2,000 | 6–10 | 10–20 |
| 12 | 60–90 | 3,000–8,000 | 12–18 + 2–4 Tier-3 | 30–60 |

These assume the content plan in §5 ships on schedule and that link acquisition actually happens. **Content alone will plateau around month 6–8 without links.** Budget for digital PR from month 3: the halal-certification and haram-ingredients research pieces are the natural link magnets, and Pakistani lifestyle/business press plus global halal-industry publications are the natural targets.

---

## 10. Launch acceptance checklist

Nothing ships until every box is ticked.

**Indexability**
- [ ] `robots.txt` live and correct; staging is Disallowed + Basic-Auth'd
- [ ] `/sitemap.xml` index + all child sitemaps return 200 and validate
- [ ] Every sitemap URL returns 200, self-canonicalises, and is not `noindex` (CI test)
- [ ] Exactly one canonical tag per page, absolute, correct (CI test)
- [ ] `noindex` applied to: cart, checkout, account, auth, search, tags, facets, thank-you, 404
- [ ] www → apex, http → https, trailing slash, and lowercase all enforced in a **single** 301

**On-page**
- [ ] Every page has a unique title (≤60) and a unique meta description (140–160)
- [ ] Exactly one H1 per page; no heading-level skips (CI test)
- [ ] Keyword registry populated; CI test proves no duplicate primary keywords
- [ ] Every image has a meaningful `alt` (CI test); LCP image is not lazy-loaded
- [ ] No orphan pages — every sitemap URL has ≥1 internal inbound link (CI test)

**Structured data**
- [ ] Organization, WebSite+SearchAction on every page; `/search?q=` works as a real GET route
- [ ] Product + Offer + shipping + return policy validate in the Rich Results Test
- [ ] **No `aggregateRating` emitted** (zero real reviews at launch)
- [ ] BreadcrumbList on every non-home page, matching visible breadcrumbs
- [ ] Article/BlogPosting with a real named author and real dates
- [ ] FAQPage only where FAQs are visible
- [ ] LocalBusiness emitted only if a real physical location exists

**Livewire 4**
- [ ] All §8.16 items verified — in particular: zero `lazy`/`defer`/`skip` islands on indexable content, real `?page=n` pagination, and a full `Seo` object on every routable page component

**Performance**
- [ ] Mobile p75 LCP < 2.0s, INP < 150ms, CLS < 0.05 on `/`, `/shop`, a product page, a blog post
- [ ] JS ≤ 150 KB, CSS ≤ 40 KB, product page ≤ 900 KB
- [ ] Lighthouse CI budgets enforced on every PR

**Content**
- [ ] Phase 0 pages live: `/about`, `/contact`, `/halal-certification`, `/faq`, `/editorial-policy`
- [ ] No category page with 0 products; no indexable page under 800 words
- [ ] Author bios with real credentials live at `/blog/author/{slug}`

**Measurement**
- [ ] GSC (both property types) verified, sitemap submitted, BigQuery export enabled
- [ ] Bing Webmaster Tools verified; IndexNow wired to publish/update
- [ ] GA4 live with data retention = 14 months, ecommerce + custom events firing, GSC linked
- [ ] `ingredient_to_product_click` verified in DebugView
- [ ] Rank tracking configured for Tiers 1–4

---

## 11. Open items requiring founder input

1. **Physical location?** Determines whether `LocalBusiness` schema and a Google Business Profile apply (§4.12).
2. **Certifying body and certificate number** — needed for `hasCertification`, `/halal-certification`, and the whole E-E-A-T foundation. Which body: SANHA Pakistan, a PSQCA-recognised body, or a foreign certifier?
3. **Named author(s) and religious reviewer** — the ingredient cluster cannot ship credibly with anonymous bylines. Who signs these pages?
4. **Legal review budget for `/blog/cosmetic-brands-haram-ingredients`** — highest-upside, highest-risk asset (§5.3).
5. **Urdu timeline** — affects whether locale-aware routing is built now (cheap) or retrofitted later (expensive).
6. **Link-building budget from month 3** — content alone plateaus without it (§9.5).
7. **Launch categories** — confirm the 2–3 categories the 3 products map to, so no thin category pages get created.

---

## Sources

- [Mordor Intelligence — Halal Cosmetic Products Market](https://www.mordorintelligence.com/industry-reports/halal-cosmetic-products-market)
- [International Journal of Halal Industry — Global trends in the halal beauty and skincare industry: a search-engine-based market analysis](https://journal.uii.ac.id/IJHI/article/view/39574)
- [Business Research Insights — Halal Cosmetics and Personal Care Products Market](https://www.businessresearchinsights.com/market-reports/halal-cosmetics-and-personal-care-products-market-109988)
- [PSQCA — Halaal Division (PS 3733, PS 5319-2014)](http://updated.psqca.com.pk/standardization/division/halaal-division/)
- [SANHA Pakistan — halal certification](https://www.sanha.org.pk/)
- [Halal Code Check — Halal cosmetics ingredients guide](https://halalcodecheck.com/blog/halal-cosmetics-ingredients-guide/)
- [Claudia Nour — What is halal makeup: more than alcohol, pork and carmine](https://claudianour.com/blogs/modest-makeup/what-is-halal-makeup-more-than-just-alcohol-pork-and-carmine)
- [Eluxe Magazine — Best halal nail polish brands](https://eluxemagazine.com/beauty/best-halal-nail-polish/)
- [Gharpar — List of halal makeup brands in Pakistan](https://gharpar.co/list-of-halal-makeup-brands-in-pakistan/)
- [Khubsurti — Complete guide to halal makeup in Pakistan](https://khubsurti.pk/blogs/news/the-complete-guide-to-halal-makeup-in-pakistan-best-brands-products)
- [Dermagin — Top halal skincare products in Pakistan](https://dermagin.com/top-halal-skincare-products-in-pakistan/)
- [Masarrat Makeup](https://masarratmakeup.com/) · [HAYA Beauty](https://www.haya-beauty.com/) · [Daraz halal cosmetics tag](https://www.daraz.pk/tag/halal-cosmetics/)
- [Livewire 4 — Islands documentation](https://livewire.laravel.com/docs/islands)
- [Livewire 4 — wire:navigate documentation](https://livewire.laravel.com/docs/navigate)
- [Livewire 4 — lazy loading documentation](https://livewire.laravel.com/docs/lazy)
- [Livewire 4 — scoped styles documentation](https://livewire.laravel.com/docs/styles)
- [Laravel News — Everything new in Livewire 4](https://laravel-news.com/everything-new-in-livewire-4)
