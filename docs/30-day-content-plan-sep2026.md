# Glow Halal — 30-Day Content Plan (4 Sep – 4 Oct 2026)

**Focus:** three products only — **Lookman-e-Hayat**, **Roghan-e-Jarain** (hair),
**Roghan-e-Sukoon** (massage / pain). Nothing else gets attention this month.
**Companions:** `docs/organic-clicks-plan-sep2026.md` (diagnosis), `docs/llm-visibility-plan-sep2026.md` (strategy).

---

## 0. The target, honestly

The ask was **1,000 organic clicks this month**. That is not reachable, and planning around it would
mean planning to fail. The arithmetic:

```
Today:        1,998 impressions  →      4 clicks   (0.20% CTR)
1,000 clicks at a good 5% CTR needs   20,000 impressions   — 10× current
1,000 clicks at today's CTR needs    500,000 impressions
```

Impressions are a function of how many keywords you rank for and how high. On a domain this young,
in one month, a 10× is not available at any effort level. Anyone promising it is guessing.

**What this plan is actually built to hit:**

| | 30 days | 90 days | 6–12 months |
|---|---|---|---|
| Impressions | 4,000 – 6,000 | 12,000 – 20,000 | 60,000+ |
| Clicks | **60 – 150** | 300 – 600 | **1,000+** |

Where the 30-day number comes from:
- **Existing 2,000 impressions × a repaired CTR.** The truncation bugs, the Roman-Urdu snippet on
  the English pillar and the dead `/shop/oils` title are fixed as of today. 2,000 × 2% ≈ **40 clicks**
  from pages that already rank, with no new content at all.
- **20 new posts.** A new post on this domain earns roughly 50–150 impressions in its first month.
  20 × 100 ≈ 2,000 impressions × 2% ≈ **40 more clicks**.
- **Recovered redirects.** 116 impressions of face-cream traffic now land on a relevant article
  instead of soft-404ing. Worth a few clicks and, more importantly, it stops leaking authority.

1,000 clicks is the **six-month** goal, and it arrives by compounding this month twelve times — not
by trying harder in September.

---

## 1. Already shipped today (4 Sep)

| Fix | Effect |
|---|---|
| `preserveWords` on all 14 meta truncation sites | No more `…5 Checks Before You` / `…stated o` in the SERP |
| Face-cream 301s → `/blog/best-herbal-face-cream-pakistan` | 116 impressions/mo stop soft-404ing |
| Last two legacy 301s → their real replacement posts | `neem-soap-benefits-skin`, `whats-really-in-your-bar-soap` — both existed, both were pointed at `/blog` |
| `/shop/oils` title → `Herbal Oils in Pakistan – Hair, Skin & Body, COD` | The only commercial category page now targets something |
| Pillar meta description rewritten in English | It was Roman Urdu on an English page ranking for English queries |
| PDP guides ordered by pivot `position`, not recency | The 732-impression pillar now renders on both Lookman PDPs — it never did before |
| **`GrowthBlogSeeder` run** | **4 posts that were written 31 Aug and never seeded are now live** — `best-massage-oil-in-pakistan`, `herbal-oil-for-back-pain`, `jism-ki-maalish-ka-behtareen-tel`, `kamar-dard-ke-liye-tel`. All four are Roghan-e-Sukoon feeders. |
| All four products curated + attached to their feeder posts | Sitemap 53 → **58** |

---

## 2. The three blockers that outrank all content

Write nothing until these are moving. Content cannot compensate for any of them.

**2.1 Photos — the only true blocker.** Neither new product has one. With no `ProductImage` row,
`SchemaGraph` emits no `image` on the `Product` node. `image` is **required** for a Google product
rich result, so both PDPs are ineligible for price/availability enhancement and will be **rejected
by `/feed/google.xml`**. `og:image` also falls back to the Lookman bottle — a different product —
and the on-page placeholder is a **face cream** on an oil page. One afternoon with a phone fixes it.

**2.2 Google Business Profile.** Does not exist. Every "Karachi / Lahore / Islamabad" query shows a
local pack first, and a verified GBP is the only entry. No on-page work substitutes. It is also the
strongest entity signal available in Pakistan and it feeds the AI-citation work in the strategy doc.

**2.3 GSC housekeeping.** Confirm the three removal requests show **Approved**, then request
re-crawl of the four face-cream URLs so the new 301 targets are seen. This is also what finally
clears the *"halal-certified skincare"* description Google still shows for the brand.

---

## 3. The content plan — 20 posts, 5 per week

Cadence: **5 posts/week, alternating EN and Roman Urdu**, published 06:00 PKT by the existing drip.
Every post follows the two-half answer template in `llm-visibility-plan-sep2026.md` §6.

**Deconfliction rule, absolute:** an EN post and its Roman-Urdu sibling never target the same
primary keyword. The tables below assign one primary each; do not let them drift.

### Week 1 (5–11 Sep) — Roghan-e-Sukoon, because it has zero content of its own

| # | Lang | Title | Primary keyword |
|---|---|---|---|
| 1 | UR | Maalish Ka Tel Konsa Acha Hai? Ghar Par Sahi Tarika | `maalish ka tel konsa acha hai` |
| 2 | EN | Roghan-e-Surkh: What It Is and What It Is Traditionally Used For | `roghan e surkh` |
| 3 | UR | Kandhe Aur Gardan Ke Dard Ke Liye Maalish | `gardan ke dard ka tel` |
| 4 | EN | Body Massage Oil in Pakistan: How to Choose One Honestly | `body massage oil in pakistan` |
| 5 | UR | Ghutno Ke Dard Ke Liye Maalish Ka Tarika | `ghutno ke dard ka tel` |

⛔ `jodon ke dard ka tel` stays with Lookman. Do not reassign it.

### Week 2 (12–18 Sep) — Roghan-e-Jarain

| # | Lang | Title | Primary keyword |
|---|---|---|---|
| 6 | UR | Arandi Ka Tel Balon Ke Liye: Kaise Lagayen | `arandi ka tel balon ke liye` |
| 7 | EN | Coconut vs Almond vs Kalonji Oil for Hair: What Each Actually Does | `best oil for hair pakistan` |
| 8 | UR | Methi Dana Ka Tel Balon Ke Liye | `methi dana ka tel` |
| 9 | EN | Herbal Hair Oil in Pakistan: An Honest Buyer's Guide | `herbal hair oil in pakistan` |
| 10 | UR | Balon Ki Jarain Mazboot Karne Ka Tarika | `balon ki jarain` |

### Week 3 (19–25 Sep) — Lookman-e-Hayat, the cluster that already earns

This is where the fastest clicks are. 967 of your 1,998 impressions already land on this cluster;
deepening it moves existing rankings rather than starting from zero.

| # | Lang | Title | Primary keyword |
|---|---|---|---|
| 11 | EN | Lookman e Hayat Oil Side Effects: The Honest Answer | `lookman e hayat oil side effects` |
| 12 | UR | Lookman e Hayat Tel Istemal Karne Ka Sahi Tarika | `lookman e hayat tel kaise istemal karein` |
| 13 | EN | Luqman e Hayat, Lukman, Lookman: Which Spelling, and Is It the Same Oil? | spelling variants |
| 14 | UR | Lookman e Hayat Tel Karachi Mein Kahan Se Milega | `lookman e hayat tel karachi` |
| 15 | EN | Lookman e Hayat Oil vs a Plain Carrier Oil: When Each Makes Sense | `lookman e hayat oil vs` |

**Why #13 matters:** three of your four all-time clicks were spelling variants —
`lookman e hayat oil`, `lukman e hayat oil`, `luqman hayat`. People cannot spell it, and nobody
owns the disambiguation page. That is a Lookman-shaped gap inside your own brand.

### Week 4 (26 Sep – 2 Oct) — the Karachi asset + gap-fill

| # | Lang | Title | Primary keyword |
|---|---|---|---|
| 16 | UR | Karachi Mein Herbal Tel Aur Jari Booti Kahan Se Milti Hai | `karachi mein herbal tel` |
| 17 | EN | Where to Buy Herbal Oil in Karachi: Markets, Prices, What to Check | `herbal oil shop in karachi` |
| 18 | UR | Piyaz Ka Tel Balon Ke Liye: Sach Aur Afsana | `piyaz ka tel balon ke liye` |
| 19 | EN | Kalonji Oil for Hair: What the Evidence Actually Shows | `kalonji oil for hair` |
| 20 | UR | Sardi Mein Jism Ki Akran Aur Maalish | `sardi mein maalish` |

Posts 16 and 17 are the Karachi guide from the strategy doc — real markets (Jodia Bazar, Empress
Market, Saddar pansar shops, Tariq Road), what to check for adulteration, typical prices, then the
COD alternative. Nobody in Pakistan writes this, and it is the highest-value citation target for
any "Karachi + herbal" query.

---

## 4. Every post ships with these, or it does not ship

1. **Answer box, 40–55 words**, closing both halves of the question — what it is *and* where to get
   it, with price and COD in the same paragraph.
2. **Internal link to its product**, and the post **attached** to that product with a curated
   `position` (this is what makes it render in the PDP guides band).
3. **Meta description ≤ 158 chars, in the post's own language.** The pillar's Roman-Urdu
   description on an English page is exactly the mistake to avoid.
4. **Title ≤ 52 chars** — the layout appends `| Glow Halal` (13 chars).
5. **A "who this is not for" block.** Compliant by construction, and the single most quotable
   block for answer engines.
6. **Ehtiyat / risk section near the top, not buried.**
7. **"Aakhri baar check kiya: {date}"** line.

Compliance floor, unchanged: no cure or treatment claims, no "halal certified", no fabricated
reviews, Roghan-e-Sukoon carries **no ingredient claims** until the label arrives.

---

## 5. Weekly gates — what to check, and what to do if it fails

| End of | Gate | If missed |
|---|---|---|
| Week 1 | All 5 posts retrievable on **Bing** (`site:` check). GSC impressions ≥ 2,500. | Indexation problem, not content — stop writing, fix discovery |
| Week 2 | Photos live on both new PDPs. GBP submitted. Clicks ≥ 20. | Photos are blocking rich results — escalate |
| Week 3 | GSC impressions ≥ 3,500, clicks ≥ 40, ≥ 3 non-brand queries with clicks | If clicks flat but impressions rising, it is a title/description problem — rewrite, do not write more |
| Week 4 | Impressions 4,000–6,000, **clicks 60–150**, Lookman pillar in top 10 for its own name | Reassess before committing October |

**Track weekly, not daily.** GSC data lags 2–3 days and daily numbers at this volume are noise.

**Before touching the pillar's title**, pull GSC → Performance, dimensions *page + query*, 28 days,
regex `lookman|luqman|lukman`. Four URLs currently compete for "lookman e hayat oil price" and the
right resolution depends on which one Google already prefers. Do not guess — it is your best asset.

---

## 6. What is deliberately NOT in this month

- **Any fourth product.** Three only.
- **Supplements, soaps, creams.** The keyword sheet has them; they wait.
- **Checkout or conversion work.** 2 of 5 people who reached checkout bought — 40% on COD is
  strong. The funnel is starved, not leaking.
- **Chasing "herbal hair oil" or "pain relief oil" head terms directly.** 6–12 months, and only
  with the off-site work. This month buys the entity wins underneath them.
- **City doorway pages** (`/karachi`, `/lahore`, `/islamabad`). One real Karachi guide instead.
- **Paid ads changes.** Note only: ~25 of 83 GA4 "users" are Facebook and Google datacenter
  crawlers, so Meta reporting deserves the same discount.
