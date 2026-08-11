# Glow Halal — SEO + AEO Status Report

**Date:** 11 August 2026 · **Site:** https://glowhalal.com · All evidence below was
collected live from the production server on this date.

---

## 1. Verdict at a glance

| Area | Status |
|---|---|
| Technical SEO (crawl, index, canonical) | ✅ Complete |
| Structured data (schema.org) | ✅ Complete — incl. shipping + returns on offers |
| Bilingual EN / Roman-Urdu with hreflang | ✅ Complete, no cannibalization |
| AEO (answer engines: ChatGPT, Copilot, Gemini, Perplexity) | ✅ Week-1 stack live |
| Content pipeline | ✅ 11 posts live + 8 scheduled (auto, daily 06:00 PKT) |
| Ads readiness (GA4 / Meta / feeds) | ✅ Live, consent-gated |
| Remaining items | 🟡 5 small user-side actions (§7) |

---

## 2. Technical SEO — live evidence

- **robots.txt** — master switch ON. `User-agent: *` with only private paths
  disallowed (`/cart`, `/checkout`, `/account`, `/login`, `/search`). Sitemap line present.
- **sitemap.xml** — **31 URLs**: home, both products, shop + active categories,
  contact, legal pages, all published EN + Roman-Urdu posts, blog categories.
  Grows automatically as drip posts go live.
- **Canonicals + hreflang** — every EN↔UR pair is a reciprocal cluster with
  `x-default`; blog hub page-1 carries the hub cluster. Roman Urdu is `ur-Latn` (LTR).
- **www → apex 301** and **HTML no-cache / assets cached** rules live in-repo
  (`public/.htaccess`) so deploys can never lose them.
- **PHP 8.4** in production via LiteSpeed handler; HTML served uncached through
  Hostinger CDN (stale-page incident of launch day cannot recur).
- **Meta robots** — public pages `index,follow`; cart/checkout/account `noindex`.
- **404/redirects** — legacy WordPress URLs 301 via RedirectSeeder map.

## 3. Structured data — what each page emits

| Page | Nodes (verified live) |
|---|---|
| Home | `OnlineStore` (alternateName, ContactPoint tel/en/ur, areaServed PK, knowsAbout), `WebSite`, `WebPage`, `ItemList`, breadcrumbs |
| Product | `Product` (real INCI, verified free-from `additionalProperty`, no false manufacturer/origin claim) + `Offer` with **price, priceValidUntil, `OfferShippingDetails` (Rs 300.00 PKR → PK, transit 4–7 days), `hasMerchantReturnPolicy` (7-day window, mirrors /shipping-returns)** + `FAQPage` (9 visible Q&As) |
| Blog post | `BlogPosting` with headline, **image (post's own AI cover)**, datePublished, **dateModified (matched by a visible "Updated" line)**, inLanguage per locale, real Person author, articleSection, keywords |
| Blog hub | `CollectionPage` + `ItemList` of posts, locale-distinct titles |

Notes kept deliberately honest: **no** `hasCertification` (no halal cert exists),
**no** aggregateRating (no real reviews yet), **no** manufacturer claim (reseller).

## 4. AEO stack (answer-engine optimization)

- **/llms.txt — dynamic** (HTTP 200): brand fact sheet + **every active product with
  live price** + all guides in both languages + trust links. New products/posts
  appear automatically; nothing to maintain.
- **IndexNow** — key file served from site root; daily ping 06:20 PKT to
  api.indexnow.org (first submission: HTTP 202, 21 URLs). Covers **Bing, Copilot,
  DuckDuckGo, Yandex**. (Google does not support IndexNow; Google discovery is
  sitemap + homepage "Latest guides" band, §5.)
- **Answer boxes** — each post opens with a 40–55-word direct answer under the H1,
  the format answer engines lift verbatim.
- **Organization signals** — alternateName "GlowHalal", ContactPoint (sales, en/ur),
  areaServed PK, expanded `knowsAbout` list — feeds "is Glow Halal legit?"-type queries.
- **Visible freshness** — "Updated {date}" line renders when a post is genuinely
  edited (>12 h after publish) and always matches JSON-LD `dateModified`.

## 5. Content engine (fully automatic)

- **19 posts total**: 11 live (EN + Roman Urdu pairs, distinct primary keywords per
  language — anti-cannibalization rule enforced) + **8 scheduled**, one per day
  **12–19 Aug at 06:00 PKT** (verified in production DB), covering the expansion
  categories: sidr/beri, shilajit, ashwagandha (men), hair oil, face cream.
- **Covers**: AI-generated, visually QA'd, Glow Halal watermark on every image;
  generated at publish time (06:05 PKT) — Gemini-first with Pollinations fallback,
  daily Gemini cap admin-configurable (Admin → Blog & AI Images).
- **Discovery without manual work**: new posts appear the same morning in
  sitemap.xml, RSS-style feeds, **homepage "Latest guides" band** (Google finds them
  by crawling the homepage — no manual "Request indexing" needed), and the 06:20
  IndexNow ping (Bing family instantly).
- One hPanel cron (`* * * * *` → `schedule:run`) powers everything — verified live.

## 6. Ads / commerce readiness

- GA4 e-commerce events (view_item, add_to_cart, begin_checkout, purchase) +
  Meta Pixel + Google Ads conversion — all consent-gated.
- **Product feeds live**: `/feed/google.xml` (200) for Google Merchant Center,
  `/feed/meta.csv` (200) for Meta catalogue — SKUs GH-OIL-50 / GH-OIL-100.
- `shippingDetails` in schema (§3) is what unlocks Google's **merchant listing**
  rich result (price + shipping shown directly in search).

## 7. Remaining actions (user-side, ~15 min total)

1. **GSC → Removals**: request removal of 3 legacy WP URLs (especially the old
   "halal-certified" snippet page).
2. **Bing Webmaster → Request re-crawl** of homepage + both products (clears the
   old title/meta/H1 errors Bing reported against the WordPress site).
3. **Rotate the SSH password** (it was shared in chat).
4. Optional: **Gemini billing** on project 785379581270 (~Rs 700/mo) → premium AI
   covers activate automatically; Pollinations keeps working free either way.
5. Optional: real **SMTP** (mail currently logs to file) + Google Ads account if
   Keyword Planner data is wanted.

## 8. What to expect

- **Bing/Copilot/DuckDuckGo**: indexing within days (IndexNow is push-based).
- **Google**: homepage + products first, blog posts typically 3–14 days per post as
  the daily drip builds topical authority through August.
- **Answer engines**: llms.txt + answer boxes + honest schema make the site
  quotable; expect gradual pickup as crawlers refresh.
- Keyword targets and the full expansion matrix live in
  [`docs/keyword-research-aug2026.md`](keyword-research-aug2026.md).
