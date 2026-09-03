<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * 30-day content plan, WEEK 2 — Roghan-e-Jarain (hair & scalp).
 * See docs/30-day-content-plan-sep2026.md.
 *
 * DECONFLICTION — one distinct primary keyword per row, and no Roman-Urdu twin
 * reuses its English sibling's phrase translated:
 *
 *   arandi        : UR "arandi ka tel balon ke liye"
 *   oil compare   : EN "best oil for hair in pakistan"
 *   methi         : UR "methi dana ka tel"
 *   buyer guide   : EN "herbal hair oil in pakistan"
 *   roots         : UR "balon ki jarain mazboot"
 *
 * ⚠️ CLAIMS. Cosmetic framing only — "conditions the scalp", "helps reduce
 *    breakage", "hair-fall control". NOTHING here promises regrowth, reverses
 *    baldness, or treats alopecia. `ganjapan ka ilaj` is on the permanent
 *    cannot-target list and appears only as an honest myth-buster.
 *
 * ⚠️ ALLERGEN. Roghan-e-Jarain contains SWEET ALMOND OIL — a tree nut — and
 *    coconut oil. Every post that recommends it states this. Do not drop it to
 *    save a line.
 *
 * PRICING: Roghan-e-Jarain Rs 1,200 (100 ml) / Rs 2,000 (200 ml). Delivery
 * Rs 300 flat; free over Rs 5,000, which one bottle does not reach — so no
 * free-delivery claim is made anywhere.
 *
 * DEPLOY-SAFE: firstOrCreate by slug. Run BlogDefaultsSeeder afterwards.
 */
class Month1Week2BlogSeeder extends Seeder
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

            if ($product = Product::where('slug', 'roghan-e-jarain-hair-oil')->first()) {
                $product->blogPosts()->syncWithoutDetaching(
                    [$post->id => ['position' => $article['product_position']]]
                );
            }
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function articles(): array
    {
        return [
            [
                'locale' => 'ur-Latn',
                'title' => 'Arandi Ka Tel Balon Ke Liye: Sahi Tarika',
                'slug' => 'arandi-ka-tel-balon-ke-liye',
                'excerpt' => 'Arandi ka tel balon ke liye kaise lagayen — gaarha tel patla karne ka tarika, kitni der rakhein, kin galtiyon se bachein, aur ye kis ke liye theek nahi.',
                'reading_time_minutes' => 6,
                'published_at' => now()->addDays(7),
                'product_position' => 2,
                'content' => $this->arandiUr(),
            ],
            [
                'locale' => 'en',
                'title' => 'Coconut, Almond or Kalonji Oil for Hair?',
                'slug' => 'coconut-almond-kalonji-oil-for-hair',
                'excerpt' => 'Coconut, almond and kalonji oil for hair compared honestly: what each one actually does, which has real evidence behind it, and how to pick for your hair.',
                'reading_time_minutes' => 7,
                'published_at' => now()->addDays(8),
                'product_position' => 1,
                'content' => $this->compareEn(),
            ],
            [
                'locale' => 'ur-Latn',
                'title' => 'Methi Dana Ka Tel Balon Ke Liye',
                'slug' => 'methi-dana-ka-tel-balon-ke-liye',
                'excerpt' => 'Methi dana ka tel ghar par banane ka tarika, sar ki khushki aur khujli ke liye istemal, kitni der rakhein — aur kab ye tel istemal nahi karna chahiye.',
                'reading_time_minutes' => 6,
                'published_at' => now()->addDays(9),
                'product_position' => 4,
                'content' => $this->methiUr(),
            ],
            [
                'locale' => 'en',
                'title' => "Herbal Hair Oil in Pakistan: A Buyer's Guide",
                'slug' => 'herbal-hair-oil-in-pakistan',
                'excerpt' => 'How to choose a herbal hair oil in Pakistan: what the label should tell you, what no oil can do, fair prices, and the claims that mean you should walk away.',
                'reading_time_minutes' => 7,
                'published_at' => now()->addDays(10),
                'product_position' => 0,
                'content' => $this->buyerGuideEn(),
            ],
            [
                'locale' => 'ur-Latn',
                'title' => 'Balon Ki Jarain Mazboot Karne Ka Tarika',
                'slug' => 'balon-ki-jarain-mazboot-karne-ka-tarika',
                'excerpt' => 'Balon ki jarain mazboot karne ke liye kya waqai kaam karta hai — champi ka sahi tarika, ghiza, aur woh galtiyan jo baal tootne ki asal wajah banti hain.',
                'reading_time_minutes' => 7,
                'published_at' => now()->addDays(11),
                'product_position' => 3,
                'content' => $this->rootsUr(),
            ],
        ];
    }

    // =====================================================================
    private function arandiUr(): string
    {
        return <<<'HTML'
<p class="answer-box"><strong>Arandi ka tel (castor oil) itna gaarha hota hai ke akela lagana mushkil hai — isay hamesha kisi halke tel mein mila kar lagayen, taqreeban 1 hissa arandi aur 3 hissa nariyal ya badam ka tel.</strong> Sar ki jild par 5 minute champi, 30–60 minute chhorein, phir dho lein. Hafte mein 1–2 baar kaafi hai.</p>

<h2>Arandi ka tel karta kya hai</h2>
<p>Arandi ka tel bohot gaarha aur chipakne wala hota hai. Uska asal faida yehi hai: <strong>ye baal par ek parat bana deta hai</strong>, jis se baal kanghi karte waqt kam tootte hain aur sar ki khushk jild par namdi rehti hai.</p>
<p><strong>Aur jo ye nahi karta:</strong> ye ganje hissay par naye baal nahi ugata. Ye mardana ganjapan (androgenetic alopecia) palat nahi sakta. Jo bhi bechne wala ye kahe, woh aap se jhoot bol raha hai — aur ye baat hum apne tel ke baare mein bhi kehte hain.</p>

<h2>Lagane ka sahi tarika</h2>
<ol>
  <li><strong>Patla karein.</strong> 1 chamach arandi + 3 chamach nariyal ya badam ka tel. Akela arandi ka tel dhona mushkil ho jata hai aur log agli baar lagate hi nahi.</li>
  <li><strong>Halka garam karein</strong> — haathon mein ragar kar, ya katori ko garam pani mein rakh kar. Seedha aag ya microwave par nahi.</li>
  <li><strong>Sar ki jild par ungliyon ke poron se 5 minute champi</strong>, golayi mein. Nakhun nahi. Baalon ko aapas mein ragrein nahi — tootenge.</li>
  <li><strong>30–60 minute chhorein.</strong> Raat bhar bhi theek hai magar takiye par purana kapra rakh lein.</li>
  <li><strong>Dhone ka tareeqa:</strong> pehle <strong>sookhe baalon par shampoo</strong> lagayein, phir pani. Geele baalon par shampoo gaarhe tel ko nahi utaarta aur log do-teen baar shampoo karte hain, jo jild khushk kar deta hai.</li>
</ol>

<h2>Kitni baar</h2>
<table>
  <thead><tr><th>Baal ki halat</th><th>Kitni baar</th></tr></thead>
  <tbody>
    <tr><td>Khushk, rookhe, tootte huay</td><td>Hafte mein 2 baar</td></tr>
    <tr><td>Aam</td><td>Hafte mein 1 baar</td></tr>
    <tr><td>Chikni jild, jaldi tel ho jane wale baal</td><td>Hafte mein 1 baar, sirf lambai par, jaron par nahi</td></tr>
    <tr><td>Sar par dane ya khujli</td><td>Rok dein, doctor ko dikhayein</td></tr>
  </tbody>
</table>

<h2>Ye kis ke liye NAHI hai</h2>
<ul>
  <li><strong>Jin ke sar par dane, phunsiyan ya seborrheic dermatitis ho.</strong> Gaarha tel masaam band kar sakta hai aur halat kharab kar sakta hai.</li>
  <li><strong>Jo rozana lagana chahte hain.</strong> Arandi rozana ke liye nahi — bhaari hai.</li>
  <li><strong>Jo tibbi wajah se baal girne ka hal dhoond rahe hain.</strong> Thyroid, khoon ki kami, hormonal masla, dawaon ka asar — in mein tel kuch nahi karta. <strong>Doctor aur khoon ka test chahiye, bottle nahi.</strong></li>
  <li><strong>Bachon par</strong> bina baray ki nigrani ke nahi.</li>
</ul>

<h2>Ghar par mila kar ya bana banaya</h2>
<p>Ghar par milana bilkul theek hai aur sasta hai. Agar bana banaya balanced tel chahiye to hamara <a href="/products/roghan-e-jarain-hair-oil"><strong>Roghan-e-Jarain</strong></a> hai — nariyal ka tel, roghan-e-badam, kalonji ka tel aur vitamin D, aur kuch nahi. Rs 1,200 (100 ml), poore Pakistan COD.</p>
<p><strong>Ehtiyat:</strong> is mein <strong>badam hai, jo tree nut hai</strong>, aur nariyal ka tel. Agar ghar mein kisi ko giri ya nariyal se allergy hai to ye na lein.</p>

<h2>Aksar poochay jane wale sawalat</h2>
<h3>Kya arandi ka tel palkon aur bhaunwon par laga sakte hain?</h3>
<p>Log lagate hain, magar aankh ke itna qareeb bohot ehtiyat chahiye. Ek rui ki teeli se bohot thora, aur aankh mein jaye to foran saada pani se dho lein.</p>
<h3>Raat bhar rakhna behtar hai?</h3>
<p>Zaroori nahi. 30–60 minute mein zyada tar faida mil jata hai. Raat bhar rakhne ka faida us se kaheen kam hai jitna log samajhte hain.</p>
<h3>Kitne dinon mein farq nazar aayega?</h3>
<p>Tootna kam hona 3–4 hafte mein mehsoos ho sakta hai. Baal ki lambai mahine mein taqreeban 1 cm barhti hai — koi tel is raftaar ko nahi badalta.</p>

<p><em>Ye aam maloomat hain, tibbi mashwara nahi. Musalsal baal girne ke liye doctor se rujoo karein.</em></p>
HTML;
    }

    // =====================================================================
    private function compareEn(): string
    {
        return <<<'HTML'
<p class="answer-box"><strong>Of the three, coconut oil has the strongest evidence: it is one of the few oils shown to penetrate the hair shaft rather than sit on top of it, which is what reduces protein loss and breakage.</strong> Almond oil is the best for slip and conditioning. Kalonji is the traditional scalp choice. Most good hair oils use more than one, for exactly that reason.</p>

<h2>The short comparison</h2>
<table>
  <thead><tr><th></th><th>Coconut (nariyal)</th><th>Sweet almond (roghan-e-badam)</th><th>Kalonji (black seed)</th></tr></thead>
  <tbody>
    <tr><td>Weight</td><td>Medium, solid when cold</td><td>Light</td><td>Light-medium</td></tr>
    <tr><td>Main strength</td><td>Penetrates the shaft; reduces protein loss</td><td>Slip and conditioning; easy to comb out</td><td>Traditional scalp care; the desi smell people expect</td></tr>
    <tr><td>Evidence</td><td><strong>Strongest of the three</strong></td><td>Modest</td><td>Traditional use, limited clinical work</td></tr>
    <tr><td>Best for</td><td>Dry, breakage-prone hair</td><td>Detangling, fine hair, sensitive noses</td><td>Flaky or itchy scalp</td></tr>
    <tr><td>Watch out</td><td>Some find it heavy; solid below ~24°C</td><td><strong>Tree nut allergen</strong></td><td>Strong smell some people dislike</td></tr>
  </tbody>
</table>

<h2>Coconut oil — the one with the research behind it</h2>
<p>Most oils coat the hair. Coconut oil is unusual in that its main fatty acid is small and straight enough to actually move into the hair shaft. That matters because a lot of everyday damage happens when hair swells with water during washing and then dries — oil inside the shaft reduces that swelling, and less swelling means less breakage.</p>
<p>This is the closest thing to a genuinely evidence-backed claim in hair oiling, which is why it is worth being precise about: <strong>it reduces damage. It does not grow hair.</strong></p>

<h2>Sweet almond oil — the one that makes the routine survivable</h2>
<p>Almond oil is light and slippery. On its own that sounds trivial, but it is the difference between an oil you use every week and one you use twice and abandon. It spreads across the scalp without dragging, and it combs out afterwards without three rounds of shampoo.</p>
<p><strong>Allergen warning, and it is a serious one:</strong> almond is a <strong>tree nut</strong>. If anyone in the house has a nut allergy, an almond-based hair oil is not for you — including oils that list it well down the ingredient list.</p>

<h2>Kalonji oil — the traditional one</h2>
<p>Kalonji (<em>Nigella sativa</em>, black seed) is the scalp ingredient people in this region reach for, and it is what makes a hair oil smell like a desi hair oil rather than a supermarket one. The traditional use is long and consistent; the clinical evidence is thinner than the marketing usually implies. We would rather say that than pretend otherwise.</p>

<h2>What none of them do</h2>
<p>No oil on this page — or any page — will:</p>
<ul>
  <li>Regrow hair on a bald patch.</li>
  <li>Reverse male-pattern baldness.</li>
  <li>Treat alopecia, thyroid-related hair loss, iron deficiency, or hair loss from medication.</li>
</ul>
<p>If hair is coming out in handfuls, coming out in patches, or thinning fast, that is a <strong>doctor and a blood test</strong>, not a bottle. Oils help hair that already exists stay on your head longer. That is a real benefit, and it is the honest size of it.</p>

<h2>So which should you pick?</h2>
<ul>
  <li><strong>Dry, rough, breaking hair</strong> → coconut-led.</li>
  <li><strong>Fine hair, or you hate the heaviness</strong> → almond-led.</li>
  <li><strong>Flaky, itchy scalp</strong> → kalonji in the blend, and see a doctor if it does not settle.</li>
  <li><strong>You just want one bottle</strong> → a blend, which is why most traditional oils are blends.</li>
</ul>

<h2>Our blend</h2>
<p><a href="/products/roghan-e-jarain-hair-oil"><strong>Roghan-e-Jarain</strong></a> uses all three plus vitamin D — coconut oil, sweet almond oil, kalonji oil, vitamin D, and nothing else. No mineral oil, no undisclosed fragrance, no long list of extracts present in amounts too small to matter. Rs 1,200 for 100 ml, Rs 2,000 for 200 ml, Cash on Delivery across Pakistan.</p>
<p><strong>It contains almond (a tree nut) and coconut.</strong> Do not use it if you have a nut or coconut allergy.</p>

<h2>FAQ</h2>
<h3>Can I mix them myself?</h3>
<p>Yes, and it is cheaper. Roughly 2 parts coconut, 1 part almond, a small amount of kalonji. Make small batches — home blends have no preservative and go rancid.</p>
<h3>Which is best for hair fall?</h3>
<p>None of them stops hair fall caused by a medical or hormonal issue. What a good oil does is reduce breakage, so less hair snaps off during combing and washing. That often looks like less hair fall, and it is a real improvement — it is just a different mechanism from what the adverts imply.</p>
<h3>Hot oil or room temperature?</h3>
<p>Slightly warm spreads better. Warm it between your palms or stand the bottle in hot water — never on direct heat.</p>

<p><em>General information, not medical advice. Sudden or patchy hair loss needs a doctor.</em></p>
HTML;
    }

    // =====================================================================
    private function methiUr(): string
    {
        return <<<'HTML'
<p class="answer-box"><strong>Methi dana ka tel ghar par banana aasan hai: 2 chamach methi dana raat bhar bhigo dein, subah peis kar 1 cup nariyal ya badam ke tel mein halki aanch par 5–7 minute garam karein, thanda kar ke chhaan lein.</strong> Sar ki jild par lagayen, 30–45 minute rakhein, phir dho lein. Hafte mein 1–2 baar.</p>

<h2>Methi kis liye istemal hoti hai</h2>
<p>Methi (fenugreek) barr-e-sagheer mein naslon se <strong>sar ki khushki, papri aur khujli</strong> ke liye istemal hoti aayi hai. Ye riwayati istemal hai — lambi aur mustaqil — magar iske peeche clinical tehqeeq utni nahi jitni ishtihar bataate hain. Hum ye saaf keh dena behtar samajhte hain.</p>
<p><strong>Jo methi nahi karti:</strong> ganje hissay par baal nahi ugati, aur tibbi wajah se girte balon ka ilaj nahi.</p>

<h2>Ghar par banane ka tarika</h2>
<ol>
  <li><strong>2 chamach methi dana</strong> raat bhar pani mein bhigo dein.</li>
  <li>Subah pani nikal kar <strong>peis kar paste</strong> bana lein.</li>
  <li><strong>1 cup nariyal ya badam ka tel</strong> lein aur paste us mein daal dein.</li>
  <li><strong>Sab se halki aanch par 5–7 minute.</strong> Ubalna nahi — jala hua tel bekaar bhi hai aur jild ke liye sakht bhi.</li>
  <li>Thanda karein, <strong>kapre se chhaan lein</strong>, saaf sookhi bottle mein rakhein.</li>
  <li><strong>2–3 hafte ke andar istemal karein</strong> aur thandi jagah rakhein. Ghar ke tel mein koi preservative nahi hota — kharab ho jata hai. Bu badal jaye to phenk dein.</li>
</ol>

<h2>Lagane ka tarika</h2>
<ul>
  <li>Halka garam kar ke <strong>sar ki jild par ungliyon ke poron se 5 minute</strong> champi.</li>
  <li><strong>30–45 minute</strong> rakhein. Raat bhar zaroori nahi.</li>
  <li>Pehle <strong>sookhe baalon par shampoo</strong>, phir pani — tel ek hi baar mein utar jata hai.</li>
  <li>Hafte mein <strong>1–2 baar</strong>. Is se zyada faida nahi barhta.</li>
</ul>

<h2>Ye kis ke liye NAHI hai</h2>
<ul>
  <li><strong>Hamal ke douran</strong> — methi ke baare mein rai mukhtalif hai. Doctor se poochein.</li>
  <li><strong>Jinhein chana, moong phali ya phaliyon (legumes) se allergy ho</strong> — methi bhi isi khandan se hai.</li>
  <li><strong>Sar par khule dane, zakham ya shadeed khujli</strong> ho to nahi — doctor ko dikhayein.</li>
  <li><strong>Jo tibbi wajah se girte baal theek karna chahte hain</strong> — khoon ki kami, thyroid, hormonal masla. Ye test se pata chalta hai, tel se nahi.</li>
</ul>

<h2>Ek baat jo koi nahi batata</h2>
<p>Methi ki bu tez hoti hai aur baalon mein ek do din reh sakti hai. Bohot se log isi wajah se do baar ke baad chhor dete hain. <strong>Jo tel aap waqai lagate rahenge woh us se behtar hai jo almari mein para rahe</strong> — is liye agar bu bardasht na ho to kisi halke tel par chale jayein, ye koi nakami nahi.</p>

<h2>Bana banaya chahiye to</h2>
<p><a href="/products/roghan-e-jarain-hair-oil"><strong>Roghan-e-Jarain</strong></a> — nariyal ka tel, roghan-e-badam, kalonji ka tel aur vitamin D. Is mein methi nahi hai; ye rozana champi ke liye halka blend hai. Rs 1,200 (100 ml), COD poore Pakistan.</p>
<p><strong>Is mein badam (tree nut) aur nariyal hai</strong> — allergy ho to na lein.</p>
<p>Champi ka mukammal tarika <a href="/blog/balon-ki-jarain-mazboot-karne-ka-tarika">is guide</a> mein hai.</p>

<p><em>Ye aam maloomat hain, tibbi mashwara nahi.</em></p>
HTML;
    }

    // =====================================================================
    private function buyerGuideEn(): string
    {
        return <<<'HTML'
<p class="answer-box"><strong>A herbal hair oil is worth buying if the label names every ingredient, the bottle is sealed with a legible batch and expiry, and the seller does not promise regrowth.</strong> Expect roughly Rs 700–2,500 depending on size. Our Roghan-e-Jarain is Rs 1,200 for 100 ml with Cash on Delivery across Pakistan.</p>

<h2>Start with what an oil can and cannot do</h2>
<p>This matters more than any brand comparison, because it decides whether you will be satisfied with what you buy.</p>
<table>
  <thead><tr><th>A good hair oil can</th><th>No hair oil can</th></tr></thead>
  <tbody>
    <tr><td>Reduce breakage during combing and washing</td><td>Regrow hair on a bald patch</td></tr>
    <tr><td>Soften dry, rough hair</td><td>Reverse male-pattern baldness</td></tr>
    <tr><td>Condition a dry, flaky scalp</td><td>Treat alopecia or thyroid-related hair loss</td></tr>
    <tr><td>Make hair easier to detangle</td><td>Fix hair loss caused by iron deficiency or medication</td></tr>
    <tr><td>Make hair look thicker by keeping more of it intact</td><td>Change how fast hair grows</td></tr>
  </tbody>
</table>
<p>If your hair is coming out in handfuls or in patches, no bottle on any shelf in Pakistan will fix it. <strong>See a doctor and get a blood test.</strong> Any seller who tells you otherwise has told you something useful about themselves.</p>

<h2>Six things to check on the label</h2>
<ol>
  <li><strong>Every ingredient named.</strong> "Herbal extracts", "ayurvedic blend" and "secret formula" are not ingredient lists. If you have any allergy, this is not optional.</li>
  <li><strong>Mineral oil position.</strong> Mineral oil is not dangerous, but it is cheap filler. If it is first on the list, you are buying mostly filler at herbal-oil prices.</li>
  <li><strong>Allergens called out.</strong> Almond, coconut and sesame are the common ones in Pakistani hair oils. Almond is a tree nut.</li>
  <li><strong>Batch number and expiry</strong>, printed and legible — not a sticker over the old date.</li>
  <li><strong>Seal intact.</strong> Oils are easy to dilute and re-bottle.</li>
  <li><strong>The claims.</strong> The fewer the better. "Stops hair fall in 7 days" is a warning, not a feature.</li>
</ol>

<h2>What a fair price looks like</h2>
<table>
  <thead><tr><th>Size</th><th>Typical range</th><th>Roghan-e-Jarain</th></tr></thead>
  <tbody>
    <tr><td>100 ml</td><td>Rs 700 – 2,000</td><td>Rs 1,200</td></tr>
    <tr><td>200 ml</td><td>Rs 1,300 – 3,000</td><td>Rs 2,000</td></tr>
  </tbody>
</table>
<p>Compare per millilitre, not per bottle. And treat a price far below the range with suspicion rather than delight — the cheapest bottle is usually the most diluted.</p>

<h2>How to actually use it</h2>
<ol>
  <li><strong>Warm slightly</strong> between your palms.</li>
  <li><strong>Scalp first</strong>, fingertips not nails, five minutes in slow circles. Do not rub lengths against each other — that is where breakage comes from.</li>
  <li><strong>Leave 30–60 minutes.</strong> Overnight is fine but adds less than people think.</li>
  <li><strong>Shampoo on dry hair first</strong>, then water. This removes oil in one wash instead of three, which is what dries the scalp out.</li>
  <li><strong>Once or twice a week.</strong> More is not better.</li>
</ol>

<h2>Who a herbal hair oil is not for</h2>
<ul>
  <li>Anyone with a <strong>nut, coconut or sesame allergy</strong> who cannot see the full list.</li>
  <li>Anyone with <strong>open sores, active scalp acne or severe flaking</strong> — see a doctor first.</li>
  <li>Anyone expecting regrowth. Read the first table again.</li>
  <li>People with very oily scalps who want daily use — oil the lengths, not the roots.</li>
</ul>

<h2>Ours, and its honest limits</h2>
<p><a href="/products/roghan-e-jarain-hair-oil"><strong>Roghan-e-Jarain</strong></a> — coconut oil, sweet almond oil, kalonji oil, vitamin D. Four ingredients, all named, nothing else. Rs 1,200 for 100 ml and Rs 2,000 for 200 ml, Cash on Delivery across Pakistan, delivery Rs 300 flat.</p>
<p>Applying our own checklist honestly: it passes items 1 to 6, and <strong>it contains almond — a tree nut — and coconut</strong>, so it fails item 3 as a product choice for anyone with those allergies. Please buy something else if that is you.</p>
<p>More on the ingredients in <a href="/blog/coconut-almond-kalonji-oil-for-hair">the coconut vs almond vs kalonji comparison</a>.</p>

<h2>FAQ</h2>
<h3>Is herbal hair oil better than a branded shampoo-brand oil?</h3>
<p>Not automatically. What matters is what is in it and how much filler it carries — not whether the word "herbal" is on the front.</p>
<h3>How long before I see a difference?</h3>
<p>Less breakage is usually noticeable in three to four weeks. Anything promising results in days is describing marketing, not hair.</p>
<h3>Can men and women use the same oil?</h3>
<p>Yes. There is no meaningful difference; the packaging is the only thing that is gendered.</p>

<p><em>General information, not medical advice. Sudden, patchy or rapid hair loss needs a doctor.</em></p>
HTML;
    }

    // =====================================================================
    private function rootsUr(): string
    {
        return <<<'HTML'
<p class="answer-box"><strong>Balon ki jarain mazboot karne ka sab se bara faida champi se nahi, <em>tootna kam karne</em> se aata hai — sahi tarah kanghi, geele baalon ko na ragarna, aur tight hairstyle chhorna.</strong> Hafte mein 1–2 baar 5 minute ki champi is ke saath kaam karti hai. Roghan-e-Jarain Rs 1,200 (100 ml), COD.</p>

<h2>Pehle ye samjhein: "baal girna" do alag cheezein hain</h2>
<table>
  <thead><tr><th>Tootna (breakage)</th><th>Jar se girna (shedding)</th></tr></thead>
  <tbody>
    <tr><td>Baal beech se toot-ta hai, sira safed nahi hota</td><td>Poora baal nikalta hai, sire par chhoti safed gaanth</td></tr>
    <tr><td>Wajah: kanghi, garmi, rang, tight bandhna, khushki</td><td>Wajah: sehat, hormone, khoon ki kami, dawa, stress</td></tr>
    <tr><td><strong>Tel aur tarika yahan kaam karte hain</strong></td><td><strong>Yahan doctor aur test chahiye</strong></td></tr>
  </tbody>
</table>
<p>Ghar par dekhein: gire hue baal ke sire par chhoti safed gaanth hai? Woh jar se gira hai. Nahi hai? Woh toota hai. <strong>Zyada tar logon ka masla tootna hota hai</strong> — aur wohi theek bhi ho sakta hai.</p>

<h2>Champi ka sahi tarika</h2>
<ol>
  <li><strong>Tel halka garam</strong> — haathon mein ragar kar.</li>
  <li><strong>Ungliyon ke poray, nakhun nahi.</strong> Nakhun sar ki jild kharab karte hain.</li>
  <li><strong>5 minute, golayi mein, halka dabao.</strong> Zor lagane ka koi faida nahi.</li>
  <li><strong>Baalon ko aapas mein na ragrein</strong> — yehi woh galti hai jis se champi ke baad zyada baal nikalte hain, aur log samajhte hain tel ne nuqsan kiya.</li>
  <li><strong>30–60 minute chhorein</strong>, phir sookhe baalon par shampoo, phir pani.</li>
  <li><strong>Hafte mein 1–2 baar.</strong> Rozana tel se sar ki jild par masaam band ho sakte hain.</li>
</ol>

<h2>Paanch galtiyan jo tel se zyada nuqsan deti hain</h2>
<ul>
  <li><strong>Geele baalon mein kanghi.</strong> Geela baal sab se kamzor hota hai. Pehle halka sukhne dein, phir chauri dandane wali kanghi, sire se shuru kar ke upar.</li>
  <li><strong>Tight joora ya choti</strong> rozana. Jaron par musalsal khinchao asli nuqsan deta hai.</li>
  <li><strong>Rozana garam blow-dry ya straightener</strong> bina kisi bachao ke.</li>
  <li><strong>Har roz shampoo</strong>, khaas kar sulphate wala, khushk baalon par.</li>
  <li><strong>Tauliye se zor se ragarna.</strong> Daba kar pani nikalein, ragrein nahi — soti kapra behtar hai.</li>
</ul>

<h2>Ghiza — jo waqai maayne rakhti hai</h2>
<p>Baal protein se bane hain aur khoon ki kami un par sab se pehle asar dikhati hai. Khaas kar khawateen mein <strong>iron ki kami</strong> baal girne ki aam wajah hai, aur ye sirf test se pata chalti hai.</p>
<p>Rozana ki ghiza mein daal, anda, gosht ya machli, sabz patte wali sabziyan, aur khushk mewa — ye kisi bhi tel se zyada asar rakhte hain. <strong>Supplement khud se shuru na karein</strong>; zaroorat se zyada iron nuqsan deta hai. Pehle test.</p>

<h2>Kab tel band aur doctor</h2>
<ul>
  <li>Baal <strong>chakkiyon (patches) mein</strong> gir rahe hon.</li>
  <li><strong>Mutthi bhar</strong> baal rozana nikal rahe hon.</li>
  <li>Sar ki jild par <strong>dane, zakham ya shadeed khujli</strong>.</li>
  <li>Saath mein <strong>thakawat, wazan ka farq, ya mahwari ki be-qaidgi</strong> — ye thyroid ya khoon ki kami ki alamat ho sakti hain.</li>
</ul>
<p>In mein se kuch bhi ho to tel ka koi kirdar nahi. <strong>Test karwayein.</strong></p>

<h2>Hamara tel</h2>
<p><a href="/products/roghan-e-jarain-hair-oil"><strong>Roghan-e-Jarain</strong></a> — nariyal ka tel, roghan-e-badam, kalonji ka tel, vitamin D. Chaar ajza, sab likhe huay. Rs 1,200 (100 ml) / Rs 2,000 (200 ml), poore Pakistan COD.</p>
<p><strong>Is mein badam (giri) aur nariyal hai</strong> — allergy ho to na lein.</p>
<p>Aur ye hum saaf keh dete hain: <strong>ye tel ganje hissay par baal nahi ugata aur mardana ganjapan nahi palat sakta.</strong> Ye tootna kam karta hai aur sar ki khushk jild ko naram karta hai. Yehi iska asal size hai.</p>

<p><em>Ye aam maloomat hain, tibbi mashwara nahi.</em></p>
HTML;
    }
}
