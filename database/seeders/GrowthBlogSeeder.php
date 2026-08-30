<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Illuminate\Database\Seeder;

/**
 * Two audience-growth topics that open NEW high-intent search doors the domain
 * does not own yet, each as a deconflicted English + Roman-Urdu pair:
 *
 *   A. "Best massage oil in Pakistan" (maalish) — high-volume commercial intent;
 *      it is literally what the product IS, so it funnels straight to the oil.
 *   B. "Herbal oil for back pain" (kamar dard) — high-intent, and DISTINCT from the
 *      existing joint-pain post (jodon-ke-dard). Framed as a comfort/relaxation
 *      massage oil, never a treatment (see compliance note below).
 *
 * ANTI-CANNIBALIZATION: each row uses a DISTINCT primary keyword/title/slug from
 * every existing post and from its own twin. The Roman-Urdu twin NEVER reuses the
 * English keyword translated — it targets a native Roman-Urdu phrase, because
 * Google reads Roman Urdu as English (twins would otherwise cannibalize):
 *   - massage : EN "best massage oil in pakistan"  / UR "jism ki maalish ka behtareen tel"
 *   - backpain: EN "herbal oil for back pain"       / UR "kamar dard ke liye tel"
 *
 * PRICING: current live prices — 50ml Rs 1,200, 100ml Rs 2,200 (verified against
 * the product pages, Aug 2026). Deliberately NO free-delivery claim: the live
 * threshold is Rs 5,000, which a single bottle does not reach.
 *
 * DEPLOY-SAFE: firstOrCreate by slug — re-running never clobbers admin edits.
 * Category + author are backfilled by BlogDefaultsSeeder (run it after this one).
 *
 * HEALTH-SAFE (content-honesty rules): traditional cosmetic massage-oil framing
 * only. No cure/treatment/disease claims, no "halal certified" claim, no
 * fabricated reviews/ratings/stats. The back-pain post LEADS with the "not a
 * medicine, see a doctor" safety line. meta_description is served from `excerpt`
 * via HasSeoMeta, so each excerpt doubles as a 140-160 char meta description.
 */
class GrowthBlogSeeder extends Seeder
{
    /** Fixed group UUIDs — one per topic, shared by the EN + Roman-Urdu twin. */
    private const GROUP_MASSAGE = 'a1c2e3f4-5b60-4d71-8e92-0f1a2b3c4d5e';

    private const GROUP_BACKPAIN = 'b2d3f4a5-6c71-4e82-9f03-1a2b3c4d5e6f';

    public function run(): void
    {
        foreach ($this->articles() as $article) {
            BlogPost::firstOrCreate(
                ['slug' => $article['slug']],
                [
                    'title' => $article['title'],
                    'locale' => $article['locale'],
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
                'translation_group_id' => self::GROUP_MASSAGE,
                'locale' => 'en',
                'title' => "Best Massage Oil in Pakistan: An Honest Buyer's Guide",
                'slug' => 'best-massage-oil-in-pakistan',
                'excerpt' => 'Looking for the best massage oil in Pakistan? An honest guide to choosing a herbal maalish oil, how to use it, and real Cash-on-Delivery prices from Rs 1,200.',
                'reading_time_minutes' => 5,
                'content' => $this->massageEn(),
            ],
            [
                'translation_group_id' => self::GROUP_MASSAGE,
                'locale' => 'ur-Latn',
                'title' => 'Jism ki Maalish ka Behtareen Tel — Imandaar Guide',
                'slug' => 'jism-ki-maalish-ka-behtareen-tel',
                'excerpt' => 'Jism ki maalish ka behtareen tel kaunsa hai? Herbal maalish tel chunne ka tareeqa, istemaal, aur asli qeemat (Rs 1,200 se) — sab kuch imandari ke saath.',
                'reading_time_minutes' => 5,
                'content' => $this->massageUr(),
            ],
            [
                'translation_group_id' => self::GROUP_BACKPAIN,
                'locale' => 'en',
                'title' => 'Herbal Oil for Back Pain: How People Use Massage Oil for a Stiff Back',
                'slug' => 'herbal-oil-for-back-pain',
                'excerpt' => 'How do people use herbal oil for a stiff, aching back? An honest guide to massage oil for tired muscles — how to use it, when to see a doctor, and COD price.',
                'reading_time_minutes' => 5,
                'content' => $this->backPainEn(),
            ],
            [
                'translation_group_id' => self::GROUP_BACKPAIN,
                'locale' => 'ur-Latn',
                'title' => 'Kamar Dard ke liye Tel: Maalish ka Tel Kaise Istemaal Karein',
                'slug' => 'kamar-dard-ke-liye-tel',
                'excerpt' => 'Kamar ki akdan aur thakan ke liye log maalish ka tel kaise istemaal karte hain? Mahfooz tareeqa, kab doctor se milna zaroori hai, aur qeemat — imandari se.',
                'reading_time_minutes' => 5,
                'content' => $this->backPainUr(),
            ],
        ];
    }

    private function massageEn(): string
    {
        return <<<'HTML'
<div class="article-answer-box">
  <p>The best massage oil in Pakistan is one with a short, honest ingredient list, a light non-greasy feel, and a price that fits everyday use. Lookman-e-Hayat Herbal Oil is a traditional til (sesame) based maalish oil — we publish what is in it and what we never add — from Rs 1,200 for 50ml, with Cash on Delivery nationwide.</p>
</div>

<h2>What makes a good massage oil?</h2>
<ul>
  <li><strong>A readable ingredient list</strong> — you should know exactly what you are rubbing into your skin.</li>
  <li><strong>The right slip</strong> — enough glide for a proper massage, without leaving skin greasy for hours.</li>
  <li><strong>A light, natural scent</strong> you can relax with, not a heavy perfume.</li>
  <li><strong>Skin comfort</strong> — a plant-based carrier oil that suits regular use once you have patch-tested it.</li>
</ul>
<p>A good massage oil is a cosmetic comfort product, not a medicine. It should not promise to cure anything.</p>

<h2>How to use a massage oil (maalish) at home</h2>
<ol>
  <li><strong>Patch test first</strong> — dab a little on your inner forearm and wait a few hours if it is your first time.</li>
  <li><strong>Warm a small amount</strong> between your palms so it spreads easily.</li>
  <li><strong>Massage in slow circles</strong> over the shoulders, neck, arms or legs for a few minutes each.</li>
  <li><strong>Relax for 20-30 minutes</strong>, then rinse off in a warm shower if you prefer.</li>
</ol>
<p>Use it on healthy, unbroken skin only. For any persistent ache or a health concern, see a doctor — an oil is for relaxation, not treatment.</p>

<h2>Lookman-e-Hayat Herbal Oil — sizes &amp; price</h2>
<table>
  <thead>
    <tr><th>Size</th><th>Price (COD)</th><th>Per ml</th><th>Best for</th></tr>
  </thead>
  <tbody>
    <tr><td><a href="/products/herbal-skin-oil-50ml">50ml</a></td><td>Rs 1,200</td><td>Rs 24 / ml</td><td>Trying it, or travel</td></tr>
    <tr><td><a href="/products/herbal-skin-oil-100ml">100ml</a></td><td>Rs 2,200</td><td>Rs 22 / ml</td><td>Regular maalish — better value per ml</td></tr>
  </tbody>
</table>
<p>New to it? Start with the <a href="/products/herbal-skin-oil-50ml">50ml bottle (Rs 1,200)</a>. If maalish is already part of your routine, the <a href="/products/herbal-skin-oil-100ml">100ml bottle (Rs 2,200)</a> lasts longer and costs less per ml. See everything on our <a href="/shop/oils">herbal oils shop</a>.</p>

<h2>Frequently asked questions</h2>
<h3>Is this a medicated or pain-relief oil?</h3>
<p>No. It is a traditional herbal cosmetic massage oil for relaxation and skin care. It is not a medicine and makes no treatment claims.</p>
<h3>Can I use it every day?</h3>
<p>Most people use it a few times a week. Patch test first and stop if your skin reacts.</p>
<h3>Which base oil is it?</h3>
<p>A light til (sesame) base — a carrier oil long used across South Asia for body and scalp massage — so it absorbs without feeling heavy.</p>
<h3>Do you deliver across Pakistan?</h3>
<p>Yes — Cash on Delivery nationwide via TCS, Leopards and M&amp;P.</p>

<h2>How to order (Cash on Delivery)</h2>
<p>Order from the <a href="/products/herbal-skin-oil-50ml">50ml</a> or <a href="/products/herbal-skin-oil-100ml">100ml</a> page, or <a href="https://wa.me/923012973886">message us on WhatsApp</a> and we will place it for you. Pay cash when it reaches your door.</p>

<p class="article-disclaimer"><em>Disclaimer: Lookman-e-Hayat Herbal Oil is a traditional cosmetic massage oil for external use, not a medicine. It is not intended to diagnose, treat, cure or prevent any condition. Patch-test before first use, keep it away from the eyes and broken skin, and see a qualified doctor for any persistent or serious health concern.</em></p>
HTML;
    }

    private function massageUr(): string
    {
        return <<<'HTML'
<div class="article-answer-box">
  <p>Jism ki maalish ka behtareen tel woh hai jiski ingredient list chhoti aur imandaar ho, jo halka ho (chipchipa na ho), aur jiski qeemat rozana istemaal ke liye theek baithe. Lookman-e-Hayat Herbal Oil ek riwayati til base wala maalish tel hai — hum batate hain ke andar kya hai aur kya hum kabhi nahi daalte — Rs 1,200 (50ml) se, poore Pakistan mein Cash on Delivery ke saath.</p>
</div>

<h2>Acha maalish tel kaise pehchanein?</h2>
<ul>
  <li><strong>Saaf ingredient list</strong> — aapko pata hona chahiye ke jild par kya laga rahe hain.</li>
  <li><strong>Sahi slip</strong> — maalish ke liye itni glide ke aaraam se phaile, magar ghanton chipchipa na chhode.</li>
  <li><strong>Halki qudrati khushbu</strong> jisme aap relax kar sakein, bhaari perfume nahi.</li>
  <li><strong>Jild ke liye naram</strong> — plant-based carrier oil jo patch-test ke baad rozana istemaal ke liye theek ho.</li>
</ul>
<p>Maalish tel ek cosmetic aaram wali cheez hai, dawa nahi. Yeh kisi bemari ke ilaaj ka daawa nahi karta.</p>

<h2>Ghar par maalish (istemaal ka tareeqa)</h2>
<ol>
  <li><strong>Pehle patch test</strong> — pehli baar hai to bazu ki androni jild par thoda laga kar kuch ghante intezaar karein.</li>
  <li><strong>Thoda tel</strong> hatheliyon mein garam karein taake aasani se phaile.</li>
  <li><strong>Halke gol haath</strong> se kaandhon, gardan, bazu ya tangon par chand minute maalish karein.</li>
  <li><strong>20-30 minute aaram</strong> karein, phir chahein to garam paani se naha lein.</li>
</ol>
<p>Sirf sehatmand, be-zakhm jild par istemaal karein. Kisi mustaqil dard ya sehat ke masle ke liye doctor se milein — tel aaram ke liye hai, ilaaj ke liye nahi.</p>

<h2>Lookman-e-Hayat Herbal Oil — size aur qeemat</h2>
<table>
  <thead>
    <tr><th>Size</th><th>Qeemat (COD)</th><th>Kis ke liye</th></tr>
  </thead>
  <tbody>
    <tr><td><a href="/products/herbal-skin-oil-50ml">50ml</a></td><td>Rs 1,200</td><td>Try karne ya safar ke liye</td></tr>
    <tr><td><a href="/products/herbal-skin-oil-100ml">100ml</a></td><td>Rs 2,200</td><td>Regular maalish — per ml behtar value</td></tr>
  </tbody>
</table>
<p>Naye hain to <a href="/products/herbal-skin-oil-50ml">50ml (Rs 1,200)</a> se shuru karein. Agar maalish aapke routine ka hissa hai to <a href="/products/herbal-skin-oil-100ml">100ml (Rs 2,200)</a> zyada chalta hai aur per ml sasta parta hai. Poori range <a href="/shop/oils">herbal oils shop</a> par dekhein.</p>

<h2>Aksar poochhe jane wale sawal</h2>
<h3>Kya yeh dawa ya pain-relief tel hai?</h3>
<p>Nahi. Yeh ek riwayati herbal cosmetic maalish tel hai — aaram aur jild ki dekhbhaal ke liye. Yeh dawa nahi aur koi ilaaj ka daawa nahi karta.</p>
<h3>Kya rozana istemaal kar sakte hain?</h3>
<p>Zyadatar log hafte mein chand baar istemaal karte hain. Pehle patch test karein aur jild reaction kare to rok dein.</p>
<h3>Iska base kya hai?</h3>
<p>Halka til (sesame) base — jo South Asia mein jism aur sar ki maalish ke liye muddat se istemaal hota hai — is liye bhaari mehsoos nahi hota.</p>
<h3>Kya poore Pakistan mein delivery hai?</h3>
<p>Ji haan — TCS, Leopards aur M&amp;P ke zariye poore mulk mein Cash on Delivery.</p>

<h2>Order kaise karein (Cash on Delivery)</h2>
<p><a href="/products/herbal-skin-oil-50ml">50ml</a> ya <a href="/products/herbal-skin-oil-100ml">100ml</a> ke page se order karein, ya hamein <a href="https://wa.me/923012973886">WhatsApp par message</a> karein — hum aapke liye order laga dete hain. Delivery par cash ada karein.</p>

<p class="article-disclaimer"><em>Disclaimer: Lookman-e-Hayat Herbal Oil ek riwayati cosmetic maalish tel hai — sirf beruni (external) istemaal ke liye, dawa nahi. Yeh kisi bemari ki tashkhees, ilaaj ya bachao ke liye nahi. Pehli baar patch-test karein, aankhon aur zakhmi jild se door rakhein, aur kisi mustaqil ya sanjeeda sehat ke masle ke liye maahir doctor se rujoo karein.</em></p>
HTML;
    }

    private function backPainEn(): string
    {
        return <<<'HTML'
<div class="article-answer-box">
  <p>Massage oil does not treat back pain — but many people gently massage a warm herbal oil into a tired, stiff back to relax the muscles and unwind. It is a comfort routine, not a medicine. Back pain that is severe, follows an injury, or comes with numbness or leg weakness needs a doctor, not an oil.</p>
</div>

<h2>Safety first: when to see a doctor</h2>
<p>Please do not rely on any oil for back pain. See a doctor promptly if your back pain:</p>
<ul>
  <li>is severe, or followed a fall, lift or accident;</li>
  <li>spreads down a leg, or comes with numbness, tingling or weakness;</li>
  <li>comes with fever, unexplained weight loss, or trouble controlling your bladder or bowels;</li>
  <li>does not settle after a couple of weeks, or keeps coming back.</li>
</ul>
<p>These can be signs of something that needs proper medical care. A massage oil is only for relaxation and comfort on healthy skin.</p>

<h2>How people use a massage oil for a stiff, tired back</h2>
<p>As a simple wind-down routine — think of it as maalish, not treatment:</p>
<ol>
  <li><strong>Patch test</strong> a little oil on your inner forearm first.</li>
  <li><strong>Warm a small amount</strong> between your palms.</li>
  <li>Have someone <strong>massage it in slow circles</strong> over the lower and upper back for a few minutes (or reach what you can yourself).</li>
  <li><strong>Rest for 20-30 minutes.</strong> Many people find gentle movement, a warm shower and good posture help a tired back far more than any product.</li>
</ol>
<p>Never massage over an injury, swelling, a rash or broken skin.</p>

<h2>Why a til-based oil for massage?</h2>
<p>Lookman-e-Hayat oil has a light <strong>til (sesame)</strong> base — a carrier oil long used across South Asia for body massage because it gives good glide and absorbs without feeling heavy. That makes it easy to work over the back without leaving skin greasy. It is a cosmetic massage oil; the comfort comes from the warm massage and relaxation, not from any medicinal effect.</p>

<h2>Sizes &amp; price</h2>
<table>
  <thead>
    <tr><th>Size</th><th>Price (COD)</th><th>Best for</th></tr>
  </thead>
  <tbody>
    <tr><td><a href="/products/herbal-skin-oil-50ml">50ml</a></td><td>Rs 1,200</td><td>Trying it</td></tr>
    <tr><td><a href="/products/herbal-skin-oil-100ml">100ml</a></td><td>Rs 2,200</td><td>Regular full-back maalish — better value</td></tr>
  </tbody>
</table>
<p>A full-back massage uses more oil, so many people prefer the <a href="/products/herbal-skin-oil-100ml">100ml bottle (Rs 2,200)</a>; the <a href="/products/herbal-skin-oil-50ml">50ml (Rs 1,200)</a> is a fine way to try it. For a general routine, see our <a href="/blog/best-massage-oil-in-pakistan">massage oil buyer's guide</a>.</p>

<h2>Frequently asked questions</h2>
<h3>Does this oil cure or treat back pain?</h3>
<p>No. It is a cosmetic massage oil, not a medicine, and it does not treat, cure or prevent back pain or any condition. For real or lasting pain, see a doctor.</p>
<h3>Can I use it after a gym or a long day?</h3>
<p>Many people enjoy a warm massage to relax tired muscles. Use it on healthy skin, never over an injury or swelling.</p>
<h3>Is it safe in pregnancy or with a medical condition?</h3>
<p>If you are pregnant, have a back injury, or any medical condition, ask your doctor before using massage or oils.</p>

<h2>How to order (Cash on Delivery)</h2>
<p>Order online with Cash on Delivery across Pakistan, or <a href="https://wa.me/923012973886">chat with us on WhatsApp</a> and pay at your door.</p>

<p class="article-disclaimer"><em>Disclaimer: Lookman-e-Hayat Herbal Oil is a traditional cosmetic massage oil for external use, not a medicine. It is not intended to diagnose, treat, cure or prevent back pain or any condition. Back pain can have serious causes — please consult a qualified doctor for diagnosis and treatment. Patch-test first and never apply to injured or broken skin.</em></p>
HTML;
    }

    private function backPainUr(): string
    {
        return <<<'HTML'
<div class="article-answer-box">
  <p>Maalish ka tel kamar dard ka ilaaj nahi karta — lekin bohat se log thaki, akdi hui kamar par garam herbal tel se halki maalish karke pathon ko relax karte hain. Yeh aaram ka tareeqa hai, dawa nahi. Agar kamar dard sanjeeda ho, kisi chot ke baad ho, ya sunn-pan/tangon mein kamzori ke saath ho, to tel nahi — doctor zaroori hai.</p>
</div>

<h2>Pehle ehtiyaat: doctor se kab milna zaroori hai</h2>
<p>Baraye meharbani kamar dard ke liye kisi bhi tel par bharosa na karein. Foran doctor se milein agar dard:</p>
<ul>
  <li>shadeed ho, ya kisi gir jane, wazan uthane ya haadse ke baad shuru hua ho;</li>
  <li>tang ki taraf phaile, ya sunn-pan, jhunjhunahat ya kamzori ke saath ho;</li>
  <li>bukhar, be-wajah wazan kam hone, ya peshaab/paakhana control mein mushkil ke saath ho;</li>
  <li>do hafte mein theek na ho, ya baar baar wapas aaye.</li>
</ul>
<p>Yeh kisi aisi cheez ki nishani ho sakti hai jise sahi tibbi ilaaj chahiye. Maalish tel sirf sehatmand jild par aaram ke liye hai.</p>

<h2>Log thaki kamar ke liye maalish tel kaise istemaal karte hain</h2>
<p>Ek saada aaram wala tareeqa — isay maalish samjhein, ilaaj nahi:</p>
<ol>
  <li>Pehle bazu par thoda tel laga kar <strong>patch test</strong> karein.</li>
  <li>Thoda tel hatheliyon mein <strong>garam</strong> karein.</li>
  <li>Kisi se <strong>halke gol haath</strong> se kamar ke oopri aur nichle hisse par chand minute maalish karwayein (ya jahan pohanch sakein khud karein).</li>
  <li><strong>20-30 minute aaram</strong> karein. Bohat se logon ko halki harkat, garam paani se nahana aur sahi posture kisi bhi cheez se zyada faida deti hai.</li>
</ol>
<p>Kisi chot, soojan, dane ya zakhmi jild par kabhi maalish na karein.</p>

<h2>Maalish ke liye til base wala tel kyun?</h2>
<p>Lookman-e-Hayat tel ka base halka <strong>til (sesame)</strong> hai — jo South Asia mein jism ki maalish ke liye muddaton se istemaal hota hai kyunke iski glide achi hoti hai aur bhaari mehsoos kiye baghair jazb ho jata hai. Isse kamar par aasani se maalish hoti hai aur jild chipchipi nahi rehti. Yeh cosmetic maalish tel hai; aaram garam maalish aur relaxation se aata hai, kisi dawaai asar se nahi.</p>

<h2>Size aur qeemat</h2>
<table>
  <thead>
    <tr><th>Size</th><th>Qeemat (COD)</th><th>Kis ke liye</th></tr>
  </thead>
  <tbody>
    <tr><td><a href="/products/herbal-skin-oil-50ml">50ml</a></td><td>Rs 1,200</td><td>Try karne ke liye</td></tr>
    <tr><td><a href="/products/herbal-skin-oil-100ml">100ml</a></td><td>Rs 2,200</td><td>Regular poori kamar ki maalish — behtar value</td></tr>
  </tbody>
</table>
<p>Poori kamar ki maalish mein tel zyada lagta hai, is liye bohat se log <a href="/products/herbal-skin-oil-100ml">100ml (Rs 2,200)</a> pasand karte hain; <a href="/products/herbal-skin-oil-50ml">50ml (Rs 1,200)</a> try karne ka acha tareeqa hai. Aam maalish routine ke liye hamari <a href="/ur-roman/blog/jism-ki-maalish-ka-behtareen-tel">maalish tel guide</a> dekhein.</p>

<h2>Aksar poochhe jane wale sawal</h2>
<h3>Kya yeh tel kamar dard ka ilaaj karta hai?</h3>
<p>Nahi. Yeh cosmetic maalish tel hai, dawa nahi, aur kamar dard ya kisi bemari ka ilaaj, bachao ya tashkhees nahi karta. Asli ya mustaqil dard ke liye doctor se milein.</p>
<h3>Kya gym ya lambe din ke baad istemaal kar sakte hain?</h3>
<p>Bohat se log thaki pathon ko relax karne ke liye garam maalish pasand karte hain. Sehatmand jild par istemaal karein, kisi chot ya soojan par kabhi nahi.</p>
<h3>Haml ya kisi bemari mein mahfooz hai?</h3>
<p>Agar aap hamila hain, kamar ki chot hai, ya koi bemari hai, to maalish ya tel istemaal karne se pehle apne doctor se poochhein.</p>

<h2>Order kaise karein (Cash on Delivery)</h2>
<p>Poore Pakistan mein Cash on Delivery par order karein, ya <a href="https://wa.me/923012973886">WhatsApp par baat karein</a> aur delivery par cash ada karein.</p>

<p class="article-disclaimer"><em>Disclaimer: Lookman-e-Hayat Herbal Oil ek riwayati cosmetic maalish tel hai — sirf beruni istemaal ke liye, dawa nahi. Yeh kamar dard ya kisi bemari ki tashkhees, ilaaj ya bachao ke liye nahi. Kamar dard ki sanjeeda wajohaat ho sakti hain — tashkhees aur ilaaj ke liye maahir doctor se rujoo karein. Pehle patch-test karein aur zakhmi ya kate hue jild par kabhi na lagayein.</em></p>
HTML;
    }
}
