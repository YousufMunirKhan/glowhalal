<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Illuminate\Database\Seeder;

/**
 * Three high-intent, low-competition English articles aimed at queries a brand-new
 * Glow Halal domain can realistically win in the coming weeks, and that double as
 * shareable social/WhatsApp content:
 *
 *   1. "Lookman-e-Hayat oil price in Pakistan (2026) & where to buy" — pure
 *      bottom-funnel commercial intent; weak, thin-listing competition.
 *   2. "How to use herbal oil for hair (champi)" — evergreen, highly shareable
 *      how-to that funnels to the product.
 *   3. "Best halal herbal oils in Pakistan — buyer's guide" — commercial pillar
 *      that reinforces the BROAD multi-category halal-beauty positioning.
 *
 * ANTI-CANNIBALIZATION: each uses a DISTINCT primary keyword/title/slug from the
 * existing posts (uses-benefits-price, joint-pain, cuts-burns) and from each other.
 * The price post is deliberately scoped to "price / where to buy / genuine", NOT
 * "uses & benefits" (which the existing lookman-e-hayat-oil-uses-benefits-price
 * post owns) and links back to it to consolidate topical authority.
 *
 * TWINS: each row carries a FIXED translation_group_id so a future DECONFLICTED
 * Roman-Urdu twin (distinct keyword, e.g. "asli lookman e hayat tel kahan se lein"
 * / "baalon mein tel lagane ka tarika") can pair without cannibalizing.
 *
 * DEPLOY-SAFE: firstOrCreate by slug — re-running never clobbers admin edits.
 *
 * Health-safe: traditional-/cosmetic-use framing only, no cure/treatment/disease
 * claims, no "halal certified" claim (halal = ethos), no fabricated reviews/stats.
 * meta_description is served from `excerpt` via HasSeoMeta, so each excerpt is
 * written to double as a 140–160 char meta description.
 */
class WinnableBlogSeeder extends Seeder
{
    /** Fixed group UUIDs — one per topic, for a future Roman-Urdu twin to share. */
    private const GROUP_PRICE = 'c3f6d4e5-2f70-4b8c-a0d9-1f2a3b4c5d6e';

    private const GROUP_HAIR_CHAMPI = 'd4a7e5f6-3081-4c9d-b1e0-2a3b4c5d6e7f';

    private const GROUP_HALAL_OILS = 'e5b8f617-4192-4d0e-92f1-3b4c5d6e7f80';

    public function run(): void
    {
        foreach ($this->articles() as $article) {
            BlogPost::firstOrCreate(
                ['slug' => $article['slug']],
                [
                    'title' => $article['title'],
                    'locale' => 'en',
                    'translation_group_id' => $article['translation_group_id'],
                    'excerpt' => $article['excerpt'],
                    'status' => 'published',
                    'published_at' => now(),
                    'reading_time_minutes' => $article['reading_time_minutes'],
                    'content' => $article['content'],
                ],
            );
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function articles(): array
    {
        return [
            [
                'translation_group_id' => self::GROUP_PRICE,
                'title' => 'Lookman-e-Hayat Oil Price in Pakistan (2026) & Where to Buy',
                'slug' => 'lookman-e-hayat-oil-price-in-pakistan',
                'excerpt' => 'Lookman-e-Hayat oil price in Pakistan (2026): 50ml is Rs 1,800 and 100ml is Rs 3,000. Where to buy genuine oil with Cash on Delivery nationwide from Glow Halal.',
                'reading_time_minutes' => 4,
                'content' => $this->priceEn(),
            ],
            [
                'translation_group_id' => self::GROUP_HAIR_CHAMPI,
                'title' => 'How to Use Herbal Oil for Hair: Champi Guide (Pakistan)',
                'slug' => 'how-to-use-herbal-oil-for-hair-champi',
                'excerpt' => 'How to use herbal hair oil (champi) the right way in Pakistan: a step-by-step scalp-massage routine, how often to oil, patch-test safety and which size to buy.',
                'reading_time_minutes' => 5,
                'content' => $this->hairChampiEn(),
            ],
            [
                'translation_group_id' => self::GROUP_HALAL_OILS,
                'title' => "Best Halal Herbal Oils in Pakistan: Buyer's Guide 2026",
                'slug' => 'best-halal-herbal-oils-in-pakistan',
                'excerpt' => 'Best halal herbal oils in Pakistan: what "halal herbal" really means, how to choose a plant-based, external-use oil you can trust, and honest COD buying tips.',
                'reading_time_minutes' => 6,
                'content' => $this->halalOilsEn(),
            ],
        ];
    }

    private function priceEn(): string
    {
        return <<<'HTML'
<div class="article-answer-box">
  <p>At Glow Halal, Lookman-e-Hayat oil is priced at Rs 1,800 for 50ml and Rs 3,000 for 100ml in Pakistan (2026). You can order genuine oil online with Cash on Delivery nationwide, or on WhatsApp, and pay only when it reaches your door.</p>
</div>

<h2>What is the price of Lookman-e-Hayat oil in Pakistan (2026)?</h2>
<p>The current 2026 price at Glow Halal is <strong>Rs 1,800 for the 50ml bottle</strong> and <strong>Rs 3,000 for the 100ml bottle</strong>. It is the same herbal oil in both sizes — the larger bottle simply costs less per millilitre. Prices can change over time, so the figure shown live on each product page is always the one to trust.</p>

<h2>50ml vs 100ml: which size is better value?</h2>
<table>
  <thead>
    <tr><th>Size</th><th>Price</th><th>Per ml</th><th>Best for</th></tr>
  </thead>
  <tbody>
    <tr><td>50ml</td><td>Rs 1,800</td><td>Rs 36 / ml</td><td>Trying it for the first time, or occasional use</td></tr>
    <tr><td>100ml</td><td>Rs 3,000</td><td>Rs 30 / ml</td><td>Regular use — better value per ml, lasts longer</td></tr>
  </tbody>
</table>
<p>First time? Start with the <a href="/products/herbal-skin-oil-50ml">50ml bottle (Rs 1,800)</a>. If you already use it regularly, the <a href="/products/herbal-skin-oil-100ml">100ml bottle (Rs 3,000)</a> works out cheaper per ml. You can see both on our <a href="/shop/oils">herbal oils shop</a>. New to the oil? Our <a href="/blog/lookman-e-hayat-oil-uses-benefits-price">uses, benefits &amp; how-to-use guide</a> explains what it is first.</p>

<h2>Where can you buy genuine Lookman-e-Hayat oil in Pakistan?</h2>
<p>You can order both sizes directly from Glow Halal with Cash on Delivery anywhere in Pakistan — from Karachi, Lahore and Islamabad to smaller towns — or message us on WhatsApp and we will arrange it in minutes. Ordering from the seller (rather than a random reseller) means the price you see is the price you pay, with no surprises at the door.</p>

<h2>How can you tell you are getting good-quality oil?</h2>
<p>A few honest checks help you buy with confidence:</p>
<ul>
  <li>The seller <strong>publishes the ingredient information</strong> so you know what is in the bottle.</li>
  <li>There is a clear <strong>external-use safety note</strong>, not a page full of miracle promises.</li>
  <li><strong>Cash on Delivery</strong> is offered, so you receive a sealed bottle before you pay.</li>
  <li>You can reach a real person (for us, on WhatsApp) before and after ordering.</li>
</ul>
<p>Be cautious of any listing that claims an oil can cure a disease — a herbal cosmetic oil is a comfort product, not a medicine.</p>

<h2>How does Cash on Delivery work?</h2>
<p>Place your order on the product page or send us a WhatsApp message, we confirm your address, the courier delivers to your door, and you pay cash on delivery. Delivery is nationwide, and orders over Rs 3,000 ship free — so the 100ml bottle qualifies on its own.</p>

<h2>Frequently asked questions</h2>
<h3>What is the price of Lookman-e-Hayat oil 50ml?</h3>
<p>The 50ml bottle is Rs 1,800 at Glow Halal, with Cash on Delivery across Pakistan.</p>
<h3>What is the price of the 100ml bottle?</h3>
<p>The 100ml bottle is Rs 3,000, which is better value per ml and ships free.</p>
<h3>Do you deliver everywhere in Pakistan?</h3>
<p>Yes. We deliver nationwide with Cash on Delivery — you pay when the parcel reaches you.</p>
<h3>Can I order on WhatsApp instead of the website?</h3>
<p>Yes. Message us and we will place the order for you and confirm delivery.</p>

<h2>How to order (Cash on Delivery)</h2>
<p>Order online with Cash on Delivery anywhere in Pakistan, or <a href="https://wa.me/923417164556">chat with us on WhatsApp</a> to order in seconds and pay at your door.</p>

<p class="article-disclaimer"><em>Disclaimer: Lookman-e-Hayat oil is a herbal cosmetic product for external use, not a medicine. It is not intended to diagnose, treat, cure or prevent any disease. Prices are current at the time of writing and may change — the live price on the product page applies. Keep out of reach of children and avoid contact with the eyes.</em></p>
HTML;
    }

    private function hairChampiEn(): string
    {
        return <<<'HTML'
<div class="article-answer-box">
  <p>To use herbal oil for hair, warm a little oil, part your hair, and massage it into your scalp in slow circles for 5–10 minutes (champi). Leave it 30–60 minutes or overnight, then wash as usual. Do a patch test first and oil once or twice a week.</p>
</div>

<h2>What is champi and why do people oil their hair?</h2>
<p>Champi is the traditional scalp-and-hair oil massage that families across Pakistan and South Asia have done for generations. People oil their hair because it is relaxing, it conditions the hair shaft, and it helps hair feel softer, look shinier and sit smoother. It is a self-care and grooming routine — a cosmetic habit, not a medical treatment.</p>

<h2>How do you use herbal oil for hair, step by step?</h2>
<ol>
  <li><strong>Patch test first:</strong> dab a little oil on your inner forearm and wait a few hours if it is your first time.</li>
  <li><strong>Warm the oil:</strong> rub a small amount between your palms, or stand the closed bottle in warm water for a minute.</li>
  <li><strong>Section your hair</strong> so you can reach the scalp.</li>
  <li><strong>Massage the scalp</strong> in slow, circular motions for 5–10 minutes.</li>
  <li><strong>Work the rest through the lengths</strong> to the ends, where hair is driest.</li>
  <li><strong>Leave it on</strong> for 30–60 minutes — or overnight with a towel on your pillow.</li>
  <li><strong>Wash out</strong> with a mild shampoo; you may need two gentle rounds.</li>
</ol>

<h2>How often should you oil your hair?</h2>
<p>Once or twice a week suits most hair. If your scalp is naturally oily, once a week or even less is plenty. There is no benefit to overdoing it — more oil just means more washing to get it out.</p>

<h2>Can herbal oil make dry, frizzy or dull hair look better?</h2>
<p>Regular oiling coats and conditions the hair shaft, which can make hair feel softer, look shinier and appear less dry or frizzy, and can tame flyaways. The massage itself is soothing at the end of a long day. These are cosmetic, feel-and-look benefits. Oiling is not a cure for hair loss, dandruff or a scalp condition — if you have ongoing hair fall, an itchy or flaky scalp, or bald patches, please see a doctor or dermatologist rather than relying on any oil.</p>

<h2>Which oil and size should you choose?</h2>
<p>Lookman-e-Hayat oil has a light <strong>til (sesame)</strong> base — a carrier oil long used for hair and scalp massage — so it absorbs without feeling heavy. Choose your size by how often you will oil:</p>
<table>
  <thead>
    <tr><th>Size</th><th>Price</th><th>Best for</th></tr>
  </thead>
  <tbody>
    <tr><td>50ml</td><td>Rs 1,800</td><td>Trying champi, or short hair / occasional oiling</td></tr>
    <tr><td>100ml</td><td>Rs 3,000</td><td>Long hair or weekly oiling for the whole family — better value per ml</td></tr>
  </tbody>
</table>
<p>Start with the <a href="/products/herbal-skin-oil-50ml">50ml bottle (Rs 1,800)</a>, or go for the <a href="/products/herbal-skin-oil-100ml">100ml bottle (Rs 3,000)</a> if you oil regularly. Browse the full range on our <a href="/shop/oils">herbal oils shop</a>, and see our <a href="/blog/best-halal-herbal-oils-in-pakistan">halal herbal oils buyer's guide</a> to compare.</p>

<h2>Frequently asked questions</h2>
<h3>Can I leave the oil in overnight?</h3>
<p>Yes. Use a towel on your pillow to protect it, then wash your hair in the morning with a mild shampoo.</p>
<h3>Will it make my hair grow or stop hair fall?</h3>
<p>It is a cosmetic conditioning oil, not a medicine, so it will not treat hair loss. It can make hair look and feel healthier. For real hair fall or thinning, see a dermatologist.</p>
<h3>Is it okay for an oily scalp or dandruff?</h3>
<p>Use a small amount less often. For persistent dandruff or an itchy, flaky scalp, see a doctor rather than relying on oil.</p>
<h3>How much oil should I use?</h3>
<p>A little goes a long way — roughly a teaspoon for short hair, up to a tablespoon for long, thick hair.</p>

<h2>How to order (Cash on Delivery)</h2>
<p>Order online with Cash on Delivery across Pakistan, or <a href="https://wa.me/923417164556">chat with us on WhatsApp</a> to order in seconds and pay at your door.</p>

<p class="article-disclaimer"><em>Disclaimer: Lookman-e-Hayat oil is a herbal cosmetic oil for external use, not a medicine. It is not intended to diagnose, treat, cure or prevent any condition, including hair loss or scalp conditions. Do a patch test first, keep it away from the eyes and broken skin, and consult a doctor or dermatologist for any persistent hair or scalp concern. Results vary from person to person.</em></p>
HTML;
    }

    private function halalOilsEn(): string
    {
        return <<<'HTML'
<div class="article-answer-box">
  <p>A halal herbal oil is a plant-based oil made for external use, sold honestly — with its ingredients listed and no exaggerated medical promises. When buying in Pakistan, check the ingredient list, look for a clear safety note, and choose a seller that offers Cash on Delivery so you can buy with confidence.</p>
</div>

<h2>What does "halal herbal oil" actually mean?</h2>
<p>For us, <strong>"halal" describes the ethos, not a certificate</strong>: plant-based ingredients, transparent labelling and honest, external cosmetic use. We hold <strong>no third-party halal certification and make no certification claim</strong> — instead we publish the ingredient information we have so you can read it and decide for yourself. If a seller stamps "halal certified" on a bottle without proof, treat that as a red flag, not a reassurance.</p>

<h2>How do you choose a halal herbal oil you can trust?</h2>
<p>A simple checklist before you buy in Pakistan:</p>
<ul>
  <li><strong>Ingredients are clearly listed</strong> — you should know what is in the bottle.</li>
  <li>It is <strong>labelled for external use</strong> with a patch-test / safety note.</li>
  <li>The claims are <strong>realistic</strong> — a cosmetic oil should not promise to cure a disease.</li>
  <li><strong>Cash on Delivery</strong> is available, so you inspect a sealed bottle before paying.</li>
  <li>There is a <strong>real person to contact</strong> (for us, WhatsApp) before and after ordering.</li>
  <li>Any "halal" claim is <strong>honest about certification</strong> — ethos, not an unproven stamp.</li>
</ul>

<h2>Which herbal oils are popular in Pakistan?</h2>
<p>These are the plant-based oils people most often ask about. Each is a traditional cosmetic oil — none is a medicine:</p>
<ul>
  <li><strong>Til (sesame) oil</strong> — a light carrier oil long used for skin and scalp massage.</li>
  <li><strong>Kalonji (black seed) oil</strong> — a well-known traditional oil in South Asian homes.</li>
  <li><strong>Coconut oil</strong> — a familiar staple for hair and skin.</li>
  <li><strong>Sweet almond oil</strong> — a light, everyday moisturising oil.</li>
  <li><strong>Zaitoon (olive) oil</strong> — the kitchen-to-skincare classic.</li>
</ul>
<p>Whichever you choose, always patch test first and use it on healthy, unbroken skin.</p>

<h2>What is Glow Halal's hero herbal oil?</h2>
<p>Our current flagship is <strong>Lookman-e-Hayat oil</strong>, a til (sesame) based herbal oil traditionally used as a gentle massage and skin oil. It is the first hero product in a growing halal beauty range — more halal skincare and cosmetics are on the way. You can read the full <a href="/blog/lookman-e-hayat-oil-uses-benefits-price">uses, benefits &amp; how-to-use guide</a>, or see the <a href="/blog/lookman-e-hayat-oil-price-in-pakistan">latest price in Pakistan</a>.</p>

<h2>50ml vs 100ml: which size should you buy?</h2>
<table>
  <thead>
    <tr><th>Size</th><th>Price</th><th>Best for</th></tr>
  </thead>
  <tbody>
    <tr><td>50ml</td><td>Rs 1,800</td><td>Trying it, or occasional use</td></tr>
    <tr><td>100ml</td><td>Rs 3,000</td><td>Regular use — better value per ml</td></tr>
  </tbody>
</table>
<p>Start with the <a href="/products/herbal-skin-oil-50ml">50ml bottle (Rs 1,800)</a> or the better-value <a href="/products/herbal-skin-oil-100ml">100ml bottle (Rs 3,000)</a>, and explore everything on our <a href="/shop/oils">herbal oils shop</a>.</p>

<h2>Frequently asked questions</h2>
<h3>Are your oils halal certified?</h3>
<p>We make no certification claim. "Halal" is our ethos — plant-based, external-use and transparently labelled — and we publish the ingredient information so you can read it and decide.</p>
<h3>Which oil is best for me?</h3>
<p>It depends on how you will use it. Our current hero is a til-based herbal massage and skin oil. Whatever you pick, patch test first.</p>
<h3>Do you deliver across Pakistan?</h3>
<p>Yes — Cash on Delivery nationwide, and you can also order on WhatsApp.</p>
<h3>Are more halal products coming?</h3>
<p>Yes. Glow Halal is a halal beauty and cosmetics store, and we are adding more honest, plant-based products over time.</p>

<h2>How to order (Cash on Delivery)</h2>
<p>Order online with Cash on Delivery across Pakistan, or <a href="https://wa.me/923417164556">chat with us on WhatsApp</a> to order in seconds and pay at your door.</p>

<p class="article-disclaimer"><em>Disclaimer: The oils described here are herbal cosmetic products for external use, not medicines. They are not intended to diagnose, treat, cure or prevent any disease. "Halal" refers to our plant-based, transparent ethos; we hold no third-party halal certification and make no certification claim. Always patch test, keep products away from the eyes and broken skin, and consult a doctor for any medical concern.</em></p>
HTML;
    }
}
