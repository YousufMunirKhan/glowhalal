# Glow Halal — SEO Impact Log

**Purpose:** a running, dated measurement log against the growth plan
(`docs/seo-growth-to-10k-aug2026.md` §8) and the AEO baseline
(`docs/aeo-agentic-readiness-aug2026.md`). Each entry re-runs the same external probe suite and
records deltas against the previous entry. Nothing in this file is a projection — only what was
observed, and an explicit note wherever a probe was inconclusive.

**Standing probe suite (re-run identically every entry):**

1. Domain-restricted web searches (`site:glowhalal.com` equivalent): `lookman e hayat`,
   `lookman e hayat oil price`, `herbal oil karachi`, `beri sidr sabun`.
2. Brand queries: `glowhalal`, `glowhalal.com`, `glow halal pakistan herbal`.
3. Money keyword: `lookman e hayat oil price in pakistan` (open SERP — record who owns it).
4. `https://glowhalal.com/sitemap.xml` — URL count + newest lastmod entries.
5. Render check on the 2 newest blog posts (title, answer box, real content).
6. Stale pre-relaunch URL check (are the 3 old WordPress URLs still surfacing?).
7. Hreflang spot-check on a new post pair (when a pair exists).

**Standing caveats (apply to every entry):**

- The search tool is **US-based**; Pakistani SERPs (the ones that matter for revenue) can and
  will differ. Domain-restricted retrievability is the reliable signal; open-SERP positions are
  directional only.
- The tool's underlying index is not guaranteed to be Google's. GSC removals hide URLs from
  *Google* results specifically — a stale URL surfacing here does not prove the Google removal
  failed, only that some major index still holds it.
- Page fetches are converted to markdown, which **strips `<head>` tags** — canonical and
  hreflang cannot be confirmed or denied this way. Those need raw-HTML checks (curl) or GSC.

## Plan checkpoints (from `seo-growth-to-10k-aug2026.md` §8 — the numbers every entry compares against)

| Checkpoint | Date | Indexed URLs | Impressions/mo | Clicks/mo | Queries top 10 | Ref. domains | Other gates |
|---|---|---|---|---|---|---|---|
| **Day 30** | 19 Sep 2026 | ≥ 60 | ≥ 3,000 | 100–250 | ≥ 3 | ≥ 5 (citations) | CWV all "Good" p75 mobile; 0 404 spikes; F1–F9 shipped; ≥ 10 queries in top 20 |
| Day 60 | 19 Oct 2026 | ≥ 72 | ≥ 12,000 | 350–600 | ≥ 8 | ≥ 10 | GSC page+query cannibalization audit clean; UR ≥ 40% of impressions |
| Day 90 | 18 Nov 2026 | ≥ 85 | ≥ 30,000 | 900–1,400 | ≥ 15 | ≥ 15 | ≥ 3 queries top 3; first organic COD orders; ≥ 1 featured snippet |
| Day 180 | 16 Feb 2027 | ≥ 120 | ≥ 100,000 | 3,000–4,000 | ≥ 35 | ≥ 25 | Organic CVR ≥ 1.5%; seasonal posts ranking; UR commercial surface live |
| Day 365 | 20 Aug 2027 | ≥ 165 | ≥ 300,000 | **10,000–12,000** | ≥ 80 | ≥ 60 | Organic CVR ≥ 2.5%; ≥ 5 pillars top 3; revenue > content cost 5:1 |

---

## 18 Aug 2026 — Day 3 (leading-indicator check, not a traffic verdict)

Plan is 3 days old. Baseline = the 17 Aug live sweep. §2.2 defects mostly fixed since
(cannibalization F2, `sameAs`, WhatsApp F1 on 15 Aug; WP cruft-suffix redirects 17–18 Aug;
GSC sitemap + ~11 indexing requests by 16 Aug; removal requests for the 3 stale URLs ~16 Aug).

### Delta table

| # | Metric | 17 Aug baseline | 18 Aug 2026 | Verdict |
|---|---|---|---|---|
| 1 | Relaunch URLs retrievable, domain-restricted `lookman e hayat` | 0 | 0 | **Flat** |
| 2 | Relaunch URLs retrievable, any domain-restricted probe | 0 (only homepage + stale) | 0 — probes return only homepage + the same 3 stale URLs | **Flat** |
| 3 | Stale pre-relaunch URLs surfacing (`/product/glow-halal-nourishing-face-cream/`, neem-soap post, store-bought-soaps post) | 3 (removals requested ~16 Aug) | **All 3 still surfacing** (all three returned together on `store bought soaps skin` probe) | **Flat / not yet processed** |
| 4 | Brand query `glowhalal.com` | site ~position 6 | site position ~6 of 10 (homepage only) | **Flat** |
| 5 | Brand query `glowhalal` (one word) | not surfacing | not surfacing (only Wikipedia "Glow" noise) | **Flat** |
| 6 | Social profiles (instagram/facebook glowhalalpk, tiktok @glowhalal3) in any brand SERP | invisible | invisible | **Flat** |
| 7 | Money keyword `lookman e hayat oil price in pakistan` | owned by shop.hamariweb.com, Daraz, Indian pharmacies | same owners (hamariweb #1, Daraz ×3, HerbsRAK UAE, Apollo/Truemeds/Dawaadost India). No glowhalal.com | **Flat (expected at Day 3)** |
| 8 | Sitemap URL count | 56 | **61** (+5; drip ran through 19 Aug 01:05 PKT lastmod) | **Moved** |
| 9 | Newest drip posts render (title + answer box) | n/a | Both verified: `/blog/best-herbal-face-cream-pakistan` and `/ur-roman/blog/sabse-acha-balon-ka-tel` — full articles, correct H1, direct-answer opener, tables + FAQ present | **Moved** |
| 10 | Third-party brand mentions | 0 | 0 (`glow halal pakistan herbal` returns competitors only: Hemani, JADE, Glow Natural, herbalglow.pk) | **Flat** |
| 11 | Hreflang pair integrity on new posts | correct-by-design (per AEO audit §1.A) | **Inconclusive** — fetch tool strips `<head>`, cannot see hreflang/canonical. Note: both newest posts (F1-UR, face-cream-EN) have no published language twin yet, so *zero alternates would be the correct state anyway*. Needs a raw curl or GSC International Targeting check | **Inconclusive** |

Probe details worth keeping:

- `lookman e hayat oil price` (domain-restricted) returned the **homepage + the stale face-cream
  URL** — i.e. the index associates the domain with the query only through pre-relaunch content.
  None of the 9 live Lookman-e-Hayat cluster URLs is retrievable yet.
- `herbal oil karachi` (domain-restricted) returned homepage + 2 stale URLs. Same pattern.
- `beri sidr sabun` — zero results (the sidr posts are in the sitemap but not the index).
- The newest sitemap entries confirm the drip executed on schedule:
  `/blog/ashwagandha-benefits-for-men` (16 Aug), `/ur-roman/blog/sabse-acha-balon-ka-tel`
  (17 Aug), `/blog/best-herbal-face-cream-pakistan` (19 Aug 01:05 PKT).

### ⚠️ Most important finding: the stale URLs are a live compliance problem, not just clutter

The only content any search index currently serves for this brand is the old WordPress site —
and the stale face-cream page describes the product as **"halal-certified, cruelty-free"**. Both
brand-summary probes (`glowhalal.com`, `herbal oil karachi`) had the search layer paraphrase
Glow Halal as a seller of *"halal-certified skincare"*. That is precisely the claim the brand's
compliance floor forbids it from making, and right now it is the brand's entire search-facing
identity. The GSC removals (requested ~16 Aug) are therefore not hygiene — they are the removal
of a false certification claim published under the brand's name. Verify they process; if the
Google removal shows "Approved" but third-party surfaces still echo it, the 410s on the old
paths are what will eventually clear it everywhere.

### Cannot be verified externally — owner needs to open these (screenshots for the next entry)

1. **GSC → Indexing → Pages**: Indexed count vs "Discovered – currently not indexed" /
   "Crawled – currently not indexed" for the 61 sitemap URLs. This is the real Day-3 number
   external probes cannot see.
2. **GSC → Sitemaps**: `/sitemap.xml` status = Success, and the "Discovered URLs" count.
3. **GSC → Removals**: status of the 3 removal requests (Processing vs Approved).
4. **GSC → URL Inspection** on 2–3 of the ~11 URLs submitted 16 Aug: "URL is on Google" or not.
5. **GSC → Performance** (since 15 Aug): total impressions, and any query at all matching regex
   `glow ?halal` (branded filter from §8).
6. **Bing Webmaster Tools**: IndexNow submission log (daily ping firing?) + Site Explorer
   indexed-page count.
7. **GA4 → Traffic acquisition**: any `Organic Search` sessions since 15 Aug, landing pages,
   and any `whatsapp_click` events attributed to organic.
8. **PageSpeed Insights (mobile)** on `/`, `/shop`, one PDP, one post — plan F16 says CWV has
   never been measured; the Day-30 gate requires all "Good" at p75.

### Next checkpoint: Day 30 — 19 Sep 2026

The numbers this log must compare against on that date (§8): **≥ 60 indexed URLs** (sitemap is
already at 61, so this is now purely an indexation race), **≥ 3,000 impressions/mo**,
**100–250 clicks/mo**, **≥ 3 queries in top 10**, **≥ 10 queries in top 20**, **≥ 5 citation
referring domains** (directories/GBP count), **CWV all "Good" p75 mobile**, **zero 404 spikes**,
**F1–F9 shipped**.

### Honest bottom line — 3 days in

What the site controls is executing: the sitemap grew 56 → 61 on schedule, the two newest posts
are live, well-formed, and open with liftable answer boxes, and the drip completed its 12–19 Aug
run. What the site does not control has not moved at all: **zero relaunch URLs are retrievable
in search, the brand still surfaces only for its own exact domain name, the 3 stale URLs still
constitute the brand's entire search identity (including a forbidden "halal-certified" claim),
and no third-party mention of the brand exists anywhere.** None of that is alarming at Day 3 —
GSC indexing requests routinely take 1–3 weeks on a new-authority domain, and the plan itself
says "do not judge before Day 120" — but it means every externally visible needle is still at
zero, and the only verified movement is publishing cadence. The two things to actually watch
this week: (a) the removal requests processing, because of the compliance angle above; (b) the
first relaunch URL becoming retrievable domain-restricted — that is the signal the new site has
entered the index at all.
