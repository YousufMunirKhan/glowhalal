# Glow Halal — Organic Growth Plan to 10,000 Sessions/Month

**Written:** 15 August 2026 · **Site:** https://glowhalal.com · **Goal:** 10,000 organic
sessions/month · **Honest ETA:** **August 2027 (12 months)**, with a realistic risk band of
14–18 months if publishing drops below 3 posts/week or link acquisition stalls.

> This document builds on — and does **not** repeat — `docs/seo-aeo-status-report.md`,
> `docs/keyword-research-aug2026.md`, `docs/seo-migration-and-content-plan.md` and
> `docs/marketing-30day-plan.md`. Where it contradicts them, the live audit in §2 wins.
>
> **Compliance floor (inherited, non-negotiable):** no cure/treat/heal claims; no
> halal-certification claim or `hasCertification` node; no fake reviews or `aggregateRating`;
> no manufacturer/origin claim (Glow Halal is a **reseller**); EN and Roman-Urdu versions of a
> topic must target **different primary keywords**; Roman Urdu locale is `ur-Latn`.

---

## 0. TL;DR

- **The traffic model closes at ~10,075 clicks/month** across 11 clusters, needing **~165
  indexable URLs** (currently 47) at an average position of 4–8. §1.
- **The foundation is genuinely done** — schema, hreflang, sitemap, llms.txt, IndexNow,
  bilingual routing, feeds. The gap is **volume of content and zero off-page authority**, not
  technical debt. §2, §3.
- **The single largest untapped asset is Roman Urdu.** 7 of 15 live posts are Roman Urdu and
  no PK herbal competitor publishes any. Roman Urdu carries **~55% of the modelled traffic**. §5.
- **Three live bugs are actively costing money**: a wrong WhatsApp number in a published post,
  a three-way cannibalization on "lookman e hayat oil price", and a `/shop` H1 that makes a
  manufacturer claim the brand is not allowed to make. §6.
- **Cadence must go from ~1/day-for-8-days (the finished drip) to a sustained 3/week rising to
  5/week.** 59 new articles + 6 reference pages are specified in §4.

---

## 1. Traffic model — how 10,000 clicks/month is actually assembled

### 1.1 Method and honesty note

WebSearch is US-locale and Google Keyword Planner is not connected (see
`keyword-research-aug2026.md` §6), so **volumes below are directional PK estimates** derived
from autocomplete breadth, SERP-result counts, competitor page counts on the same terms, and
Pakistan's ~130M-user search base. They are deliberately conservative on commercial "price"
terms (dominated by established stores) and aggressive on Roman-Urdu informational terms
(structurally empty SERPs). **Re-baseline every number against GSC impressions at Day 90.**

CTR assumptions used: pos 1 → 27%, pos 2–3 → 13%, pos 4–6 → 6.5%, pos 7–10 → 2.5%.

### 1.2 The model

| # | Cluster | URLs | Est. combined PK volume/mo | Target avg. position | Blended CTR | Est. clicks/mo | Lands by |
|---|---|---|---|---|---|---|---|
| 1 | Lookman-e-Hayat / branded oil (live) | 9 | 1,500 | 1–3 | 25% | **375** | Nov 2026 |
| 2 | Salajeet / Shilajit (EN + UR) | 12 | 20,000 | 4–8 | 6% | **1,200** | May 2027 |
| 3 | Asgandh / Ashwagandha (EN + UR) | 9 | 12,000 | 4–8 | 6% | **700** | May 2027 |
| 4 | Balon ka tel / hair oils (UR-heavy) | 14 | 30,000 | 5–9 | 5% | **1,500** | Jun 2027 |
| 5 | Beri / Sidr (EN + UR) | 7 | 4,000 | 1–3 | 20% | **800** | Feb 2027 |
| 6 | Chehra / face & skin care (EN + UR) | 14 | 25,000 | 5–9 | 4% | **1,000** | Jul 2027 |
| 7 | Jari booti / ingredient education (EN + UR) | 22 | 60,000 | 5–9 | 4% | **2,400** | Aug 2027 |
| 8 | Halal ingredient index ("is X halal") — global EN | 9 | 40,000 (worldwide) | 3–7 | 3% | **1,200** | Apr 2027 |
| 9 | Commercial "price in pakistan" / category pages | 12 | 8,000 | 5–9 | 5% | **400** | Jun 2027 |
| 10 | Seasonal (Ramzan, sardi, dulhan) | 6 | 10,000 | 6–10 | 4% | **400** | Feb 2027 peak |
| 11 | Brand + navigational ("glow halal") | 5 | 250 | 1 | 40% | **100** | Nov 2026 |
| | **TOTAL** | **~119 content URLs (+46 existing/commerce = ~165)** | | | | **10,075** | **Aug 2027** |

**Read this honestly:**

- Cluster 8 is **not Pakistani traffic**. "is glycerin halal" / "is collagen halal" demand is
  mostly Indonesia, Malaysia, UK, US. It is real traffic that counts toward 10k and it is the
  easiest cluster to rank (near-zero competition, already 3 pages live), but it will convert at
  a fraction of PK COD traffic. If the owner wants **10,000 PK sessions specifically**, remove
  cluster 8 and add ~1,200 clicks to clusters 4 and 7 — which pushes the date to **Nov 2027**.
- Clusters 2, 3 and 7 depend on **publishing the herb education even before the SKU exists**.
  That is the correct order: content earns the ranking first, the product page inherits the
  internal-link equity when it launches.
- No cluster assumes a #1 for a head commercial term like "shilajit price in pakistan" — a
  zero-authority domain against chiltanpure.com, pakwild.com and an exact-match domain
  (shilajitpriceinpakistan.pk) will not win that inside 12 months.

### 1.3 Monthly trajectory (the honest curve)

| Checkpoint | Date | Indexed URLs | Organic sessions/mo | What is driving it |
|---|---|---|---|---|
| Baseline | 15 Aug 2026 | 47 | ~0–50 | Nothing indexed long enough |
| Month 1 | 19 Sep 2026 | 60 | 100–250 | Brand + Lookman-e-Hayat long-tail |
| Month 3 | 18 Nov 2026 | 85 | 800–1,300 | Clusters 1, 5, 11; first UR posts maturing |
| Month 6 | 16 Feb 2027 | 120 | 2,500–3,500 | Clusters 2, 3, 5, 8, 10 (Ramzan/wedding peak) |
| Month 9 | 17 May 2027 | 150 | 5,500–7,000 | Clusters 2, 3, 4 at scale + first 20 ref. domains |
| **Month 12** | **20 Aug 2027** | **165** | **10,000–12,000** | Cluster 7 (jari booti) compounding + authority |

SEO on a domain with no backlink profile does not go linear. Expect a flat Months 1–2, a step
change around Month 4 (Google's evaluation lag on new-domain topical clusters), and compounding
from Month 7. **Do not judge the plan before Day 120.**

---

## 2. Live audit — 15 August 2026 (what the 11 Aug report claims vs. what is actually live)

### 2.1 Verified accurate (credit where due)

| Claim | Live status |
|---|---|
| Sitemap grows automatically | ✅ **47 URLs** now, up from 31 on 11 Aug |
| Bilingual EN + `ur-Latn` with reciprocal hreflang | ✅ 8 EN posts + 7 UR posts, hub-level hreflang on `/blog` ↔ `/ur-roman/blog` |
| `/llms.txt` dynamic, includes live prices | ✅ HTTP 200, lists Rs 1,200 / Rs 2,200, WhatsApp, safety line |
| Answer boxes on posts | ✅ Verified on `/blog/shilajit-side-effects-honest-guide` and `/ur-roman/blog/asli-salajeet-ki-pehchan` |
| No `aggregateRating`, no cert claim | ✅ `/reviews` explicitly states "that space stays honestly empty" |
| Product schema with shipping + returns | ✅ Rs 300 flat / free over Rs 5,000 rendered visibly and in `Offer` |
| Robots.txt with feed Allow | ✅ Matches `public/robots.txt` in repo |
| PDP → guide internal links | ✅ 3 contextual guide links on the 50ml PDP |

### 2.2 Drift and defects found live (not in the 11 Aug report)

| # | Finding | Evidence | Severity |
|---|---|---|---|
| D1 | **Wrong WhatsApp number published** | `/blog/lookman-e-hayat-oil-price-in-pakistan` body: "WhatsApp at **0341-7164556**". Every other surface (llms.txt, footer, `StoreSettings`) says **0301 2973886** | 🔴 Critical — NAP inconsistency + lost orders |
| D2 | **3-way cannibalization on price intent** | `/blog/lookman-e-hayat-oil-price-in-pakistan` (exact-match title+H1) vs `/blog/lookman-e-hayat-oil-uses-benefits-price` (H2 "Lookman-e-Hayat oil price in Pakistan", "price" in slug + title) vs PDP title "Buy Lookman e Hayat Oil 50ml – **Rs 1,200 COD Pakistan**" | 🔴 Critical |
| D3 | **`/shop` H1 = "Everything we make"** and intro "ingredients we will **not formulate with**" | Live on `/shop`. Glow Halal is a reseller — this is a manufacturer claim, and it targets zero keywords | 🔴 Critical (compliance + SEO) |
| D4 | Homepage delivery copy contradicts PDP + schema | Homepage: "2–3 working days in Lahore, Karachi and Islamabad. 3–5 elsewhere". PDP + `Offer` schema: "2–4 working days to major cities; 4–7 days elsewhere" | 🟠 High |
| D5 | Homepage roadmap is fictional relative to the real plan | Live "In development": Hydrating Serum (Nov 2026), Mineral Sunscreen (Jan 2027), Cream Blush (Mar 2027). The actual expansion per `keyword-research-aug2026.md` is sidr soap, shilajit, ashwagandha, hair oil, face cream | 🟠 High |
| D6 | Homepage "Latest guides" band shows only **2** posts | The status report names this band as Google's primary discovery path for new posts. It surfaces 2 of 15 | 🟠 High |
| D7 | `/ur-roman/blog/category/{slug}` is **absent from sitemap.xml** | Sitemap contains `/blog/category/herbal-care` but no `ur-roman` equivalent, although the route `blog.category.ur` exists | 🟠 High |
| D8 | Author byline is **"Glow Halal Editorial"** | On every post. `seo-migration-and-content-plan.md` §B4 mandates "real named author + credentials (no 'Admin'/'Team' bylines)" for YMYL E-E-A-T | 🟠 High |
| D9 | `/blog` title tag has no target keyword | "Journal - Ingredient Guides & Halal Formulation Notes" — nobody in Pakistan searches that string | 🟡 Medium |
| D10 | `launchMonth` still says "June 2026" | Site relaunched Aug 2026 | 🟡 Medium |
| D11 | PDP has a **duplicated H2 "What is in it"** | Live on `/products/herbal-skin-oil-50ml` | 🟡 Medium |
| D12 | No Roman-Urdu commercial surface at all | `/ur-roman` serves **blog only**. A Roman-Urdu reader who lands on `asli-salajeet-ki-pehchan` has no UR shop, UR product page, or UR homepage to convert on | 🟡 Medium (large upside) |
| D13 | `SchemaGraph::webPage()` hardcodes `inLanguage: en-PK` | Fine today (only EN pages use it), guaranteed wrong the moment D12 is fixed | 🟡 Medium (pre-emptive) |
| D14 | `/shop` has one category (`oils`) and 2 SKUs | The category tree cannot carry cluster 9 until SKUs exist | 🟡 Medium |

---

## 3. Gap analysis — done vs. missing

### 3.1 Already done — do not rebuild

- **Crawl/index plumbing**: robots.txt, dynamic sitemap with mirrored `xhtml:link` alternates,
  self-canonicals, pagination soft-404 guard, one-hop www→apex, legacy WP 301 map, 410s on
  `/wp-*`.
- **Structured data**: `OnlineStore` + `WebSite` + `WebPage` + `ItemList` + `BreadcrumbList`;
  `Product` + `Offer` with `OfferShippingDetails` and `hasMerchantReturnPolicy`; `FAQPage` on
  visible Q&As; `BlogPosting` with real dates, per-locale `inLanguage`, cover image.
- **Bilingual architecture**: `/ur-roman/blog/*` behind `SetLocale`, reciprocal hreflang cluster
  + `x-default`, distinct primary keywords per language. This is the hardest thing to retrofit
  and it is already correct.
- **AEO**: `/llms.txt` generated from live catalogue, IndexNow daily ping, 40–55-word answer
  boxes, honest `knowsAbout` / `ContactPoint`.
- **Commerce/ads**: `/feed/google.xml`, `/feed/meta.csv`, GA4 e-commerce, Consent Mode v2.
- **Compliance discipline**: `/reviews` policy page, `/what-we-never-use`, `/disclaimer`, safety
  line on burn content, no cert claim anywhere in `SchemaGraph.php`.
- **Keyword research**: `keyword-research-aug2026.md` is good work. §4 below **extends** it into
  ingredient pillars; it does not replace it.

### 3.2 Actually missing (this is the whole job)

| Gap | Size | Where it is solved |
|---|---|---|
| **Content volume** — 15 posts live, model needs ~119 content URLs | Largest gap by far | §4 |
| **Off-page authority** — 0 deliberately-earned referring domains | Second-largest | §7 |
| **Herb pillar pages** — no `/blog` pillar owns "shilajit", "ashwagandha", "kalonji", "sidr" as a topic; only scattered posts | Blocks clusters 2, 3, 4, 7 | §4, §5 |
| **Roman-Urdu commercial surface** — UR blog exists, UR shop/product/home do not | Blocks conversion of 55% of modelled traffic | §5.3, §6 (F10) |
| **Named human author + reviewer** (E-E-A-T on YMYL herbal content) | Caps ranking ceiling on cluster 2/3 | §6 (F8) |
| **Halal ingredient index depth** — 3 pages live, 9 needed | Blocks cluster 8 | §4 Pillar H |
| **Category pages for planned herbs** — cannot exist before SKUs; must be planned so blog posts internal-link into them on day 1 | Blocks cluster 9 | §4 Pillar J |
| **CWV field data** — never measured; no CrUX/PSI number appears in any doc | Unknown risk | §6 (F16) |
| **GSC/GA4 baseline export** — flagged as missing since the migration plan; still missing | Blocks the Phase-2.5 cannibalization audit becoming data-driven | §8 |

### 3.3 Cannibalization audit — pre-GSC method (GSC access not yet available)

Applying the sitemap + query-intent fallback across all 47 live URLs:

| Query | Candidate URLs | Current likely winner | **Designated owner** | Action |
|---|---|---|---|---|
| `lookman e hayat oil price in pakistan` | `/blog/lookman-e-hayat-oil-price-in-pakistan`, `/blog/lookman-e-hayat-oil-uses-benefits-price`, `/products/herbal-skin-oil-50ml` | PDP (authority) | **`/blog/lookman-e-hayat-oil-price-in-pakistan`** | De-optimise the other two — see F2 |
| `lookman e hayat oil uses / benefits` | `/blog/lookman-e-hayat-oil-uses-benefits-price` | itself | **itself** (rename to drop "Price") | Retitle |
| `buy lookman e hayat oil 50ml` | PDP 50ml, PDP 100ml | PDP 50ml | **PDP 50ml** | OK, no conflict |
| `asli lookman e hayat tel qeemat` (UR) | `/ur-roman/blog/asli-lookman-e-hayat-tel-kahan-se-lein` | itself | **itself** | ✅ clean — different language, different keyword |
| `shilajit side effects` | `/blog/shilajit-side-effects-honest-guide` | itself | **itself** | ✅ clean |
| `asli salajeet ki pehchan` | `/ur-roman/blog/asli-salajeet-ki-pehchan` | itself | **itself** | ✅ clean |
| `best halal herbal oil pakistan` | `/blog/best-halal-herbal-oils-in-pakistan`, `/shop/oils`, `/shop` | blog post | **`/blog/best-halal-herbal-oils-in-pakistan`** for info; **`/shop/oils`** for "herbal oil price in pakistan" | Retitle `/shop` (F3) so it stops competing |
| `herbal oil for hair pakistan` | `/blog/how-to-use-herbal-oil-for-hair-champi`, `/blog/best-halal-herbal-oils-in-pakistan` | champi post | **champi post** | Watch — add F6/F8 (§4) as the dedicated owners |

**Verdict:** exactly **one** active conflict (the price triangle, D2). Everything else is clean —
the bilingual deconfliction rule has been enforced properly. Re-run this audit with real GSC
`page + query` data at Day 60, and again before every batch of 10 new posts.

**Standing rule for every new article in §4:** before it is written, grep the existing corpus for
its primary keyword. If the string already appears in another page's `<title>` or `H1`, the new
article must take a distinct long-tail modifier or the topic must be merged.

---

## 4. Content plan — 59 articles + 6 reference pages

Rules applied to every row: EN and UR versions of the same herb take **different primary
keywords and different angles** (never a translation); every post opens with a 40–55-word answer
box; every post carries an FAQ built from live PAA/autocomplete; every post links to ≥1 commerce
URL and ≥2 sibling posts; cosmetic/traditional-use language only; no DRAP-restricted claim.

Internal-link shorthand: `PDP50` = `/products/herbal-skin-oil-50ml`, `PDP100` =
`/products/herbal-skin-oil-100ml`, `OILS` = `/shop/oils`, `HI` = `/halal-ingredients`,
`WWNU` = `/what-we-never-use`.

### Pillar A — Salajeet / Shilajit (10 new; 2 live)

| # | Title | Primary keyword | Lang | Intent | Words | Links to | Pri |
|---|---|---|---|---|---|---|---|
| A1 | Shilajit Price in Pakistan 2026: What Real Resin Costs (and Why Cheap Is a Red Flag) | shilajit price in pakistan | EN | Comm | 1,800 | A5, live shilajit post, `OILS` | P1 |
| A2 | Salajeet Ke Fayde Aur Nuqsanat: Mukammal Roman Urdu Guide | salajeet ke fayde | UR | Info | 2,000 | A4, live UR pehchan post, `HI` | **P1** |
| A3 | How to Use Shilajit: Dose, Timing and What to Avoid | how to use shilajit | EN | Info | 1,500 | A1, A7, live shilajit post | P1 |
| A4 | Salajeet Istemal Karne Ka Tarika: Miqdaar Aur Waqt | salajeet istemal karne ka tarika | UR | Info | 1,400 | A2, A6 | P1 |
| A5 | Shilajit Resin vs Powder vs Capsules: Which to Buy in Pakistan | shilajit resin vs powder | EN | Comm | 1,500 | A1, A3 | P2 |
| A6 | Naqli Salajeet Se Kaise Bachein: Kharidne Se Pehle 7 Sawal | naqli salajeet | UR | Info | 1,300 | live UR pehchan post, A2 | P2 |
| A7 | Is Shilajit Halal? What the Ingredient Actually Is | is shilajit halal | EN | Info | 1,200 | `HI`, `WWNU`, A3 | P2 |
| A8 | Salajeet Khawateen Ke Liye: Fayde, Ehtiyat Aur Aam Sawalat | khawateen ke liye salajeet | UR | Info | 1,400 | A2, A4 | P2 |
| A9 | Gilgit-Baltistan Shilajit: How Pakistani Resin Is Collected and Graded | gilgit baltistan shilajit | EN | Info | 1,600 | A1, A5 | P3 |
| A10 | Salajeet Ki Qeemat Pakistan Mein: Asli Aur Naqli Ka Farq | salajeet ki qeemat | UR | Comm | 1,200 | A6, A2, `OILS` | P2 |

### Pillar B — Asgandh / Ashwagandha (7)

| # | Title | Primary keyword | Lang | Intent | Words | Links to | Pri |
|---|---|---|---|---|---|---|---|
| B1 | Ashwagandha Benefits for Men: What the Research Actually Shows | ashwagandha benefits for men | EN | Info | 1,800 | B6, B5, A3 | **P1** |
| B2 | Asgandh Nagori Ke Fayde: Roman Urdu Mein Mukammal Maloomat | asgandh nagori ke fayde | UR | Info | 1,800 | B4, B7, A2 | **P1** |
| B3 | Ashwagandha Benefits for Women: Sleep, Stress and the Honest Caveats | ashwagandha benefits for women | EN | Info | 1,600 | B6, B1 | P1 |
| B4 | Asgandh Khane Ka Tarika: Kitni Miqdaar, Kis Waqt | asgandh khane ka tarika | UR | Info | 1,300 | B2, B7 | P2 |
| B5 | Ashwagandha Price in Pakistan 2026: Powder, Capsule and Root Compared | ashwagandha price in pakistan | EN | Comm | 1,500 | B1, B3, `OILS` | P1 |
| B6 | Ashwagandha Side Effects: Who Should Not Take It | ashwagandha side effects | EN | Info | 1,400 | B1, B3, A7 | P2 |
| B7 | Asgandh Aur Salajeet Mein Farq: Kis Ke Liye Kya | asgandh aur salajeet mein farq | UR | Info | 1,200 | B2, A2 | P2 |

### Pillar C — Kalonji / black seed (6)

| # | Title | Primary keyword | Lang | Intent | Words | Links to | Pri |
|---|---|---|---|---|---|---|---|
| C1 | Kalonji Oil Benefits for Hair: Evidence, Uses and Limits | kalonji oil benefits for hair | EN | Info | 1,600 | F2, F8, `OILS` | **P1** |
| C2 | Kalonji Ke Fayde: Baal, Jild Aur Rozana Istemal | kalonji ke fayde | UR | Info | 2,000 | C4, C6, I1 | **P1** |
| C3 | Kalonji Oil Price in Pakistan: Cold-Pressed vs Refined | kalonji oil price in pakistan | EN | Comm | 1,400 | C1, C5, `OILS` | P2 |
| C4 | Kalonji Ka Tel Balon Mein Lagane Ka Tarika | kalonji ka tel balon mein lagane ka tarika | UR | Info | 1,300 | C2, F1 | P2 |
| C5 | Is Black Seed Oil Safe on the Face? A Cosmetic Guide | black seed oil for face | EN | Info | 1,400 | G2, C1, `WWNU` | P2 |
| C6 | Kalonji Ka Tel Aur Zaitoon Ka Tel: Kaunsa Kab Behtar | kalonji aur zaitoon ka tel | UR | Comm | 1,200 | C2, E2 | P3 |

### Pillar D — Beri / Sidr (5 new; 2 live)

| # | Title | Primary keyword | Lang | Intent | Words | Links to | Pri |
|---|---|---|---|---|---|---|---|
| D1 | Sidr Leaf Powder for Hair: How to Make and Use a Sidr Hair Mask | sidr powder for hair | EN | Info | 1,500 | live sidr post, F2, D5 | **P1** |
| D2 | Beri Ke Patton Ka Powder Ghar Par Banane Ka Tarika | beri ke patte ka powder | UR | Info | 1,300 | live UR beri post, D4 | P1 |
| D3 | Sidr Leaf Soap in Pakistan: What to Look For Before You Buy | sidr soap pakistan | EN | Comm | 1,300 | D1, D5, `/shop` | P2 |
| D4 | Beri Ke Patte Ka Sabun: Kya Hai Aur Kis Ke Liye | beri ke patte ka sabun | UR | Comm | 1,200 | D2, `/shop` | **P1** (empty SERP) |
| D5 | Sidr vs Henna vs Shikakai: Choosing a Natural Hair Wash | sidr vs henna for hair | EN | Comm | 1,500 | D1, F2 | P2 |

### Pillar E — Til, guggul and the existing oil cluster (5)

| # | Title | Primary keyword | Lang | Intent | Words | Links to | Pri |
|---|---|---|---|---|---|---|---|
| E1 | Sesame (Til) Oil for Skin: Traditional Uses and What It Actually Does | til oil for skin | EN | Info | 1,500 | `PDP50`, G2, E3 | P1 |
| E2 | Til Ke Tel Ke Fayde: Maalish, Jild Aur Baal | til ke tel ke fayde | UR | Info | 1,600 | E4, C2, `PDP100` | **P1** |
| E3 | What Is Guggul? The Resin Inside Traditional Herbal Oils | guggul benefits | EN | Info | 1,300 | `HI`, `PDP50`, E1 | P2 |
| E4 | Maalish Ka Tel Ghar Par Kaise Chunein | maalish ka tel | UR | Comm | 1,200 | E2, `OILS` | P2 |
| E5 | Old Marks and Scars: What Cosmetic Oils Can and Cannot Do | do oils fade old scars | EN | Info | 1,500 | `PDP50`, live cuts-and-burns post, `/disclaimer` | **P1** (honesty wedge) |

### Pillar F — Balon ka tel / hair (8)

| # | Title | Primary keyword | Lang | Intent | Words | Links to | Pri |
|---|---|---|---|---|---|---|---|
| F1 | Sabse Acha Balon Ka Tel Konsa Hai? Har Masle Ke Liye Sahi Intekhab | sabse acha balon ka tel | UR | Comm | 2,000 | C4, F3, F5, `OILS` | **P1** |
| F2 | Best Herbal Hair Oils in Pakistan 2026: An Honest Buyer's Guide | best hair oil in pakistan | EN | Comm | 2,200 | C1, F4, F8, `OILS` | **P1** |
| F3 | Balon Ki Khushki Ke Liye Ghareloo Dekh Bhaal | balon ki khushki ke liye | UR | Info | 1,500 | F1, C4 | P1 |
| F4 | Amla Oil Benefits for Hair — and Who Should Skip It | amla oil benefits for hair | EN | Info | 1,400 | F2, F8 | P2 |
| F5 | Sarson Ke Tel Ke Fayde Balon Ke Liye | sarson ka tel balon ke liye | UR | Info | 1,400 | F1, E2 | P2 |
| F6 | Hair Oiling (Champi) Frequency: How Often Is Too Often? | how often to oil hair | EN | Info | 1,200 | live champi post, F2 | P2 |
| F7 | Baal Ugane Wale Tel Ke Daawe: Kya Sach Hai, Kya Nahi | baal ugane ka tel | UR | Info | 1,500 | F1, F3, `/disclaimer` | P2 (mythbuster only) |
| F8 | Coconut vs Almond vs Mustard Oil for Hair: A Straight Comparison | best oil for hair comparison | EN | Comm | 1,600 | F2, F4, C1 | P2 |

### Pillar G — Chehra / face & skin (8)

| # | Title | Primary keyword | Lang | Intent | Words | Links to | Pri |
|---|---|---|---|---|---|---|---|
| G1 | Chehre Par Tel Lagane Ka Sahi Tarika Aur Fayde | chehre par tel lagane ka tarika | UR | Info | 1,500 | G7, E2, `PDP50` | **P1** |
| G2 | Herbal Face Oil for Oily Skin: Does It Actually Make Sense? | face oil for oily skin | EN | Info | 1,400 | E1, C5, `PDP50` | P1 |
| G3 | Multani Mitti Ke Fayde Aur Face Pack Banane Ka Tarika | multani mitti ke fayde | UR | Info | 1,800 | G5, G7, I1 | **P1** |
| G4 | Ubtan for Skin: The Traditional Recipe, Honestly Reviewed | ubtan for skin | EN | Info | 1,500 | G6, G8, `WWNU` | P2 |
| G5 | Arq E Gulab Ke Fayde: Chehre Ke Liye Istemal | arq e gulab ke fayde | UR | Info | 1,300 | G3, G1 | P1 |
| G6 | Rose Water for Face: Real Benefits vs Marketing | rose water for face | EN | Info | 1,300 | G4, G8 | P2 |
| G7 | Chehre Ke Daag Dhabbe: Ghareloo Ehtiyat Aur Jhoothe Daawe | chehre ke daag dhabbe | UR | Info | 1,600 | G1, E5, `/disclaimer` | P2 |
| G8 | Neem for Skin: What Neem Soap and Neem Oil Can Realistically Do | neem benefits for skin | EN | Info | 1,600 | G4, `WWNU`, `/shop` | P2 |

### Pillar H — Halal ingredient index (6 reference pages at `/halal-ingredients/{slug}` — NOT blog)

| # | Page | Primary keyword | Lang | Words | Pri |
|---|---|---|---|---|---|
| H1 | Is Gelatin Halal in Cosmetics? | is gelatin halal | EN | 900 | **P1** |
| H2 | Is Collagen Halal? Marine, Bovine and Synthetic | is collagen halal | EN | 1,000 | **P1** |
| H3 | Is Stearic Acid Halal? Plant vs Tallow Sources | is stearic acid halal | EN | 900 | P1 |
| H4 | Is Lanolin Halal? | is lanolin halal | EN | 800 | P2 |
| H5 | Is Keratin Halal? | is keratin halal | EN | 800 | P2 |
| H6 | Is Squalene Halal? Shark-Derived vs Olive-Derived | is squalene halal | EN | 900 | P2 |

Schema per §B4 of the migration plan: `Article` + `DefinedTerm` (`sameAs` → Wikipedia) +
`FAQPage`. **Never assert an unsourced religious ruling** — present the sourcing question
("this INCI can be plant- or animal-derived; here is how to tell") and link to `WWNU`.

### Pillar I — Jari booti glossary & Unani education (5)

| # | Title | Primary keyword | Lang | Intent | Words | Links to | Pri |
|---|---|---|---|---|---|---|---|
| I1 | Jari Bootiyon Ke Naam Aur Fayde: Roman Urdu List (Tasveeron Ke Sath) | jari booti ke naam | UR | Info | 2,500 | every UR pillar post | **P1** (link magnet) |
| I2 | A Glossary of Pakistani Herbs: Desi Names, English Names, Traditional Uses | pakistani herbs list | EN | Info | 2,500 | every EN pillar post, `HI` | **P1** (link magnet) |
| I3 | Unani Aur Ayurvedic Mein Farq Kya Hai | unani aur ayurvedic mein farq | UR | Info | 1,300 | I1, I5 | P2 |
| I4 | What Is Unani Medicine? A Plain-Language Explainer | what is unani medicine | EN | Info | 1,500 | I2, E3 | P2 |
| I5 | Pansari Se Jari Booti Kharidte Waqt Kya Dekhein | pansari se jari booti | UR | Info | 1,200 | I1, A6 | P2 |

### Pillar J — Commercial & seasonal (5)

| # | Title | Primary keyword | Lang | Intent | Words | Links to | Pri |
|---|---|---|---|---|---|---|---|
| J1 | Halal Herbal Products Online in Pakistan: How COD Ordering Works | herbal products online pakistan | EN | Comm | 1,200 | `/shop`, `/shipping-returns`, `/reviews` | P2 |
| J2 | Sardiyon Mein Khushk Jild Ke Liye Ghareloo Dekh Bhaal | sardiyon mein khushk jild | UR | Info | 1,400 | G1, `PDP100` | P1 — **publish 1 Nov 2026** |
| J3 | Winter Dry Skin in Pakistan: A Simple Oil-Based Routine | dry skin winter pakistan | EN | Info | 1,400 | G2, E1, `PDP100` | P1 — **publish 5 Nov 2026** |
| J4 | Dulhan Ki Skin Care Routine: Shadi Se 3 Mahine Pehle | dulhan skin care routine | UR | Comm | 1,800 | G1, G3, G5, `/shop` | P1 — **publish 1 Oct 2026** (wedding season) |
| J5 | Ramzan Mein Jild Aur Balon Ki Dekh Bhaal | ramzan mein jild ki dekhbhal | UR | Info | 1,400 | G1, F3 | P1 — **publish 15 Jan 2027** (Ramadan ~Feb 2027) |

### 4.1 The first 20, in publish order, with dates

Cadence: **Mon / Wed / Fri, 06:00 PKT** via the existing drip scheduler. Sequencing logic:
Roman Urdu first (emptiest SERPs, fastest wins), highest-volume pillar heads first, alternating
language so both hubs grow in step, and the two link-magnet glossaries early so later posts have
something authoritative to link to.

| # | Date (2026) | Ref | Title | Lang |
|---|---|---|---|---|
| 1 | Mon 24 Aug | A2 | Salajeet Ke Fayde Aur Nuqsanat: Mukammal Roman Urdu Guide | UR |
| 2 | Wed 26 Aug | C2 | Kalonji Ke Fayde: Baal, Jild Aur Rozana Istemal | UR |
| 3 | Fri 28 Aug | F1 | Sabse Acha Balon Ka Tel Konsa Hai? Har Masle Ke Liye Sahi Intekhab | UR |
| 4 | Mon 31 Aug | A1 | Shilajit Price in Pakistan 2026: What Real Resin Costs | EN |
| 5 | Wed 2 Sep | B2 | Asgandh Nagori Ke Fayde: Roman Urdu Mein Mukammal Maloomat | UR |
| 6 | Fri 4 Sep | I1 | Jari Bootiyon Ke Naam Aur Fayde: Roman Urdu List | UR |
| 7 | Mon 7 Sep | B1 | Ashwagandha Benefits for Men: What the Research Actually Shows | EN |
| 8 | Wed 9 Sep | G3 | Multani Mitti Ke Fayde Aur Face Pack Banane Ka Tarika | UR |
| 9 | Fri 11 Sep | F2 | Best Herbal Hair Oils in Pakistan 2026: An Honest Buyer's Guide | EN |
| 10 | Mon 14 Sep | A4 | Salajeet Istemal Karne Ka Tarika: Miqdaar Aur Waqt | UR |
| 11 | Wed 16 Sep | C1 | Kalonji Oil Benefits for Hair: Evidence, Uses and Limits | EN |
| 12 | Fri 18 Sep | G1 | Chehre Par Tel Lagane Ka Sahi Tarika Aur Fayde | UR |
| 13 | Mon 21 Sep | I2 | A Glossary of Pakistani Herbs: Desi Names, English Names | EN |
| 14 | Wed 23 Sep | E2 | Til Ke Tel Ke Fayde: Maalish, Jild Aur Baal | UR |
| 15 | Fri 25 Sep | B3 | Ashwagandha Benefits for Women: Sleep, Stress and the Caveats | EN |
| 16 | Mon 28 Sep | D4 | Beri Ke Patte Ka Sabun: Kya Hai Aur Kis Ke Liye | UR |
| 17 | Wed 30 Sep | E5 | Old Marks and Scars: What Cosmetic Oils Can and Cannot Do | EN |
| 18 | Fri 2 Oct | J4 | Dulhan Ki Skin Care Routine: Shadi Se 3 Mahine Pehle | UR |
| 19 | Mon 5 Oct | B5 | Ashwagandha Price in Pakistan 2026: Powder, Capsule, Root | EN |
| 20 | Wed 7 Oct | D1 | Sidr Leaf Powder for Hair: How to Make and Use a Sidr Hair Mask | EN |

In parallel, ship the 6 Pillar-H reference pages at **2 per week during Weeks 1–3** (they are
800–1,000 words, structurally templated, and are the highest-achievability URLs on the domain).

**After #20:** hold Mon/Wed/Fri through December 2026 (≈40 more posts), then move to **5/week
(Mon–Fri) from January 2027** to hit 165 URLs by August 2027. Reserve every Friday slot from
Jan 2027 for **refreshing** an existing post in striking distance (positions 5–20) rather than
publishing new — that is the fastest ROI on a young domain.

---

## 5. Herbal strategy — the core of the business

### 5.1 One herb = one pillar, six satellites, one future SKU

The catalogue is going to be herb-led (sidr, shilajit, ashwagandha, hair oil, face cream), so the
site architecture should be herb-led too. For every herb, build this exact six-page shape
**before the SKU exists**:

```
        [EN pillar: "<herb> benefits — evidence and limits"]  ←→ hreflang ←→  [UR pillar: "<herb> ke fayde"]
                     │                                                              │
        ┌────────────┼────────────┐                                    ┌────────────┼────────────┐
   EN "how to use"  EN "price/    EN "is <herb>                   UR "istemal ka   UR "asli/naqli  UR "<herb> aur
                    buying guide"  halal?" → /halal-ingredients      tarika"         pehchan"       <herb2> mein farq"
                     │                                                              │
                     └──────────────────►  /shop/<category>  ◄──────────────────────┘
                                          /products/<sku>   (launches later, inherits every link)
```

Why this shape:

- **The EN and UR pillars are hreflang alternates but never keyword competitors.** EN pillar owns
  "<herb> benefits"; UR pillar owns "<herb> ke fayde". Different strings, different SERPs, one
  reciprocal cluster. This is already how the live corpus works — keep enforcing it.
- **The "is X halal" satellite always lives at `/halal-ingredients/{slug}`, never on the blog.**
  That is the moat page type, it gets `DefinedTerm` schema, and it is the page AI answer engines
  quote. Blog posts give it ~120 words and link out.
- **The authenticity satellite ("asli/naqli pehchan") is the conversion page.** In PK herbal, the
  #1 buyer anxiety is fakes, and almost nobody addresses it honestly. `asli-salajeet-ki-pehchan`
  is already live and is the single most defensible page on the site. Replicate it for
  ashwagandha (adulterated root powder), kalonji (refined vs cold-pressed), sidr (leaf vs
  fillers), and honey/oil if those launch.
- **The commercial category page is created only when a SKU exists** — but every satellite is
  written with the internal link already pointing at it, so launch day is a 200-line diff, not a
  content project.

Herb build order, matched to the expansion roadmap: **shilajit → ashwagandha → kalonji → sidr →
hair-oil blends → face-oil/cream botanicals (multani mitti, arq-e-gulab, ubtan)**.

### 5.2 Roman Urdu is the whole edge — treat it as the primary language, not the mirror

`keyword-research-aug2026.md` §"THE ONE BIG FINDING" is correct and the live audit confirms it is
still true: Roman-Urdu PK SERPs are served Devanagari Hindi content most Pakistanis cannot read,
and no PK herbal brand (Saeed Ghani, ChiltanPure, Hemani, Conatural, Qarshi, Hamdard, or any of
the shilajit sellers) publishes Roman Urdu. What the current implementation does **not** yet do
is treat Roman Urdu as first-class:

1. **Publish UR first, EN second, for every herb.** The traffic model puts ~5,500 of the 10,075
   clicks on Roman-Urdu clusters. That is where a zero-authority domain converts effort into
   rankings fastest. The §4.1 schedule reflects this (12 of the first 20 are UR).
2. **Write the way people type, not the way people transliterate correctly.** Cover the real
   spelling variants in body copy and H2s — `salajeet / silajeet`, `fayde / faide / faidey`,
   `balon / baalon`, `tarika / tareeqa`, `qeemat / keemat`, `jild / jild ki`, `chehre / chehray`.
   One canonical spelling in the title/H1/slug; the variants live in H2s, the FAQ, and the answer
   box. This is not keyword stuffing — it is matching how Pakistanis actually key Latin-script
   Urdu.
3. **Build the Roman-Urdu commercial surface** (currently missing, D12). Minimum viable:
   `/ur-roman` homepage, `/ur-roman/shop`, `/ur-roman/products/{slug}` with UR product copy and
   UR FAQ. Without it, every UR reader who is ready to buy has to jump to an English page.
4. **Roman Urdu is `ur-Latn` and LTR** — already correct in `SetLocale`, the sitemap, and the
   hreflang cluster. Do not add `dir="rtl"`; do not create an Urdu-script (`ur`) tree yet — that
   is a separate keyword universe and a Phase-3 decision.
5. **Never mix scripts on one URL.** A page containing both Roman Urdu and Urdu-script blocks
   with no language tagging is one ambiguous document to Google and ranks for neither.

### 5.3 Owning "halal herbal" as a category

The brand cannot claim certification, so authority has to come from being the **most honest and
most complete reference** in the category. Three mechanisms:

- **The disclosure moat**: `/what-we-never-use` + full INCI on every PDP + `/halal-ingredients`.
  Extend it: publish an "ingredient sourcing questions we ask suppliers" page (reseller-honest —
  it describes a process, not an accreditation), and a "why we don't say halal certified" page.
  That last page is the single strongest link-bait and E-E-A-T asset available to this brand and
  it costs nothing but honesty.
- **The mythbuster lane**: every competitor in PK herbal makes claims they cannot support
  (ChiltanPure's "Increase Testosterone" is the documented example). Publishing the honest
  counter-content — A6 `naqli salajeet`, B6 `ashwagandha side effects`, E5 `what oils cannot do`,
  F7 `baal ugane wale tel ke daawe` — is both compliance-safe and the fastest route to
  citations, shares and PK-media pickup. Every one of these posts must state plainly what the
  product does **not** do.
- **Entity building for AI answers**: `knowsAbout` on the `OnlineStore` node should enumerate the
  herbs as the pillars ship (shilajit, ashwagandha, Nigella sativa, Ziziphus/sidr, sesame oil,
  guggul, multani mitti). `/llms.txt` already regenerates automatically; confirm each new pillar
  appears in it within 24h of publish.

---

## 6. Technical & on-page fixes — ranked by impact, with the exact file

| # | Fix | Exact file / URL | Impact |
|---|---|---|---|
| **F1** | **Correct the wrong WhatsApp number.** Body copy of `/blog/lookman-e-hayat-oil-price-in-pakistan` says `0341-7164556`; the real number is `0301 2973886`. Fix the post body in Admin → Blog → Posts (and in whichever `database/seeders/*BlogPost*Seeder.php` row created it, so a reseed cannot reintroduce it). Then add a phone/number lint to the existing compliance gate that fails any body containing a digit string matching `03\d{2}[- ]?\d{7}` that is not `StoreSettings::$contact_phone` | Post body (DB) + `database/seeders/`; source of truth `app/Settings/StoreSettings.php` | 🔴 Critical — lost orders + NAP inconsistency |
| **F2** | **Resolve the price-intent cannibalization (D2).** Owner = `/blog/lookman-e-hayat-oil-price-in-pakistan`. (a) Retitle `/blog/lookman-e-hayat-oil-uses-benefits-price` → **"Lookman-e-Hayat Oil: Uses, Benefits & How to Apply"** and change its H2 "Lookman-e-Hayat oil price in Pakistan" → "How much does it cost?" with a 40-word summary + link to the owner post. Keep the slug (301s cost more than they gain here). (b) PDP title → **"Lookman e Hayat Oil 50ml – Buy Online, COD Pakistan \| Glow Halal"** (drop "Rs 1,200") | `seo_metas` rows via Admin → SEO; post body in Admin → Blog | 🔴 Critical |
| **F3** | **`/shop` H1 and intro make a manufacturer claim.** `$heading = 'Everything we make'` → **"Halal Herbal Oils & Personal Care"**; `$title = 'Shop all products'` → **"Herbal Oils & Halal Personal Care in Pakistan"**; `$intro` "ingredients we will **not formulate with**" → "…the ingredients we will not stock." Also drop "we will not formulate with" wherever else it appears | `app/Http/Controllers/ShopController.php` lines **47–55** | 🔴 Critical (compliance + a wasted keyword slot) |
| **F4** | **Delivery times contradict the PDP and the `Offer` schema.** Homepage `$delivery` says 2–3 days major cities / 3–5 elsewhere; schema + PDP say 2–4 / 4–7. Make the homepage match the schema (or fix both to one truth) | `routes/web.php` lines **157–170** | 🟠 High — inconsistent facts are a trust and merchant-listing risk |
| **F5** | **Replace the fictional roadmap.** `$inDevelopment` lists Hydrating Serum / Mineral Sunscreen / Cream Blush with 2026–27 dates that do not match the real expansion plan. Replace with the actual roadmap (sidr soap, shilajit, ashwagandha, hair oil, face cream) or remove the block. Also fix `'launchMonth' => 'June 2026'` → `'August 2026'` | `routes/web.php` lines **130–155** and **231** | 🟠 High |
| **F6** | **Homepage "Latest guides" band shows 2 of 15 posts** — this band is the documented Google discovery path. Raise `->take(2)` to `->take(6)`, and add a second band of the 3 newest `ur-Latn` posts (currently `forLocale('en')` only, so no UR post is ever linked from the homepage) | `routes/web.php` lines **177–195** | 🟠 High — directly slows indexation of every new post |
| **F7** | **`/ur-roman/blog/category/{slug}` is missing from the sitemap.** The EN loop adds `/blog/category/{slug}`; add the mirrored UR loop with the same guard (`is_active` + has published UR posts) | `app/Http/Controllers/SitemapController.php` lines **89–96** | 🟠 High |
| **F8** | **Replace the "Glow Halal Editorial" byline with a named human** plus a one-line credential and a `/about#author` anchor, and reflect it in the `BlogPosting.author` node. YMYL herbal content with a corporate byline has a hard ranking ceiling | `BlogPost` author relation (Admin → Blog → Authors) + the author node in `app/Support/JsonLd.php` / `app/Support/Seo/SchemaGraph.php` | 🟠 High |
| **F9** | **`/blog` title tag targets nothing.** EN: "Journal - Ingredient Guides & Halal Formulation Notes" → **"Herbal Guides Pakistan: Oils, Jari Booti & Halal Ingredients"**. UR is already better; tighten to **"Herbal Tel Aur Jari Booti Ke Roman Urdu Guides \| Glow Halal"** | `app/Http/Controllers/BlogController.php` lines **69–82** | 🟡 Medium |
| **F10** | **Build the Roman-Urdu commercial surface (D12).** Add `/ur-roman` (home), `/ur-roman/shop`, `/ur-roman/products/{slug}` inside the existing `SetLocale` prefix group, each with UR copy, UR FAQ, self-canonical and a reciprocal hreflang pair against its EN twin. This is the biggest conversion unlock in the plan | `routes/web.php` lines **386–395** (the `ur-roman` prefix group) | 🟡 Medium effort, **High revenue impact** |
| **F11** | **`SchemaGraph::webPage()` hardcodes `inLanguage: 'en-PK'`** — becomes wrong the moment F10 ships. Accept a locale argument like `JsonLd::webPage()` already does | `app/Support/Seo/SchemaGraph.php` line **58** | 🟡 Medium (pre-emptive) |
| **F12** | **Sitemap hygiene**: no `lastmod` on the static hubs (home, /shop, /about, /blog, /ur-roman/blog) and no `<image:image>` entries. Add `setLastModificationDate()` driven by the newest child record, and image tags for PDPs and post covers (the covers were upscaled to 1200px specifically for Discover — they should be in an image sitemap) | `app/Http/Controllers/SitemapController.php` lines **47–96**, **98–125** | 🟡 Medium |
| **F13** | **Duplicate H2 "What is in it" on the PDP** — two identical headings in one document | PDP template under `resources/views/products/` | 🟡 Medium |
| **F14** | **robots.txt**: add `Disallow: /*?sort=` (the `/shop?sort=` variants are noindex,follow but still burn crawl budget on a 2-SKU catalogue) and add a `# LLMs: https://glowhalal.com/llms.txt` comment line | `public/robots.txt` | 🟢 Low |
| **F15** | **Blog pagination at 9/page** will produce 7+ pages by Month 6 and bury older posts. Before URL #60, add topic-hub pages (`/blog/category/{herb}`) for each pillar and link them from the blog hub and every post in the cluster | `app/Http/Controllers/BlogController.php` line **34** (`PER_PAGE`) + `BlogCategory` records | 🟢 Low now, High by Month 6 |
| **F16** | **Core Web Vitals have never been measured.** No CrUX/PSI figure appears in any doc. Run PageSpeed Insights (field data, mobile) on `/`, `/shop`, a PDP and a post; record LCP/INP/CLS p75 against the budget in `seo-migration-and-content-plan.md` §B4 (LCP <2.0s, INP <150ms, CLS <0.05). PK traffic is low-end Android on 4G — this is not optional | Measurement task; fixes land in `resources/views/components/layouts/app.blade.php` and image pipeline | 🟢 Low if passing, 🔴 if not |
| **F17** | **Link posts to ingredient pages as entities.** Add `about` / `mentions` to `BlogPosting` pointing at the `/halal-ingredients/{slug}` `DefinedTerm` `@id`s the post references. Cheap topical-authority and AI-citation signal | `app/Support/Seo/SchemaGraph.php` (blogPosting node) | 🟢 Low |

**Sequencing:** F1–F3 today. F4–F9 this week (before post #1 of the new schedule ships on 24 Aug).
F10–F13 in September. F14–F17 as capacity allows.

---

## 7. Off-page — realistic Pakistani authority building

Current state: **zero deliberately-earned referring domains.** Target: **25+ by Month 6, 60+ by
Month 12.** No paid links, no PBNs, no guest-post networks, no comment spam, no Fiverr gigs —
one exposed scheme on a brand whose entire positioning is honesty is unrecoverable.

### 7.1 Entity & citation layer (Weeks 1–4, ~6 hours total)

Mostly `nofollow` — the value is **entity consistency**, which is what Google uses to decide the
business is real. NAP must be byte-identical everywhere: `Glow Halal` · Karachi, Pakistan ·
`0301 2973886` · `support@glowhalal.com` · `https://glowhalal.com`. (Fix F1 first — a wrong
number in a published article poisons this from day one.)

- **Google Business Profile** — service-area retailer, address hidden, service area Pakistan.
  Already Week-1 in `marketing-30day-plan.md`; it is also the single biggest branded-SERP asset.
- **Bing Places** (feeds Copilot, and IndexNow already covers Bing indexing).
- Directories: `businesslist.pk`, `listaaj.com`, `smartbizdir.com`, `enests.co`,
  `yellowpages.com.pk`, `ebizkarachi.com`, `pakbiz.com`. Budget 20 min each, do not buy premium
  placements.
- **Daraz seller storefront** and **Facebook Shop** — brand-entity corroboration and a second
  demand channel; also where PK buyers verify a COD brand is real.
- Social profile links: `@glowhalalpk` on Instagram, TikTok, Facebook, plus a Pinterest board for
  the herb photography (Pinterest is an underused PK image-search source for `ubtan`/`multani
  mitti` content).

### 7.2 Linkable assets (build once, pitch for 12 months)

| Asset | Why it earns links | Ship by |
|---|---|---|
| **I1/I2 — the bilingual jari-booti glossary** (Roman Urdu + English, desi name ↔ English name ↔ traditional use ↔ photo) | Nothing like it exists in Roman Urdu. This is the reference page PK bloggers, FB group admins and Quora answerers cite because it saves them work | Sep 2026 |
| **"Naqli Salajeet Report"** — buy 8–10 shilajit samples from Daraz sellers and Karachi pansari shops, run the 5 documented home tests on camera, photograph every result, publish a comparison table. Add a third-party lab heavy-metals test on 3 samples if budget allows (~Rs 15–25k) | Original PK consumer-protection data. This is the one asset with genuine national-media potential | Nov 2026 |
| **"Why we don't say halal certified"** explainer | Contrarian, honest, quotable, and unique in a category where every competitor slaps a badge on | Sep 2026 |
| **Ingredient-decoder tool** — paste an INCI list, get flags for the `/what-we-never-use` set with links to the ingredient pages | Free tools are the most reliably linked asset type; it also directly monetises the disclosure moat | Feb 2027 |
| **PK herbal price index** — quarterly tracked prices for shilajit, ashwagandha, kalonji oil, sidr powder across major sellers | Journalists and bloggers cite price data; it refreshes quarterly so it stays linkable | Mar 2027 |

### 7.3 Outreach lanes (start Month 2, ~2 hours/week)

1. **PK digital media, consumer/health desks** — pitch the Naqli Salajeet Report and the whitening
   cream safety data to: Dawn *Images*/*Prism*, Geo Digital, Samaa Digital, ProPakistani (consumer
   angle), MangoBaaz, Parhlo, Something Haute, Diva Magazine. One pitch email, the data table
   attached, no ask beyond "credit us if you use it."
2. **Non-competing PK lifestyle/parenting/wedding blogs** — the dulhan skincare and winter-care
   posts are natural resource links for wedding planners and shaadi blogs. Target Karachi/Lahore
   city blogs and wedding directories, not other herbal stores.
3. **HARO / Connectively / Qwoted / SourceBottle**, filtered on "halal", "cosmetics",
   "ingredients", "Pakistan". This is the only realistic path to DR 50+ international links for
   this brand, and the ingredient index makes the answers credible. 3 responses/week.
4. **Unlinked mention conversion** — a Google Alert plus a monthly `"Glow Halal" -site:glowhalal.com`
   search; email anyone who names the brand without linking.
5. **Community participation, not link-dropping** — r/pakistan, r/PakistaniFashion,
   Facebook groups (Pakistani Skincare Addicts, Skincare Pakistan, desi totkay groups). Answer
   herb questions properly with no URL; link only when someone explicitly asks where to read
   more. These are `nofollow`/no-link surfaces — their value is brand-search demand, which is a
   ranking input.
6. **Supplier/pansari relationships** — if a supplier has a website, a "stockists" or "our
   partners" mention is a clean, relevant, editorially-given link.
7. **Micro-creator barter** (already in `marketing-30day-plan.md` Week 3) — creators with blogs,
   not just TikToks. A barter parcel for an honest review post is legitimate as long as the
   relationship is disclosed and no link terms are dictated.

### 7.4 Explicitly forbidden

Paid guest-post marketplaces · PBNs · directory link packages · comment/forum signature links ·
link exchanges · expired-domain redirects · any "1000 backlinks Rs 5000" Fiverr gig ·
AI-spun guest posts. Also forbidden: **any link built on a halal-certification claim**.

---

## 8. Measurement — checkpoints with real numbers

**Prerequisite (do this week):** connect **Google Search Console** (verify via the existing
`google_site_verification` field in `app/Settings/SeoSettings.php`), submit `/sitemap.xml`, and
export a **12-month Queries + Pages** baseline. Everything below is unmeasurable without it, and
the cannibalization audit in §3.3 stays hypothetical until GSC `page + query` data exists.

**GSC segments to save as filters:**

- Branded: query matches regex `glow ?halal`
- Roman Urdu: query matches regex `(fayde|faide|tarika|tareeqa|kaise|konsa|behtareen|qeemat|keemat|istemal|nuqsan|ke liye|ghareloo|totk)`
- Commercial: query contains `price|qeemat|buy|kharid|kahan se`
- Cluster: page matches regex `(salajeet|shilajit|asgandh|ashwagandha|kalonji|beri|sidr|balon|tel)`

**GA4:** organic-only exploration segmented by landing page and by `/ur-roman/` vs root, with
`whatsapp_click` (already has a `location` param) and COD `purchase` as the conversion pair.

| Checkpoint | Date | Indexed URLs | Impressions/mo | Clicks/mo | Queries top 10 | Ref. domains | Other gates |
|---|---|---|---|---|---|---|---|
| **Day 30** | 19 Sep 2026 | ≥ 60 | ≥ 3,000 | 100–250 | ≥ 3 | ≥ 5 (citations) | CWV all "Good" p75 mobile; 0 "Not found (404)" spikes; F1–F9 shipped; ≥ 10 queries in top 20 |
| **Day 60** | 19 Oct 2026 | ≥ 72 | ≥ 12,000 | 350–600 | ≥ 8 | ≥ 10 | First GSC page+query cannibalization audit run and clean; UR pages ≥ 40% of impressions |
| **Day 90** | 18 Nov 2026 | ≥ 85 | ≥ 30,000 | 900–1,400 | ≥ 15 | ≥ 15 | ≥ 3 queries in top 3; first organic-attributed COD orders in GA4; ≥ 1 featured snippet captured |
| **Day 180** | 16 Feb 2027 | ≥ 120 | ≥ 100,000 | 3,000–4,000 | ≥ 35 | ≥ 25 | Organic conversion rate ≥ 1.5%; Ramzan + winter seasonal posts ranking; UR commercial surface (F10) live and converting |
| **Day 365** | 20 Aug 2027 | ≥ 165 | ≥ 300,000 | **10,000–12,000** | ≥ 80 | ≥ 60 | Organic conversion ≥ 2.5%; ≥ 5 pillar pages in top 3; organic revenue > content cost 5:1 |

**Monthly 30-minute loop** (first Monday of each month):

1. Index coverage sweep → fix anything "Discovered/Crawled – not indexed" by adding internal
   links from the relevant pillar.
2. Cannibalization audit (`page + query`, filtered to that month's new keywords).
3. **Striking-distance harvest** — every URL at positions 5–20 gets one refresh pass. On a young
   domain this is the highest-ROI hour of the month.
4. Snippet/PAA check — turn every new PAA question into an H2 or FAQ entry on the owning page.
5. Conversion check by cluster → shift the following month's publishing weight toward whichever
   cluster produced WhatsApp clicks and COD orders.
6. CWV field-data check (CrUX, mobile).

**Kill criteria — be willing to act on these.** If at Day 120 a cluster has < 500 impressions/mo
despite 8+ published URLs, stop publishing into it and reallocate to a cluster that is moving.
If at Day 180 total clicks are below 1,500/mo, the bottleneck is authority, not content — freeze
new posts for one month and spend the entire budget on §7.2 linkable assets and §7.3 outreach.

---

## 9. What this plan does not promise

- It does not promise 10,000 sessions in 90 days. On a domain with no backlink profile in a
  category with entrenched incumbents, that is not achievable by honest means.
- It does not promise top-3 for head commercial terms like `shilajit price in pakistan` inside
  12 months.
- It assumes the content cadence in §4.1 is actually met. At 1 post/week instead of 3, the
  10,000 date moves to roughly **early 2029**. Cadence is the variable that matters most.
- It assumes no halal-certification claim, no cure claim, and no fabricated review is ever added
  to close a ranking gap. Those shortcuts exist, they work briefly, and they end this brand.
