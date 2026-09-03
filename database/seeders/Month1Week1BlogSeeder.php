<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * 30-day content plan, WEEK 1 (see docs/30-day-content-plan-sep2026.md).
 *
 * Five posts, two jobs:
 *
 *   A. Give Roghan-e-Sukoon content of its own. It launched 3 Sep with zero
 *      posts pointing at it beyond the four GrowthBlogSeeder pieces.
 *   B. Open the LOCAL door — Karachi, Hyderabad, Lahore. The owner wants local
 *      visibility, and the honest way to get it is one genuinely useful
 *      "where do people actually buy herbal oil" guide per language, NOT three
 *      thin city doorway pages (Google treats those as duplicates, and the
 *      strategy doc rules them out explicitly).
 *
 * DECONFLICTION: every row has a distinct primary keyword, and no Roman-Urdu
 * twin reuses its English sibling's phrase translated — Google reads Roman Urdu
 * as English, so twins on the same phrase would cannibalise each other.
 *
 *   maalish choice : UR "maalish ka tel konsa acha hai"      (no EN twin)
 *   roghan-e-surkh : EN "roghan e surkh"                     (no UR twin)
 *   neck/shoulder  : UR "gardan ke dard ka tel"              (no EN twin)
 *   local buying   : EN "herbal oil shop in karachi"
 *                  / UR "herbal tel kahan se milta hai"      (twins, distinct)
 *
 * ⛔ `jodon ke dard ka tel` belongs to the Lookman cluster. Nothing here targets
 *    it, and nothing here should be edited to.
 *
 * ⚠️ ROGHAN-E-SUKOON HAS NO PUBLISHED INGREDIENT LIST. Its label is still
 *    unconfirmed, so NO post here states or implies what is in it. Where the
 *    product is mentioned, it is mentioned as a massage oil with the gap
 *    disclosed — same as the product page. Do not "helpfully" add ingredients.
 *
 * HEALTH-SAFE: traditional cosmetic massage framing only. No cure, treatment or
 * disease claims; no "halal certified"; no fabricated reviews or statistics.
 * Pain posts lead with "not a medicine, see a doctor".
 *
 * PRICING: live prices — Lookman 50ml Rs 1,200 / 100ml Rs 2,200; Roghan-e-Sukoon
 * 100ml Rs 1,300 / 200ml Rs 2,200. Delivery Rs 300 flat, free over Rs 5,000
 * (a single bottle does not reach it, so no free-delivery claim is made).
 *
 * DEPLOY-SAFE: firstOrCreate by slug — re-running never clobbers admin edits.
 * Run BlogDefaultsSeeder afterwards to backfill category + author.
 */
class Month1Week1BlogSeeder extends Seeder
{
    private const GROUP_LOCAL = 'c3e4a5b6-7d82-4f93-a014-2b3c4d5e6f70';

    public function run(): void
    {
        foreach ($this->articles() as $article) {
            $post = BlogPost::firstOrCreate(
                ['slug' => $article['slug']],
                [
                    'title' => $article['title'],
                    'locale' => $article['locale'],
                    'translation_group_id' => $article['translation_group_id'] ?? null,
                    'excerpt' => $article['excerpt'],
                    'status' => 'published',
                    'published_at' => $article['published_at'],
                    'reading_time_minutes' => $article['reading_time_minutes'],
                    'content' => $article['content'],
                ],
            );

            // Attach to its product so the PDP guides band surfaces it. The pivot
            // position is what decides which three render — see ProductController.
            if ($slug = $article['product'] ?? null) {
                if ($product = Product::where('slug', $slug)->first()) {
                    $product->blogPosts()->syncWithoutDetaching(
                        [$post->id => ['position' => $article['product_position'] ?? 20]]
                    );
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
                'title' => 'Maalish Ka Tel Konsa Acha Hai? Ghar Par Sahi Tarika',
                'slug' => 'maalish-ka-tel-konsa-acha-hai',
                'excerpt' => 'Maalish ka tel konsa acha hai? Tel chunne ke 5 asool, ghar par maalish ka sahi tarika, kis ke liye ye theek nahi, aur asli qeemat Rs 1,300 se — COD ke saath.',
                'reading_time_minutes' => 6,
                'published_at' => now(),
                'product' => 'roghan-e-sukoon-massage-oil',
                'product_position' => 2,
                'content' => $this->maalishChoiceUr(),
            ],
            [
                'locale' => 'en',
                'title' => 'Roghan-e-Surkh: What It Is and How It Is Used',
                'slug' => 'roghan-e-surkh-what-it-is',
                'excerpt' => 'What is Roghan-e-Surkh? The traditional Unani massage oil explained — what the name means, how families use it, and how to choose one honestly.',
                'reading_time_minutes' => 6,
                'published_at' => now()->addDay(),
                'product' => 'roghan-e-sukoon-massage-oil',
                'product_position' => 3,
                'content' => $this->roghanESurkhEn(),
            ],
            [
                'locale' => 'ur-Latn',
                'title' => 'Gardan Aur Kandhe Ke Dard Ke Liye Maalish',
                'slug' => 'gardan-ke-dard-ka-tel',
                'excerpt' => 'Gardan aur kandhe ki akran ke liye maalish ka tarika: kitni der, kitna tel, kin galtiyon se bachna hai, aur kab tel nahi balke doctor chahiye.',
                'reading_time_minutes' => 6,
                'published_at' => now()->addDays(2),
                'product' => 'roghan-e-sukoon-massage-oil',
                'product_position' => 4,
                'content' => $this->neckShoulderUr(),
            ],
            [
                'translation_group_id' => self::GROUP_LOCAL,
                'locale' => 'en',
                'title' => 'Buy Herbal Oil: Karachi, Hyderabad & Lahore',
                'slug' => 'herbal-oil-shop-karachi-hyderabad-lahore',
                'excerpt' => 'Where to buy herbal oil in Karachi, Hyderabad and Lahore: the bazaars people actually go to, what a fair price looks like, how to spot a diluted bottle.',
                'reading_time_minutes' => 8,
                'published_at' => now()->addDays(3),
                'product' => 'roghan-e-sukoon-massage-oil',
                'product_position' => 5,
                'content' => $this->localGuideEn(),
            ],
            [
                'translation_group_id' => self::GROUP_LOCAL,
                'locale' => 'ur-Latn',
                'title' => 'Herbal Tel Kahan Se Milta Hai? Sheher Ke Hisab Se',
                'slug' => 'herbal-tel-kahan-se-milta-hai',
                'excerpt' => 'Herbal tel kahan se milta hai — Karachi, Hyderabad aur Lahore ke bazaar, munasib qeemat kya hoti hai, milawat kaise pehchanein, aur ghar bethe COD ka option.',
                'reading_time_minutes' => 8,
                'published_at' => now()->addDays(4),
                'product' => 'roghan-e-sukoon-massage-oil',
                'product_position' => 6,
                'content' => $this->localGuideUr(),
            ],
        ];
    }

    // =====================================================================
    // 1. UR — maalish ka tel konsa acha hai
    // =====================================================================
    private function maalishChoiceUr(): string
    {
        return <<<'HTML'
<p class="answer-box"><strong>Maalish ka acha tel woh hai jo aap ki jild par bhaari na lage, haathon ko itni slip de ke aap 10 minute tak aaram se maalish kar sakein, aur jis ke ajza aap parh sakein.</strong> Khushbu, rang ya "jadu asar" ke daawe maayne nahi rakhte. Glow Halal ka maalish ka tel Rs 1,300 (100 ml) se shuru hota hai, poore Pakistan mein Cash on Delivery.</p>

<h2>Tel chunne ke 5 asool</h2>

<h3>1. Ajza ki fehrist mojood honi chahiye</h3>
<p>Sab se pehla sawal yehi hai: <strong>bottle par likha hai ke andar kya hai?</strong> Agar sirf "qudrati jari bootiyan" likha ho aur naam koi na ho, to aap ko pata hi nahi ke aap jild par kya laga rahe hain. Jis ko allergy ho, hamal ho, ya jild sensitive ho — us ke liye ye sirf pasand ka masla nahi, mehfooz rehne ka masla hai.</p>

<h3>2. Slip — yehi asal kaam hai</h3>
<p>Maalish ka tel dawa nahi, <strong>zariya</strong> hai. Uska asal kaam ye hai ke aap ke haath jild par phislte rahein taake aap 10 minute maalish kar sakein, do minute mein ruk na jayein. Jo tel foran jazb ho kar khushk ho jaye woh maalish ke liye theek nahi — chahe jild ke liye kitna hi acha ho.</p>

<h3>3. Bhaarapan aur chipchipahat</h3>
<p>Gaarha tel kapron par lagta hai aur bistar kharab karta hai — natija ye ke aap dobara istemal hi nahi karte. Halka tel raat ko lagaya ja sakta hai. Sab se acha tel woh hai jo aap <strong>waqai rozana lagayen</strong>, woh nahi jo almari mein para rahe.</p>

<h3>4. Khushbu ka dhoka</h3>
<p>Tez khushbu ka matlab asar nahi hota. Aksar iska matlab sirf ye hota hai ke us mein khushbu daali gayi hai — aur bina batayi hui khushbu jild ki hassasiyat ki aam wajah hai. Halki, qudrati mehak wala tel kam-tar nahi hota.</p>

<h3>5. Qeemat fi ml dekhein, bottle ki nahi</h3>
<p>Bari bottle mehngi lagti hai magar aksar sasti parti hai. Hisaab aasan hai: qeemat ko ml par taqseem karein.</p>

<table>
  <thead><tr><th>Size</th><th>Qeemat</th><th>Fi ml</th></tr></thead>
  <tbody>
    <tr><td>100 ml</td><td>Rs 1,300</td><td>Rs 13.0</td></tr>
    <tr><td>200 ml</td><td>Rs 2,200</td><td><strong>Rs 11.0</strong></td></tr>
  </tbody>
</table>

<h2>Ghar par maalish ka sahi tarika</h2>
<ol>
  <li><strong>Pehle patch test.</strong> Bazu ke andarooni hissay par thora sa lagayein aur chand ghante intezar karein. Har naye tel ke saath, har baar.</li>
  <li><strong>Tel haathon mein garam karein.</strong> 5–10 qatray le kar haath chand second ragrein. Garam tel behtar phailta hai.</li>
  <li><strong>5–10 minute, golayi mein.</strong> Dil ki taraf harkat karein. <strong>Waqt zyada ahem hai, miqdaar nahi</strong> — ye woh ek baat hai jo log sab se zyada ghalat karte hain.</li>
  <li><strong>20–30 minute laga rehne dein</strong>, ya raat bhar dheele soti kapre ke neeche.</li>
  <li><strong>Behtareen waqt:</strong> garam pani se nahane ke baad, jab pathe naram hon.</li>
</ol>

<h2>Ye kis ke liye NAHI hai</h2>
<ul>
  <li><strong>Chhote bachay.</strong> Riwayati maalish ke telon mein aksar kafoor hota hai, jo jild ke raste jism mein jazb hota hai — chhote bachay par baar baar malne se daure hone ke wakiyat report ho chuke hain. Bachay ke liye saada, kafoor aur menthol se paak tel lein, aur seene par resha ke liye kabhi nahi.</li>
  <li><strong>Hamal ke douran</strong> — pehle doctor se poochein.</li>
  <li><strong>Kati phati jild, khule zakham, dane ya taza jalan par</strong> — tel kabhi nahi.</li>
  <li><strong>Jise kisi tashkhees shuda bimari ka ilaj chahiye.</strong> Arthritis, disc ka masla, sciatica — maalish ka tel aaram de sakta hai, <strong>ilaj nahi kar sakta</strong>. Jo dard musalsal hai ya barh raha hai, usay doctor chahiye.</li>
</ul>

<h2>Hamara tel</h2>
<p><a href="/products/roghan-e-sukoon-massage-oil"><strong>Roghan-e-Sukoon</strong></a> hamara maalish ka tel hai — Rs 1,300 (100 ml) aur Rs 2,200 (200 ml), poore Pakistan mein Cash on Delivery, delivery Rs 300.</p>
<p><strong>Ek baat saaf keh dein:</strong> is tel ki ajza ki fehrist hum abhi manufacturer se confirm kar rahe hain aur woh safhe par nahi hai. Hum andaza laga kar list nahi likhenge. Agar upar wala pehla asool aap ke liye ahem hai — aur hona chahiye — to fehrist aane tak intezar karein, ya <a href="/contact">WhatsApp par poochein</a>.</p>
<p>Agar aap ko abhi mukammal ajza wala tel chahiye to <a href="/products/herbal-skin-oil-50ml">Lookman-e-Hayat</a> (Rs 1,200 / 50 ml) ki poori fehrist us ke safhe par mojood hai.</p>

<h2>Aksar poochay jane wale sawalat</h2>
<h3>Rozana maalish kar sakte hain?</h3>
<p>Ji haan, agar jild theek rehti hai. Zyada tar log hafte mein 2–3 baar karte hain. Jild surkh ho ya khujli ho to band kar dein.</p>
<h3>Maalish ke baad nahana chahiye?</h3>
<p>Zaroori nahi. Log aam tor par 20–30 minute rakhte hain ya raat bhar. Agar chipchipa lage to halke garam pani se dho lein.</p>
<h3>Kitna tel ek baar mein?</h3>
<p>Ek hissay ke liye 5–10 qatray kaafi hain. Zyada tel ka matlab behtar maalish nahi — zyada waqt ka matlab behtar maalish hai.</p>

<p><em>Ye maloomat aam nauiyat ki hain aur tibbi mashwara nahi. Musalsal ya shadeed dard ke liye doctor se rujoo karein.</em></p>
HTML;
    }

    // =====================================================================
    // 2. EN — Roghan-e-Surkh
    // =====================================================================
    private function roghanESurkhEn(): string
    {
        return <<<'HTML'
<p class="answer-box"><strong>Roghan-e-Surkh is a traditional Unani massage oil whose name simply means "red oil" — <em>roghan</em> (oil) and <em>surkh</em> (red).</strong> It is not one fixed recipe: it is a family of warm-toned herbal massage oils made by many different hakeems and manufacturers, used across South Asia for massaging tired shoulders, backs and legs.</p>

<h2>What the name actually tells you — and what it does not</h2>
<p>This is the part most sellers skip. <strong>"Roghan-e-Surkh" is a description, not a brand and not a standardised formula.</strong> Two bottles carrying that name from two different makers can contain substantially different things.</p>
<p>So the name alone tells you almost nothing about what you are buying. What tells you something is the ingredient list on the bottle — and if there isn't one, that is your answer.</p>

<h3>Why "surkh"?</h3>
<p>The reddish colour traditionally comes from the botanicals infused into the base oil. Some makers also add colouring, which is worth knowing: <strong>a deeper red does not mean a stronger oil.</strong> If a seller tells you the colour is the proof of quality, they are selling you the colour.</p>

<h2>How it is traditionally used</h2>
<ul>
  <li><strong>Shoulders, neck and lower back</strong> after long hours sitting or standing.</li>
  <li><strong>Legs and calves</strong> after walking or standing all day.</li>
  <li><strong>Winter stiffness</strong>, when joints and muscles feel tighter.</li>
  <li><strong>A weekly maalish routine</strong> — the way it is done at home, often by a family member.</li>
</ul>
<p>These are traditional, everyday uses. Roghan-e-Surkh is a <strong>comfort and massage oil, not a medicine.</strong> It does not treat, cure or prevent any condition, and no honest seller will tell you otherwise.</p>

<h2>How to choose one</h2>
<table>
  <thead><tr><th>Check</th><th>What good looks like</th></tr></thead>
  <tbody>
    <tr><td>Ingredients</td><td>Named on the bottle. "Herbal extracts" is not a list.</td></tr>
    <tr><td>Camphor content</td><td>Stated. It matters — see the safety section below.</td></tr>
    <tr><td>Batch &amp; expiry</td><td>Printed and legible, not smudged or missing.</td></tr>
    <tr><td>Seal</td><td>Intact. Oils are easy to dilute and re-bottle.</td></tr>
    <tr><td>Consistency</td><td>Light enough to spread for ten minutes without dragging.</td></tr>
    <tr><td>Claims</td><td>The fewer the better. "Cures joint pain" is a warning sign, not a feature.</td></tr>
  </tbody>
</table>

<h2>Safety — the part that is usually left out</h2>
<p>Many traditional massage oils in this family contain <strong>camphor</strong>. That is not automatically a problem for an adult, but it matters a great deal for children:</p>
<ul>
  <li><strong>Do not use camphor-containing oils on babies or young children</strong> — including the very common practice of rubbing warm oil on a child's chest and back for congestion (<em>resha</em>). Camphor is absorbed through the skin, repeated rubbing on a small child has caused seizures, and the American Academy of Pediatrics advises against camphor use in children altogether.</li>
  <li><strong>External use only.</strong> Camphor is harmful if swallowed — keep the bottle out of reach.</li>
  <li><strong>Patch-test</strong> on the inner forearm before first use.</li>
  <li><strong>Never on broken skin, open wounds, rashes or fresh burns.</strong></li>
  <li><strong>Ask a doctor during pregnancy.</strong></li>
</ul>

<h2>Who this is not for</h2>
<ul>
  <li>Anyone looking for a treatment for a diagnosed condition. Arthritis, a slipped disc, sciatica, an injury that is not settling — see a doctor. A massage oil is comfort, not care.</li>
  <li>Anyone with sensitive or reactive skin who cannot see the ingredient list.</li>
  <li>Small children, as above.</li>
</ul>

<h2>Where this leaves you</h2>
<p>If you want a Roghan-e-Surkh specifically, buy it from a seller who will show you the ingredient list, and check the seal and the batch date before you pay.</p>
<p>Our own massage oil is <a href="/products/roghan-e-sukoon-massage-oil"><strong>Roghan-e-Sukoon</strong></a> — Rs 1,300 for 100 ml, Rs 2,200 for 200 ml, Cash on Delivery across Pakistan. In the spirit of the checklist above, we will be straight with you: <strong>we have not yet published its ingredient list</strong> because we are still confirming it with the manufacturer, and we are not going to guess. If the first row of that table matters to you — and it should — wait for the list, or <a href="/contact">ask us on WhatsApp</a>.</p>
<p>If you want a full ingredient list today, <a href="/products/herbal-skin-oil-50ml">Lookman-e-Hayat</a> publishes its own on the product page.</p>

<h2>FAQ</h2>
<h3>Is Roghan-e-Surkh the same as Lookman-e-Hayat oil?</h3>
<p>No. They are different traditional preparations. Lookman-e-Hayat is a specific named oil; Roghan-e-Surkh is a general description of a red herbal massage oil that many makers produce.</p>
<h3>Can I use it on my face?</h3>
<p>Massage oils in this family are generally made for the body, and many contain camphor or menthol, which is not what you want near the eyes. Use a plain facial oil instead.</p>
<h3>How often can I use it?</h3>
<p>Most people use a massage oil two or three times a week. Stop if you see redness or irritation.</p>

<p><em>This article is general information, not medical advice. Persistent or severe pain needs a doctor.</em></p>
HTML;
    }

    // =====================================================================
    // 3. UR — gardan aur kandhe ka dard
    // =====================================================================
    private function neckShoulderUr(): string
    {
        return <<<'HTML'
<p class="answer-box"><strong>Pehli baat sab se ahem: gardan ya kandhe ka dard jo hafton se ho, bazu mein jhunjhunahat ho, ya raat ko jaga deta ho — usay maalish nahi, <em>doctor</em> chahiye.</strong> Maalish ka tel us aam akran ke liye hai jo lambi der baithne, ghalat takiye, ya thakawat se hoti hai. Roghan-e-Sukoon Rs 1,300 (100 ml) se, COD ke saath.</p>

<h2>Pehle ye parhein — kab tel nahi, doctor chahiye</h2>
<p>Ye section upar isi liye hai ke aksar log ise aakhir mein parhte hain, ya bilkul nahi parhte. In mein se koi bhi baat ho to <strong>maalish rok dein aur doctor ko dikhayein</strong>:</p>
<ul>
  <li>Bazu ya ungliyon mein <strong>jhunjhunahat, sunn-pan ya kamzori</strong>.</li>
  <li>Dard jo <strong>2 hafte se zyada</strong> chal raha ho ya barh raha ho.</li>
  <li>Kisi <strong>chot, girne ya accident</strong> ke baad shuru hua dard.</li>
  <li>Saath mein <strong>bukhar</strong>, wazan kam hona, ya gardan ka akar jana.</li>
  <li>Dard jo <strong>raat ko jaga de</strong> ya jis se sar ghoome.</li>
</ul>
<p>Maalish ka tel aaram ka zariya hai. Ye kisi bimari ki tashkhees, ilaj ya bachao ke liye nahi hai, aur jo bechne wala ye kahe ke uska tel gardan ka dard "theek" kar deta hai — woh aap se jhoot bol raha hai.</p>

<h2>Aam akran ke liye maalish ka tarika</h2>
<ol>
  <li><strong>Pehle garmi.</strong> Garam pani se nahayein, ya 5 minute garam kapra rakhein. Naram pathe par maalish behtar lagti hai.</li>
  <li><strong>Tel haathon mein garam karein</strong> — 5–8 qatray, chand second haath ragrein.</li>
  <li><strong>Kandhe se shuru karein, gardan se nahin.</strong> Kandhe ke upar wale hissay par angoothe se golayi mein, halka dabao.</li>
  <li><strong>Gardan par sirf halka haath.</strong> Gardan ke peechay dono taraf, upar se neeche. <strong>Gardan ke saamne aur galay par bilkul nahi.</strong></li>
  <li><strong>5–10 minute.</strong> Waqt zyada ahem hai, dabao nahi. Zor lagane se pathe aur kas jaate hain.</li>
  <li><strong>Baad mein 20–30 minute laga rehne dein</strong>, ya raat bhar. Sardi mein gardan dhak kar rakhein.</li>
</ol>

<h2>Paanch aam galtiyan</h2>
<table>
  <thead><tr><th>Galti</th><th>Behtar</th></tr></thead>
  <tbody>
    <tr><td>Poori taqat se dabana</td><td>Halka, lambe waqt tak dabao</td></tr>
    <tr><td>Do minute mein khatam</td><td>Kam se kam 5 minute</td></tr>
    <tr><td>Thandi jild par tel</td><td>Pehle garmi, phir tel</td></tr>
    <tr><td>Gardan ki haddi par seedha dabana</td><td>Haddi ke doosri taraf, pathon par</td></tr>
    <tr><td>Sirf maalish, baqi sab waisa hi</td><td>Takiya, screen ki oonchai aur baithne ka tareeqa bhi theek karein</td></tr>
  </tbody>
</table>
<p>Aakhri wali sab se ahem hai. Agar aap rozana 8 ghante jhuk kar phone dekhte hain, to koi tel us ka muqabla nahi kar sakta. <strong>Screen ko aankhon ki seedh mein rakhna maalish se zyada faida deta hai.</strong></p>

<h2>Ye kis ke liye nahi hai</h2>
<ul>
  <li><strong>Chhote bachay</strong> — riwayati maalish ke telon mein aksar kafoor hota hai jo jild ke raste jazb hota hai. Bachon par ye tel nahi.</li>
  <li><strong>Hamal ke douran</strong> — pehle doctor se poochein.</li>
  <li><strong>Kati jild, dane, ya taza chot par</strong> — kabhi nahi.</li>
  <li><strong>Upar wali "doctor chahiye" list mein se koi bhi alamat ho</strong> — tel band, doctor.</li>
</ul>

<h2>Kaunsa tel</h2>
<p><a href="/products/roghan-e-sukoon-massage-oil"><strong>Roghan-e-Sukoon</strong></a> — Rs 1,300 (100 ml), Rs 2,200 (200 ml), poore Pakistan COD, delivery Rs 300. Iski ajza ki fehrist hum abhi confirm kar rahe hain aur safhe par nahi hai — hum andaza laga kar nahi likhenge. Agar aap ko allergy hai ya jild sensitive hai to fehrist aane tak intezar karein.</p>
<p>Maalish ka tarika mukammal parhna ho to <a href="/blog/maalish-ka-tel-konsa-acha-hai">maalish ka tel chunne wali guide</a> dekhein.</p>

<p><em>Ye maloomat aam nauiyat ki hain aur tibbi mashwara nahi.</em></p>
HTML;
    }

    // =====================================================================
    // 4. EN — local buying guide (Karachi / Hyderabad / Lahore)
    // =====================================================================
    private function localGuideEn(): string
    {
        return <<<'HTML'
<p class="answer-box"><strong>In Pakistan, herbal oils are sold in three places: the old-bazaar <em>pansar</em> and <em>attar</em> shops, general pharmacies, and online.</strong> The bazaars are cheapest and have the widest range; they are also where dilution is most common. This guide covers Karachi, Hyderabad and Lahore — where people go, what a fair price looks like, and how to check a bottle before you pay.</p>

<h2>Karachi</h2>
<p>The traditional place to buy herbs and herbal oils is the <strong>Saddar area around Empress Market</strong>, where pansar shops sell dried herbs, resins and oils by weight and by bottle. <strong>Jodia Bazar</strong> is the wholesale end of the same trade — better prices if you are buying in quantity, less useful for a single bottle. For ready-made branded bottles, general stores and pharmacies along <strong>Tariq Road</strong> and in the main neighbourhood markets carry the common names.</p>
<p>Practical notes: bazaar prices are negotiable and the same oil can vary a lot between shops. Ask to see the sealed bottle rather than a decanted one.</p>

<h2>Hyderabad</h2>
<p>The old city bazaars are the place — the <strong>Shahi Bazaar</strong> stretch and the surrounding lanes have long-established pansar shops. Hyderabad's range is narrower than Karachi's, so a specific named oil may need ordering in, and prices are often a little higher than Karachi for the same bottle simply because volumes are smaller.</p>

<h2>Lahore</h2>
<p><strong>Akbari Mandi</strong> is the major wholesale market for herbs and spices in Lahore and the natural first stop; the <strong>Anarkali</strong> bazaars have retail pansar and attar shops. As in Karachi, the wholesale market is best for quantity and the retail bazaars for a single bottle.</p>

<h2>What a fair price looks like</h2>
<p>For a herbal massage or hair oil in a sealed bottle from a known maker, roughly:</p>
<table>
  <thead><tr><th>Size</th><th>Typical range</th><th>Our price</th></tr></thead>
  <tbody>
    <tr><td>50 ml</td><td>Rs 700 – 1,500</td><td>Rs 1,200 (Lookman-e-Hayat)</td></tr>
    <tr><td>100 ml</td><td>Rs 1,100 – 2,500</td><td>Rs 1,300 – 2,200</td></tr>
    <tr><td>200 ml</td><td>Rs 1,900 – 3,500</td><td>Rs 2,000 – 2,200</td></tr>
  </tbody>
</table>
<p>These are broad ranges, not a price list, and they move. Treat anything dramatically below the range with suspicion rather than delight — <strong>the cheapest bottle in the bazaar is usually the most diluted one.</strong></p>

<h2>Six checks before you pay</h2>
<ol>
  <li><strong>Is there an ingredient list?</strong> "Herbal extracts" is not a list. This is the single most useful check and the one most people skip.</li>
  <li><strong>Batch number and expiry</strong> — printed, legible, not smudged or stickered over.</li>
  <li><strong>Is the seal intact?</strong> Oils are trivially easy to top up with a cheap carrier oil and re-bottle. A decanted bottle from a large tin tells you nothing about what is in it.</li>
  <li><strong>Smell it.</strong> A harsh chemical note usually means added fragrance. It does not mean strength.</li>
  <li><strong>Rub a drop between your fingers.</strong> It should spread, not feel gritty or instantly sticky.</li>
  <li><strong>Listen to the claims.</strong> A shopkeeper who says an oil <em>cures</em> arthritis or regrows hair on a bald patch is telling you something useful — about the shop.</li>
</ol>

<h2>Bazaar vs online — an honest comparison</h2>
<table>
  <thead><tr><th></th><th>Bazaar</th><th>Online (COD)</th></tr></thead>
  <tbody>
    <tr><td>Price</td><td>Usually lower, negotiable</td><td>Fixed, plus Rs 300 delivery</td></tr>
    <tr><td>Range</td><td>Widest, especially Karachi and Lahore</td><td>Whatever that seller stocks</td></tr>
    <tr><td>See before you buy</td><td>Yes</td><td>No</td></tr>
    <tr><td>Ingredient list</td><td>Often absent</td><td>Should be on the page — check before ordering</td></tr>
    <tr><td>Dilution risk</td><td>Higher, especially decanted bottles</td><td>Lower on sealed stock, but you are trusting the seller</td></tr>
    <tr><td>Travel</td><td>Yes</td><td>No — pay at your door</td></tr>
  </tbody>
</table>
<p>If you live near one of these bazaars and enjoy the trip, go. You will likely pay less. If you do not, or you want the ingredient list in writing before you commit, online is the more practical option.</p>

<h2>Ordering from us</h2>
<p>We ship across Pakistan with <strong>Cash on Delivery</strong> — you pay at your door. Karachi is usually 2–4 working days, Hyderabad and Lahore 2–4, elsewhere 4–7. Delivery is Rs 300 flat.</p>
<ul>
  <li><a href="/products/herbal-skin-oil-50ml"><strong>Lookman-e-Hayat</strong></a> — Rs 1,200 (50 ml) / Rs 2,200 (100 ml). Full ingredient list on the page.</li>
  <li><a href="/products/roghan-e-jarain-hair-oil"><strong>Roghan-e-Jarain</strong></a> hair oil — Rs 1,200 (100 ml). Four ingredients, all named. Contains almond, a tree nut.</li>
  <li><a href="/products/roghan-e-sukoon-massage-oil"><strong>Roghan-e-Sukoon</strong></a> massage oil — Rs 1,300 (100 ml). <strong>Ingredient list still being confirmed</strong> — we say so rather than guess.</li>
</ul>
<p>Applying check #1 to ourselves: two of those three publish a full list today, and one does not. That is the honest state of it.</p>

<h2>FAQ</h2>
<h3>Is bazaar oil worse than branded oil?</h3>
<p>Not necessarily — some of the best oils in Pakistan come from small makers with no marketing at all. The difference is that a sealed, labelled bottle lets you check what you bought, and a decanted one does not.</p>
<h3>Can I get herbal oil delivered in Hyderabad?</h3>
<p>Yes. We deliver across Pakistan with Cash on Delivery, usually 2–4 working days to Hyderabad.</p>
<h3>How do I know an oil is not diluted?</h3>
<p>With certainty, you cannot without testing. What you can do is buy sealed, check the batch and expiry, avoid unusually cheap bottles, and buy from somewhere that will tell you what is in it.</p>

<p><em>Prices and market descriptions are general guidance as of September 2026 and will change. This is not medical advice.</em></p>
HTML;
    }

    // =====================================================================
    // 5. UR — local buying guide twin
    // =====================================================================
    private function localGuideUr(): string
    {
        return <<<'HTML'
<p class="answer-box"><strong>Pakistan mein herbal tel teen jagah milta hai: purane bazaar ki <em>pansar</em> aur <em>attar</em> ki dukanein, aam medical store, aur online.</strong> Bazaar sab se sasta hai aur variety sab se zyada — magar milawat bhi wahin sab se aam hai. Ye guide Karachi, Hyderabad aur Lahore ke liye hai: kahan jayen, munasib qeemat kya hai, aur paise dene se pehle kya check karein.</p>

<h2>Karachi</h2>
<p>Riwayati jagah <strong>Saddar mein Empress Market ke aas paas</strong> ki pansar dukanein hain, jahan jari bootiyan, gond aur tel wazan aur bottle dono se milte hain. <strong>Jodia Bazar</strong> isi karobar ka thok wala sira hai — zyada miqdaar leni ho to behtar, ek bottle ke liye zyada faida nahi. Bani banai branded bottlein <strong>Tariq Road</strong> aur mohallay ke bare markets ki dukanon par mil jati hain.</p>
<p>Kaam ki baat: bazaar mein qeemat par baat hoti hai, aur ek hi tel dukan se dukan mukhtalif daam par milta hai. <strong>Seal bandh bottle mangein</strong>, tin se nikal kar bhari hui nahi.</p>

<h2>Hyderabad</h2>
<p>Purane sheher ke bazaar hi asal jagah hain — <strong>Shahi Bazaar</strong> aur us se milti hui galiyon mein purani pansar dukanein hain. Hyderabad mein variety Karachi se kam hai, is liye koi khaas naam ka tel mangwana par sakta hai, aur wohi bottle aksar Karachi se thori mehngi milti hai kyunke miqdaar kam chalti hai.</p>

<h2>Lahore</h2>
<p><strong>Akbari Mandi</strong> Lahore mein jari booti aur masalon ki bari thok mandi hai aur pehla parao. <strong>Anarkali</strong> ke bazaron mein pansar aur attar ki retail dukanein hain. Karachi ki tarah yahan bhi thok mandi zyada miqdaar ke liye behtar hai, aur retail bazaar ek bottle ke liye.</p>

<h2>Munasib qeemat kya hoti hai</h2>
<table>
  <thead><tr><th>Size</th><th>Aam range</th><th>Hamari qeemat</th></tr></thead>
  <tbody>
    <tr><td>50 ml</td><td>Rs 700 – 1,500</td><td>Rs 1,200 (Lookman-e-Hayat)</td></tr>
    <tr><td>100 ml</td><td>Rs 1,100 – 2,500</td><td>Rs 1,300 – 2,200</td></tr>
    <tr><td>200 ml</td><td>Rs 1,900 – 3,500</td><td>Rs 2,000 – 2,200</td></tr>
  </tbody>
</table>
<p>Ye motay taur par range hai, price list nahi, aur ye badalti rehti hai. Jo cheez is range se bohot neeche mile us par khush hone ke bajaye <strong>shak</strong> karein — bazaar ki sab se sasti bottle aam tor par sab se zyada milawat wali hoti hai.</p>

<h2>Paise dene se pehle 6 check</h2>
<ol>
  <li><strong>Ajza ki fehrist hai?</strong> "Qudrati jari bootiyan" fehrist nahi hoti. Ye sab se kaam ka check hai aur log yehi sab se zyada chhorte hain.</li>
  <li><strong>Batch number aur expiry</strong> — chhapa hua, parha jane wala, mita ya sticker se dhaka hua nahi.</li>
  <li><strong>Seal saabit hai?</strong> Tel mein sasta tel milana aur dobara bharna bohot aasan hai. Bare tin se nikal kar di gayi bottle aap ko kuch nahi batati.</li>
  <li><strong>Soonghein.</strong> Tez chemical jaisi bu ka matlab aam tor par daali hui khushbu hai — taqat nahi.</li>
  <li><strong>Ungliyon mein ek qatra ragrein.</strong> Phailna chahiye — rait jaisa ya foran chipakne wala nahi.</li>
  <li><strong>Daawon par ghor karein.</strong> Jo dukandaar kahe ke tel arthritis "theek" kar deta hai ya ganje sar par baal uga deta hai — woh aap ko dukan ke bare mein bata raha hai.</li>
</ol>

<h2>Bazaar ya online — imaandar moqabla</h2>
<table>
  <thead><tr><th></th><th>Bazaar</th><th>Online (COD)</th></tr></thead>
  <tbody>
    <tr><td>Qeemat</td><td>Aam tor par kam, mol tol ho sakta hai</td><td>Muqarrar, saath Rs 300 delivery</td></tr>
    <tr><td>Variety</td><td>Sab se zyada — khaas kar Karachi aur Lahore</td><td>Jo us dukan par ho</td></tr>
    <tr><td>Dekh kar lena</td><td>Ho jata hai</td><td>Nahi</td></tr>
    <tr><td>Ajza ki fehrist</td><td>Aksar nahi hoti</td><td>Safhe par honi chahiye — order se pehle dekh lein</td></tr>
    <tr><td>Milawat ka khatra</td><td>Zyada, khaas kar khuli bottle mein</td><td>Seal bandh par kam, magar bharosa dukan par</td></tr>
    <tr><td>Safar</td><td>Karna parta hai</td><td>Nahi — darwaze par paise</td></tr>
  </tbody>
</table>
<p>Agar aap in bazaron ke qareeb rehte hain to zaroor jayen — aam tor par sasta parega. Agar nahi, ya aap order se pehle likhi hui ajza ki fehrist dekhna chahte hain, to online zyada aasan hai.</p>

<h2>Hamare paas se mangwana</h2>
<p>Hum poore Pakistan mein <strong>Cash on Delivery</strong> bhejte hain — aap darwaze par paise dete hain. Karachi aam tor par 2–4 kaam ke din, Hyderabad aur Lahore 2–4, baqi Pakistan 4–7. Delivery Rs 300.</p>
<ul>
  <li><a href="/products/herbal-skin-oil-50ml"><strong>Lookman-e-Hayat</strong></a> — Rs 1,200 (50 ml) / Rs 2,200 (100 ml). Poori fehrist safhe par.</li>
  <li><a href="/products/roghan-e-jarain-hair-oil"><strong>Roghan-e-Jarain</strong></a> balon ka tel — Rs 1,200 (100 ml). Chaar ajza, sab likhe huay. Is mein badam (giri) hai.</li>
  <li><a href="/products/roghan-e-sukoon-massage-oil"><strong>Roghan-e-Sukoon</strong></a> maalish ka tel — Rs 1,300 (100 ml). <strong>Ajza ki fehrist abhi confirm ho rahi hai</strong> — hum ye likh rahe hain, andaza nahi laga rahe.</li>
</ul>
<p>Upar wala check #1 apne aap par lagayen to: teen mein se do aaj poori fehrist detay hain, ek nahi. Yehi sach hai.</p>

<h2>Aksar poochay jane wale sawalat</h2>
<h3>Kya bazaar ka tel branded se kam-tar hota hai?</h3>
<p>Zaroori nahi — Pakistan ke kuch behtareen tel chhote karkhanon ke hain jin ki koi marketing hi nahi. Farq sirf itna hai ke seal bandh, label wali bottle aap ko check karne deti hai, khuli bottle nahi.</p>
<h3>Kya Hyderabad mein herbal tel delivery ho sakta hai?</h3>
<p>Ji haan. Poore Pakistan mein COD, Hyderabad aam tor par 2–4 kaam ke din.</p>
<h3>Milawat ka pata kaise chale?</h3>
<p>Yaqeen se, bina test ke nahi chal sakta. Jo aap kar sakte hain: seal bandh khareedein, batch aur expiry dekhein, ghair maamooli sasti bottle se bachein, aur wahan se lein jo bataye ke andar kya hai.</p>

<p><em>Qeematein aur bazaar ki tafseelat September 2026 tak ki aam maloomat hain aur badalti rehti hain. Ye tibbi mashwara nahi.</em></p>
HTML;
    }
}
