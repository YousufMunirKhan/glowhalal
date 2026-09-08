<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * 30-day content plan, WEEK 4 — gap-fill across all three focus products.
 * See docs/30-day-content-plan-sep2026.md.
 *
 * Week 1 already took the Karachi/Hyderabad/Lahore local pair, so week 4 fills
 * the remaining high-intent entity gaps instead:
 *
 *   piyaz (onion) : UR "piyaz ka tel balon ke liye"      -> Jarain
 *   kalonji hair  : EN "kalonji oil for hair"            -> Jarain
 *   winter maalish: UR "sardi mein jism ki akran"        -> Sukoon
 *   pehchan       : UR "asli herbal tel ki pehchan"      -> all three
 *   COD trust     : EN "cash on delivery herbal oil pakistan" -> all three
 *
 * The last two are deliberate. The COD post targets the buying-anxiety query
 * that sits closest to a purchase, and the pehchan (authenticity) post is the
 * honesty wedge identified in docs/keyword-research-aug2026.md — the #1 buyer
 * anxiety in this market that almost no seller addresses straight.
 *
 * ⚠️ Glow Halal Massage Oil still has NO published ingredient list. Nothing here
 *    states or implies what is in it. Where it is recommended, the gap is
 *    disclosed exactly as the product page does.
 *
 * ⚠️ CLAIMS: cosmetic/traditional framing only. No cure claims, no halal
 *    certification claim, no fabricated reviews. `ganjapan ka ilaj` appears
 *    only as a myth-buster, never as a promise.
 *
 * DEPLOY-SAFE: firstOrCreate by slug. Run BlogDefaultsSeeder afterwards.
 */
class Month1Week4BlogSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->articles() as $article) {
            $post = BlogPost::firstOrCreate(
                ['slug' => $article['slug']],
                [
                    'title' => $article['title'],
                    'locale' => $article['locale'],
                    'excerpt' => $article['excerpt'],
                    'status' => 'published',
                    'published_at' => $article['published_at'],
                    'reading_time_minutes' => $article['reading_time_minutes'],
                    'content' => $article['content'],
                ],
            );

            foreach ($article['products'] as $slug => $position) {
                if ($product = Product::where('slug', $slug)->first()) {
                    $product->blogPosts()->syncWithoutDetaching([$post->id => ['position' => $position]]);
                }
            }
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function articles(): array
    {
        return [
            [
                'locale' => 'ur-Latn',
                'title' => 'Piyaz Ka Tel Balon Ke Liye: Sach Aur Afsana',
                'slug' => 'piyaz-ka-tel-balon-ke-liye',
                'excerpt' => 'Piyaz ka tel balon ke liye waqai kaam karta hai? Tehqeeq kya kehti hai, ghar par banane ka tarika, bu ka masla, aur kin logon ko ye nahi lagana chahiye.',
                'reading_time_minutes' => 6,
                'published_at' => now()->addDays(21),
                'products' => ['herbal-hair-oil' => 5],
                'content' => $this->piyazUr(),
            ],
            [
                'locale' => 'en',
                'title' => 'Kalonji Oil for Hair: What Evidence Shows',
                'slug' => 'kalonji-oil-for-hair',
                'excerpt' => 'Kalonji (black seed) oil for hair: what it is traditionally used for, what the research does and does not support, how to use it, and who should avoid it.',
                'reading_time_minutes' => 6,
                'published_at' => now()->addDays(22),
                'products' => ['herbal-hair-oil' => 6],
                'content' => $this->kalonjiEn(),
            ],
            [
                'locale' => 'ur-Latn',
                'title' => 'Sardi Mein Jism Ki Akran Aur Maalish',
                'slug' => 'sardi-mein-jism-ki-akran',
                'excerpt' => 'Sardi mein jism akar kyun jata hai, garam maalish ka sahi tarika, kaunsa tel sardi mein behtar hai, aur woh alamaat jin par tel nahi doctor chahiye.',
                'reading_time_minutes' => 6,
                'published_at' => now()->addDays(23),
                'products' => ['herbal-massage-oil' => 7],
                'content' => $this->winterUr(),
            ],
            [
                'locale' => 'ur-Latn',
                'title' => 'Asli Herbal Tel Ki Pehchan: 6 Ghar Ke Test',
                'slug' => 'asli-herbal-tel-ki-pehchan',
                'excerpt' => 'Asli herbal tel ki pehchan kaise karein — 6 ghar ke test, label par kya dekhna hai, milawat ki nishaniyan, aur kab dukan chhor deni chahiye.',
                'reading_time_minutes' => 7,
                'published_at' => now()->addDays(24),
                'products' => ['herbal-skin-oil-50ml' => 12, 'herbal-hair-oil' => 7],
                'content' => $this->pehchanUr(),
            ],
            [
                'locale' => 'en',
                'title' => 'Cash on Delivery Herbal Oil in Pakistan',
                'slug' => 'cash-on-delivery-herbal-oil-pakistan',
                'excerpt' => 'Buying herbal oil with Cash on Delivery in Pakistan: how COD works, what it protects you from, delivery times by city, returns, and what to check on arrival.',
                'reading_time_minutes' => 5,
                'published_at' => now()->addDays(25),
                'products' => ['herbal-skin-oil-50ml' => 13, 'herbal-massage-oil' => 8],
                'content' => $this->codEn(),
            ],
        ];
    }

    // =====================================================================
    private function piyazUr(): string
    {
        return <<<'HTML'
<p class="answer-box"><strong>Piyaz ke tel par kuch chhoti tehqeeqat mojood hain — khaas kar alopecia areata (chakkiyon wale baal girne) mein — magar woh tehqeeqat chhoti hain aur unka daira mehdood hai.</strong> Ye aam baal girne ka ilaj nahi hai, aur bu asli masla hai. Ghar par banayen, hafte mein 1–2 baar, 30 minute.</p>

<h2>Tehqeeq waqai kya kehti hai</h2>
<p>Piyaz ke ras par sab se zyada zikr hone wali tehqeeq <strong>alopecia areata</strong> ke mareezon par thi — woh khaas surat jis mein baal chakkiyon mein girte hain — aur us mein kuch behtari dekhi gayi. Magar:</p>
<ul>
  <li>Us mein <strong>bohot kam log</strong> shamil thay.</li>
  <li>Woh <strong>alopecia areata</strong> ke liye thi, aam baal girne ke liye nahi.</li>
  <li>Us mein <strong>piyaz ka ras</strong> istemal hua tha, "piyaz wala tel" nahi.</li>
</ul>
<p>To agar koi bechne wala kahe ke "tehqeeq se sabit hai ke piyaz ka tel baal ugata hai" — woh baat ko bohot khainch raha hai. <strong>Mardana ganjapan par is ka koi saboot nahi.</strong></p>

<h2>Ghar par banane ka tarika</h2>
<ol>
  <li><strong>1 darmiyani piyaz</strong> peis kar ras nikal lein (kapre se chhaan lein).</li>
  <li><strong>Barabar miqdaar mein nariyal ya badam ka tel</strong> milayein.</li>
  <li>Sar ki jild par lagayein, <strong>5 minute halki champi</strong>.</li>
  <li><strong>30 minute chhorein</strong> — is se zyada nahi, jild jal sakti hai.</li>
  <li><strong>Dho lein.</strong> Bu ke liye aakhir mein thora sa shampoo dobara, ya pani mein nimbu ka ras.</li>
</ol>
<p><strong>Roz banayein.</strong> Piyaz ka ras jaldi kharab hota hai — bana kar rakhna theek nahi.</p>

<h2>Bu — sab se bara aur sab se kam bataya jane wala masla</h2>
<p>Piyaz ki bu baalon mein <strong>ek se do din</strong> reh sakti hai, aur ye wahi wajah hai jis se zyada tar log do baar ke baad chhor dete hain. Jo cheez aap istemal hi nahi karenge, us ka koi faida nahi — <strong>ye nakami nahi, ye sirf haqeeqat hai.</strong> Bu bardasht na ho to kisi aur tel par chale jayein.</p>

<h2>Ye kis ke liye NAHI hai</h2>
<ul>
  <li><strong>Sensitive jild.</strong> Piyaz ka ras jild jala sakta hai — hamesha patch test.</li>
  <li><strong>Sar par dane, zakham ya khujli</strong> ho to bilkul nahi.</li>
  <li><strong>Chakkiyon mein baal girna</strong> — ye doctor ka masla hai, ghar ke totke ka nahi. Alopecia areata ka ilaj mojood hai; waqt zaya na karein.</li>
  <li><strong>Piyaz se allergy</strong> — kam aam hai, magar hoti hai.</li>
</ul>

<h2>Aasan raasta</h2>
<p>Agar bu ke baghair rozana champi ka tel chahiye to <a href="/products/herbal-hair-oil"><strong>Glow Halal Hair Oil</strong></a> — nariyal ka tel, roghan-e-badam, kalonji ka tel, vitamin D. Is mein piyaz nahi hai. Rs 1,200 (100 ml), COD.</p>
<p><strong>Is mein badam (tree nut) aur nariyal hai</strong> — allergy ho to na lein.</p>
<p>Aur ye hum saaf kehte hain: <strong>koi bhi tel — hamara bhi — ganje hissay par baal nahi ugata.</strong> Tootna kam karta hai. Yehi iska asal size hai.</p>

<p><em>Ye aam maloomat hain, tibbi mashwara nahi. Chakkiyon mein baal girne par doctor se rujoo karein.</em></p>
HTML;
    }

    // =====================================================================
    private function kalonjiEn(): string
    {
        return <<<'HTML'
<p class="answer-box"><strong>Kalonji (black seed, <em>Nigella sativa</em>) has a long traditional use for scalp care in South Asia and the Middle East, and a small amount of early clinical work — but nothing that supports the regrowth claims commonly made for it.</strong> It is a good scalp-conditioning ingredient in a blend. It is not a hair-loss treatment.</p>

<h2>What kalonji oil is</h2>
<p>Kalonji is the small black seed of <em>Nigella sativa</em>. Pressed for oil, it is dark, aromatic and fairly strong-smelling — it is what makes a hair oil smell like a desi hair oil rather than a supermarket one. In this region it has been used on the scalp for generations.</p>

<h2>What the evidence supports, and what it does not</h2>
<table>
  <thead><tr><th>Claim</th><th>Honest status</th></tr></thead>
  <tbody>
    <tr><td>Conditions a dry, flaky scalp</td><td>Plausible; long traditional use, limited formal study</td></tr>
    <tr><td>Reduces itchiness</td><td>Plausible; same basis</td></tr>
    <tr><td>Helps hair look thicker by reducing breakage</td><td>Reasonable — true of most conditioning oils</td></tr>
    <tr><td>Regrows hair on bald areas</td><td><strong>No. Not supported.</strong></td></tr>
    <tr><td>Reverses male-pattern baldness</td><td><strong>No.</strong></td></tr>
    <tr><td>Treats alopecia</td><td><strong>No — see a doctor.</strong></td></tr>
  </tbody>
</table>
<p>There is a real research literature on <em>Nigella sativa</em>, mostly on taking it internally for other purposes, and it is often quoted to imply things about hair that it does not actually show. We would rather point that out than borrow it.</p>

<h2>How to use it</h2>
<ol>
  <li><strong>Dilute it.</strong> Kalonji is strong; most people use it as part of a blend rather than neat. Roughly 1 part kalonji to 4 parts coconut or almond oil.</li>
  <li><strong>Patch-test</strong> on the inner forearm first.</li>
  <li><strong>Scalp, fingertips, five minutes</strong>, slow circles — not nails, and do not rub the lengths together.</li>
  <li><strong>Leave 30–60 minutes</strong>, then shampoo on dry hair first, then water.</li>
  <li><strong>Once or twice a week.</strong></li>
</ol>

<h2>Who should avoid it</h2>
<ul>
  <li><strong>Pregnant women</strong> — advice on <em>Nigella sativa</em> is mixed. Ask your doctor.</li>
  <li><strong>Anyone with a known seed allergy</strong>, or who reacts to strong botanicals.</li>
  <li><strong>Broken skin, active scalp acne or open sores</strong> — nothing goes on those.</li>
  <li><strong>Anyone hoping it will regrow hair.</strong> Read the table again — this is the honest part, and it is the part that decides whether you will be happy with what you bought.</li>
</ul>

<h2>The smell, honestly</h2>
<p>Kalonji has a distinct, slightly peppery smell that lingers. Some people love it; some find it too much. Diluting helps, and washing with a mild shampoo removes most of it. If you dislike strong scents, a coconut- or almond-led blend with only a little kalonji is the better choice.</p>

<h2>In our blend</h2>
<p><a href="/products/herbal-hair-oil"><strong>Glow Halal Hair Oil</strong></a> uses kalonji alongside coconut oil, sweet almond oil and vitamin D — four ingredients, all named, no mineral oil and no undisclosed fragrance. Rs 1,200 for 100 ml, Rs 2,000 for 200 ml, Cash on Delivery across Pakistan.</p>
<p><strong>It contains almond, a tree nut, and coconut.</strong> Not suitable if you have those allergies.</p>
<p>How it compares to the alternatives: <a href="/blog/coconut-almond-kalonji-oil-for-hair">coconut vs almond vs kalonji</a>.</p>

<h2>FAQ</h2>
<h3>Can I use pure kalonji oil on my scalp?</h3>
<p>You can, but most people find it strong. Diluting makes it easier to spread and easier to wash out.</p>
<h3>Is kalonji oil the same as black cumin oil?</h3>
<p>They are usually sold under both names, yes — <em>Nigella sativa</em>. It is not the same as cumin (zeera).</p>
<h3>How long before I notice anything?</h3>
<p>Scalp comfort within a few weeks, if it suits you. Anything promised in days is marketing.</p>

<p><em>General information, not medical advice. Patchy or rapid hair loss needs a doctor.</em></p>
HTML;
    }

    // =====================================================================
    private function winterUr(): string
    {
        return <<<'HTML'
<p class="answer-box"><strong>Sardi mein pathe sukar jaate hain aur khoon ka daura jild ke qareeb kam ho jata hai — isi liye kandhe, gardan aur ghutne zyada akray hue lagte hain.</strong> Garam pani se nahane ke baad 5–10 minute maalish sab se zyada faida deti hai. Glow Halal Massage Oil Rs 1,300 (100 ml), COD.</p>

<h2>Sardi mein akran barhti kyun hai</h2>
<ul>
  <li><strong>Pathe sukarte hain</strong> garmi bachane ke liye — natija akran.</li>
  <li><strong>Harkat kam</strong> ho jati hai; log kam chalte hain aur zyada baithte hain.</li>
  <li><strong>Pani kam pite hain</strong>, kyunke pyaas kam lagti hai.</li>
  <li><strong>Purani chot ya jodon ka masla</strong> sardi mein zyada mehsoos hota hai.</li>
</ul>
<p>Ye aam sardi ki akran hai. <strong>Ye kisi bimari ka ilaj karne ki baat nahi</strong> — aur agar dard sardi ke saath nahi, balki musalsal hai, to woh alag masla hai (neeche dekhein).</p>

<h2>Sardi mein maalish ka sahi tarika</h2>
<ol>
  <li><strong>Pehle garmi, phir tel.</strong> Garam pani se nahane ke baad, ya 5 minute garam kapra rakh kar. Thandi jild par maalish ka faida aadha reh jata hai.</li>
  <li><strong>Tel bhi garam karein</strong> — haathon mein ragar kar, ya katori garam pani mein rakh kar. Seedha aag par nahi.</li>
  <li><strong>5–10 minute, golayi mein</strong>, halka dabao, dil ki taraf.</li>
  <li><strong>Baad mein dhak lein.</strong> Maalish ke foran baad thandi hawa lagne se faida zaya ho jata hai. Soti kapra pehen lein.</li>
  <li><strong>Raat ko sonay se pehle</strong> sab se munasib waqt hai.</li>
</ol>

<h2>Sardi mein kaunsa tel</h2>
<table>
  <thead><tr><th></th><th>Sardi mein</th></tr></thead>
  <tbody>
    <tr><td>Nariyal ka tel</td><td>24°C se neeche <strong>jam jata hai</strong> — maalish ke liye mushkil</td></tr>
    <tr><td>Sarson ka tel</td><td>Riwayati sardi ka intekhab, bhaari aur bu tez</td></tr>
    <tr><td>Halka blend</td><td>Behta rehta hai, 10 minute maalish ke liye behtar</td></tr>
  </tbody>
</table>
<p>Sardi mein woh tel behtar hai jo <strong>behta rahe</strong> — kyunke maalish ka asal faida <em>waqt</em> se aata hai, aur jama hua tel aap ko do minute mein rukwa deta hai.</p>

<h2>Maalish ke ilawa jo waqai kaam karta hai</h2>
<ul>
  <li><strong>Rozana 15–20 minute chalna</strong> — akran ke liye kisi bhi tel se zyada asar rakhta hai.</li>
  <li><strong>Pani pina</strong>, chahe pyaas na lage.</li>
  <li><strong>Subah 5 minute stretching</strong>, bistar se uthte hi.</li>
  <li><strong>Garam kapre</strong> — khaas kar gardan aur ghutne dhak kar rakhna.</li>
</ul>

<h2>Kab tel nahi, doctor chahiye</h2>
<ul>
  <li>Jor <strong>sooj</strong> jaye, garam ho, ya laal ho.</li>
  <li>Subah ki akran <strong>ek ghante se zyada</strong> rehti ho.</li>
  <li>Dard <strong>raat ko jaga</strong> de.</li>
  <li>Bazu ya tang mein <strong>jhunjhunahat ya kamzori</strong>.</li>
  <li>Saath mein <strong>bukhar</strong> ya wazan kam hona.</li>
</ul>
<p>In mein se koi bhi baat ho to maalish rok dein. <strong>Ye alamaat tel ki nahi, doctor ki hain.</strong></p>

<h2>Hamara tel</h2>
<p><a href="/products/herbal-massage-oil"><strong>Glow Halal Massage Oil</strong></a> — Rs 1,300 (100 ml), Rs 2,200 (200 ml), poore Pakistan COD, delivery Rs 300.</p>
<p><strong>Saaf baat:</strong> is tel ki ajza ki fehrist hum abhi manufacturer se confirm kar rahe hain aur woh safhe par nahi hai. Hum andaza laga kar list nahi likhenge. Agar aap ko allergy hai ya jild sensitive hai to fehrist aane tak intezar karein, ya <a href="/contact">WhatsApp par poochein</a>.</p>
<p>Maalish ka poora tarika <a href="/blog/maalish-ka-tel-konsa-acha-hai">is guide</a> mein hai.</p>

<p><em>Ye aam maloomat hain, tibbi mashwara nahi.</em></p>
HTML;
    }

    // =====================================================================
    private function pehchanUr(): string
    {
        return <<<'HTML'
<p class="answer-box"><strong>Asli herbal tel pehchanne ka sab se kaam ka tareeqa koi ghar ka test nahi — <em>label</em> hai.</strong> Ajza ki poori fehrist, banane wale ka naam aur pata, batch number aur expiry, aur saabit seal. Ye chaar cheezein na hon to baqi koi test us kami ko poora nahi karta.</p>

<h2>Pehle label — chaar cheezein</h2>
<ol>
  <li><strong>Ajza ki poori fehrist.</strong> "Qudrati jari bootiyan", "khaas nuskha", "ayurvedic formula" — ye fehrist nahi hain. Agar aap ko allergy hai to ye zindagi aur maut ka farq ho sakta hai.</li>
  <li><strong>Banane wale ka naam aur pata.</strong> Jis bottle par ye nahi, uski koi zimmedari nahi.</li>
  <li><strong>Batch number aur expiry</strong> — chhapa hua, parha jane wala. Sticker ke neeche purani tareekh na chhupi ho.</li>
  <li><strong>Seal saabit.</strong> Khuli ya dobara bhari bottle aap ko kuch nahi batati.</li>
</ol>

<h2>6 ghar ke test</h2>

<h3>1. Kaghaz wala test</h3>
<p>Ek qatra saade kaghaz par dalein aur 15 minute chhor dein. Asli tel ek <strong>chikna, barabar dhabba</strong> chhorta hai. Agar bohot patla halqa bane aur beech mein pani jaisa nishan ho, to us mein pani ya sasta patla tel mila ho sakta hai.</p>

<h3>2. Freezer test</h3>
<p>Thori si bottle 2 ghante freezer mein rakhein. Nariyal wale tel jam jaate hain — <strong>ye normal hai</strong>. Magar agar tel <strong>do parton mein alag</strong> ho jaye, to ye milawat ki nishani hai.</p>

<h3>3. Ungliyon ka test</h3>
<p>Ek qatra ungliyon mein ragrein. Achi tarah <strong>phailna</strong> chahiye. Rait jaisa mehsoos ho ya foran chipak jaye to ghor karein.</p>

<h3>4. Bu ka test</h3>
<p>Asli herbal tel ki bu <strong>halki aur murakkab</strong> hoti hai. Tez, chubhti hui khushbu ka aam matlab hai <strong>daali hui khushbu</strong> — jo taqat ki nishani nahi, aur jild ki hassasiyat ki aam wajah hai.</p>

<h3>5. Rang ka test — aur is ka ulta</h3>
<p>Log samajhte hain gehra rang matlab taqatwar tel. <strong>Ye ghalat hai.</strong> Rang daala bhi ja sakta hai. Agar dukandaar rang ko saboot ke tor par pesh kare, to woh aap ko rang bech raha hai.</p>

<h3>6. Qeemat ka test</h3>
<p>Bazaar ki range se bohot neeche wali bottle par khush hone ke bajaye shak karein. Tel mein sasta carrier oil milana bohot aasan hai aur khareedne wale ko pata nahi chalta. <strong>Sab se sasti bottle aksar sab se zyada milawat wali hoti hai.</strong></p>

<h2>Teen baatein jin par dukan chhor deni chahiye</h2>
<ul>
  <li>"Ye tel <strong>ganjapan theek</strong> kar deta hai."</li>
  <li>"Ye <strong>jodon ka dard jar se khatam</strong> kar deta hai."</li>
  <li>"Ye <strong>halal certified</strong> hai" — bina kisi certificate number ya idaray ke naam ke.</li>
</ul>
<p>Teenon soorton mein aap ko tel ke baare mein nahi, <strong>dukan ke baare mein</strong> maloomat mil gayi hai.</p>

<h2>Imaandari ka taqaza — apne aap par</h2>
<p>Yehi test hum par bhi lagte hain, to jawab bhi de dete hain:</p>
<ul>
  <li><a href="/products/herbal-skin-oil-50ml"><strong>Lookman-e-Hayat</strong></a> — poori ajza ki fehrist safhe par. Hum <strong>bechne wale hain, banane wale nahi</strong>; banane wale ka naam bottle par hai.</li>
  <li><a href="/products/herbal-hair-oil"><strong>Glow Halal Hair Oil</strong></a> — chaar ajza, sab likhe huay. Is mein <strong>badam (giri) aur nariyal</strong> hai.</li>
  <li><a href="/products/herbal-massage-oil"><strong>Glow Halal Massage Oil</strong></a> — <strong>ajza ki fehrist abhi confirm nahi hui</strong>, aur hum ye likh rahe hain, andaza nahi laga rahe. Upar wala pehla asool abhi is par poora nahi utarta.</li>
</ul>
<p>Aur: hamare paas <strong>koi third-party halal certification nahi hai aur hum aisa dawa nahi karte.</strong></p>

<p><em>Ye aam maloomat hain, tibbi mashwara nahi. Ghar ke test qatai saboot nahi hote — label sab se qaabil-e-bharosa cheez hai.</em></p>
HTML;
    }

    // =====================================================================
    private function codEn(): string
    {
        return <<<'HTML'
<p class="answer-box"><strong>Cash on Delivery means you pay the courier when the parcel reaches your door — nothing upfront, no card details, no online payment.</strong> We ship COD across Pakistan: 2–4 working days to Karachi, Hyderabad, Lahore, Islamabad and Rawalpindi, 4–7 days elsewhere. Delivery is Rs 300 flat.</p>

<h2>Why COD still matters in Pakistan</h2>
<p>Most people buying herbal products online here have the same two worries: <em>will anything arrive at all</em>, and <em>will it be what was described</em>. COD removes the first one entirely. You are not sending money to a website — you are handing cash to a courier holding a box you can see.</p>
<p>It does not solve the second worry, which is why the ingredient list on the product page matters more than any payment method.</p>

<h2>How it works, step by step</h2>
<ol>
  <li><strong>Order on the site</strong>, or message us on WhatsApp. No payment at this stage.</li>
  <li><strong>We confirm</strong> the order and the address.</li>
  <li><strong>The courier delivers</strong> and you pay them the total — product price plus Rs 300 delivery.</li>
  <li><strong>Check the parcel</strong> before the courier leaves (see below).</li>
</ol>

<h2>Delivery times</h2>
<table>
  <thead><tr><th>City</th><th>Working days</th></tr></thead>
  <tbody>
    <tr><td>Karachi</td><td>2–4</td></tr>
    <tr><td>Hyderabad</td><td>2–4</td></tr>
    <tr><td>Lahore</td><td>2–4</td></tr>
    <tr><td>Islamabad / Rawalpindi</td><td>2–4</td></tr>
    <tr><td>Rest of Pakistan</td><td>4–7</td></tr>
  </tbody>
</table>
<p>These are courier estimates, not guarantees. Working days exclude Sundays and public holidays, and final delivery is in the courier's hands.</p>

<h2>What to check when it arrives</h2>
<ol>
  <li><strong>Is the bottle sealed?</strong> A broken seal is a refuse-it.</li>
  <li><strong>Batch number and expiry</strong> printed and legible.</li>
  <li><strong>Ingredient list</strong> present on the label.</li>
  <li><strong>Any leakage</strong> in the box.</li>
</ol>
<p>These take thirty seconds and are the whole benefit of paying on arrival. If something is wrong, that is the moment to say so.</p>

<h2>Returns</h2>
<p>We take returns within <strong>7 days</strong> for unopened or damaged items — the full terms are on <a href="/shipping-returns">shipping &amp; returns</a>. Opened cosmetic products cannot be resold, so those are handled case by case; message us and we will deal with it honestly.</p>

<h2>Cost</h2>
<table>
  <thead><tr><th>Product</th><th>Price</th></tr></thead>
  <tbody>
    <tr><td><a href="/products/herbal-skin-oil-50ml">Lookman-e-Hayat 50 ml</a></td><td>Rs 1,200</td></tr>
    <tr><td><a href="/products/herbal-skin-oil-100ml">Lookman-e-Hayat 100 ml</a></td><td>Rs 2,200</td></tr>
    <tr><td><a href="/products/herbal-hair-oil">Glow Halal Hair Oil 100 ml</a></td><td>Rs 1,200</td></tr>
    <tr><td><a href="/products/herbal-massage-oil">Glow Halal Massage Oil 100 ml</a></td><td>Rs 1,300</td></tr>
    <tr><td>Delivery, anywhere in Pakistan</td><td>Rs 300 flat</td></tr>
  </tbody>
</table>
<p>Delivery is free over Rs 5,000, which a single bottle does not reach — so assume Rs 300 unless you are ordering several.</p>

<h2>FAQ</h2>
<h3>Do I pay anything before delivery?</h3>
<p>No. Nothing upfront, and we never ask for card details.</p>
<h3>Can I open the parcel before paying?</h3>
<p>Courier policies vary and it is the courier's call, not ours. You can always inspect the outside of the box, and refuse it if it is damaged.</p>
<h3>What if nobody is home?</h3>
<p>The courier normally re-attempts and will call the number on the order. Give a number that will be answered.</p>
<h3>Can I order on WhatsApp instead?</h3>
<p>Yes — <a href="/contact">message us here</a>. Same prices, same COD.</p>

<p><em>Delivery estimates and charges are current as of September 2026 and may change. See <a href="/shipping-returns">shipping &amp; returns</a> for the full terms.</em></p>
HTML;
    }
}
