<?php

use App\Models\Product;
use Illuminate\Database\Migrations\Migration;

/**
 * Roman Urdu content for the two Lookman-e-Hayat PDPs — ships as a migration
 * (same pattern as 2026_08_13 lookmaan variant content) so production receives
 * it through the ordinary deploy + migrate path.
 *
 * Keyword contract (SERP-researched, collision-checked against all 9 existing
 * primaries): 100ml owns "lookman e hayat tel 100ml online order", 50ml owns
 * "lookman e hayat tel 50ml qeemat". "Tel" vs the English pages' "oil" is the
 * deliberate language split; "asli", "jodon", "jalne", "fayde" and
 * "price in pakistan" stay OUT of these titles/H1s — those belong to blog posts.
 *
 * Compliance: the manufacturer's own marketing ("treatment of burns…piles and
 * paralysis", "even if dropped in eyes…no harm") is deliberately NOT repeated —
 * traditional-use framing + pharmacy-style cautions only, per the house rules.
 *
 * Idempotent: fills only products whose slug_ur is still blank, so re-running
 * (or an owner who already wrote UR content in admin) is never overwritten.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->fill('herbal-skin-oil-100ml', [
            'name_ur' => 'Lookman e Hayat Tel — 100ml, Online Order',
            'slug_ur' => 'lookman-e-hayat-tel-100ml',
            'meta_title_ur' => 'Lookman e Hayat Tel 100ml Online Order – Ghar Bethe Mangwayen',
            'meta_description_ur' => 'Asli Lookman e Hayat Tel 100ml Pakistan bhar mein cash on delivery par mangwayen. Til (sesame) 97% + guggul 3%, sealed imported pack, Karachi se dispatch.',
            'short_description_ur' => 'Asli Lookman e Hayat Tel 100ml — til (sesame) 97% + guggal 3%. Riwayati maalish ka tel, sealed imported pack, poore Pakistan mein Cash on Delivery. Sirf bairooni istemal.',
            'description_ur' => <<<'HTML'
<h2>Lookman e Hayat Tel kya hai?</h2>
<p>Lookman e Hayat Tel aik riwayati jari-bootiyon wala tel hai jo 1974 se M.U. Amrelia (Mumbai, India) banata aa raha hai — manufacturer apne taaruf mein ise "the priceless gift of India" kehta hai. Formula sirf do cheezon ka hai: <strong>til (sesame) ka tel 97%</strong> aur <strong>guggal (Commiphora mukul) 3%</strong>. Na koi mineral oil, na artificial khushbu, na rang declare kiya gaya hai — poori ingredient list isi page par published hai.</p>
<p>South Asia mein ye tel naslon se maalish aur rozmarra ki jild ki dekh-bhaal ke liye istemal hota aa raha hai — khaas kar purane, mukammal theek ho chuke nishanon par narmi se rozana maalish ke liye. Ye aik riwayati cosmetic tel hai, koi dawa nahi.</p>
<h2>Online order — ghar bethe mangwayen</h2>
<p>Poore Pakistan mein <strong>Cash on Delivery</strong>: order karein, parcel ghar aaye, seal khud check karein, phir paise dein. Har bottle sealed imported pack mein Karachi se dispatch hoti hai — batch number aur expiry pack par printed hoti hai.</p>
<p>100ml bara pack rozana istemal ke liye behtar value hai. Pehli baar try karna ho to <a href="/ur-roman/products/lookman-e-hayat-tel-50ml">50ml chota pack</a> bhi maujood hai.</p>
<h2>Log ise kin kaamon ke liye istemal karte hain?</h2>
<ul>
<li>Jism aur pathon (muscles) ki maalish ke liye</li>
<li>Purane, mukammal theek ho chuke nishanon par rozana halki maalish — sabar zaroori hai, aur natijay har jild par mukhtalif hote hain</li>
<li>Rukhi (dry) jild ko narm rakhne ke liye</li>
<li>Baalon aur scalp ki <a href="/ur-roman/blog/baalon-mein-tel-lagane-ka-tarika">champi</a> ke liye</li>
</ul>
<p>Mazeed parhein: <a href="/ur-roman/blog/jodon-ke-dard-ka-tel">jodon ke dard ke liye riwayati tel ka istemal</a> aur <a href="/ur-roman/blog/asli-lookman-e-hayat-tel-kahan-se-lein">asli Lookman e Hayat Tel kahan se lein</a>.</p>
<h2>Zaroori ehtiyat</h2>
<ul>
<li>Taza jale, kate ya khule zakham par kabhi na lagayen — pehle 10–20 minute thanda paani daalein aur doctor se rujoo karein.</li>
<li>Aankhon aur munh se door rakhein. Sirf bairooni (external) istemal ke liye hai.</li>
<li>Pehli dafa istemal se pehle inner forearm par patch test karein aur 24 ghante intezar karein.</li>
<li>Bachon ki pohanch se door, thandi aur khushk jagah par rakhein. Pack par likhi expiry check karein.</li>
</ul>
<p><em>Ye aik riwayati herbal tel hai, koi dawa nahi — hum kisi bimari ke ilaj ka koi dawa nahi karte. Sehat ke kisi bhi masle ke liye doctor se mashwara karein.</em></p>
HTML,
            'how_to_use_ur' => <<<'HTML'
<ul>
<li>Thora sa tel haath mein le kar halka sa garm karein.</li>
<li>Jild par narmi se maalish karein jab tak jazb na ho jaye.</li>
<li>Purane nishanon ke liye rozana istemal aur sabar zaroori hai — hafton ka mamla hai, dinon ka nahi.</li>
<li>Baalon ke liye: scalp par maalish karein, 30–60 minute chhor kar dho lein.</li>
<li>Manufacturer ki hidayat ke mutabiq din mein 2–3 dafa tak istemal ho sakta hai.</li>
</ul>
HTML,
            'faqs_ur' => [
                ['q' => 'Lookman e Hayat Tel 100ml online kaise order karein?', 'a' => 'Isi page par size select kar ke order karein, ya WhatsApp 0301 2973886 par apna size aur address bhej dein. Poore Pakistan mein Cash on Delivery — parcel haath mein aane par paise dein.'],
                ['q' => 'Kya ye asli (original) tel hai?', 'a' => 'Ji haan — ye M.U. Amrelia (Mumbai, India) ka banaya hua asli Lookman e Hayat Tel hai, sealed imported pack mein. Pack par batch number aur expiry printed hoti hai, aur COD par aap parcel kholne se pehle seal khud check kar sakte hain.'],
                ['q' => 'Is mein kya kya shamil hai?', 'a' => 'Sirf do ajza: til (sesame) ka tel 97% aur guggal (Commiphora mukul) 3% — manufacturer ki declared composition. Koi mineral oil, artificial khushbu ya rang declare nahi kiya gaya.'],
                ['q' => 'Kya taza jale par laga sakte hain?', 'a' => 'Bilkul nahi. Taza jale par pehle 10–20 minute thanda paani daalein aur doctor ke paas jayen. Ye tel sirf purane, mukammal theek ho chuke nishanon aur sehatmand jild ke liye hai.'],
                ['q' => 'Kya ye koi dawa hai?', 'a' => 'Nahi — ye aik riwayati herbal tel hai, sirf bairooni istemal ke liye. Ye kisi bimari ka ilaj nahi aur doctor ke ilaj ka mutabadil bhi nahi.'],
                ['q' => 'Delivery kitne din mein hoti hai?', 'a' => 'Order confirm hone ke baad aam tor par 2–7 working days mein poore Pakistan mein delivery ho jati hai — Karachi se dispatch, Cash on Delivery.'],
            ],
        ]);

        $this->fill('herbal-skin-oil-50ml', [
            'name_ur' => 'Lookman e Hayat Tel — 50ml, Chota Pack',
            'slug_ur' => 'lookman-e-hayat-tel-50ml',
            'meta_title_ur' => 'Lookman e Hayat Tel 50ml Qeemat – Chota Pack, Order Karein',
            'meta_description_ur' => 'Lookman e Hayat Tel ka 50ml chota pack Rs 1,200 mein — pehli baar try karne ke liye behtareen size. Cash on delivery, sealed pack, poore Pakistan mein delivery.',
            'short_description_ur' => 'Lookman e Hayat Tel ka 50ml chota pack — Rs 1,200. Try karne ke liye behtareen size. Til 97% + guggal 3%, sealed pack, Cash on Delivery poore Pakistan mein.',
            'description_ur' => <<<'HTML'
<h2>Lookman e Hayat Tel 50ml — qeemat aur size</h2>
<p>50ml chote pack ki qeemat <strong>Rs 1,200</strong> hai — pehli baar try karne, ya kisi ek choti jagah par rozana istemal ke liye sab se munasib size. Bara <a href="/ur-roman/products/lookman-e-hayat-tel-100ml">100ml pack Rs 2,200</a> mein hai, jo rozana istemal par per-ml sasta parta hai.</p>
<p>Ye wohi asli tel hai jo M.U. Amrelia (Mumbai, India) 1974 se bana raha hai: <strong>til (sesame) ka tel 97%</strong> aur <strong>guggal (Commiphora mukul) 3%</strong> — bas yehi do ajza. Sealed imported pack, batch number aur expiry printed.</p>
<h2>Chota pack kis ke liye behtar hai?</h2>
<ul>
<li>Pehli dafa istemal — patch test aur apni jild ka radd-e-amal dekhne ke liye</li>
<li>Chehre ya kisi ek purane nishan par rozana halki maalish ke liye</li>
<li>Safar mein sath rakhne ke liye</li>
</ul>
<p>Rozana maalish ya baalon ki <a href="/ur-roman/blog/baalon-mein-tel-lagane-ka-tarika">champi</a> ke liye 100ml pack behtar value deta hai.</p>
<h2>Zaroori ehtiyat</h2>
<ul>
<li>Taza jale, kate ya khule zakham par kabhi na lagayen — pehle 10–20 minute thanda paani, phir doctor se rujoo karein.</li>
<li>Aankhon aur munh se door rakhein. Sirf bairooni (external) istemal.</li>
<li>Pehli dafa inner forearm par patch test karein, 24 ghante intezar karein.</li>
<li>Bachon ki pohanch se door, thandi khushk jagah par rakhein.</li>
</ul>
<p><em>Ye aik riwayati herbal tel hai, koi dawa nahi. Sehat ke kisi bhi masle ke liye doctor se mashwara karein.</em></p>
HTML,
            'how_to_use_ur' => <<<'HTML'
<ul>
<li>Chand qatre haath mein le kar halka garm karein.</li>
<li>Jild par narmi se maalish karein jab tak jazb na ho.</li>
<li>Chehre par bohat kam miqdar, raat ko — aankhon se door.</li>
<li>Purane nishanon par rozana, sabar ke sath — natijay har jild par mukhtalif.</li>
</ul>
HTML,
            'faqs_ur' => [
                ['q' => 'Lookman e Hayat Tel 50ml ki qeemat kya hai?', 'a' => 'Rs 1,200 — Cash on Delivery poore Pakistan mein. 100ml pack Rs 2,200 ka hai, jo rozana istemal par per-ml behtar value deta hai.'],
                ['q' => '50ml ya 100ml — kaunsa pack lein?', 'a' => 'Pehli baar try kar rahe hain ya kisi ek choti jagah ke liye chahiye to 50ml kaafi hai. Rozana maalish, champi ya poore jism ke liye 100ml sasta parta hai.'],
                ['q' => 'Order kaise hoga?', 'a' => 'Isi page se order karein, ya WhatsApp 0301 2973886 par "50ml" aur apna address likh kar bhej dein. Parcel aane par paise dein — pehle seal check kar lein.'],
                ['q' => 'Kya taza jale ya zakham par laga sakte hain?', 'a' => 'Nahi, kabhi nahi. Taza jale par pehle 10–20 minute thanda paani daalein aur doctor ke paas jayen. Ye tel sirf purane, theek ho chuke nishanon aur sehatmand jild ke liye hai.'],
                ['q' => 'Kya chehre par istemal ho sakta hai?', 'a' => 'Ji, bohat thori miqdar mein — pehle patch test karein, aankhon se door rakhein, aur sirf sehatmand jild par lagayen. Oily ya acne-prone jild par kam istemal karein aur koi reaction ho to rok dein. Ye acne ka ilaj nahi hai.'],
            ],
        ]);
    }

    public function down(): void
    {
        foreach (['herbal-skin-oil-100ml', 'herbal-skin-oil-50ml'] as $slug) {
            Product::withoutEvents(fn () => Product::query()->where('slug', $slug)->update([
                'name_ur' => null, 'slug_ur' => null, 'short_description_ur' => null,
                'description_ur' => null, 'how_to_use_ur' => null, 'faqs_ur' => null,
                'meta_title_ur' => null, 'meta_description_ur' => null,
            ]));
        }
    }

    /** @param array<string, mixed> $content */
    private function fill(string $slug, array $content): void
    {
        $product = Product::query()->where('slug', $slug)->first();

        // Prod-safe guards: product may not exist on a fresh install, and an
        // owner-written UR page (slug_ur already set) is never overwritten.
        if (! $product || filled($product->slug_ur)) {
            return;
        }

        // faqs_ur stays a raw array — the model's 'array' cast does the JSON
        // encoding exactly once (hand-encoding here double-encodes it).

        // Quiet update: no observers, no touched updated_at side effects beyond
        // the ordinary timestamp — this is content, not a catalogue change.
        Product::withoutEvents(fn () => $product->forceFill($content)->save());
    }
};
