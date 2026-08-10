# Glow Halal — UX Research & Foundational Insights

**Prepared for:** Design system + build teams
**Market:** Pakistan (PKR), mobile-dominant, COD-dominant
**Catalog at launch:** 2–3 SKUs, architecture must scale to hundreds
**Core value proposition:** Verifiable halal integrity — not price, not novelty

> **Read this first.** The single hardest job this site has is not selling cosmetics. It is *proving a claim*. Every layout decision below is downstream of one question a skeptical Pakistani Muslim woman asks within 8 seconds of landing: **"Says who?"** If the design cannot answer that question with evidence rather than adjectives, nothing else in this document matters.

---

## 0. Research basis & confidence levels

| Source type | What it covers | Confidence |
|---|---|---|
| Desk research on Pakistani e-commerce (payment, RTO, device, connectivity stats) | Sections 3, 5, 6 | High — multiple corroborating sources, cited inline |
| Competitive teardown of live halal beauty stores (Masarrat Misbah, HAYA Beauty, Iba, Khubsurti) | Sections 2, 3, 4 | High — direct observation of live sites, Aug 2026 |
| Halal certification landscape (PNAC/PSQCA/SANHA/SMIIC) | Section 2 | High — official/accreditor sources |
| Personas | Section 1 | **Medium — synthesized, not yet validated by primary interviews.** See §9 for the validation plan. Treat as hypotheses to test, not findings. |

**Honest caveat:** no primary interviews have been run for Glow Halal. The personas below are constructed from market data, competitor review corpora, and established Muslim-consumer behavior patterns. They are good enough to design v1 against. They are not good enough to defend a major bet on. Section 9 specifies the cheapest research that would upgrade them.

**Immediate flag — current live site is a trust liability.** A fetch of `glowhalal.com` (Aug 2026) returns a generic skincare template showing face creams priced at **₨30–₨50**. Nobody believes a premium halal face cream costs thirty rupees; a Pakistani shopper reads that as a scam storefront or a broken site, and it poisons the domain's first impression. The local repo is a bare Laravel install (`routes/web.php` has only the welcome route, `app/Models` has only `User.php`), so nothing here is built yet. **Take the placeholder down or replace it with a real coming-soon page before any traffic is driven to the domain.**

---

## 1. Target personas

Three personas. They differ less by age than by **what they are afraid of**, which is the axis that actually drives design decisions.

---

### Persona A — Ayesha, 27 — "The Practising Professional"
**The primary persona. Design for her first; the others are accommodated, not led.**

| | |
|---|---|
| **Age / location** | 26–34, Lahore DHA / Karachi Clifton / Islamabad F-sectors |
| **Occupation** | Marketing exec, dentist, university lecturer, bank officer. Household income PKR 250k–600k/month |
| **Education** | Private university, English-comfortable, code-switches Urdu/English |
| **Device** | iPhone 13/14 or Samsung A-series. Instagram-first. Buys on mobile in bed, 10pm–1am |
| **Payment** | Has a debit card and uses Easypaisa/JazzCash — **but still chooses COD for a first order from an unknown brand** |

**Motivations**
- Started praying regularly in her mid-20s and audited her makeup bag. Discovered her foundation contains alcohol denat and her lipstick contains carmine. Felt genuinely unsettled.
- Does *not* want to downgrade aesthetically. She currently owns Charlotte Tilbury and Huda Beauty. A halal brand that looks cheap is not a trade she will make.
- Wants to stop the mental overhead of decoding INCI lists on the back of every box.

**Anxieties — ranked, and this ranking matters**
1. **"Is this actually halal, or is 'halal' just the marketing?"** She has seen brands slap a crescent on a box. She is looking for a *certificate number and an issuing body she can look up*, not a badge.
2. **"Is this brand a real company or three people with an Instagram page?"** Pakistan's counterfeit-cosmetics problem is severe — fake products in the market commonly contain heavy metals and bacteria ([The Vault PK](https://thevault.pk/blogs/cosmetics-daily-blogs/original-or-fake-cosmetics-easy-ways-to-check-authentic-products)), and fake-seller scams on Instagram/Facebook/WhatsApp are widespread enough to be a standard consumer-safety topic ([Affordable.pk safety guide](https://www.affordable.pk/blog-detail/avoid-online-shopping-scams-in-pakistan-2025-safety-guide)).
3. **"Will it perform?"** Halal/clean carries a reputation for chalky texture, bad shade range, and 2-hour wear.
4. **"Will my shade match?"** She is a medium-tan South Asian complexion routinely ignored by both Western and local ranges.
5. Delivery reliability — real but *fifth*, because COD neutralizes most of the financial risk for her.

**Shopping habits**
- Discovery: Instagram Reels and Stories → saves the post → visits the site 2–5 days later, often via the profile link. **The site's first visit is rarely the moment of intent; it is the moment of vetting.**
- Reads reviews obsessively. Cross-checks the brand name on Facebook groups and asks in her friends' WhatsApp group.
- Screenshots the ingredient list and sends it to a more religiously-knowledgeable friend or sister.
- Session pattern: 2–3 visits over roughly a week before first purchase.

**Cart abandonment triggers (in observed order of lethality)**
1. Shipping cost revealed late at checkout — the classic killer, and worse here because she has already mentally committed to a PKR figure.
2. Forced account creation. Instant exit.
3. No COD option, or COD hidden behind card as the default.
4. Any request for CNIC, or a card form on a brand she has not verified.
5. Ingredient list absent or incomplete on the product page — for *this* persona this is a genuine bounce cause, not a nice-to-have.
6. A checkout that demands a postal/ZIP code as a required field. Pakistani shoppers frequently do not know theirs.

> *"I don't want to be told it's halal. I want to see the certificate and read the ingredients myself."* — the sentiment this entire site must design around.

---

### Persona B — Fatima, 21 — "The Discovery Buyer"

| | |
|---|---|
| **Age / location** | 18–24, Rawalpindi / Faisalabad / Multan / Gujranwala — tier-2 cities as much as tier-1 |
| **Occupation** | University student or first job. Personal spend PKR 8k–20k/month, some of it from parents |
| **Device** | Android mid-range (Infinix, Tecno, Redmi, Samsung A0x). 4G, frequently throttled. **Storage-constrained — will not install an app** |
| **Payment** | COD, almost exclusively. May not have a bank account of her own |

**Motivations**
- Halal is assumed background context rather than an active research project — but she will not knowingly buy haram, and if a friend tells her a product contains pork gelatin she will drop it immediately.
- Trend-driven. Buying because a Pakistani creator she follows used it.
- **Price-sensitive in absolute terms.** A PKR 4,500 lipstick is a genuine decision. She will buy the cheapest entry SKU as a trial.

**Anxieties**
1. **"Will the parcel even arrive, and will it be the real thing?"** Highest-ranked fear for this persona.
2. "Will my mother/father approve of this delivery arriving at the house?" — the household context is real; discreet packaging matters.
3. "Will this shade suit me?" She is buying on a phone screen with an inaccurate colour profile.
4. "Can I return it if it's wrong?" She expects to be able to *refuse at the door* — the informal Pakistani return mechanism.

**Shopping habits**
- Discovery: TikTok and Instagram Reels. Pakistan had ~17.3M Instagram users as of early 2024, growing ~4.4M year-on-year ([Markaz social commerce guide](https://www.markaz.app/blog-post/social-commerce)).
- **Will DM on WhatsApp before buying** — to ask "is this original?", "how many days delivery?", "COD available?". A brand that does not answer WhatsApp within a few hours loses her.
- Compares against Daraz listings for the same or similar product.
- Browses on the move, in short low-attention sessions, on a connection that may drop.

**Cart abandonment triggers**
1. **Shipping fee added on a low-value order.** On a PKR 1,800 order, a PKR 250 delivery fee is a 14% surcharge and reads as a bait-and-switch.
2. Slow page load. She will leave a product page that takes more than ~4 seconds on a congested cell.
3. Too many form fields. Anything past ~6 fields and she abandons.
4. No visible WhatsApp contact — reads as "nobody is home."
5. A price that is not a round, comprehensible PKR number.

---

### Persona C — Sadia, 38 — "The Cautious Matriarch"

| | |
|---|---|
| **Age / location** | 34–50, established household, Karachi / Lahore / Peshawar, plus significant overseas-Pakistani presence |
| **Occupation** | Homemaker or part-time professional; controls significant household discretionary spend |
| **Device** | Larger-screen phone or tablet. **Uses default browser text size — often bumped up.** Low tolerance for small type |
| **Payment** | COD strongly preferred; may hand the phone to a family member for card entry |

**Motivations**
- Religious observance is the organizing principle, not a recent shift. She is the one *other people ask* whether something is permissible.
- Buys for herself and for daughters/daughters-in-law. **A satisfied Sadia is a 4–6 unit repeat order and a WhatsApp-group referral.** Highest lifetime value of the three.
- Prefers skincare over colour cosmetics — creams, cleansers, oils.

**Anxieties**
1. **Scholarly credibility.** "Who certified this? Under which standard?" She may genuinely recognize SANHA or ask her local mosque. Certifier *name* carries meaning for her that it does not for Fatima.
2. **Ingredient origin, specifically:** the source of glycerin, stearic acid, and any collagen or gelatin. She knows these are the common animal-derived offenders.
3. Skin safety — she is more worried about mercury and steroid content in local whitening creams than about brand aesthetics.
4. Whether a real human is reachable if something goes wrong.

**Shopping habits**
- Discovery: WhatsApp family/friend groups and Facebook. **Not** Instagram-first.
- Will phone the number on the site before ordering. **A phone number that nobody answers is worse than no phone number.**
- Reads the About page properly — one of the few users who does.
- Wants a printed invoice in the box and prefers a physical proof of purchase.

**Cart abandonment triggers**
1. Text too small to read; low-contrast gold-on-white body copy is a *hard* failure for this persona (see §6 and the accessibility note in §2).
2. No phone number, or a contact page with only a web form.
3. Vague sourcing language — "ethically sourced ingredients" with no specifics reads as evasion.
4. Any perception that the site is foreign-run or that the product ships from abroad with customs risk.

---

### Cross-persona design implications

| Insight | Design consequence |
|---|---|
| All three vet before buying; the first visit is a trust audit | Homepage must lead with evidence, not a promo carousel |
| COD is the default expectation, not a fallback | COD is the **pre-selected** payment option, shown as a first-class choice |
| Two of three will contact via WhatsApp before purchase | Persistent WhatsApp affordance is a conversion tool, not a support cost |
| Persona A screenshots ingredient lists to share | Ingredient list must be **fully visible in one screenshot** — no accordion that hides half of it, no lazy-render |
| Persona C is the referral engine but the weakest-sighted | Minimum 16px body text, WCAG AA contrast, no gold text on white |
| Persona B abandons on shipping surprises | Free shipping threshold stated in the announcement bar, on the PDP, and in the cart — three times, before checkout |

---

## 2. Trust architecture

**This is the most important section of this document.**

The governing principle: **evidence over adjectives.** Every competitor in this space says "100% Halal." That phrase has been fully commoditized and now carries close to zero persuasive weight with a skeptical buyer. Glow Halal's differentiation is being the brand that shows its work.

A second governing principle: **the skeptic reads the negative space.** A buyer evaluating halal claims does not look for what you say you are. She looks for **what you refuse to hide**. Full INCI lists, a scanned certificate, a named human, a physical address, and an honest limitation are worth more than any quantity of premium styling.

### 2.1 Trust element inventory

Nine elements, ranked by persuasive power per pixel.

---

**T1 — The certificate itself, not a badge** ⭐ highest value

Everyone shows a badge. Almost nobody shows the certificate. Showing the actual document is the single strongest differentiator available.

Must render, as text (crawlable and screenshot-able), not baked into an image:
- **Issuing body name and logo** — a PNAC-accredited certifier: SANHA Pakistan, Halal Research Council (HRC), or Punjab Halal Development Agency (PHDA). PNAC is Pakistan's national accreditation authority and, per its own account, the first in the world to launch a Halal Accreditation Scheme, in 2012 ([PNAC](https://www.pnac.gov.pk/public/index.php/Halal-Certification-Bodies/Active)).
- **Certificate number**, selectable text
- **Standard applied** — cite `PS 5319-2014` (Pakistan Standard: General Guidelines for Halal Cosmetics and Personal Care Products) and `PS 4992 / OIC-SMIIC 2` where applicable. PSQCA is an OIC/SMIIC member, which means SMIIC-chain certification carries recognition across the 57 OIC member states ([PSQCA halal standards](https://www.psqca.com.pk/division-wise-standards/halaal/)).
- **Issue and expiry dates.** Pakistani halal certificates typically run 3 years ([SANHA Pakistan](https://www.sanha.org.pk/)). **Show the expiry.** Nobody does this, and it is a spectacular credibility signal — it says the brand expects to be checked.
- **A viewable scan of the certificate** — high-resolution image, tap to zoom, plus a PDF download.
- **An outbound link to the certifier's public directory listing.**

*Placement:* dedicated `/halal-certification` page (the destination); compact evidence block on the homepage above the fold; certificate number in the footer on every page; a line item on every PDP.

> **Honesty gate — non-negotiable.** If certification is still in process at launch, the site must say **"Certification in progress with [body], expected [month]"** and publish the full ingredient dossier in the interim. A fabricated or vague certification claim, discovered once, ends the brand. This is also the *most* likely near-term failure mode: the founder's positioning is entirely built on certification, so the site's copy must not be written to assume a certificate that does not yet exist. Build the component so it renders an honest in-progress state, not just a certified state.

---

**T2 — "What We Never Use" — with INCI names** ⭐ second-highest value

This is Glow Halal's signature content asset and the founder's positioning rendered as a usable tool. Competitors say "alcohol-free, cruelty-free." Nobody publishes the INCI names — which is exactly what the skeptic needs, because **the INCI name is what is printed on the back of the box she already owns.** This turns the page into a reference she returns to and shares.

Structure as a table:

| Ingredient | INCI / label name | Why it's a problem | What we use instead |
|---|---|---|---|
| Denatured alcohol | `Alcohol Denat.`, `SD Alcohol 40` | Intoxicant-derived; the most common halal disqualifier in cosmetics | Glycerin-based, alcohol-free solvent system |
| Carmine | `CI 75470`, `Cochineal Extract`, `Carminic Acid` | Crushed cochineal insects; a primary concern in lip products since ingestion is likely | Mineral and plant-derived pigments (`CI 77491`, `CI 77492`) |
| Pork-derived gelatin / collagen | `Gelatin`, `Hydrolyzed Collagen`, `Elastin` | Frequently porcine unless the source is documented | Marine or plant alternatives, source-documented |
| Animal glycerin | `Glycerin` (unspecified source) | Can be tallow-derived; source is invisible on the label | Certified vegetable glycerin — source stated |
| Tallow derivatives | `Stearic Acid`, `Sodium Stearate`, `Glyceryl Stearate` | Common animal-fat derivatives, extremely common in creams | Palm/coconut-derived, certified |
| Lanolin | `Lanolin`, `Lanolin Alcohol` | From wool grease — *mushbooh* (doubtful) depending on extraction | Plant butters — shea, cocoa |
| Shark squalene | `Squalene` (animal) | Shark liver derived | Olive-derived `Squalane` |
| Keratin | `Keratin`, `Hydrolyzed Keratin` | Typically animal horn/hoof/feather | Plant protein alternatives |

*Placement:* dedicated `/what-we-never-use` page linked from the primary navigation — **in the main nav, not buried in the footer.** This is a top-level nav item, and that placement is itself a positioning statement. Condensed 6-row version on the homepage. Cross-linked from every PDP ingredient section.

*Extension worth building:* a searchable **Halal Ingredient Index** — type any INCI name, get a halal/haram/mushbooh verdict with reasoning. This is a genuine SEO and authority moat, it costs nothing but content, and it partially solves the empty-store problem in §4.

---

**T3 — Full INCI list on every product page, expanded by default**

Non-negotiable. Do not collapse it behind "Ingredients ▸". Persona A came specifically to read this.

Per-ingredient annotation, which is what elevates this above a wall of Latin:

```
Glycerin              [Plant — palm]        ✓ Halal certified
Butyrospermum Parkii  [Plant — shea]        ✓ Halal certified
Tocopherol            [Plant — vitamin E]   ✓ Halal certified
Phenoxyethanol        [Synthetic]           ✓ Halal — non-animal
```

Three source categories only: **Plant / Mineral / Synthetic.** Simple, legible, and it makes the absence of a fourth category — Animal — the loudest thing on the page. Include a plain-language "what it does" tooltip for each.

*Design constraint from research:* Persona A screenshots this list to send to someone. It must be **fully rendered and screenshot-able in one or two vertical scrolls, with no lazy-loading, no virtualization, and no accordion.** Provide an explicit "Share ingredient list" action that generates a clean image or a copyable text block. This is a small feature with disproportionate word-of-mouth value.

---

**T4 — Wudu / prayer-compatibility statement**

Underserved by nearly every competitor and highly specific to this audience. Whether a product forms a water-impermeable barrier is a live concern — the debate around "breathable" nail polish is well documented, and scholars are openly skeptical of loosely-substantiated permeability claims ([Cosmetics Design Asia](https://www.cosmeticsdesign-asia.com/Article/2021/02/25/Can-nail-polish-be-halal-Concerns-over-breathable-and-water-permeable-claims-of-wudu-friendly-nail-polish/)).

Add a labeled field to every PDP:

> **Wudu note:** This cream absorbs fully and leaves no occlusive film. Wudu is unaffected.

And where honesty requires it:

> **Wudu note:** This is a long-wear formula. We recommend removing before wudu. We would rather tell you than let you assume.

**That second variant is worth more than the first.** A brand that volunteers an inconvenient limitation earns credibility that no amount of positive claiming can buy. Design the component to make the honest-limitation state look as intentional and well-made as the positive state — not like an error or warning.

---

**T5 — The founder, named and photographed**

In a market where fake-seller scams are a standard consumer-safety warning, **anonymity is itself a scam signal.**

Required:
- Real full name
- A real photograph — not a stock image, not an illustration
- City of operation
- The specific origin moment: what she read on a label that started this
- A signature or handwritten mark
- A named point of accountability: *"If a product doesn't meet what we promised, email me directly: [address]"*

*Placement:* a substantial block on the homepage (not just the About page — most users never reach About), the full story on `/about`, and a small founder avatar with a one-line quote in the footer.

---

**T6 — Company legitimacy block** (Pakistan-specific, high value, near-zero cost)

Sits in the footer on every page. This is the anti-scam proof, and it is cheap:

- **Full registered business name and legal form**
- **Complete physical address** including city and postal code
- **NTN (National Tax Number)** and **STRN** if registered — a fake seller does not publish a tax number
- **SECP incorporation number** if a registered company
- **A landline where possible**, alongside mobile — landlines imply a fixed premises
- **A branded email domain** (`hello@glowhalal.com`, never a Gmail address)

Persona C specifically looks for these. Persona A checks them subconsciously. The cost is one footer component.

---

**T7 — Third-party lab testing**

Beyond halal certification, and specifically countering the fake-cosmetics fear:
- Link to the **actual PDF report**, not a summary
- Heavy-metals screening (lead, mercury, arsenic) — highly resonant given documented mercury content in local whitening creams
- Microbial testing
- Batch number the report applies to
- The testing lab's name

*Placement:* PDP under ingredients, plus a `/testing` page.

---

**T8 — Batch verification**

A code on the physical box, enterable at `/verify`, returning manufacture date, batch, expiry, and the applicable lab report. Directly attacks the counterfeit fear and makes the brand feel like a real manufacturer rather than a reseller. Low build cost, high perceived sophistication.

---

**T9 — Reviews, honestly handled**

At 3 SKUs there will be almost no reviews, and that is fine if handled correctly.

- **Do not fabricate reviews.** Pakistani beauty shoppers actively spot this and discuss it in Facebook groups.
- **Do not display a star average until there are ≥5 reviews.** A lone 5-star review reads as fake and is worse than no rating at all.
- Do use clearly-labeled **"Founding customer"** testimonials with first name, city, and — ideally — a real photo.
- Add a **"Verified purchase"** flag from launch day.
- Consider seeding with an honest **"We're new — be one of our first 50 reviewers and get 20% off your next order"** offer. Transparency about newness converts better than pretending to be established.

---

### 2.2 Trust hierarchy by page

**Homepage, above the fold (first viewport, mobile 390×844):**
1. Logo + one-line positioning: *"Certified halal cosmetics. Every ingredient named."*
2. Hero product image
3. **Trust strip — 3 items max, and they must be specific:**
   `SANHA Certified · #HC-XXXXX` | `Full INCI on every product` | `Cash on Delivery`
   Not `100% Halal | Cruelty-Free | Premium Quality` — that is what every competitor says and it is noise.
4. Primary CTA

**Homepage, remaining scroll — order matters:**
5. Certificate evidence block (visible certificate thumbnail, tap to enlarge)
6. "What We Never Use" — 6 rows with INCI names, link to full page
7. Product feature blocks (full-bleed editorial, **not** a grid — see §4)
8. Founder block with photo and signature
9. How delivery works — COD, timelines, courier
10. Founding-customer testimonials
11. Footer with the full legitimacy block

**Product detail page — order:**
1. Image gallery (swipeable, minimum 4 images: product, texture/swatch, on-skin, packaging back showing the ingredient panel)
2. Name, price PKR, wudu note
3. Shade selector (if applicable)
4. Add to cart / Buy now
5. Trust row: `Halal Certified #XXXX` · `COD Available` · `Delivery 3–5 days`
6. What it does — short
7. **Full annotated INCI list, expanded**
8. What's not in it — mini "never use" table
9. Certification detail with certificate thumbnail
10. Lab report link
11. How to use
12. Reviews
13. Shipping and returns
14. FAQ

**Checkout:**
- Certificate number and "Secure order — Cash on Delivery available" persistent in the header
- Courier logo
- Return policy in one plain sentence, visible without a click

---

### 2.3 Trust anti-patterns to avoid

| Anti-pattern | Why it backfires |
|---|---|
| Generic crescent/halal badge with no issuer name | Universally used by uncertified sellers; now reads as decoration |
| "100% Natural" / "Chemical Free" | Scientifically illiterate; erodes credibility with the educated Persona A |
| Stock photography of non-South-Asian models | Instantly reads as a dropshipping template |
| Padlock/SSL badge graphics | 2010-era signal; a modern buyer reads the browser, not your badge |
| Countdown timers, "17 people viewing" | Pattern-matched to scam sites in Pakistan. **Actively harmful here.** |
| Contact page with only a web form | Reads as unreachable, especially to Persona C |
| Ingredient list as a flat image | Not selectable, not searchable, not screenshot-friendly at high quality — defeats T3's entire purpose |
| "Certified by international standards" (unnamed) | Vagueness is the tell that Persona C is scanning for |

---

## 3. Competitive landscape

### 3.1 Direct competitors — teardown

**Masarrat Misbah Makeup** — [masarratmakeup.com](https://masarratmakeup.com/) — *the incumbent*
Claims the position of Pakistan's first halal-certified makeup brand, built on a well-known founder with real public standing.

*Does well:* founder credibility is genuine and unmatched; deep catalog (Face/Eyes/Lips/Nails/Accessories/Bundles/Fragrances); halal certificate linked in the footer; clear delivery promise (free shipping on card payment and COD orders above Rs. 8,000, 3–4 day delivery); COD stated up front.

*Does badly:* **halal is treated as a badge, not as an argument** — the certificate is a footer link rather than a merchandised asset, and there is no "what we never use" content or ingredient-level transparency anywhere on the journey. Heavy discount-led merchandising (large seasonal sales) undercuts premium positioning. Homepage reads as a conventional cosmetics store that happens to be halal.

*The gap Glow Halal exploits:* the incumbent owns "halal cosmetics" as a category label but has left **ingredient-level proof** completely unclaimed.

---

**HAYA Beauty** — [haya-beauty.com](https://www.haya-beauty.com/) — *closest analogue*
The nearest structural comparison: premium-leaning, small catalog (~11–12 SKUs), clean positioning.

*Does well:* a genuinely good small-catalog site. Three-badge trust block (`100% Halal`, `Alcohol-Free`, `Cruelty-Free`); a four-item reassurance row (Premium Quality, Fast Shipping, Easy Returns, Cash on Delivery); free shipping over Rs. 2,500 stated clearly; a dedicated "Brand Story" in the **primary nav** — correct instinct; blog content padding a thin catalog — also correct instinct (see §4).

*Does badly:* review counts of 1–4 per product are displayed prominently, which **advertises how new they are** — a cautionary example for T9. Badges are unsubstantiated: "100% Halal" with no certifier, number, or certificate. No ingredient lists surfaced. Founder is not named or photographed on the homepage.

*Read:* HAYA has the right **structure**. Glow Halal wins by putting real evidence inside that structure.

---

**Iba Cosmetics** (India) — [ibacosmetics.com](https://www.ibacosmetics.com/) — *best-in-class content model*
Not a direct market competitor, but the strongest reference for how to argue the halal case.

*Does well — copy this:* explicitly names forbidden ingredients rather than gesturing at them — free from "alcohol, pig fat, any type of animal derived ingredients," plus sulphates, parabens, ammonia. **Explains reasoning**, e.g. lipsticks exclude carmine and beeswax specifically because lip products are ingested during eating and drinking. That single sentence does more persuasive work than any badge. Dedicated educational pages ("What are Halal Certified Cosmetics"). Product descriptions lead with **exclusions** rather than inclusions. Dual-axis navigation: category (Makeup/Skin/Hair/Body/Fragrance) crossed with benefit ("Anti-Acne & Oil Control") and ingredient range (Vitamin C, Green Tea).

*Does badly:* very large catalog creates choice paralysis; halal messaging is diluted by broad clean-beauty positioning.

*The lesson:* **explain the reasoning, don't just state the exclusion.** "No carmine" is a fact. "No carmine, because carmine is crushed cochineal insects and lip products are inevitably ingested" is a *conversion*.

---

**Khubsurti.pk** — [khubsurti.pk](https://khubsurti.pk/) — *the multi-brand aggregator*
Retails Iba, SAAF Organic, PHB Ethical Beauty, Shiffa Dubai, Amara Halal Cosmetics.

*Threat model:* competes on selection and on borrowed brand trust. Glow Halal cannot win on breadth and should not try. It wins on being **the source** — own manufacture, own certificate, own named founder. An aggregator can never credibly say "we control our supply chain."

---

**Adjacent:** Golden Rose, Flormar, Rivaj UK, Miss Rose occupy budget/mid halal-adjacent space; 786 Cosmetics does halal nail polish specifically. None compete on transparency.

### 3.2 Pakistani e-commerce conventions — mandatory compliance

**Cash on Delivery is the market, not an option.**

The numbers vary by source but all point the same direction: Pakistan's e-Commerce Policy 2.0 (2025–30) cites **60–70% COD reliance** ([Digital Watch Observatory](https://dig.watch/resource/pakistans-e-commerce-policy-2-0-2025-30)); other 2025 reporting puts it at **~70%, i.e. 7 in 10 online consumers**; and logistics-side sources still cite **over 80% of transactions** ([TrackMyOrder.pk](https://trackmyorder.pk/blog/shopify-tips/cod-return-rate-pakistan-shopify)). Historical figures ran as high as 85–95%. Over 30% of the population is unbanked, which structurally floors the COD share.

*Design consequences:*
- COD is the **default-selected** payment method, listed first
- Never gate COD behind a minimum order value at launch
- Display the exact cash amount to have ready: **"Have PKR 4,850 ready for the courier"**
- Do not describe COD as "alternative" or "other" — it is the primary path

**The COD tax: RTO.** Estimates for return-to-origin range from **18–20% nationally** to **30–45% industry average**, with well-run stores below 15% ([TrackMyOrder.pk](https://trackmyorder.pk/blog/shopify-tips/cod-return-rate-pakistan-shopify), [DHL Pakistan](https://www.dhl.com/discover/en-pk/e-commerce-advice/e-commerce-best-practice/subscription-marketing/how-pakistani-e-commerce-sellers-can-reduce-cod-returns)). The mechanism is simple: a COD order requires zero upfront commitment, so the barrier to *refusing* is as low as the barrier to ordering.

*This is the one place where the design must deliberately ADD friction.* See §5.4.

**WhatsApp is customer service.** WhatsApp commerce is standard practice — brands use it for order processing, support, and converting hesitant buyers. Requirements: floating WhatsApp button on every page; the number rendered as **selectable text** as well as a link (Persona C will save it to contacts); WhatsApp Business with catalog, away-message, and stated response time; **pre-filled context messages** — the PDP button should open with *"Hi, I have a question about the Nourishing Face Cream"* already typed.

**Instagram is discovery.** ~17.3M Pakistani Instagram users, growing ~4.4M year-on-year. Pakistan's social commerce market is projected to reach **PKR 500 billion by end of 2026** ([Markaz](https://www.markaz.app/blog-post/social-commerce)).

*Design consequences:* every page needs correct OG/Twitter card images (1200×630) because links get pasted into DMs and groups constantly; product URLs must be short and clean for bio links; product photography must be Reels-native (9:16 crops available); an Instagram feed embed on the homepage is a **liveness signal** that directly counters the empty-store problem (§4) — but must be lazy-loaded so it never blocks LCP.

**Other conventions:** courier logos (TCS, Leopards, M&P, Trax) are recognized trust marks — display them. Free-shipping thresholds are near-universal and expected. Prices should be round numbers (PKR 2,450, not PKR 2,447). Bilingual comfort — English UI is fine for these personas, but Urdu is warranted for high-anxiety moments (the halal explainer, delivery instructions).

---

## 4. Information architecture

### 4.1 Sitemap

```
/                             Home
/shop                         All products (grid; becomes real at ~8+ SKUs)
  /shop/skincare              ─┐
  /shop/makeup                 │ Categories built now,
    /shop/makeup/lips          │ REVEALED only when
    /shop/makeup/face          │ stocked (see 4.3)
    /shop/makeup/eyes          │
  /shop/haircare               │
  /shop/fragrance             ─┘
/product/{slug}               PDP

── TRUST CLUSTER (top-level nav — this is the differentiator) ──
/halal-certification          Certificate, issuer, standard, expiry, scan, verification link
/what-we-never-use            The INCI exclusion table
/ingredient-index             Searchable INCI → halal status lookup ⭐ moat
/testing                      Third-party lab reports
/verify                       Batch code verification

── BRAND ──
/about                        Founder story, mission, manufacturing
/journal                      Editorial content
  /journal/{slug}

── COMMERCE ──
/cart
/checkout
/order/{id}/confirmation
/track                        Order tracking (COD buyers check obsessively)

── SUPPORT ──
/contact
/faq
/shipping-returns
/privacy  /terms
```

### 4.2 Navigation

**Mobile header (persistent):**
`[☰]  GLOW HALAL  [🔍] [🛒 badge]`

**Drawer contents, in this order:**
```
Shop  ──────────────
  Face Cream
  Lip Tint
  Cleansing Oil
  → Shop all

Why Glow Halal  ────
  Our Halal Certification
  What We Never Use
  Ingredient Index
  Lab Testing

  About the Founder
  Journal
  Contact

  📱 WhatsApp us
  Cash on Delivery available
```

**At launch, list individual products in the nav, not categories.** With 3 SKUs, a "Skincare" link leading to a page holding one item is a dead end that broadcasts emptiness. Naming products directly makes the nav feel populated and gets users to the PDP in one tap. Flip to category nav at ~8 SKUs.

**Bottom bar (mobile, persistent, thumb zone):**
`[Home] [Shop] [WhatsApp] [Cart]` — WhatsApp in the nav bar is unusual in Western design and exactly right here.

### 4.3 The empty-store problem — the concrete answer

Three products in a standard e-commerce grid looks abandoned. Here is the specific set of rules that fixes it.

**Rule 1 — Never render a product grid with fewer than 4 tiles.**
A 3-column grid with 3 items and whitespace where row 2 should be is the single visual that says "this store is dead." Below 4 products, the homepage uses **full-bleed editorial feature blocks** — one product per section, large image, headline, ingredient highlight, CTA. Three of those is a rich, intentional-looking page. Three grid tiles is an empty shelf.

**Rule 2 — Reframe scarcity as curation, explicitly in copy.**
Name it: **"The Founding Collection"** or **"Volume One — Three Formulas."** Add a homepage statement:

> *"We launch a product only after it passes halal certification and third-party testing. That takes months. It's why we have three products instead of thirty."*

This converts the weakest fact about the store into a proof point for its core claim. **Small catalog becomes evidence of rigor.** No other framing does this much work.

**Rule 3 — Build depth vertically, not horizontally.**
Three products with 8-section PDPs (ingredients, sourcing, certification, lab report, wudu note, how-to-use, FAQ, reviews) is more total substance than thirty products with two-line descriptions. Each PDP should take 3–4 minutes to read.

**Rule 4 — Non-product pages carry the site's weight.**
The Ingredient Index, What We Never Use, Certification, Testing, and Journal pages are **content inventory**. A visitor who lands and finds a searchable halal-ingredient database does not perceive an empty store — she perceives an authority. This is the highest-leverage answer to the problem and it costs content, not engineering.

**Rule 5 — Make "coming next" a designed feature, not an absence.**
A homepage section: **"In development"** — 3–4 upcoming products, each with a name, a one-line description, a target month, and a **"Notify me"** email capture. This transforms the gap into anticipation *and* builds the launch list. Show real progress states: `Formulation complete · In halal certification · Expected Nov 2026`.

**Rule 6 — Hide empty categories; do not gray them out.**
Categories exist in the database and routing from day one. The nav renders only categories with `product_count > 0`. A greyed-out "Coming soon" category is worse than no category — it advertises the gap. `/shop/haircare` with zero products should 404 or redirect, not render an empty state.

**Rule 7 — Single-product categories collapse.**
If a category holds exactly one product, its route redirects to that PDP. Never show a category page containing one tile.

**Rule 8 — Use liveness signals that don't depend on catalog size.**
Instagram feed embed (lazy-loaded), recent journal posts, "certified on [date]" timestamps, a founder's note dated this month. These say *someone is here* independent of SKU count.

**Rule 9 — `/shop` does not exist as a grid until 8+ SKUs.**
Before that, "Shop all" points to an editorial collection page in the same feature-block format as the homepage.

---

## 5. Key user flows

### 5.1 Discovery → purchase (the realistic path)

```
Instagram Reel
  ↓ (saves post — often days pass)
Profile link → Homepage
  ↓ TRUST AUDIT — the critical moment
  ├─ Certificate visible? ──── no ──→ EXIT
  ├─ Founder real? ─────────── no ──→ EXIT
  └─ yes ↓
Product page
  ↓ reads full INCI list — screenshots it — sends to a friend
  ├─ WhatsApp question ──→ answered ──→ returns
  ↓
Add to cart OR Buy now
  ↓
Checkout — COD default
  ↓
Order confirmation + WhatsApp confirmation
  ↓
Delivery 3–5 days, cash to courier
  ↓
Review request → repeat purchase
```

**Design implication that is easy to miss:** the trust audit and the purchase are usually **different sessions, days apart**. The site must be optimized for *returning* as much as for converting. Practical consequences: persistent cart across sessions; email capture early and low-friction; short shareable URLs; correct OG images so a link pasted into a WhatsApp group renders as a product card, not a bare URL.

### 5.2 The three add-to-cart flows the founder specified

**Flow A — One-click add to cart (from product card)**

```
Tap [+ Add]
  ↓
Button → spinner (~120ms) → ✓ Added
  ↓
Cart badge increments with a subtle bounce
  ↓
Toast, bottom, 3s: "Added — Nourishing Face Cream  [View cart]"
  ↓
User stays exactly where they were. No navigation. No modal.
```

*Rules:* never navigate away; never open a full-screen drawer on a single add (it interrupts browsing); the button becomes a `[− 1 +]` stepper after the first add so a second tap doesn't blind-add; the whole interaction is optimistic — update the UI immediately, reconcile with the server after, and roll back with an inline error if it fails.

⚠ **Friction point — variants.** One-click add is only safe for products with no options. If a product has shades, a single tap must open a **bottom-sheet shade picker**, not silently add a default. Silently adding the wrong shade produces a COD refusal at the door, which is the most expensive possible failure. Design the shade sheet now, before the first shaded SKU ships.

⚠ **Friction point — out of stock.** With 3 SKUs, one out-of-stock item is 33% of the catalog. The card must show `Notify me` in place of `Add`, never a disabled button with no explanation.

---

**Flow B — Buy Now (skips cart entirely)**

```
Tap [Buy Now] on PDP
  ↓
Direct to /checkout with this item only
  ↓ (cart contents preserved separately, NOT merged)
Single-page checkout, COD pre-selected
  ↓
[Place Order]
```

*Rules:* Buy Now must **not** clear or merge an existing cart — a user with 2 items who taps Buy Now on a third and loses the other two will not come back. Use a separate "instant checkout" session. Visually secondary to Add to Cart (outlined vs filled), because Add to Cart is the higher-AOV path.

⚠ **Friction point — AOV.** Buy Now structurally caps order value at one item. With a 3-SKU catalog and delivery cost per parcel, this materially hurts unit economics. **Mitigation: an order bump on the checkout page** — a single compact checkbox, e.g. *"Add the Lip Tint for PKR 1,950 (save 15%)"* — placed above the Place Order button. This recovers AOV without reintroducing the cart step. It is the highest-ROI single component in the checkout.

⚠ **Friction point — trust.** Buy Now bypasses the cart, which is where users normally re-read the total. The checkout page must therefore restate the item, quantity, shipping, and the exact cash total with extra prominence.

---

**Flow C — Cart page with live updates (no page reload)**

```
/cart
  ├─ Line item: image, name, shade, unit price
  ├─ Quantity stepper [− 2 +]
  │    ↓ tap +
  │    • quantity updates INSTANTLY (optimistic)
  │    • line total recalculates
  │    • subtotal recalculates
  │    • free-shipping progress bar advances
  │    • cart badge updates
  │    • small inline spinner on THAT LINE ONLY
  │    • server reconciles in background (~300ms debounce)
  ├─ [Remove] with 5s Undo — never a confirm dialog
  ├─ Free shipping progress: "PKR 550 away from free delivery"
  ├─ Subtotal / Shipping / TOTAL (PKR)
  ├─ COD notice: "Have PKR 4,850 ready for the courier"
  └─ [Proceed to Checkout]  — sticky, bottom
```

*Rules:* debounce server writes at ~300ms so rapid `+ + +` sends one request, not three; **never** show a full-page loading overlay — spinner scoped to the affected line; quantity edits must survive a dropped connection (queue and retry — connections drop routinely, see §6); the free-shipping progress bar is the strongest AOV lever available on this page and should be visually prominent.

⚠ **Friction point — price changes on reconciliation.** If the server returns a different price than the optimistic UI showed, do not silently correct it. Show an inline notice: *"Price updated — please review."* Silent price changes are the fastest way to lose a Pakistani buyer's trust.

### 5.3 Checkout — the whole thing, one page

Field list. **Six fields. Do not add a seventh without deleting one.**

```
1. Full name                    [text]
2. Mobile number                [tel, +92 prefix locked, 10 digits]
3. City                         [searchable dropdown]
4. Complete address             [textarea, 2 rows, with a helper example]
5. Email                        [optional — clearly marked]
6. Payment                      ( • ) Cash on Delivery   ← PRE-SELECTED
                                (   ) Card / Wallet
```

*Rules that matter specifically in Pakistan:*
- **No postal code field.** Most buyers don't know theirs. If a courier API needs it, derive it from the city.
- **No CNIC.** Ever. It is a hard trust failure.
- **Guest checkout only** at launch. No account creation. Offer "Save my details" as a *post-purchase* option on the confirmation page.
- Address is unstructured free text with a placeholder like `House 12, Street 4, Block B, Gulberg III` — do not impose a rigid multi-field address form.
- Phone field: mask as `+92 3XX XXX XXXX`, numeric keypad, and validate on blur rather than per-keystroke.
- Order summary is **visible without expanding** — no "show order details" accordion.
- The exact cash amount is restated adjacent to the Place Order button.

### 5.4 Deliberate friction: COD verification

Given RTO rates of 18–45%, this is the one place to add a step. After Place Order, before the order is confirmed:

```
"We've sent a 4-digit code to +92 3XX XXX XXXX.
 Enter it to confirm your order."
 [_][_][_][_]
```

This costs about 8 seconds and eliminates fake numbers and impulse orders that get refused at the door. Pair it with an immediate WhatsApp order confirmation — a real message from a real number is both a service touch and a second verification that the number is live.

For high-value orders (>PKR 10,000), consider a partial-advance option incentivized with free shipping, rather than blocking COD outright.

### 5.5 Post-purchase

Order confirmation must show: order number, the cash amount to have ready, expected delivery window, courier name, a tracking link, and a WhatsApp button for questions. Then: WhatsApp confirmation immediately, dispatch notification with tracking, delivery-day reminder (*"Your parcel arrives today — please have PKR 4,850 ready"* — this single message measurably reduces RTO), and a review request 5 days post-delivery.

---

## 6. Mobile-first considerations

**80.2% of Pakistani internet traffic originates from smartphones**, versus 12.3% from desktop/laptop ([Bloom Pakistan](https://bloompakistan.com/smartphones-now-drive-80-of-internet-traffic-in-pakistan/)). Mobile internet penetration is approaching 60% of the population, roughly 140 million users ([TechMag](https://techmag.com.pk/mobile-internet-usage-trends-in-pakistan-2025/)).

**Design mobile-only first.** Desktop is a scaled-up afterthought, not a parallel deliverable.

### 6.1 Connection reality

Pakistan ranks around **98th globally for mobile internet**, with median download speeds near 24–25 Mbps against a global average close to 60 Mbps ([Digital Rights Monitor](https://digitalrightsmonitor.pk/pakistan-internet-speeds-global-rank/), [ProPakistani/Ookla H1 2025](https://propakistani.pk/2025/09/24/the-best-mobile-networks-in-pakistan-ranked-again-ookla-report/)). Regional variance is severe — Azad Jammu & Kashmir records ~11.22 Mbps. **And the median is not the design target: peak-hour congestion, indoor coverage, and network handoffs mean the real experienced speed is often a fraction of it.** Design for the bad connection, not the average one.

**Performance budget — treat as hard constraints:**

| Metric | Budget |
|---|---|
| Homepage total weight | < 600 KB |
| Hero image | < 120 KB (AVIF with WebP fallback) |
| Product image | < 80 KB each |
| JS bundle (initial) | < 150 KB gzipped |
| Web fonts | 2 weights max, `font-display: swap`, subset to Latin |
| LCP on 4G | < 2.5s |
| Time to Interactive | < 4s |

**Implementation requirements:** server-rendered HTML for the first paint (Blade suits this well — do not put the PDP behind a client-side SPA); `srcset` on every image with 400w/800w/1200w variants; lazy-load everything below the fold including the Instagram embed; skeleton screens rather than spinners; **optimistic UI on every cart action** so a slow round-trip is invisible; queue-and-retry on network failure rather than showing an error.

⚠ **Data cost is a real constraint** for Persona B. Do not autoplay video. Do not load a 4MB hero. A heavy site is a site she cannot afford to browse.

### 6.2 Thumb reach

Reference viewport: **390 × 844** (iPhone 13/14 class). Also test **360 × 800** — the most common Android class in this market.

```
┌─────────────────────┐  0–15%   HARD ZONE
│  logo, search       │          Static/low-frequency only
├─────────────────────┤
│                     │  15–55%  STRETCH ZONE
│   content, images   │          Reading, scrolling
│                     │
├─────────────────────┤
│  key info, price    │  55–80%  NATURAL ZONE ⭐
│                     │          Best real estate
├─────────────────────┤
│  [Add to Cart]      │  80–100% EASY ZONE ⭐
│ [Home][Shop][WA][🛒]│          Primary actions live here
└─────────────────────┘
```

**Rules:**
- Sticky bottom bar on the PDP: price on the left, `Add to Cart` filled on the right, `Buy Now` outlined beneath or beside. Appears once the user scrolls past the hero.
- Minimum tap target **48 × 48 px**; minimum 8px between adjacent targets.
- Quantity steppers: 44px tap targets minimum — the classic mis-tap.
- Primary CTAs bottom-right (right-handed majority); destructive actions (Remove) never adjacent to primary ones.
- Menu opens as a **bottom sheet**, not a top-anchored drawer — top-left is the hardest point on the screen to reach one-handed.
- **No hover states carry meaning.** Every hover affordance needs a tap equivalent.
- Horizontal swipe for image galleries; never rely on tiny arrow buttons.

### 6.3 Typography & contrast — a specific warning about the brand palette

The brand gold (#C9A961 → #E4C87F) is beautiful and **fails WCAG AA as body text on white.** #C9A961 on #FFFFFF is roughly a 2.2:1 contrast ratio against a 4.5:1 requirement. Persona C — the highest-lifetime-value persona and the referral engine — is the most likely to be affected.

**Rules for the design system:**
- Gold is for **decoration, dividers, icons, and large display type only** — never for body copy, never for links, never for form labels, never for error text.
- Body text: near-black (#1A1A1A) on white. Minimum **16px**, 1.6 line-height.
- Gold on white is acceptable at 24px+ bold (large-text AA threshold, 3:1) — verify each usage rather than assuming.
- For gold that must sit on a light surface at small sizes, darken to roughly #8A6D28 for the accessible variant and keep the bright gold for large display.
- Buttons: gold background with **black** text, not white — check the contrast on the darkest and lightest ends of the gradient.
- Never place gold text over a photograph without a scrim.

### 6.4 Other mobile requirements

Correct `inputmode` and `autocomplete` on every field (`tel`, `name`, `email`, `street-address`); no zoom-on-focus (16px minimum input font-size); tel/WhatsApp links that open the native app; a share sheet on the PDP; visible focus states for external-keyboard users; and full functionality in portrait — do not require landscape for anything.

---

## 7. Content requirements — About Us & Contact Us

### 7.1 About Us

Not a brand-mission essay. It is **the primary evidence page** and, for Persona C, often the deciding page. Every element below is a trust instrument.

**Required, in this order:**

1. **Opening: the origin moment, concrete and specific**
   Not "we believe in clean beauty." Something like: *"In 2024 I turned over my foundation and read 'Alcohol Denat.' I had been wearing it to Jummah for two years."* A specific, dated, personal moment is what makes a founder story land.

2. **Founder, fully identified** — real name, real photograph (not stock, not illustrated), city, background/credentials, and a handwritten signature.

3. **The problem, stated plainly** — the named haram ingredients common in mainstream cosmetics, and the fact that a Pakistani Muslim woman currently has no reliable way to check them. Link to `/what-we-never-use`.

4. **Our standard** — the specific promise. *"Every ingredient sourced with a halal certificate from the supplier. Every batch tested. Every INCI name published."*

5. **Certification, in detail** — issuing body, standard (PS 5319-2014 / PS 4992-OIC-SMIIC 2), certificate number, expiry, embedded scan, verification link.

6. **How products are made** — where, by whom, under what conditions. Photographs of the actual facility if at all possible. This is where the counterfeit fear gets addressed.

7. **What we're not** — a short, disarming honesty section. *"We're new. We have three products. We're not a big company. What we are is careful."* Persona A finds this far more credible than claimed scale, and it doubles as a pre-emptive answer to the empty-store perception.

8. **Company details** — registered name, address, NTN, SECP number.

9. **Direct contact from the founder** — a personal email address with an explicit invitation.

10. **CTA** — back to products.

*Format notes:* first-person voice throughout ("I", not "we", for the founder sections); real photography only; 800–1,200 words is right — long enough to be substantial, short enough to be read on a phone; scannable with subheadings and pull quotes.

### 7.2 Contact Us

The purpose is **proof of reachability**, and its most important design constraint is that the form is the *least* important element on it.

**Required, in this order:**

1. **WhatsApp — first and most prominent.** Number as selectable text *and* as a tap-to-chat button. State response hours: *"We reply within 2 hours, 10am–8pm PKT."*
2. **Phone number** — tap-to-call, with hours. A landline alongside mobile if one exists.
3. **Email** — on the brand domain, never Gmail.
4. **Physical address** — full, with a map embed (lazy-loaded). Note whether it is an office or a warehouse; state clearly if it is not open to walk-in visitors, so an unannounced visitor is not a broken promise.
5. **Company registration details** — registered name, NTN, SECP number.
6. **Response-time commitment**, stated explicitly.
7. **Form** — last. Four fields maximum: name, contact (phone or email), subject dropdown, message. With a confirmation state that names a real timeframe.
8. **Quick links** — Track order, Shipping & returns, FAQ. These deflect the most common contact reasons.
9. **Social links** with follower counts if they are respectable (social proof); omit counts if they are not.
10. **A founder photo with a short note** — *"You'll usually be talking to me."* Converts a support page into a trust page.

⚠ **The commitment behind this page is operational, not visual.** Publishing a phone number that rings out, or a WhatsApp that goes unanswered for two days, does more damage than not publishing it at all. Persona C in particular will phone *before* ordering and treat silence as disqualifying. Only publish channels that will actually be staffed.

---

## 8. Priority recommendations

### Must have at launch (P0)
1. Halal certificate published as a **document** — issuer, number, standard, expiry, scan (or an honest in-progress state)
2. "What We Never Use" with **INCI names** — in the primary navigation
3. Full annotated INCI list on every PDP, expanded by default, screenshot-friendly
4. Founder named and photographed on the homepage
5. Company legitimacy block in the footer (address, NTN, phone)
6. COD pre-selected, 6-field guest checkout, no CNIC, no postal code
7. WhatsApp on every page with pre-filled context messages
8. Editorial feature blocks instead of product grids below 4 SKUs
9. "Founding Collection" scarcity-as-rigor framing in homepage copy
10. Performance budget enforced — under 600 KB homepage
11. Accessible colour system — gold never used for body text
12. Live-updating cart with optimistic UI and debounced writes

### Should have (P1)
13. Wudu-compatibility note on every PDP, including honest negative cases
14. OTP phone verification on COD orders
15. Order bump on checkout (recovers Buy Now AOV)
16. Third-party lab reports as linked PDFs
17. "In development" section with waitlist capture
18. Delivery-day WhatsApp reminder stating the cash amount
19. Ingredient Index (searchable INCI lookup)

### Nice to have (P2)
20. Batch verification at `/verify`
21. Urdu language toggle for trust-critical pages
22. Shade-matching quiz
23. Instagram feed embed (lazy-loaded)

---

## 9. What would upgrade this research

The personas in §1 are synthesized hypotheses, not validated findings. Three cheap studies, in priority order:

1. **8–10 remote interviews** with Pakistani Muslim women aged 22–40 who buy cosmetics online, recruited via Instagram and existing WhatsApp networks. 45 minutes each. Primary question: *what would actually convince you a halal claim is true?* This directly validates or refutes the entire trust hierarchy in §2. Highest value by a wide margin.
2. **A 5-participant unmoderated mobile usability test** on the checkout prototype, measuring completion rate, time on task, and where COD hesitation appears. Cheap, fast, and directly protects the highest-revenue flow.
3. **A first-click test on the homepage** with the trust strip in three variants (generic badges vs. certificate-number-forward vs. founder-forward) to settle the above-the-fold hierarchy with data rather than argument.

Nothing in this document should be treated as settled once (1) has been run.

---

## Sources

- [Digital Watch Observatory — Pakistan e-Commerce Policy 2.0 (2025–30)](https://dig.watch/resource/pakistans-e-commerce-policy-2-0-2025-30)
- [TrackMyOrder.pk — COD Return Rate in Pakistan](https://trackmyorder.pk/blog/shopify-tips/cod-return-rate-pakistan-shopify)
- [DHL Pakistan — How Pakistani E-Commerce Sellers Can Reduce COD Returns](https://www.dhl.com/discover/en-pk/e-commerce-advice/e-commerce-best-practice/subscription-marketing/how-pakistani-e-commerce-sellers-can-reduce-cod-returns)
- [Bloom Pakistan — Smartphones Now Drive 80% of Internet Traffic in Pakistan](https://bloompakistan.com/smartphones-now-drive-80-of-internet-traffic-in-pakistan/)
- [TechMag — Mobile Internet Usage Trends in Pakistan 2025](https://techmag.com.pk/mobile-internet-usage-trends-in-pakistan-2025/)
- [ProPakistani — Ookla H1 2025 Mobile Network Rankings](https://propakistani.pk/2025/09/24/the-best-mobile-networks-in-pakistan-ranked-again-ookla-report/)
- [Digital Rights Monitor — Pakistan Internet Speeds Global Rank](https://digitalrightsmonitor.pk/pakistan-internet-speeds-global-rank/)
- [DataReportal — Digital 2026: Pakistan](https://datareportal.com/reports/digital-2026-pakistan)
- [Markaz — Social Commerce Guide in Pakistan](https://www.markaz.app/blog-post/social-commerce)
- [PNAC — Halal Certification Bodies (PS 4992-OIC/SMIIC 2)](https://www.pnac.gov.pk/public/index.php/Halal-Certification-Bodies/Active)
- [PSQCA — Halal Standards Division](https://www.psqca.com.pk/division-wise-standards/halaal/)
- [SANHA Pakistan — Halal Certification](https://www.sanha.org.pk/)
- [Masarrat Misbah Makeup](https://masarratmakeup.com/)
- [HAYA Beauty](https://www.haya-beauty.com/)
- [Iba Cosmetics](https://www.ibacosmetics.com/)
- [Khubsurti.pk — Complete Guide to Halal Makeup in Pakistan](https://khubsurti.pk/blogs/news/the-complete-guide-to-halal-makeup-in-pakistan-best-brands-products)
- [Cosmetics Design Asia — Can nail polish be halal? Concerns over breathable and water-permeable claims](https://www.cosmeticsdesign-asia.com/Article/2021/02/25/Can-nail-polish-be-halal-Concerns-over-breathable-and-water-permeable-claims-of-wudu-friendly-nail-polish/)
- [The Vault PK — Original or Fake Cosmetics?](https://thevault.pk/blogs/cosmetics-daily-blogs/original-or-fake-cosmetics-easy-ways-to-check-authentic-products)
- [Affordable.pk — Avoid Online Shopping Scams in Pakistan 2025](https://www.affordable.pk/blog-detail/avoid-online-shopping-scams-in-pakistan-2025-safety-guide)

---

**Document status:** v1 — foundational research. Personas are unvalidated hypotheses (see §9). Market and competitive data are sourced.
**Next deliverable:** visual design system derived from §2 (trust hierarchy), §4 (IA), §6.3 (accessible gold palette).
