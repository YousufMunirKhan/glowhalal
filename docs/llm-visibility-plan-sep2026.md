# Glow Halal — LLM Visibility Plan: from one entity win to a herbal category

**Date:** 3 September 2026 · **Site:** https://glowhalal.com
**Trigger:** ChatGPT now names Glow Halal for *"Lookman e Hayat oil kya hai aur kahan se milega"*
— ~15 days after the cluster went live.
**Goal asked for:** the same result for **herbal hair oil**, **herbal pain-relief oil**, and
herbal generally — in Karachi and Pakistan — across ChatGPT, Claude, Gemini, Perplexity, Copilot.

> House rules apply unchanged: no cure claims, no "halal certified", no seeded reviews,
> EN / Roman-Urdu keyword deconfliction, DRAP floor. See `docs/keyword-research-aug2026.md` §4.

---

## 0. Read this before planning anything

**The Lookman win was not a content win. It was a supply-of-answers win.** Understanding
*which* mechanism fired decides whether the next 90 days work or are wasted.

### 0.1 What actually happened

Three conditions were all true at once. Remove any one and there is no citation:

| # | Condition | Evidence |
|---|---|---|
| 1 | **A named entity with real search demand and no owner.** "Lookman-e-Hayat oil" is searched by name, and the PK web had no authoritative page for it. The retrieval candidate pool was near-empty. | `docs/keyword-research-aug2026.md` — same structural gap it found for `beri ke patte ka sabun` |
| 2 | **The page shape matched the query shape.** The query is *two-part*: "kya hai" **AND** "kahan se milega". The site answers both halves — `/blog/lookman-e-hayat-oil-uses-benefits-price` and `/ur-roman/blog/asli-lookman-e-hayat-tel-kahan-se-lein`. One domain closed the whole question, so it was the efficient citation. | 9-URL cluster, both languages |
| 3 | **Bing had the pages.** ChatGPT's search layer is Bing-backed, and IndexNow pings Bing daily at 06:20 PKT. Bing indexed in days where Google takes weeks. | `docs/seo-aeo-status-report.md` §4 |

### 0.2 Two consequences people usually get wrong

**(a) This is retrieval, not memory.** No model "learned" Glow Halal. It searched, found the
only usable page, and quoted it. That is good news — it happened in 15 days instead of a
training cycle — and bad news: **it is revocable.** Fall out of Bing's index, or let a
competitor publish a better page, and the citation disappears with no warning. It has to be
monitored (§5), not celebrated once.

**(b) Bing is the lever, not Google.** For classic traffic Google is the prize. For *being
named by an assistant*, Bing indexation is the gate — and Bing is the index we can move fastest,
because IndexNow is already firing daily. Bing Webmaster Tools deserves as much attention as
GSC from here on.

### 0.3 Why "herbal hair oil" will NOT repeat this on its own

The target asked for is the structural opposite of the condition that won:

| | Lookman-e-Hayat oil | herbal hair oil |
|---|---|---|
| Query type | Named entity, effectively single-brand | Head category, comparative |
| Candidate pool | **Starved** — almost nothing to cite | **Saturated** — ChiltanPure, Saeed Ghani, Marhaba, Hemani, Daraz, dozens of blogs |
| Document type the model wants | An explainer + a place to buy | A **listicle** of several brands |
| Whose listicle gets cited | — | **80.9% third-party**, not brand-published (AirOps, 863k results) |
| Do we sell one? | Yes | **No** |

That last row is the hard blocker, and it is not a marketing problem. **The catalogue is one
product in two sizes** (`herbal-skin-oil-50ml`, `herbal-skin-oil-100ml`). An assistant asked
"which herbal hair oil should I buy in Pakistan" cannot honestly name a seller who does not
sell one. No amount of content fixes that; §3 does.

**Conclusion: do not attack "herbal hair oil". Attack the 40 named entities underneath it.**
Category-level recall is an *output* of many entity wins, not an input you can buy.

---

## 1. The strategy: the Entity Ladder

Three rungs. Each rung's wins are the fuel for the next. Working out of order burns money.

```
Rung 3  CATEGORY   "best herbal hair oil in Pakistan"          6–12 months, needs §4 off-site
           ▲        "herbal pain relief oil Pakistan"           may not arrive at all without SKUs
           │
Rung 2  PROBLEM    "balon ka girna kaise roke"                 2–4 months
           ▲        "jodon ke dard ke liye kaunsa tel"          we already own one of these
           │
Rung 1  ENTITY     "kalonji ka tel kya hai aur kahan milega"    2–6 weeks  ← THE LOOKMAN SHAPE
                   "roghan e surkh kya hota hai"                repeat it 40 times
```

**Rung 1 is the entire near-term plan.** Every entity page is a small, fast, near-certain win in
a starved pool. Forty of them make the domain co-occur with "herbal oil Pakistan" across the
whole retrieval surface — which is how Rung 3 eventually gets answered with our name.

---

## 2. Target map — Rung 1 entities

Selection rule, applied to every candidate: **is the PK / Roman-Urdu SERP for this name empty,
or served Hindi-Devanagari content?** If yes, it is a Lookman-shaped opportunity. If a PK brand
already owns it, skip it — that is Rung 3 work.

### 2.A Pain / maalish oils (highest opportunity — Unani names, near-zero PK content)

| Entity | Query shape to own | Lang | Notes |
|---|---|---|---|
| **Roghan-e-Surkh** | roghan e surkh kya hai / kis liye istemal hota hai | UR + EN | Classic Unani pain oil, PK SERP essentially empty. **Best single pick.** |
| **Roghan-e-Kalonji** | roghan e kalonji ke fayde / jodon ke liye | UR | Overlaps hair too — one entity, two use pages |
| **Roghan-e-Badam Shirin** | roghan e badam shirin ke fayde aur price | UR + EN | High demand, weak PK pages |
| **Roghan-e-Zaitoon** | roghan e zaitoon ka istemal | UR | Sunnah association, large query family |
| **Roghan-e-Baiza-e-Murgh** | roghan e baiza e murgh kya hai | UR | Very niche, literally zero PK pages |
| Kamar / ghutna / gardan | kamar dard ka tel · ghutno ke dard ka tel · gardan ke dard ka tel | UR | Rung 1.5 — problem-shaped but still starved |
| Maalish generally | maalish ka tel konsa acha hai | UR | Comparison page |

⛔ **`jodon ke dard ka tel` stays with the Lookman cluster.** The cluster rule in
`keyword-research-aug2026.md` is right — do not build a second page and split the entity.

⛔ **Sanda oil is on the cannot-target list.** It is probably the highest-volume "herbal oil"
query in PK and it is off the table permanently. Anyone who proposes it does not understand the
compliance floor.

⚠️ **Competitor-brand comparisons** (`Dr Ortho oil`, `Zandu Balm`, `Iodex` vs a herbal oil) are
legitimate organic pages, and models lift comparison tables — but cap at **2–3 pages**, keep
them factual and non-disparaging, and never use those names in paid ads.

### 2.B Hair oils (named single herbs — the Roman-Urdu gap is widest here)

| Entity | Query shape | Lang | Comp |
|---|---|---|---|
| **Arandi ka tel** (castor) | arandi ka tel balon ke liye / kaise lagayen | UR | **Empty PK SERP, Hindi-only** |
| **Methi dana ka tel** | methi dana ka tel balon ke liye | UR | Very low |
| **Kalonji ka tel** | kalonji ke fayde balon ke liye | UR | Low — already P1 in the keyword sheet |
| **Amla ka tel** | amla oil price in pakistan / amla ka tel | EN + UR | Med |
| **Piyaz ka tel** (onion) | piyaz ka tel balon ke liye | UR | Low in UR, Med-High in EN |
| **Sarson ka tel** | sarson ka tel balon ke liye | UR | Low |
| **Bhringraj** | bhringraj tel kya hai | UR | Low, Indian-dominated |
| **Roghan-e-Amla / Roghan-e-Gul** | roghan e amla ke fayde | UR | Low |
| Rosemary oil | rosemary oil price in pakistan | EN | Med-High — Rung 2, do later |

`sabse-acha-balon-ka-tel` and `baalon-mein-tel-lagane-ka-tarika` already exist. They become the
**hub** these entity pages link up into — that is what turns 12 pages into one authority signal
instead of 12 orphans.

### 2.C The Karachi asset (build this — it is underrated)

Do **not** build doorway city pages (`/karachi`, `/lahore`, `/islamabad` duplicates). Models do
not reward them and thin duplication is a real risk.

Build **one genuinely useful local guide** instead:

> **"Karachi mein herbal tel aur jari booti kahan se milti hai"** — naming the actual places
> honestly: Jodia Bazar, the Empress Market area, Saddar attar/pansar shops, Tariq Road — what
> each is good for, what to check for adulteration, typical price ranges, and then: *or order
> online with COD*.

Nobody writes this. It is the highest-value citation target for any "Karachi + herbal" prompt,
it is honest, it costs one afternoon, and it pairs with the Google Business Profile in §4.
Publish EN + Roman-Urdu with distinct primary keywords per the deconfliction rule.

---

## 3. The blocker nobody can content their way around: SKUs

**One product = one question you can honestly be the answer to.**

Today the honest answer an assistant can give is: *"Glow Halal sells Lookman-e-Hayat herbal oil,
50 ml and 100 ml, COD across Pakistan."* That is a good answer — and it is the only one
available. To be named for **herbal hair oil** and **herbal pain relief**, those SKUs must exist.

Recommended, in order:

1. **Herbal Hair Oil** — kalonji / amla / arandi base, 100 ml. Unlocks the whole §2.B tier and is
   the single highest-leverage catalogue move for this plan.
2. **Maalish / Body Massage Oil** — `massage oil for body in pakistan` is already P1 in the
   keyword sheet at Low-Med competition, and it does not collide with the Lookman cluster.
3. **Sidr (Beri) Soap** — already identified as a zero-competition launch SKU. Not this plan's
   target, but the cheapest Rung-1 win in the whole catalogue.

Do **not** reposition the existing Lookman oil as a hair oil unless that use is genuinely true of
the product. A stretched claim on the one product that is currently working is a bad trade.

---

## 4. Off-site — the actual ceiling on Rung 3

`docs/aeo-agentic-readiness-aug2026.md` §4.2 already ranked these levers against real study data.
Nothing there has changed; what follows is the **sequence**, not new research.

The Lookman win happened *without* any of this — which tells us precisely what off-site is for:
starved-pool entity queries do not need corroboration, and saturated-pool category queries need
almost nothing else.

| When | Action | Lever |
|---|---|---|
| Week 1 | **Google Business Profile**, Karachi 74400, verified. Handles on IG / FB / TikTok / YouTube all exactly "GlowHalal". NAP identical everywhere; wire into `sameAs`. | Entity spine |
| Week 2 | **Daraz + PriceOye listing.** Not for the sales — for an independent page an assistant can cross-check price and existence against. | Lever 6 |
| Weeks 2–8 | **Point the existing delivered-order WhatsApp review request at Google Business Profile** instead of on-site. The funnel shipped last week; only the destination changes. Target 20 real reviews. | Lever 1 — largest measured effect (53.5% vs 1% citation rate) |
| Weeks 3–8 | **10 outreach approaches** to PK beauty/wellness bloggers and listicle publishers: free 50 ml, **no editorial conditions**. Goal: be *inside* someone else's round-up. | Lever 2 — ~4× more likely to be the cited source |
| Ongoing | **60–90 second Roman-Urdu YouTube video per top guide.** Doubles as the `sameAs` YouTube entry; transcripts are indexed and heavily cited. | Levers 3 + 5 |
| Ongoing | Honest, **disclosed-as-seller** participation in relevant threads. Lead with the fresh-burn warning and the no-certificate fact. Never seed. | Lever 5 — volatile, do not budget on it |

❌ Still forbidden, unchanged: Wikipedia article, seeded reviews anywhere, bought backlinks,
`llms.txt` / WebMCP vendor packages, any halal-certification wording.

---

## 5. Measurement — a fixed prompt panel, run monthly

Without this the whole programme is vibes. The Lookman result was noticed by accident; the next
twenty should not be.

**Method.** A frozen list of 30 prompts. Run on the 1st of each month across **ChatGPT,
Perplexity, Gemini, Copilot, Claude**, in a fresh/logged-out session, PK context. Log verbatim.

Record per row: `date · engine · prompt · named? Y/N · position in answer · URL cited ·
competitors named · answer contained a forbidden claim? Y/N`.

That last column matters. If an assistant paraphrases us as selling "halal-certified" products —
which has already happened via the stale WordPress pages, see `seo-impact-log.md` — that is a
compliance incident to chase, not a vanity metric.

**The panel (freeze this wording — changing it destroys comparability):**

*Won / control (3) — confirm the win still holds*
1. lookman e hayat oil kya hai aur kahan se milega
2. Lookman e Hayat oil price in Pakistan
3. asli lookman e hayat tel kahan se milega

*Rung 1 targets (12)*
4. roghan e surkh kya hai · 5. roghan e kalonji ke fayde · 6. arandi ka tel balon ke liye kaise
lagayen · 7. methi dana ka tel balon ke liye · 8. kalonji ka tel balon ke liye kaisa hai ·
9. amla oil price in pakistan · 10. piyaz ka tel balon ke liye · 11. roghan e badam shirin ke
fayde · 12. kamar dard ka tel konsa acha hai · 13. ghutno ke dard ke liye maalish ka tel ·
14. maalish ka tel konsa acha hai · 15. sarson ka tel balon ke liye

*Karachi / geo (5)*
16. Karachi mein herbal tel kahan se milta hai · 17. herbal oil shop in Karachi ·
18. jari booti Karachi kahan milti hai · 19. herbal hair oil Karachi home delivery ·
20. cash on delivery herbal oil Pakistan

*Rung 3 category — expect zero for months; that is the point (6)*
21. best herbal hair oil in Pakistan · 22. best herbal pain relief oil in Pakistan ·
23. sabse acha herbal tel konsa hai · 24. halal herbal skincare brands in Pakistan ·
25. herbal hair oil brands Pakistan COD · 26. best massage oil for body pain in Pakistan

*Brand / trust (4)*
27. is Glow Halal legit · 28. Glow Halal kya bechta hai · 29. Glow Halal reviews ·
30. Glow Halal contact number

**Retrieval precondition check.** Before blaming content for a miss, verify the page is actually
retrievable: `site:glowhalal.com <slug>` on **Bing** (not Google). If Bing does not have it, no
model can cite it, and the fix is indexation — not more writing.

Log results in `docs/llm-visibility-log.md`, one section per month.

---

## 6. Page template — the "two-half answer page"

This is the shape that won. Make it a template, not a one-off. Every Rung 1 page ships with all
ten blocks; `database/seeders/LookmanBlogSeeder.php` is the working model.

1. **H1 = the exact question**, in the phrasing people actually type.
2. **Answer box, 40–55 words** — and it must close **both halves**: what it is *and* where to get
   it, with price and COD in the same paragraph. This is the sentence that gets lifted verbatim.
3. **`Ek nazar mein` table** — kya hai · kis se banta hai · kis ke liye · kis ke liye nahi ·
   price · kahan se. Models lift tables preferentially.
4. **Ehtiyat (risk) section near the top, not buried.** A page that leads with the risk is a safer
   citation than one that hides it — `shilajit-side-effects-honest-guide` is the proof.
5. **"Ye kis ke liye nahi hai"** — almost nobody writes this, it is uniquely quotable, and it is
   compliant by construction.
6. **Availability in prose, not only in schema**: "Rs 1,200 for 50 ml, Rs 2,200 for 100 ml, Cash
   on Delivery across Pakistan, 2–7 days" as a literal sentence.
7. **Karachi paragraph** — real local context, then the COD alternative.
8. **Comparison table** against the named alternatives from §2.
9. **FAQ** built from live autocomplete for that entity.
10. **"Aakhri baar check kiya: {date}"** + internal links to the product page and the EN↔UR pair.

Cadence: **3 entity pages per week**, alternating EN and Roman Urdu with distinct primary
keywords. Forty pages ≈ 13 weeks. The existing 06:00 PKT drip and 06:20 IndexNow ping already
handle publication and Bing submission with no manual step.

---

## 7. Ninety-day sequence

| Weeks | Content | Catalogue | Off-site | Gate to pass |
|---|---|---|---|---|
| 1–2 | Karachi guide (EN+UR); Roghan-e-Surkh; Arandi ka tel | Decide the hair-oil SKU | GBP live + verified; handles claimed | All 4 pages retrievable on **Bing** within 10 days |
| 3–6 | 12 entity pages (§2.A + §2.B) | Hair oil SKU listed with real INCI | Daraz / PriceOye live; GBP reviews flowing; 10 blogger approaches sent | ≥ 4 of panel prompts 4–15 name us |
| 7–10 | 12 more entity pages; hubs updated to link them | Massage oil SKU | ≥ 1 third-party listicle inclusion; 3 YouTube shorts | ≥ 8 panel prompts name us; ≥ 10 GBP reviews |
| 11–13 | 12 more; first Rung 2 problem pages | — | 2nd + 3rd listicle inclusion | ≥ 12 panel prompts; **first non-zero on prompts 21–26** |

Monthly on the 1st: run the panel, write up `docs/llm-visibility-log.md`, and re-check that the
Lookman control prompts (1–3) still hold.

---

## 8. Honest expectations — read this before spending

- **Rung 1 works, and works fast.** 2–6 weeks per entity, high hit rate, low cost. This is real
  and repeatable, because the Lookman win proves the mechanism in this exact market.
- **Rung 3 may take a year, and might not arrive at all without the SKUs and the listicle
  placements.** A model will not name a hair-oil brand that has no hair oil, and it will not pick
  us out of a saturated field on our own say-so. Anyone promising "best herbal hair oil in
  Pakistan" inside 90 days is selling something.
- **Size the prize honestly.** ~15% of Pakistani adults have ever used an AI chatbot, and ChatGPT
  Shopping is still US-only. This is a cheap forward position for 2027–28, **not a 2026 revenue
  channel.** Classic search plus WhatsApp/COD remains the engine that pays this year.
- **The overlap is the saving grace.** Almost everything above — entity pages, honest framing,
  GBP, reviews, marketplace listings, Bing indexation — is *also* straight classic SEO and
  conversion work. Nothing here is AEO-only spend. That is why it is worth doing now.
- **Citations are revocable.** Retrieval, not memory. The monthly panel is not optional.
- **Compliance is the moat, not the tax.** The competitor outranking us today with "Increase
  Testosterone" is exactly the page an assistant learns to distrust. Every honest refusal — no
  cure claim, no certificate, real INCI, stated risks — is why a careful model can quote us
  safely. Do not trade that for a quick win.

---

## Appendix — decisions taken in this plan, so they can be argued with

1. **Do not target "herbal hair oil" directly.** Saturated pool, wrong document type, no SKU.
2. **Do not build city doorway pages.** One real Karachi guide instead.
3. **Do not split `jodon ke dard ka tel` off the Lookman cluster.**
4. **Do not reposition the Lookman oil as a hair oil** unless it genuinely is one.
5. **Bing indexation is the LLM gate**, so Bing Webmaster Tools joins GSC as a weekly check.
6. **Sanda oil stays permanently off the list**, despite being the biggest herbal-oil query in PK.

---

## 9. The brand SERP — "glow" vs "glow halal" (added 3 Sep 2026)

The owner asked why the site ranks for **"glow halal"** but not for **"glow"**, and how to fix it.

### 9.1 "Glow" is not winnable, and winning it would not help

Live SERP check, 3 Sep 2026. The first page for `glow` is held by:

| Result | Owner |
|---|---|
| Glow (disambiguation) | Wikipedia |
| GLOW — definition | Cambridge Dictionary |
| GLOW — definition | Merriam-Webster |
| GLOW (TV series, Netflix) | Wikipedia + IMDb + Instagram |
| We Are GLOW | a US digital agency |

Two separate reasons to walk away, and the second matters more than the first:

1. **Authority.** These are dictionaries, Wikipedia and a Netflix property. A two-month-old
   Pakistani store does not enter that set, at any budget.
2. **Intent — the real argument.** Nobody typing `glow` is shopping. They want a definition or a
   wrestling comedy. Ranking #1 for it would convert at roughly zero. It is a vanity metric that
   costs everything and returns nothing — the equivalent of a chai dhaba trying to rank for "water".

**Ranking for "glow halal" and not "glow" is not a failure. It is branded search working correctly.**

### 9.2 The actual finding: the brand SERP is telling a forbidden story

Searching `glow halal` returns Glow Halal — and describes the brand as selling
**"handmade, halal-certified skincare products."**

That is the one claim the compliance floor forbids, and it is being published under the brand's name.

Verified live the same day: **the site itself is clean.** `/` and `/contact` contain zero instances
of "certified"; the single instance on `/about` is an explicit denial ("na banawti 'certified'
stamps"). The claim is coming from **stale cached WordPress content**, exactly as
`seo-impact-log.md` warned on 19 Aug. The old URL now 301s correctly
(`/embrace-natural-care-the-benefits-of-neem-soap/` → `/blog`, verified), so the redirect is not
the problem — Google's index simply has not caught up, two weeks on.

**This outranks every ranking question in this document.** Priority order:

1. Confirm the GSC removal requests actually processed (Approved, not Processing).
2. Re-submit the old URLs for re-crawl so the 301s are seen.
3. Keep the flat denial on `/about` and `/contact` — it is the page a careful model will quote
   instead, once it is indexed.
4. Re-run the check monthly as prompt 27–29 of the §5 panel, with the "forbidden claim" column.

### 9.3 Own the whole brand SERP

Page 1 for `glow halal` currently also carries Grace and Glow, Glad 2 Glow, Glow Recipe,
Glowming and Glow & Lovely — five unrelated brands crowding a query that should be entirely ours.
Every result on that page should be a property we control:

| Slot | Status |
|---|---|
| glowhalal.com | ✅ live |
| Google Business Profile | ❌ does not exist — also blocks every city query (§2.C) |
| Instagram / Facebook / TikTok / YouTube, handle exactly "GlowHalal" | ❌ |
| Daraz / PriceOye listing | ❌ |
| Wikidata entity | ❌ (needs an independent reference first — see §4) |

This is the same §4 entity work, and it does double duty: it disambiguates the brand from five
similarly-named companies *and* it is the corroboration an assistant needs before naming us.

### 9.4 The metric that replaces "rank for glow"

**Branded search volume**, not a generic keyword position. This is not a softer goal — it is the
harder and more valuable one, and the Ahrefs 75,000-brand data in §4.2 says branded web mentions
(r = 0.664) is the single strongest correlate of being mentioned by AI, ahead of backlinks (0.218).

How it grows: the entity-ladder content in §2 brings people in on generic questions, the honest
framing makes them remember who answered, and they come back typing "glow halal".

**Track it in GSC → Performance, branded filter `glow ?halal`** — impressions and clicks, month
over month. That trend line is the answer to "are we getting bigger". A position for `glow` is not.
