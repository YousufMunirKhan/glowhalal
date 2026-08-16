<?php

use App\Models\BlogPost;
use Illuminate\Database\Migrations\Migration;

/**
 * The two English posts that fill 20 and 24 Aug, completing the ten-day slate:
 * the authenticity guide (the widest-open gap the SERP research found — no
 * ranking page explains how to tell a real bottle from a refill) and the
 * honest answer to the un-served "is it good for face pimples?" question.
 *
 * Both were drafted earlier, converted to house format, then read by a
 * compliance reviewer. Its two "critical" findings — that our English PDPs
 * lack the 97/3 composition and contradict the post about who manufactures
 * the oil — were checked against production and REFUTED: it had queried the
 * stale local database. Live prod carries the full declared composition and
 * the M.U. Amrelia attribution on both English PDPs.
 *
 * Its sustained findings ARE applied here: the market-wide "no licensed local
 * production" negative is hedged to what we can evidence, the unverified
 * manufacturer-photo instruction becomes conditional, an invented usage rate
 * ("a 100ml bottle lasts months") is removed from what was a buy/reject test,
 * the doorstep-refusal promise is scoped to our own COD orders, and the price
 * check now explains per-millilitre economics — without which the article
 * armed a reader to doubt our own 50 ml listing.
 *
 * Both are only-children: the natural Roman Urdu twin of the authenticity post
 * is already paired with the English price post, and a cluster cannot carry
 * two English alternates.
 *
 * Idempotent — an existing slug is skipped, never overwritten.
 */
return new class extends Migration
{
    public function up(): void
    {
        $category = \App\Models\BlogCategory::query()->where('slug', 'herbal-care')->value('id');
        $author = \App\Models\User::query()->where('email', 'yousufmunir59@gmail.com')->value('id')
            ?? \App\Models\User::query()->min('id');

        if (! $category || ! $author) {
            return;
        }

        foreach (self::POSTS as $row) {
            if (BlogPost::withoutGlobalScopes()->where('slug', $row['slug'])->exists()) {
                continue;
            }

            BlogPost::withoutEvents(fn () => BlogPost::create([
                ...$row,
                'blog_category_id' => $category,
                'author_id' => $author,
                'status' => 'published',
                'allow_comments' => false,
                'is_featured' => false,
            ]));
        }
    }

    public function down(): void
    {
        BlogPost::withoutGlobalScopes()
            ->whereIn('slug', array_column(self::POSTS, 'slug'))
            ->forceDelete();
    }

    private const POSTS = array (
  0 => 
  array (
    'slug' => 'how-to-identify-original-lookman-e-hayat-oil',
    'locale' => 'en',
    'title' => 'How to Identify Original Lookman-e-Hayat Oil: 5 Checks Before You Buy',
    'excerpt' => 'Lookman e Hayat oil original or fake? Use five honest checks before you pay: manufacturer markings, batch and expiry, the seal, seller signals and price.',
    'content' => '<p class="article-answer-box">To identify original Lookman-e-Hayat oil, check five things before you pay: the manufacturer M.U. Amrelia printed on the label, a machine-printed batch number and expiry date, an intact factory seal, a seller who publishes the full composition, and a price that matches the real market rate.</p>

<p>Search for Lookman-e-Hayat oil on any marketplace in Pakistan and you will see the same word repeated in listing after listing: "Original". "New Original". "100% Original Imported". When every seller has to say it, it usually means buyers have learned to ask — because copies and refilled bottles exist wherever a trusted product sells for decades.</p>

<p>This guide gives you five practical checks. None of them requires opening the bottle, none of them requires a lab, and all five can be done in the two minutes a rider is standing at your gate.</p>

<h2>1. Check the manufacturer\'s name on the label</h2>

<p>Genuine Lookman-e-Hayat Tel is made by <strong>M.U. Amrelia</strong>, a manufacturer based in Mumbai, India, and the name appears on the packaging. If the label shows no manufacturer at all, a different company name, or only an importer\'s sticker covering the printing underneath, treat that as a red flag.</p>

<p>If you can find the manufacturer\'s own product photography, compare your label against it — spelling, layout and colours should match exactly. Copies most often get small things wrong: letter spacing, the shade of the label, or the spelling of "Lookman" itself.</p>

<p>One more thing worth knowing before you compare: this is an <strong>imported</strong> product in Pakistan. We have not found any licensed local production of it — so a bottle marked "made in Pakistan", or a plain bottle carrying a locally printed label, is worth questioning before you buy, whatever the oil inside turns out to be.</p>

<h2>2. Look for a printed batch number and expiry date</h2>

<p>A legitimately manufactured herbal oil carries a <strong>batch number and expiry date printed on the pack</strong> — printed by machine, not on a paper sticker someone could apply later. No batch number, no expiry, or a sticker where printing should be means you cannot verify what is inside or how old it is. Walk away.</p>

<p>This is also your practical check after delivery: the expiry date should give you comfortable time to use the bottle. The date should leave you comfortable time to finish the bottle at your own rate of use — an expiry only weeks away means either very old stock or printing that was never meant to be read closely.</p>

<h2>3. The seal must be intact — and you should refuse a broken one</h2>

<p>Buy only a <strong>sealed</strong> bottle. If you ordered online with Cash on Delivery, you can inspect the seal when the parcel arrives — that is one of the quiet advantages of COD. A loose cap, oil residue around the neck, or a seal that looks re-wrapped are all reasons to refuse the parcel — with our COD orders you check the seal at the door and pay nothing if you turn it away.</p>

<p>Refilling is the simplest fraud in this category: the bottle is real, the label is real, the oil is not. A seal is the one part a refiller struggles to reproduce convincingly, which is why it deserves more of your attention than any wording in the listing. Check it before you hand over money, not after — once you have paid, you have lost the only leverage the process gives you.</p>

<h2>Order the genuine oil with Cash on Delivery</h2>

<p>Glow Halal ships the genuine M.U. Amrelia product across Pakistan with <strong>Cash on Delivery</strong>: a sealed bottle with its printed batch and expiry, which you inspect at your door before you hand over a single rupee. Our sizes are <a href="/products/herbal-skin-oil-50ml">50 ml at Rs 1,200</a> and <a href="/products/herbal-skin-oil-100ml">100 ml at Rs 2,200</a>. Send your size and address to <a href="https://wa.me/923012973886">WhatsApp 0301 2973886</a> and a real person replies — ask us anything on this list before you order. Roman Urdu readers can order from <a href="/ur-roman/products/lookman-e-hayat-tel-100ml">Lookman-e-Hayat Tel 100ml</a> or the <a href="/ur-roman/products/lookman-e-hayat-tel-50ml">50ml</a> page instead.</p>

<h2>4. Judge the seller, not just the listing</h2>

<p>A listing can say anything, and the word "original" costs nothing to type. These signals are worth more:</p>

<ul>
  <li><strong>Does the seller publish the full composition?</strong> Genuine Lookman-e-Hayat Tel has a declared formula of <strong>97% til (sesame) seed oil and 3% guggul (Commiphora mukul)</strong>. A seller who states this plainly — and states what is <em>not</em> in it — is describing a product they actually know. Ours is set out on the product pages and in our <a href="/what-we-never-use">what we never use</a> list.</li>
  <li><strong>Does the seller make honest claims?</strong> This is a traditional herbal massage oil, used for generations for massage and for the look and feel of skin and hair. A seller promising to cure disease is either careless or counting on you not to check — and honesty elsewhere in a shop is evidence about the bottle too. Our own <a href="/blog/lookman-e-hayat-oil-uses-benefits-price">guide to its traditional uses</a> is deliberately written without cure claims.</li>
  <li><strong>Does the seller tell you where <em>not</em> to use it?</strong> A shop that only lists benefits is selling; a shop that also gives you limits is informing. This oil is for intact skin only. It should never go on fresh burns, cuts or broken skin — for a fresh burn, cool the area under clean running water for 10 to 20 minutes and see a doctor first. Old, fully healed marks are a different situation from an injury.</li>
  <li><strong>Can you reach them?</strong> A real phone number that answers on WhatsApp before you order will also answer after. Test it with a question while you are still deciding.</li>
</ul>

<h2>5. Be suspicious of prices that are too good</h2>

<p>Price is the check most buyers skip, and it is the one that catches the most copies. As a market observation in 2026, a genuine 100ml bottle typically sells in the <strong>Rs 2,000–2,300 range</strong> on major marketplaces in Pakistan. Smaller packs always cost more per millilitre — a 50 ml bottle priced above half the 100 ml rate is ordinary packaging economics, not a warning sign, so judge each size against its own size. That is a range, not a fixed rate: it moves with the exchange rate, import costs and how the seller ships.</p>

<p>Against that background, you will also see listings offering two bottles for less than the honest price of one. An imported product cannot be sold at a fraction of its landed cost and still be the same product. A deep "discount" on an imported herbal oil is not a bargain; it is the single most common signal of a refill or a copy.</p>

<p>The inverse is also worth watching. A price far <em>above</em> the range is not proof of authenticity either — it is just a bigger margin. What you want is a price that sits inside the range with an explanation attached. For the full picture of what sets the floor and what moves it, read our <a href="/blog/lookman-e-hayat-oil-price-in-pakistan">Lookman-e-Hayat oil price in Pakistan breakdown</a>.</p>

<h2>What to do if you already have a bottle</h2>

<p>Run the same five checks in reverse on what is already on your shelf. Look for the manufacturer\'s name, find the printed batch and expiry, and and, if you can find the manufacturer\'s own product photography, compare the label against it. If the printing is a sticker, if the manufacturer is missing, or if the bottle came at a price that never made sense, stop using it — you do not know what is inside, and an unknown oil is not something to put on skin.</p>

<p>If everything checks out, store it as you would any oil: capped, out of direct sunlight, away from heat. And do a patch test on your inner forearm before your first proper use, even with a genuine bottle. Skin is individual; a product being authentic does not make it right for everyone.</p>

<h2>What we do at Glow Halal</h2>

<p>We are a Karachi-based reseller of the genuine M.U. Amrelia product. Every bottle we ship is sealed, carries its printed batch and expiry, and we publish the full composition on the product page — along with what the oil is traditionally used for and, just as importantly, what it should never be used on. You inspect the parcel before you pay, because every order is Cash on Delivery.</p>

<ul>
  <li><a href="/products/herbal-skin-oil-100ml">Lookman-e-Hayat Herbal Oil — 100 ml, Rs 2,200</a></li>
  <li><a href="/products/herbal-skin-oil-50ml">Lookman-e-Hayat Herbal Oil — 50 ml, Rs 1,200</a></li>
  <li><a href="/shop/oils">Browse all our herbal oils</a></li>
</ul>

<p><strong>Order on WhatsApp:</strong> send your size and address to <a href="https://wa.me/923012973886">0301 2973886</a> — Cash on Delivery across Pakistan, and you check the seal before you pay.</p>

<p class="article-disclaimer"><em>Disclaimer: Lookman-e-Hayat Tel is a traditional herbal oil for external use only. It is not a medicine and not a substitute for medical care, and it is not intended to diagnose, treat, cure or prevent any disease. Never apply it to fresh burns, cuts or broken skin — for any burn or injury, cool the area under clean running water for 10 to 20 minutes and see a doctor. Patch test before first use, and consult a qualified doctor or dermatologist for any persistent skin concern.</em></p>

<h2>Frequently asked questions</h2>

<h3>How can I tell if my Lookman-e-Hayat oil is original?</h3>
<p>Check five things: the manufacturer&#039;s name (M.U. Amrelia) printed on the label, a machine-printed batch number and expiry date, an intact seal, a seller who publishes the full composition (97% til oil, 3% guggul), and a price in line with the market. A bottle failing any of these deserves suspicion.</p>

<h3>Who makes the original Lookman-e-Hayat Tel?</h3>
<p>M.U. Amrelia of Mumbai, India. It is an imported product in Pakistan. We have not found any licensed local production of it, so we would not treat a &quot;made in Pakistan&quot; bottle as the imported original.</p>

<h3>Why is Lookman-e-Hayat oil sometimes so cheap online?</h3>
<p>Listings far below the Rs 2,000–2,300 range that 100ml bottles typically sell for on major marketplaces are the most common sign of refilled or copied stock. Importing, shipping and customs set a real floor under the honest price.</p>

<h3>Is it safe to buy Lookman-e-Hayat oil with Cash on Delivery?</h3>
<p>COD is actually the safer way: you can check the seal, the batch printing and the manufacturer markings while the rider waits, and refuse the parcel if anything looks wrong. Every Glow Halal order is COD across Pakistan — 50ml is Rs 1,200 and 100ml is Rs 2,200, and you can ask questions first on WhatsApp 0301 2973886.</p>

<h3>Can I use Lookman-e-Hayat oil on a burn?</h3>
<p>No. Never put any oil on a fresh burn or on broken skin — cool the area under clean running water for 10 to 20 minutes and see a doctor first. This is a traditional herbal oil for intact skin only; people apply it to old, fully healed marks, which is a different situation from an injury.</p>
',
    'published_at' => '2026-08-20 01:00:00',
    'translation_group_id' => NULL,
    'reading_time_minutes' => 9,
  ),
  1 => 
  array (
    'slug' => 'lookman-e-hayat-oil-for-face-honest-answer',
    'locale' => 'en',
    'title' => 'Lookman-e-Hayat Oil for Face & Pimples: An Honest Answer',
    'excerpt' => 'Lookman-e-Hayat oil for face use, answered honestly: what it can do, why it can make pimples worse on oily skin, and how to patch test before you try.',
    'content' => '<p class="article-answer-box">Lookman-e-Hayat oil can be used on the face as a light massage and moisturising oil, but it is not an acne treatment. On oily or acne-prone skin, a rich sesame-based oil applied heavily can make breakouts worse. Patch test first, use two or three drops at night, and see a dermatologist for persistent acne.</p>

<p>"Is Lookman-e-Hayat Tel good for face pimples?" is one of the most-asked questions about this oil — and most answers you will find online were written to sell you a bottle. We sell this oil too, but here is the answer we would give a family member.</p>

<p><strong>Short version: it can be used on the face as a massage and moisturising oil — but it is not an acne treatment, and on acne-prone skin a heavy hand can make breakouts worse.</strong></p>

<h2>What Lookman-e-Hayat oil actually is</h2>

<p>Lookman-e-Hayat Tel is a traditional herbal oil with a declared composition of just two ingredients: <strong>97% til (black sesame) seed oil and 3% guggul (Commiphora mukul) resin</strong>. It has been used across South Asia for generations as a massage oil for skin and hair. It is a cosmetic, traditional product — not a registered medicine, and nothing in this article is medical advice.</p>

<p>That short ingredient list matters more on the face than anywhere else on the body. Facial skin is thinner and more reactive than the skin on your arms or scalp, so the fewer things a product contains, the easier it is to work out what your skin is responding to. With two ingredients, there are only two suspects.</p>

<p>It also means there is no clever formulation at work here. No actives, no exfoliating acids, no oil-control system, no anything designed by a lab to do a specific job on facial skin. It is a carrier oil with a resin in it, and on the face it behaves exactly like a carrier oil with a resin in it. If you want the longer version of what goes into the products we stock and what we refuse to stock, our <a href="/halal-ingredients">halal ingredients page</a> and the <a href="/what-we-never-use">what we never use</a> page are both blunter than most brand pages you will read.</p>

<h2>Can you put it on your face at all?</h2>

<p>Yes. Many people use a few drops as a light face massage oil or an overnight moisturising layer, and traditionally it is massaged into <strong>old, fully healed</strong> marks. Sesame oil is a classic carrier oil in South Asian skincare — rich, softening, and generally well tolerated on normal to dry skin.</p>

<p>What it is not is a product anyone designed for faces. The same bottle is sold for body massage, for champi, and for general dry skin, and it was never reformulated lighter for the face. That is not a fault — it is just the honest starting point. It means the way you use it matters far more than it would with a purpose-built face oil.</p>

<h3>Four rules that make face use sensible</h3>

<ol>
<li><strong>Patch test first.</strong> A drop on the inner forearm, wait 24 hours. Herbal does not mean allergy-proof — sesame is a recognised allergen for some people, and a reaction on a patch of forearm is a much smaller problem than a reaction across your whole face.</li>
<li><strong>Use a small amount.</strong> Two or three drops warmed between the fingers is a face\'s worth. More is not better — the surplus just sits on the surface, transfers to your pillowcase, and gives your pores more to deal with overnight.</li>
<li><strong>Keep it away from the eyes.</strong> If any does get in, rinse with clean water. It is an oil, not an eye product, and it was never meant to go near them.</li>
<li><strong>Never apply it to broken skin.</strong> Fresh pimple wounds, picked spots, scratches, cuts or any unhealed burn are off-limits. For a fresh burn the first steps are cool running water for 10–20 minutes and a doctor — never oil of any kind. This oil is only ever for old, fully healed marks on intact skin.</li>
</ol>

<p><strong>Not sure whether it suits your skin?</strong> Message us on WhatsApp at <a href="https://wa.me/923012973886">0301 2973886</a> before you order — a real person replies, and if the honest answer is "this is not the right product for your skin", that is the answer you will get. Every order ships sealed, cash on delivery anywhere in Pakistan, so you can inspect the bottle at your door before you pay a rupee.</p>

<h2>The pimples question, honestly</h2>

<p>Here is where we will not oversell. <strong>Sesame oil is a rich oil.</strong> On skin that is already oily or acne-prone, layering a rich oil can trap sebum and make breakouts more likely, not less. Guggul has a long history in traditional practice, but at 3% of a massage oil, this product is not formulated as — and must not be treated as — an acne remedy.</p>

<p>It is worth naming where the opposite impression comes from. Search the question and what you mostly get back is not an answer but a product listing: a line lifted off the packaging, a bullet list of everything the oil has ever been associated with anywhere, and a buy button underneath. None of that is evidence that a sesame-based massage oil clears acne, and we are not going to dress it up as if it were.</p>

<h3>If you have pimples and still want to try it</h3>

<ul>
<li>Use it <strong>sparingly, at night only</strong>, on the drier areas of the face rather than the oily T-zone.</li>
<li>Give it a fair trial on a small area first — one cheek, or the dry patch that bothers you — before you commit the whole face to it.</li>
<li><strong>Stop at the first sign of new breakouts.</strong> That is your skin\'s answer, and it outranks any article, including this one.</li>
<li>If acne is persistent or painful, the right person to see is a <strong>dermatologist</strong>, not an oil seller. Persistent acne has real treatments; a massage oil is not one of them.</li>
</ul>

<h2>Where it fits better on the face</h2>

<p>Where this oil earns its reputation on the face is the gentler jobs: softening dry patches, an occasional evening massage, and traditional daily massage into old, fully healed marks — with patience measured in weeks, and what people notice varying a great deal from person to person.</p>

<p>In practice that means the dry, tight skin along the jaw and cheeks in winter; the flaky patch beside the nose that no amount of face wash fixes; and the after-shave dryness men get on the neck. These are moisturising jobs, and a rich oil is genuinely good at them. For the full picture of the traditional uses this oil is known for, see <a href="/blog/lookman-e-hayat-oil-uses-benefits-price">Lookman-e-Hayat Oil: Uses, Benefits, Price &amp; How to Use</a>.</p>

<h3>A sensible evening routine</h3>

<p>If you are going to use it on your face, the routine that gives it the best chance is boring and short. Wash your face as normal and pat it almost dry. Warm two or three drops between your palms. Press them onto the skin rather than dragging them around — the face does not need scrubbing. Skip the T-zone if you are oily there. Leave it overnight, use a clean pillowcase, and wash your face in the morning as usual. Then judge it after two or three weeks, not after two or three nights.</p>

<h3>If your skin is oily, send it to your hair instead</h3>

<p>For a lot of people with oily or congested skin, the sensible answer is that this oil belongs above the hairline, not on the face at all. That is where a rich sesame oil is at its most at home, and the traditional method has been refined over generations — our <a href="/blog/how-to-use-herbal-oil-for-hair-champi">guide to champi with herbal oil</a> covers the warming, the massage and how long to leave it on. Nothing is wasted if the face is not the right home for it.</p>

<h2>Trying it sensibly</h2>

<p>If you want to test how your skin responds without committing to a full bottle, the small size exists for exactly this:</p>

<ul>
<li><a href="/products/herbal-skin-oil-50ml">Lookman-e-Hayat Herbal Oil — 50 ml, Rs 1,200</a> — the trial size</li>
<li><a href="/products/herbal-skin-oil-100ml">Lookman-e-Hayat Herbal Oil — 100 ml, Rs 2,200</a> — better per-ml value for regular use</li>
</ul>

<p>Both ship sealed, cash on delivery across Pakistan, and both are the same oil in different quantities — the 50 ml is not a lesser version. To order or ask anything at all, message <a href="https://wa.me/923012973886">WhatsApp 0301 2973886</a>, or browse <a href="/shop/oils">our oils</a>. If you want the size-by-size cost breakdown before you decide, we have written that out in full in our <a href="/blog/lookman-e-hayat-oil-price-in-pakistan">Lookman-e-Hayat oil price in Pakistan</a> guide. Reading in Roman Urdu? The same bottles are at <a href="/ur-roman/products/lookman-e-hayat-tel-50ml">Lookman-e-Hayat Tel 50ml</a> and <a href="/ur-roman/products/lookman-e-hayat-tel-100ml">100ml</a>.</p>

<p>And if the honest conclusion for your skin is that a rich oil is the wrong thing for your face right now, that is a perfectly good outcome for this article. We would rather sell you nothing today than sell you something that gives you a worse week.</p>

<p><em>Lookman-e-Hayat Tel is a traditional herbal oil for external use only. It is not a medicine and not a substitute for medical care. For persistent skin problems, please see a doctor or dermatologist.</em></p>

<h2>Frequently asked questions</h2>

<h3>Is Lookman-e-Hayat Tel good for face pimples?</h3>
<p>It is not an acne treatment and we make no claim that it clears pimples. On acne-prone skin, a rich sesame-based oil applied heavily can make breakouts worse. If you try it, use a tiny amount at night, patch test first, and stop if your skin reacts. For persistent or painful acne, see a dermatologist.</p>

<h3>Can I use Lookman-e-Hayat oil on my face every day?</h3>
<p>On normal to dry skin, a few drops as a night oil is a common traditional use. Patch test on your inner forearm first, keep it away from the eyes, and never apply it over broken skin. If your skin is oily or congested, use it sparingly or keep it for the hair instead.</p>

<h3>Does it remove pimple marks?</h3>
<p>We make no claim that it removes marks, and you should be wary of anyone who does. What we can honestly describe is the traditional use: it is massaged into old, fully healed marks over a period of weeks as part of a routine, and what people notice varies a great deal from person to person. It must never be used on fresh or healing pimple wounds.</p>

<h3>Is Lookman-e-Hayat oil non-comedogenic?</h3>
<p>No such claim is made for this oil. Sesame oil is moderately rich, so if your skin clogs easily, use it very sparingly on dry areas only, or keep it for the body and hair instead — a weekly champi is where a rich oil like this is most at home.</p>
',
    'published_at' => '2026-08-24 01:00:00',
    'translation_group_id' => NULL,
    'reading_time_minutes' => 9,
  ),
);
};
