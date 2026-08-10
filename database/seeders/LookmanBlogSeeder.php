<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Illuminate\Database\Seeder;

/**
 * A comprehensive "Lookman-e-Hayat Oil" article — the content lever to outrank
 * thin Daraz listings for the informational + long-tail queries they ignore
 * (uses, benefits, price, side effects, how to use). Links to both product SKUs.
 *
 * DEPLOY-SAFE: firstOrCreate by slug, so re-running never clobbers edits the
 * owner makes in Admin → Content → Blog.
 *
 * Health-safe: traditional-use framing only, no cure/treatment claims, a
 * dermatologist note, and no unsourced halal ruling.
 */
class LookmanBlogSeeder extends Seeder
{
    public function run(): void
    {
        BlogPost::firstOrCreate(
            ['slug' => 'lookman-e-hayat-oil-uses-benefits-price'],
            [
                'title' => 'Lookman-e-Hayat Oil: Uses, Benefits, Price & How to Use',
                'excerpt' => 'What Lookman-e-Hayat (Luqman-e-Hayat) herbal oil is, its traditional uses, how to use it, side effects to know, and its price in Pakistan — with Cash on Delivery.',
                'status' => 'published',
                'published_at' => now(),
                'reading_time_minutes' => 6,
                'content' => $this->body(),
            ],
        );
    }

    private function body(): string
    {
        return <<<'HTML'
<p><strong>Lookman-e-Hayat oil</strong> (also spelled <strong>Luqman-e-Hayat</strong> or <strong>Lukman-e-Hayat Tel</strong>) is a traditional herbal oil made from a sesame (til) oil base infused with guggul resin. It is used as a massage oil for tired muscles and joints and as a soothing oil for dry skin, scalp and hair. It is a herbal comfort oil for external use — not a medicine.</p>

<h2>What is Lookman-e-Hayat oil?</h2>
<p>Lookman-e-Hayat Tel is a warm, amber herbal oil that South Asian households have reached for over generations. Its two signature ingredients are <strong>Til (sesame) oil</strong> — a light, easily absorbed carrier oil rich in natural fatty acids — and <strong>guggul</strong> (the resin of <em>Commiphora mukul</em>), which gives the oil its distinctive resinous note. Together they make a nourishing oil that sinks into the skin without feeling heavy or sticky.</p>

<h2>What is Lookman-e-Hayat oil used for?</h2>
<p>Traditionally, people reach for it as:</p>
<ul>
  <li>A <strong>massage oil</strong> for tired muscles, shoulders, legs and joints after a long day.</li>
  <li>A <strong>skin oil</strong> to soften dry, rough or flaky patches and help lock in moisture.</li>
  <li>A <strong>scalp and hair oil</strong>, massaged in before washing.</li>
  <li>A comforting oil for everyday skin roughness.</li>
</ul>
<p>These are traditional, everyday uses. Lookman-e-Hayat oil is a herbal comfort oil, <strong>not a medicine</strong>: it is not intended to diagnose, treat or cure any condition, and nothing here is medical advice.</p>

<h2>How to use Lookman-e-Hayat oil</h2>
<ol>
  <li><strong>Patch test first:</strong> dab a little on your inner forearm and wait a few hours before wider use.</li>
  <li><strong>For massage:</strong> warm a few drops between your palms and massage into muscles or joints in slow, circular motions until absorbed.</li>
  <li><strong>For skin:</strong> smooth a small amount over clean, dry skin on rough areas — a little goes a long way.</li>
  <li><strong>For scalp &amp; hair:</strong> part the hair, massage into the scalp, leave 30–60 minutes (or overnight), then wash as usual.</li>
</ol>
<p>External use only. Keep it away from the eyes, broken skin and open wounds.</p>

<h2>Does Lookman-e-Hayat oil have side effects?</h2>
<p>Used externally as directed, it is generally well tolerated. As with any oil, some people may notice redness or irritation — especially those with sensitive or allergy-prone skin. Always patch-test first, stop use if irritation appears, and see a doctor or dermatologist if a reaction is serious, if you are pregnant or nursing, or if you have a skin or medical condition. It is not for use on serious burns or open wounds.</p>

<h2>Lookman-e-Hayat oil price in Pakistan</h2>
<p>At Glow Halal you can buy it at:</p>
<ul>
  <li><strong><a href="/products/herbal-skin-oil-50ml">Lookman-e-Hayat Oil 50 ml</a> — Rs 1,800</strong></li>
  <li><strong><a href="/products/herbal-skin-oil-100ml">Lookman-e-Hayat Oil 100 ml</a> — Rs 3,000</strong></li>
</ul>
<p>We deliver across Pakistan with <strong>Cash on Delivery</strong> — pay when it reaches your door — and free delivery on orders over Rs 3,000. Unlike a bare marketplace listing, every one of our product pages publishes the ingredient information and a clear safety note, so you know exactly what you are buying and how to use it.</p>

<h2>Is Lookman-e-Hayat oil halal?</h2>
<p>The oil is a plant-based, external-use herbal preparation. We publish the ingredient information we have so you can check it yourself, and we hold no third-party halal certification and make no certification claim. Please read the label on the product you receive and make your own decision.</p>

<h2>Frequently asked questions</h2>
<p><strong>Which size should I buy?</strong><br>It is the same oil in two sizes. The 50 ml is ideal to try it or keep in a bag; the 100 ml is better value if you use it often for massage.</p>
<p><strong>Can I use it on my face?</strong><br>It can be used on the skin, including the face, as a light overnight oil — use a small amount, patch-test first, avoid the eyes, and use sparingly if your skin is oily or acne-prone.</p>
<p><strong>Where can I buy genuine Lookman-e-Hayat oil in Pakistan?</strong><br>You can order both sizes from our <a href="/shop/oils">oils collection</a> with Cash on Delivery nationwide.</p>

<p><em>Disclaimer: This article is general information about a traditional herbal oil, not medical advice. Products are for external use and are not intended to diagnose, treat or cure any condition. See our full <a href="/disclaimer">disclaimer</a>.</em></p>
HTML;
    }
}
