<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Illuminate\Database\Seeder;

/**
 * Roman-Urdu (ur-Latn) TWINS of the three WinnableBlogSeeder English posts.
 * Each shares the SAME translation_group_id as its English counterpart, so the
 * bilingual foundation pairs them via reciprocal hreflang (en <-> ur-Latn).
 *
 * ANTI-CANNIBALIZATION: each twin targets a DISTINCT Roman-Urdu primary keyword
 * (never a translation of the English phrase), because Google often reads Roman
 * Urdu as English and would otherwise make the pair compete:
 *   - price   EN "price in pakistan / where to buy" -> UR "asli ... tel kahan se lein"
 *   - champi  EN "how to use herbal oil for hair"    -> UR "baalon mein tel lagane ka tarika"
 *   - guide   EN "best halal herbal oils"            -> UR "behtareen halal herbal tel"
 *
 * Genuine Pakistani Roman Urdu (not transliterated English, not machine
 * translation). DEPLOY-SAFE: firstOrCreate by slug. Health-safe: cosmetic /
 * traditional-use framing only, no cure claims, no "halal certified" claim.
 */
class WinnableBlogUrduSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->articles() as $article) {
            BlogPost::firstOrCreate(
                ['slug' => $article['slug']],
                [
                    'title' => $article['title'],
                    'locale' => 'ur-Latn',
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
                // pairs with WinnableBlogSeeder::GROUP_PRICE
                'translation_group_id' => 'c3f6d4e5-2f70-4b8c-a0d9-1f2a3b4c5d6e',
                'title' => 'Asli Lookman e Hayat Tel Kahan Se Lein — Qeemat',
                'slug' => 'asli-lookman-e-hayat-tel-kahan-se-lein',
                'excerpt' => 'Asli Lookman e Hayat tel kahan se lein Pakistan mein? Qeemat: 50ml Rs 1,800, 100ml Rs 3,000. Cash on Delivery poore Pakistan mein Glow Halal se, ghar par pay karein.',
                'reading_time_minutes' => 4,
                'content' => $this->priceUr(),
            ],
            [
                // pairs with WinnableBlogSeeder::GROUP_HAIR_CHAMPI
                'translation_group_id' => 'd4a7e5f6-3081-4c9d-b1e0-2a3b4c5d6e7f',
                'title' => 'Baalon Mein Tel Lagane Ka Tarika: Champi Guide',
                'slug' => 'baalon-mein-tel-lagane-ka-tarika',
                'excerpt' => 'Baalon mein tel lagane ka sahih tarika (champi): step-by-step sar ki maalish, kitni baar lagayein, patch test aur kaunsa size lein — asaan Roman Urdu guide.',
                'reading_time_minutes' => 5,
                'content' => $this->hairChampiUr(),
            ],
            [
                // pairs with WinnableBlogSeeder::GROUP_HALAL_OILS
                'translation_group_id' => 'e5b8f617-4192-4d0e-92f1-3b4c5d6e7f80',
                'title' => 'Pakistan Mein Behtareen Halal Herbal Tel',
                'slug' => 'behtareen-halal-herbal-tel-pakistan',
                'excerpt' => 'Pakistan mein behtareen halal herbal tel kaise chunein: "halal herbal" ka matlab, bharosemand tel ki nishaniyan, aur Cash on Delivery par honest kharidari.',
                'reading_time_minutes' => 6,
                'content' => $this->halalOilsUr(),
            ],
        ];
    }

    private function priceUr(): string
    {
        return <<<'HTML'
<div class="article-answer-box">
  <p>Glow Halal par Lookman e Hayat tel ki qeemat 50ml ki Rs 1,800 aur 100ml ki Rs 3,000 hai (2026). Asli tel aap online Cash on Delivery par poore Pakistan mein manga sakte hain, ya WhatsApp par — aur paisay ghar par parcel milne par dete hain.</p>
</div>

<h2>Lookman e Hayat tel ki qeemat kya hai?</h2>
<p>Glow Halal par abhi qeemat hai: <strong>50ml bottle Rs 1,800</strong> aur <strong>100ml bottle Rs 3,000</strong>. Dono mein wohi ek herbal tel hai — bari bottle sirf per ml sasti parti hai. Qeemat waqt ke saath badal sakti hai, is liye jo qeemat product page par live likhi ho wohi asal maani jaye.</p>

<h2>50ml ya 100ml — kaunsi behtar value hai?</h2>
<table>
  <thead>
    <tr><th>Size</th><th>Qeemat</th><th>Per ml</th><th>Kis ke liye</th></tr>
  </thead>
  <tbody>
    <tr><td>50ml</td><td>Rs 1,800</td><td>Rs 36 / ml</td><td>Pehli baar try karne ya kabhi kabhaar istemal</td></tr>
    <tr><td>100ml</td><td>Rs 3,000</td><td>Rs 30 / ml</td><td>Rozana istemal — per ml sasti, ziyada chalti hai</td></tr>
  </tbody>
</table>
<p>Pehli baar? <a href="/products/herbal-skin-oil-50ml">50ml bottle (Rs 1,800)</a> se shuru karein. Agar rozana istemal karte hain to <a href="/products/herbal-skin-oil-100ml">100ml bottle (Rs 3,000)</a> per ml sasti parti hai. Dono <a href="/shop/oils">herbal oils shop</a> par maujood hain.</p>

<h2>Asli Lookman e Hayat tel kahan se lein?</h2>
<p>Dono size aap seedha Glow Halal se Cash on Delivery par le sakte hain — Karachi, Lahore, Islamabad se le kar chhote shehron tak — ya WhatsApp par message karein, hum minton mein arrange kar dete hain. Seedha seller se lene ka faida ye hai ke jo qeemat dikhti hai wohi deni parti hai, darwazay par koi surprise nahi.</p>

<h2>Achhi quality ka tel kaise pehchanein?</h2>
<p>Kuch aasan cheezein dhyan mein rakhein:</p>
<ul>
  <li>Seller <strong>ingredient ki maloomat batata ho</strong> — pata ho ke bottle mein kya hai.</li>
  <li>Saaf <strong>bahri istemal (external use) ka safety note</strong> ho, na ke miracle ke daawon se bhara page.</li>
  <li><strong>Cash on Delivery</strong> ho, taake seal bandh bottle pehle milay, paisay baad mein.</li>
  <li>Koi asli insaan raabtay ke liye ho (hamare liye WhatsApp par).</li>
</ul>
<p>Aisi kisi listing se bachein jo kahe ke tel kisi bimari ka ilaj karta hai — herbal cosmetic tel aaram ke liye hai, dawai nahi.</p>

<h2>Cash on Delivery kaise kaam karti hai?</h2>
<p>Product page par order karein ya WhatsApp message bhejein, hum address confirm karte hain, courier ghar par pohanchata hai, aur aap cash on delivery par pay karte hain. Delivery poore Pakistan mein hai, aur Rs 5,000 se upar ke order par free — jodon ke dard aur baalon wale mazameen ke liye bhi hamari <a href="/ur-roman/blog/jodon-ke-dard-ka-tel">jodon ke dard ka tel</a> guide dekhein.</p>

<h2>Aam sawal jawab</h2>
<h3>50ml ki qeemat kya hai?</h3>
<p>50ml bottle Rs 1,800 hai, Cash on Delivery ke saath poore Pakistan mein.</p>
<h3>100ml ki qeemat kya hai?</h3>
<p>100ml bottle Rs 3,000 hai — per ml sasti aur Rs 5,000+ order par free delivery.</p>
<h3>Kya poore Pakistan mein delivery hai?</h3>
<p>Ji haan. Cash on Delivery poore mulk mein — paisay tab dein jab parcel aap tak pohanche.</p>
<h3>Kya WhatsApp par order kar sakte hain?</h3>
<p>Ji haan. Humein message karein, hum aap ka order laga kar delivery confirm kar denge.</p>

<h2>Order kaise karein (Cash on Delivery)</h2>
<p>Online Cash on Delivery par poore Pakistan mein order karein, ya <a href="https://wa.me/923012973886">WhatsApp par baat karein</a> — seconds mein order aur ghar par pay.</p>

<p class="article-disclaimer"><em>Notice: Lookman e Hayat tel ek herbal cosmetic product hai bahri istemal ke liye, dawai nahi. Ye kisi bimari ki tashkhees, ilaj ya rokthaam ke liye nahi hai. Qeemat likhne ke waqt ki hai aur badal sakti hai — product page ki live qeemat lagu hoti hai. Bachon ki pohanch se door rakhein aur aankhon se bachayein.</em></p>
HTML;
    }

    private function hairChampiUr(): string
    {
        return <<<'HTML'
<div class="article-answer-box">
  <p>Baalon mein tel lagane ka sahih tarika: thora tel garam karein, baalon ki maang nikaal kar sar ki jild par halke gol haath se 5–10 minute maalish karein (champi). 30–60 minute ya raat bhar laga rehne dein, phir hasb-e-mamool dho lein. Pehle patch test karein aur hafte mein ek-do baar lagayein.</p>
</div>

<h2>Champi kya hai aur log baalon mein tel kyun lagate hain?</h2>
<p>Champi wohi rivayati sar aur baalon ki tel maalish hai jo Pakistan aur poore barr-e-sagheer mein naslon se hoti aa rahi hai. Log tel is liye lagate hain kyunke ye sukoon deti hai, baalon ko conditioning deti hai, aur baal naram, chamakdaar aur behtar mehsoos hote hain. Ye ek self-care aur grooming routine hai — cosmetic aadat, koi medical ilaj nahi.</p>

<h2>Baalon mein tel lagane ka step-by-step tarika</h2>
<ol>
  <li><strong>Pehle patch test:</strong> pehli baar hai to baazu par thora tel laga kar kuch ghante dekh lein.</li>
  <li><strong>Tel garam karein:</strong> thora tel haathon ke darmiyan rageir lein, ya band bottle ek minute garam pani mein rakh lein.</li>
  <li><strong>Baalon ko hisson mein baantein</strong> taake sar ki jild tak haath pohanche.</li>
  <li><strong>Sar ki maalish</strong> halke gol haath se 5–10 minute karein.</li>
  <li><strong>Baqi tel lambai mein</strong> siron tak lagayein, jahan baal sab se khushak hote hain.</li>
  <li><strong>Laga rehne dein</strong> 30–60 minute — ya raat bhar takiye par tauliya rakh kar.</li>
  <li><strong>Dho lein</strong> halke shampoo se; do halke round lag sakte hain.</li>
</ol>

<h2>Hafte mein kitni baar tel lagana chahiye?</h2>
<p>Ziyada tar baalon ke liye hafte mein ek ya do baar theek hai. Agar sar qudrati taur par chikna hai to hafte mein ek baar ya us se bhi kam kaafi hai. Ziyada lagane ka koi faida nahi — sirf dhone mein mehnat barhti hai.</p>

<h2>Kya khushak ya uljhe baal behtar dikh sakte hain?</h2>
<p>Rozana champi baalon ke resháy ko conditioning deti hai, jis se baal naram, chamakdaar aur kam khushak lag sakte hain, aur ultay-seedhe baal kaabu mein aate hain. Maalish khud din bhar ki thakan ke baad sukoon deti hai. Ye cosmetic, dikhne aur mehsoos hone wale faide hain. Champi baalon ke jharne, khushki (dandruff) ya sar ki kisi bimari ka ilaj nahi — agar musalsal baal jharein, sar mein kharish/khushki ho ya khali jagah ho to kisi tel par bharosa karne ke bajaye doctor ya skin specialist se milein.</p>

<h2>Kaunsa tel aur size chunein?</h2>
<p>Lookman e Hayat tel ki base halki <strong>til (sesame)</strong> hai — jo baalon aur sar ki maalish ke liye sadyon se istemal hoti aa rahi hai — is liye ye bhaari mehsoos kiye baghair jazb ho jata hai. Size apni zaroorat ke hisab se lein:</p>
<table>
  <thead>
    <tr><th>Size</th><th>Qeemat</th><th>Kis ke liye</th></tr>
  </thead>
  <tbody>
    <tr><td>50ml</td><td>Rs 1,800</td><td>Champi try karne, chhote baal ya kabhi kabhaar</td></tr>
    <tr><td>100ml</td><td>Rs 3,000</td><td>Lambe baal ya poore ghar ki hafta-waar champi — per ml sasti</td></tr>
  </tbody>
</table>
<p><a href="/products/herbal-skin-oil-50ml">50ml bottle (Rs 1,800)</a> se shuru karein, ya rozana lagate hain to <a href="/products/herbal-skin-oil-100ml">100ml bottle (Rs 3,000)</a> lein. Poori range <a href="/shop/oils">herbal oils shop</a> par dekhein.</p>

<h2>Aam sawal jawab</h2>
<h3>Kya tel raat bhar laga rehne de sakte hain?</h3>
<p>Ji haan. Takiye par tauliya rakh lein, subah halke shampoo se baal dho lein.</p>
<h3>Kya is se baal barhein ge ya jharna ruk jayega?</h3>
<p>Ye cosmetic conditioning tel hai, dawai nahi, is liye baalon ke jharne ka ilaj nahi karta. Baal behtar dikh aur mehsoos ho sakte hain. Asal baal jharne/patlay hone ke liye skin specialist se milein.</p>
<h3>Chikne sar ya dandruff par theek hai?</h3>
<p>Thora sa aur kam baar lagayein. Musalsal dandruff ya kharish wali khushki ke liye tel ke bajaye doctor ko dikhayein.</p>
<h3>Kitna tel istemal karun?</h3>
<p>Thora hi kaafi hota hai — chhote baalon ke liye taqreeban ek chammach, lambe ghane baalon ke liye ek bara chammach tak.</p>

<h2>Order kaise karein (Cash on Delivery)</h2>
<p>Online Cash on Delivery par poore Pakistan mein order karein, ya <a href="https://wa.me/923012973886">WhatsApp par baat karein</a> — seconds mein order aur ghar par pay.</p>

<p class="article-disclaimer"><em>Notice: Lookman e Hayat tel ek herbal cosmetic tel hai bahri istemal ke liye, dawai nahi. Ye baalon ke jharne ya sar ki kisi bimari samet kisi cheez ki tashkhees, ilaj ya rokthaam ke liye nahi hai. Pehle patch test karein, aankhon aur tooti jild se bachayein, aur kisi musalsal baal/sar ke masle ke liye doctor ya skin specialist se rujoo karein. Nataij har shakhs mein alag hote hain.</em></p>
HTML;
    }

    private function halalOilsUr(): string
    {
        return <<<'HTML'
<div class="article-answer-box">
  <p>Halal herbal tel ek plant-based (nabaati) tel hai jo bahri istemal ke liye banta hai aur imandari se becha jata hai — ingredients likhe hue, bina barhe-charhe medical daawon ke. Pakistan mein kharidte waqt: ingredient list dekhein, saaf safety note dhoondein, aur aisa seller chunein jo Cash on Delivery de.</p>
</div>

<h2>"Halal herbal tel" ka asal matlab kya hai?</h2>
<p>Hamare liye <strong>"halal" ek soch (ethos) hai, koi certificate nahi</strong>: nabaati ajza, shafaaf labelling, aur imandaar bahri cosmetic istemal. Hamare paas <strong>koi third-party halal certification nahi aur hum aisa koi daawa nahi karte</strong> — is ke bajaye hum ingredient ki maloomat share karte hain taake aap khud parh kar faisla karein. Agar koi seller bina saboot bottle par "halal certified" ki mohar lagaye, to usay yaqeen nahi, shak ki nazar se dekhein.</p>

<h2>Bharosemand halal herbal tel kaise chunein?</h2>
<p>Kharidne se pehle ek aasan checklist:</p>
<ul>
  <li><strong>Ingredients saaf likhe hon</strong> — pata ho bottle mein kya hai.</li>
  <li><strong>Bahri istemal</strong> ka aur patch-test / safety note ho.</li>
  <li>Daawe <strong>haqeeqi</strong> hon — cosmetic tel kisi bimari ka ilaj ka waada na kare.</li>
  <li><strong>Cash on Delivery</strong> ho, taake seal bandh bottle pehle dekhein.</li>
  <li>Koi <strong>asli insaan raabtay ke liye</strong> ho (hamare liye WhatsApp).</li>
  <li>Koi bhi "halal" baat <strong>certification ke baare mein imandaar</strong> ho — soch, na ke bina saboot mohar.</li>
</ul>

<h2>Pakistan mein kaunse herbal tel mashhoor hain?</h2>
<p>Ye wo nabaati tel hain jin ke baare mein log sab se ziyada poochte hain. Har ek rivayati cosmetic tel hai — koi bhi dawai nahi:</p>
<ul>
  <li><strong>Til (sesame) ka tel</strong> — halka carrier tel, jild aur sar ki maalish ke liye purana.</li>
  <li><strong>Kalonji ka tel</strong> — desi gharon mein ek maroof rivayati tel.</li>
  <li><strong>Nariyal (coconut) ka tel</strong> — baalon aur jild ka jaana-pehchana.</li>
  <li><strong>Badam (sweet almond) ka tel</strong> — halka rozana moisturising tel.</li>
  <li><strong>Zaitoon (olive) ka tel</strong> — kitchen se skincare tak classic.</li>
</ul>
<p>Jo bhi chunein, pehle patch test karein aur sehatmand, be-zakhm jild par istemal karein.</p>

<h2>Glow Halal ka hero herbal tel kaunsa hai?</h2>
<p>Hamara mojooda flagship <strong>Lookman e Hayat tel</strong> hai, til (sesame) base wala herbal tel jo rivayati taur par halki maalish aur skin oil ke tor par istemal hota hai. Ye ek barhti hui halal beauty range ka pehla hero product hai — aur halal skincare/cosmetics aa rahe hain. Qeemat aur asli kahan se milega ke liye hamari <a href="/ur-roman/blog/asli-lookman-e-hayat-tel-kahan-se-lein">Roman Urdu qeemat guide</a> dekhein.</p>

<h2>50ml ya 100ml — kaunsa lein?</h2>
<table>
  <thead>
    <tr><th>Size</th><th>Qeemat</th><th>Kis ke liye</th></tr>
  </thead>
  <tbody>
    <tr><td>50ml</td><td>Rs 1,800</td><td>Try karne ya kabhi kabhaar</td></tr>
    <tr><td>100ml</td><td>Rs 3,000</td><td>Rozana istemal — per ml sasti</td></tr>
  </tbody>
</table>
<p><a href="/products/herbal-skin-oil-50ml">50ml (Rs 1,800)</a> ya behtar value wali <a href="/products/herbal-skin-oil-100ml">100ml (Rs 3,000)</a> lein, aur sab kuch <a href="/shop/oils">herbal oils shop</a> par dekhein.</p>

<h2>Aam sawal jawab</h2>
<h3>Kya aap ke tel halal certified hain?</h3>
<p>Hum koi certification ka daawa nahi karte. "Halal" hamari soch hai — nabaati, bahri istemal aur shafaaf labelling — aur hum ingredient ki maloomat share karte hain taake aap khud faisla karein.</p>
<h3>Mere liye kaunsa tel behtar hai?</h3>
<p>Ye is par depend karta hai ke aap kaise istemal karenge. Hamara mojooda hero til-base herbal maalish aur skin oil hai. Jo bhi lein, pehle patch test karein.</p>
<h3>Kya poore Pakistan mein delivery hai?</h3>
<p>Ji haan — Cash on Delivery poore mulk mein, aur WhatsApp par bhi order ho sakta hai.</p>
<h3>Kya aur halal products aa rahe hain?</h3>
<p>Ji haan. Glow Halal ek halal beauty aur cosmetics store hai, aur hum waqt ke saath aur imandaar, nabaati products add kar rahe hain.</p>

<h2>Order kaise karein (Cash on Delivery)</h2>
<p>Online Cash on Delivery par poore Pakistan mein order karein, ya <a href="https://wa.me/923012973886">WhatsApp par baat karein</a> — seconds mein order aur ghar par pay.</p>

<p class="article-disclaimer"><em>Notice: Yahan bataye gaye tel herbal cosmetic products hain bahri istemal ke liye, dawaiyan nahi. Ye kisi bimari ki tashkhees, ilaj ya rokthaam ke liye nahi hain. "Halal" hamari nabaati aur shafaaf soch ko zahir karta hai; hamare paas koi third-party halal certification nahi aur hum aisa koi daawa nahi karte. Hamesha patch test karein, aankhon aur tooti jild se bachayein, aur kisi medical masle ke liye doctor se rujoo karein.</em></p>
HTML;
    }
}
