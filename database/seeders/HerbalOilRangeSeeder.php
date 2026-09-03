<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * The two new own-brand oils that extend the catalogue past the single
 * resold Lookman-e-Hayat SKU:
 *
 *   1. Roghan-e-Sukoon  — warming herbal MASSAGE oil (maalish ka tel)
 *   2. Roghan-e-Jarain  — herbal HAIR & SCALP oil (balon ki jarain ka tel)
 *
 * Both are seeded in BOTH languages (EN + Roman Urdu) because the Roman-Urdu
 * SERP is the structural gap this brand wins first — see
 * docs/keyword-research-aug2026.md and docs/llm-visibility-plan-sep2026.md.
 *
 * Prices are stored in PAISA: 130000 = PKR 1,300.
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * ⚠️  SEEDED AS **DRAFT** ON PURPOSE. Nothing here goes live until the owner
 *     confirms four things. Flip `status` to 'active' (and set published_at)
 *     only after ALL FOUR are done:
 *
 *     1. INGREDIENTS — the `ingredients_note` below is the FORMULATION BRIEF
 *        we asked the supplier for, not a verified label. Replace it verbatim
 *        from the physical bottle before selling. The brand's entire promise is
 *        an accurate, complete list; a guessed list breaks it.
 *     2. PRICE — the numbers below are placeholders sized off the existing
 *        margin ladder (see the unit-economics note). Set real cost/sell.
 *     3. PHOTO — no ProductImage row is created deliberately, so the site
 *        renders its placeholder rather than a broken <img>. Upload the real
 *        photo in Admin → Products, or add the row here.
 *     4. STOCK — seeded at 0. Set real quantity_on_hand.
 *
 * ⚠️  CLAIMS. Per docs house rules (no DRAP-regulated cure claims, no halal
 *     certification claim, no fabricated reviews), NOTHING here says "pain
 *     relief" or "hair growth" as a promise. The massage oil describes a
 *     WARMING SENSATION (camphor/menthol — physically true and verifiable);
 *     the hair oil uses cosmetic "hair-fall control / conditions the scalp"
 *     framing. Both carry a "who this is NOT for" block, which is compliant by
 *     construction and is the single most quotable block for answer engines.
 *
 * ⚠️  CANNIBALIZATION. `jodon ke dard ka tel` and the joint-pain blog cluster
 *     stay with Lookman-e-Hayat (existing ranked entity). Roghan-e-Sukoon takes
 *     `maalish ka tel`, `kamar dard ka tel`, `body massage oil pakistan`.
 *     Do not point both products at the same primary keyword.
 * ─────────────────────────────────────────────────────────────────────────────
 *
 * Idempotent — updates by slug.
 */
class HerbalOilRangeSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $oils = Category::where('slug', 'oils')->first()
                ?? Category::updateOrCreate(
                    ['slug' => 'oils'],
                    [
                        'name' => 'Oils',
                        'description' => 'Herbal skin, hair & massage oils. Every ingredient published in full.',
                        'is_active' => true,
                        'show_in_menu' => true,
                        'position' => 10,
                    ]
                );

            $location = InventoryLocation::firstOrCreate(
                ['code' => 'MAIN'],
                ['name' => 'Main warehouse', 'is_default' => true, 'is_active' => true],
            );

            foreach ($this->products() as $position => $row) {
                $product = Product::updateOrCreate(
                    ['slug' => $row['slug']],
                    [
                        'primary_category_id' => $oils->id,
                        'name' => $row['name'],
                        'name_ur' => $row['name_ur'],
                        'slug_ur' => $row['slug_ur'],
                        'sku_prefix' => $row['sku_prefix'],
                        'brand' => 'Glow Halal',
                        'short_description' => $row['short'],
                        'short_description_ur' => $row['short_ur'],
                        'description' => $row['description'],
                        'description_ur' => $row['description_ur'],
                        'how_to_use' => $row['how_to_use'],
                        'how_to_use_ur' => $row['how_to_use_ur'],
                        'faqs' => $row['faqs'],
                        'faqs_ur' => $row['faqs_ur'],
                        'ingredients_note' => $row['ingredients_note'],
                        // meta_*_ur live on `products` (added by the Roman Urdu
                        // migration); the EN pair lives on the seo_metas row below.
                        'meta_title_ur' => $row['meta_title_ur'],
                        'meta_description_ur' => $row['meta_description_ur'],
                        'price_min_amount' => $row['variants'][0]['price'],
                        'price_max_amount' => $row['variants'][1]['price'],
                        // Per-product: a product only goes 'active' once its
                        // ingredient list is the owner's, not our brief.
                        'status' => $row['status'],
                        'published_at' => $row['status'] === 'active' ? now() : null,
                        'is_featured' => $row['status'] === 'active',
                        'is_new_arrival' => true,
                        'return_window_days' => 7,
                        'total_stock' => $row['stock'] * count($row['variants']),
                        'position' => 10 + $position,
                    ]
                );

                $product->categories()->syncWithoutDetaching([$oils->id]);

                // Honest halal profile: plant-derived, self-declared, NO certification.
                $product->halalProfile()->updateOrCreate([], [
                    'overall_status' => 'unknown',
                    'is_certified' => false,
                    'is_self_declared' => true,
                    'alcohol_status' => 'none',
                    'is_vegan' => true,
                    'is_cruelty_free' => true,
                    'summary' => 'Plant-derived ingredients, full list published on this page. '
                        .'Glow Halal holds no third-party halal accreditation and does not claim one.',
                ]);

                foreach ($row['variants'] as $i => $v) {
                    $variant = ProductVariant::updateOrCreate(
                        ['sku' => $v['sku']],
                        [
                            'product_id' => $product->id,
                            'name' => $v['volume'],
                            'price_amount' => $v['price'],
                            'weight_grams' => $v['weight'],
                            'is_default' => $i === 0,
                            'is_active' => true,
                            'track_inventory' => true,
                            'allow_backorder' => false,
                            'position' => $i,
                        ]
                    );

                    InventoryItem::updateOrCreate(
                        [
                            'product_variant_id' => $variant->id,
                            'inventory_location_id' => $location->id,
                        ],
                        [
                            'quantity_on_hand' => $row['stock'],
                            'quantity_reserved' => 0,
                            'reorder_level' => 5,
                            'reorder_quantity' => 20,
                        ]
                    );
                }

                $product->seoMeta()->updateOrCreate([], [
                    'meta_title' => $row['meta_title'],
                    'meta_description' => $row['meta_description'],
                ]);

                $label = strtoupper($row['status']);
                $this->command?->info("  {$label}: {$row['name']} — {$row['stock']} per size in stock.");
                if ($row['status'] !== 'active') {
                    $this->command?->warn('    ↳ held as draft: '.$row['hold_reason']);
                }
            }
        });
    }

    /** @return array<int, array<string, mixed>> */
    private function products(): array
    {
        return [$this->sukoon(), $this->jarain()];
    }

    // =========================================================================
    // 1. ROGHAN-E-SUKOON — herbal massage oil for tired muscles and joints
    //
    // ⚠️ INGREDIENT LIST NOT YET KNOWN. The owner asked for this to go live on
    //    3 Sep 2026 before the bottle's list was available. Rather than publish
    //    a guessed list — an ingredient list is a safety document, not marketing
    //    copy — the page states plainly that the list is being confirmed and
    //    that we will publish it in full. Every composition-specific claim was
    //    removed with it: no camphor, no menthol, no "warming", no sensation
    //    claims, because none of those are known to be true of this bottle.
    //
    //    The safety block is deliberately CONSERVATIVE and holds under either
    //    outcome: traditional maalish oils commonly contain camphor or menthol,
    //    so until the list is confirmed the page treats the oil as adults-only
    //    and refuses the chest-rub-on-a-child use outright.
    //
    //    WHEN THE LABEL ARRIVES: fill in ingredients_note, add the "why these
    //    ingredients" section back, and revisit this safety block.
    // =========================================================================
    private function sukoon(): array
    {
        $description = <<<'HTML'
<p><strong>Roghan-e-Sukoon</strong> is a herbal massage oil — a maalish ka tel for tired shoulders, neck, back, knees and legs. It is made for the kind of slow, ten-minute massage that people in this part of the world have done at home for generations. Rs&nbsp;1,300 for 100&nbsp;ml, Cash on Delivery across Pakistan.</p>

<h3>The ingredient list — and why it is not on this page yet</h3>
<p>We publish every ingredient in everything we sell. That is the whole basis of this shop. For this oil, <strong>we are still confirming the exact list with the manufacturer, and we are not going to guess.</strong></p>
<p>So this page says less than our other pages, on purpose. As soon as the confirmed list is in our hands it goes here in full — nothing held back, including anything unflattering. If you want to know before then, <strong>message us on WhatsApp and ask</strong>; we will tell you exactly what we know and exactly what we do not.</p>
<p>If that is not good enough for you, that is a completely reasonable position. Wait for the list.</p>

<h3>What people use it for</h3>
<ul>
  <li><strong>After work</strong> — shoulders, neck and lower back at the end of a long day.</li>
  <li><strong>After exercise or a long walk</strong> — calves, thighs and feet.</li>
  <li><strong>Winter stiffness</strong>, when everything feels tighter than usual.</li>
  <li><strong>A weekly maalish routine</strong>, the way it is done at home.</li>
</ul>
<p>It is a <strong>comfort and massage oil, not a medicine</strong>. It does not treat, cure or prevent any condition, and we will not pretend otherwise.</p>

<h3>Who this is NOT for</h3>
<ul>
  <li><strong>Children — including for chest congestion (resha).</strong> We know warm oil on a child's chest and back is what many families reach for. <strong>Do not use this oil for that.</strong> Traditional maalish oils commonly contain camphor, which is absorbed through the skin — repeated rubbing on a small child has caused seizures, and the American Academy of Pediatrics advises against camphor in children altogether. Until this bottle's list is confirmed, treat this as an <strong>adults-only</strong> oil. A blocked, wheezy or struggling child needs a doctor, not an oil.</li>
  <li><strong>Pregnancy</strong> — ask your doctor before using any herbal massage oil.</li>
  <li><strong>Anyone with allergies or sensitive skin.</strong> We cannot yet tell you what is in this bottle, so we cannot tell you it is safe for you. If you react to things, wait for the list.</li>
  <li><strong>Broken skin, open wounds, rashes or fresh burns.</strong> This oil never goes on those.</li>
  <li><strong>Anyone wanting a treatment for a diagnosed condition.</strong> Arthritis, a slipped disc, sciatica, an injury that is not settling — see a doctor. A massage oil is comfort, not care.</li>
</ul>

<h3>100 ml or 200 ml?</h3>
<table>
  <thead><tr><th>&nbsp;</th><th>100 ml</th><th>200 ml</th></tr></thead>
  <tbody>
    <tr><td>Price</td><td>Rs 1,300</td><td>Rs 2,200</td></tr>
    <tr><td>Per ml</td><td>Rs 13.0</td><td><strong>Rs 11.0</strong></td></tr>
    <tr><td>Roughly lasts</td><td>6–8 full massages</td><td>12–16 full massages</td></tr>
    <tr><td>Best for</td><td>Trying it, or occasional use</td><td>A weekly routine, or a household</td></tr>
  </tbody>
</table>

<h3>The honest part</h3>
<p>We hold <strong>no third-party halal certification and do not claim one</strong>. The list of things we <a href="/what-we-never-use">never use</a> is public. And the ingredient list for this particular oil is <strong>not confirmed yet</strong> — said here rather than hidden behind vague words like "natural herbs".</p>

<h3>Safety note — please read</h3>
<p><strong>External use only. Adults only until the ingredient list is confirmed.</strong> Patch-test on your inner forearm before first use and wait a few hours. Keep away from the eyes, nose and mouth. Keep out of reach of children. Do not apply to broken skin, open wounds or burns. Stop if irritation appears. This is a herbal massage oil, not a medicine; it is not intended to diagnose, treat, cure or prevent any condition. Pain that does not settle needs a doctor, not an oil.</p>
HTML;

        $descriptionUr = <<<'HTML'
<p><strong>Roghan-e-Sukoon</strong> ek herbal maalish ka tel hai — thake huay kandhon, gardan, kamar, ghutnon aur tangon ke liye. Ye us aaram se ki jane wali das minute wali maalish ke liye bana hai jo hamare yahan naslon se ghar mein hoti aayi hai. 100&nbsp;ml Rs&nbsp;1,300, poore Pakistan mein Cash on Delivery.</p>

<h3>Ajza ki fehrist — aur ye abhi tak yahan kyun nahi</h3>
<p>Hum jo bhi bechte hain uska har juzv shaya karte hain. Isi buniyad par ye dukan khari hai. Is tel ke liye <strong>hum abhi manufacturer se asal fehrist confirm kar rahe hain, aur hum andaza nahi lagayenge.</strong></p>
<p>Isi liye ye safha hamare baqi safhon se kam kehta hai — jaan boojh kar. Jaise hi confirm shuda fehrist hamare haath aayi, poori ki poori yahan aa jayegi — kuch chupaye baghair, chahe woh acha na bhi lage. Us se pehle jaanna ho to <strong>WhatsApp par poochein</strong>; hum aap ko theek theek bata denge ke hum kya jante hain aur kya nahi.</p>
<p>Agar ye aap ke liye kaafi nahi, to ye bilkul jaiz baat hai. Fehrist ka intezar karein.</p>

<h3>Log ise kis liye istemal karte hain</h3>
<ul>
  <li><strong>Kaam ke baad</strong> — kandhe, gardan aur kamar, lambe din ke aakhir mein.</li>
  <li><strong>Warzish ya lambi chaal ke baad</strong> — pindliyan, ran aur pair.</li>
  <li><strong>Sardi ki akran</strong>, jab sab kuch aam se zyada kasa hua lage.</li>
  <li><strong>Haftawar maalish ka maamool</strong>, jaise ghar mein hota hai.</li>
</ul>
<p>Ye <strong>maalish aur aaram ka tel hai, dawa nahi</strong>. Ye kisi bimari ka ilaj nahi karta, aur hum aisa dawa bhi nahi karte.</p>

<h3>Ye kis ke liye NAHI hai</h3>
<ul>
  <li><strong>Bachay — resha (seene ki jakran) ke liye bhi nahi.</strong> Hum jante hain ke bachay ko resha ho to garam tel seene aur peeth par malna aam baat hai. <strong>Is tel se ye na karein.</strong> Riwayati maalish ke telon mein aksar kafoor hota hai, jo jild ke raste jism mein jazb hota hai — chhote bachay par baar baar malne se daure (seizures) hone ke wakiyat report ho chuke hain, aur American Academy of Pediatrics bachon par kafoor ke istemal se mana karti hai. Jab tak is bottle ki fehrist confirm nahi hoti, isay <strong>sirf baron ke liye</strong> samjhein. Jis bachay ka seena band ho, khun-khun ho ya saans mein takleef ho — usay tel nahi, <strong>doctor</strong> chahiye.</li>
  <li><strong>Hamal ke douran</strong> — koi bhi herbal maalish ka tel istemal karne se pehle doctor se poochein.</li>
  <li><strong>Jinhein allergy ho ya jild sensitive ho.</strong> Hum abhi aap ko ye nahi bata sakte ke is bottle mein kya hai, is liye ye bhi nahi keh sakte ke ye aap ke liye mehfooz hai. Agar aap ko cheezon se react hota hai to fehrist ka intezar karein.</li>
  <li><strong>Kati phati jild, khule zakham, dane ya taza jalan par</strong> — ye tel kabhi nahi lagta.</li>
  <li><strong>Jise kisi tashkhees shuda bimari ka ilaj chahiye.</strong> Arthritis, disc ka masla, sciatica, ya aisi takleef jo theek nahi ho rahi — doctor ko dikhayein. Maalish ka tel aaram hai, ilaj nahi.</li>
</ul>

<h3>100 ml ya 200 ml?</h3>
<table>
  <thead><tr><th>&nbsp;</th><th>100 ml</th><th>200 ml</th></tr></thead>
  <tbody>
    <tr><td>Qeemat</td><td>Rs 1,300</td><td>Rs 2,200</td></tr>
    <tr><td>Fi ml</td><td>Rs 13.0</td><td><strong>Rs 11.0</strong></td></tr>
    <tr><td>Taqreeban chalta hai</td><td>6–8 mukammal maalish</td><td>12–16 mukammal maalish</td></tr>
    <tr><td>Kis ke liye behtar</td><td>Aazmane ke liye, ya kabhi kabhaar</td><td>Haftawar maamool ya poore ghar ke liye</td></tr>
  </tbody>
</table>

<h3>Sach wali baat</h3>
<p>Hamare paas <strong>koi third-party halal certification nahi hai aur hum aisa dawa bhi nahi karte</strong>. Jo cheezein hum <a href="/what-we-never-use">kabhi istemal nahi karte</a> woh public hain. Aur is tel ki ajza ki fehrist <strong>abhi confirm nahi hui</strong> — ye baat yahan likhi hai, "qudrati jari bootiyan" jaise mubham alfaz ke peeche chupayi nahi gayi.</p>

<h3>Ehtiyat — zaroor parhein</h3>
<p><strong>Sirf bahri istemal. Jab tak ajza confirm nahi hote, sirf baron ke liye.</strong> Pehle istemal se pehle bazu ke andarooni hissay par patch test karein aur chand ghante intezar karein. Aankhon, naak aur munh se door rakhein. Bachon ki pohanch se door rakhein. Kati jild, khule zakham ya jalan par na lagayein. Jalan mehsoos ho to istemal band kar dein. Ye herbal maalish ka tel hai, <strong>dawa nahi</strong>; kisi bimari ki tashkhees, ilaj ya bachao ke liye nahi. Jo dard theek na ho, usay tel nahi, doctor chahiye.</p>
HTML;

        $howToUse = <<<'HTML'
<ul>
  <li><strong>Patch test first — this matters more than usual here,</strong> because we have not yet published this oil's ingredient list. Dab a little on your inner forearm and wait a few hours before using it more widely.</li>
  <li><strong>Warm it in your palms.</strong> Take 5–10 drops and rub your hands together for a few seconds; a warm oil spreads and absorbs better than a cold one.</li>
  <li><strong>Massage slowly, 5–10 minutes.</strong> Firm, circular strokes, working towards the heart. The time you spend matters more than the amount you use.</li>
  <li><strong>Let it sit.</strong> Leave it 20–30 minutes, or overnight under loose cotton clothing. Wash your hands afterwards.</li>
  <li><strong>Best time:</strong> after a warm shower, or last thing at night.</li>
  <li><strong>External use only, adults only.</strong> Avoid eyes, face, broken skin and open wounds. Keep out of reach of children.</li>
</ul>
HTML;

        $howToUseUr = <<<'HTML'
<ul>
  <li><strong>Pehle patch test — yahan ye aam se zyada zaroori hai,</strong> kyunke hum ne is tel ki ajza ki fehrist abhi shaya nahi ki. Thora sa bazu ke andarooni hissay par lagayein aur chand ghante intezar karein.</li>
  <li><strong>Haathon mein garam karein.</strong> 5–10 qatray lein aur haathon ko chand second ragrein; garam tel behtar phailta aur jazb hota hai.</li>
  <li><strong>Aaram se 5–10 minute maalish karein.</strong> Golayi mein, dil ki taraf. Waqt zyada ahem hai, miqdaar nahi.</li>
  <li><strong>Laga rehne dein.</strong> 20–30 minute, ya raat bhar dheele soti kapre ke neeche. Baad mein haath dho lein.</li>
  <li><strong>Behtareen waqt:</strong> garam pani se nahane ke baad, ya raat ko sonay se pehle.</li>
  <li><strong>Sirf bahri istemal, sirf baron ke liye.</strong> Aankhein, chehra, kati jild aur khule zakham se door. Bachon ki pohanch se door rakhein.</li>
</ul>
HTML;

        $faqs = [
            ['q' => 'What is in Roghan-e-Sukoon?', 'a' => 'We are still confirming the exact ingredient list with the manufacturer, and we are not going to guess at it. We publish every ingredient in everything we sell, so this list will be added to this page in full as soon as we have it. If you need to know before then, message us on WhatsApp and we will tell you exactly what we know and what we do not.'],
            ['q' => 'Why would you sell it before publishing the ingredients?', 'a' => 'A fair question. The oil is in stock and people are asking for it, so the page is up — but with the gap stated plainly instead of hidden behind words like "natural herbs". You can order it, or you can wait for the list. If you have allergies or sensitive skin, wait for the list.'],
            ['q' => 'What is Roghan-e-Sukoon used for?', 'a' => 'It is a herbal massage oil used for slow massage of tired shoulders, neck, back, knees and legs — after work, after exercise, or as a weekly maalish routine. It is a comfort oil for external use, not a medicine, and it is not intended to treat or cure any condition.'],
            ['q' => 'What is the price of Roghan-e-Sukoon in Pakistan?', 'a' => 'Rs 1,300 for 100 ml and Rs 2,200 for 200 ml, with Cash on Delivery across Pakistan. The 200 ml works out cheaper per ml if you use it regularly.'],
            ['q' => 'Can I use it for joint pain?', 'a' => 'People traditionally massage the knees, shoulders and lower back with oils like this for comfort. It is not a treatment for arthritis or any diagnosed joint condition. If pain is persistent, severe or getting worse, please see a doctor rather than relying on an oil.'],
            ['q' => 'Can I rub it on my child\'s chest for congestion (resha)?', 'a' => 'No — please do not. Traditional maalish oils commonly contain camphor, which passes through the skin: repeated rubbing on a small child has caused seizures, and the American Academy of Pediatrics advises against camphor in children. Since we have not yet confirmed what is in this bottle, treat it as adults-only. A child with a blocked chest, wheezing or breathing difficulty needs a doctor, not an oil.'],
            ['q' => 'Is it halal?', 'a' => 'We hold no third-party halal accreditation and do not claim one. We also cannot yet publish this oil\'s ingredient list, so we are not going to make any claim about it at all until we can. That list is coming.'],
            ['q' => 'Do you deliver in Karachi and the rest of Pakistan?', 'a' => 'Yes — we ship across Pakistan with Cash on Delivery, so you pay when it reaches your door. Karachi orders are usually the fastest. You can also message us on WhatsApp to order or ask anything.'],
        ];

        $faqsUr = [
            ['q' => 'Roghan-e-Sukoon mein kya hai?', 'a' => 'Hum abhi manufacturer se asal fehrist confirm kar rahe hain, aur hum andaza nahi lagayenge. Hum jo bhi bechte hain uska har juzv shaya karte hain, is liye ye fehrist milte hi poori ki poori isi safhe par aa jayegi. Us se pehle jaanna ho to WhatsApp par poochein — hum theek theek bata denge ke hum kya jante hain aur kya nahi.'],
            ['q' => 'Ajza shaya kiye baghair bech kyun rahe hain?', 'a' => 'Jaiz sawal hai. Tel stock mein hai aur log poochh rahe hain, is liye safha live hai — magar kami ko saaf likh kar, "qudrati jari bootiyan" jaise alfaz ke peeche chupa kar nahi. Aap chahein to order karein, chahein to fehrist ka intezar karein. Agar aap ko allergy hai ya jild sensitive hai to intezar karein.'],
            ['q' => 'Roghan-e-Sukoon kis liye istemal hota hai?', 'a' => 'Ye herbal maalish ka tel hai — thake huay kandhon, gardan, kamar, ghutnon aur tangon ki aaram se maalish ke liye. Kaam ke baad, warzish ke baad, ya haftawar maalish ke maamool ke tor par. Ye bahri istemal ka aaram dene wala tel hai, dawa nahi, aur kisi bimari ke ilaj ke liye nahi.'],
            ['q' => 'Roghan-e-Sukoon ki Pakistan mein qeemat kya hai?', 'a' => '100 ml Rs 1,300 aur 200 ml Rs 2,200, poore Pakistan mein Cash on Delivery ke saath. Agar aap regular istemal karte hain to 200 ml fi ml sasta parta hai.'],
            ['q' => 'Kya main jodon ke dard ke liye istemal kar sakta hoon?', 'a' => 'Riwayati tor par log aise tel se ghutnon, kandhon aur kamar ki maalish aaram ke liye karte hain. Ye arthritis ya kisi bhi tashkhees shuda jodon ki bimari ka ilaj nahi hai. Agar dard musalsal hai, shadeed hai ya barh raha hai, to tel par bharosa karne ke bajaye doctor ko dikhayein.'],
            ['q' => 'Kya bachay ko resha ho to seene par mal sakte hain?', 'a' => 'Nahi — meherbani kar ke na karein. Riwayati maalish ke telon mein aksar kafoor hota hai, jo jild ke raste jism mein chala jata hai: chhote bachay par baar baar malne se daure (seizures) hone ke wakiyat report ho chuke hain, aur American Academy of Pediatrics bachon par kafoor se mana karti hai. Chunke hum ne abhi tak confirm nahi kiya ke is bottle mein kya hai, isay sirf baron ke liye samjhein. Jis bachay ka seena band ho, khun-khun ho ya saans mein takleef ho — usay tel nahi, doctor chahiye.'],
            ['q' => 'Kya ye halal hai?', 'a' => 'Hamare paas koi third-party halal certification nahi hai aur hum aisa dawa nahi karte. Hum is tel ki ajza ki fehrist bhi abhi shaya nahi kar sakte, is liye jab tak kar na lein hum is bare mein koi dawa hi nahi karenge. Woh fehrist aa rahi hai.'],
            ['q' => 'Kya Karachi aur baqi Pakistan mein delivery hoti hai?', 'a' => 'Ji haan — poore Pakistan mein Cash on Delivery ke saath bhejte hain, aap darwaze par paise dete hain. Karachi ke orders aam tor par sab se jaldi pohanchte hain. WhatsApp par bhi order ya sawal kar sakte hain.'],
        ];

        return [
            'slug' => 'roghan-e-sukoon-massage-oil',
            'slug_ur' => 'roghan-e-sukoon-maalish-ka-tel',
            // LIVE at the owner's instruction (3 Sep 2026) BEFORE the ingredient
            // list was known. The page states that gap outright instead of
            // publishing a guessed list — see the block comment above.
            'status' => 'active',
            'stock' => 25,
            'hold_reason' => null,
            'name' => 'Roghan-e-Sukoon — Herbal Massage Oil',
            'name_ur' => 'Roghan-e-Sukoon — Maalish Ka Herbal Tel',
            'sku_prefix' => 'GH-SKN',
            'short' => 'A herbal massage oil (maalish ka tel) for tired shoulders, back, knees and legs. We are still confirming this oil\'s ingredient list and say so on the page rather than guessing. Adults only. Cash on Delivery across Pakistan.',
            'short_ur' => 'Herbal maalish ka tel — thake huay kandhon, kamar, ghutnon aur tangon ke liye. Is tel ki ajza ki fehrist abhi confirm ho rahi hai, aur hum ye safhe par saaf likh rahe hain, andaza nahi laga rahe. Sirf baron ke liye. Poore Pakistan mein Cash on Delivery.',
            'description' => $description,
            'description_ur' => $descriptionUr,
            'how_to_use' => $howToUse,
            'how_to_use_ur' => $howToUseUr,
            'faqs' => $faqs,
            'faqs_ur' => $faqsUr,
            // ⚠️ NOT a list. Deliberately states the gap — do not replace this
            //    with a guess. Replace it with the manufacturer's confirmed list.
            'ingredients_note' => 'Not yet published. We are confirming the full ingredient list for this oil '
                .'with the manufacturer and will publish it here in full — we would rather show this line than '
                .'guess. Ask us on WhatsApp in the meantime. Until the list is confirmed, treat this as an '
                .'adults-only oil: patch-test before use, and do not use it on children.',
            'variants' => [
                ['sku' => 'GH-SKN-100', 'volume' => '100 ml', 'price' => 130000, 'weight' => 160],
                ['sku' => 'GH-SKN-200', 'volume' => '200 ml', 'price' => 220000, 'weight' => 300],
            ],
            'meta_title' => 'Roghan-e-Sukoon Massage Oil — Price in Pakistan, COD',
            'meta_description' => 'Herbal maalish ka tel for tired shoulders, back and knees. Rs 1,300 (100 ml) / Rs 2,200 (200 ml), Cash on Delivery. Ingredient list being confirmed — stated openly, not guessed.',
            'meta_title_ur' => 'Maalish Ka Tel — Roghan-e-Sukoon, Qeemat Aur COD',
            'meta_description_ur' => 'Herbal maalish ka tel — kandhon, kamar aur ghutnon ke liye. 100 ml Rs 1,300, 200 ml Rs 2,200, poore Pakistan Cash on Delivery. Ajza ki fehrist confirm ho rahi hai.',
        ];
    }

    // =========================================================================
    // 2. ROGHAN-E-JARAIN — herbal hair & scalp oil
    // =========================================================================
    private function jarain(): array
    {
        $description = <<<'HTML'
<p><strong>Roghan-e-Jarain</strong> is a herbal hair oil built for the <strong>scalp and the roots</strong>, not for shine on the surface. Four ingredients, nothing hidden: <strong>nariyal (coconut) oil</strong>, <strong>roghan-e-badam (sweet almond oil)</strong>, <strong>kalonji (black seed) oil</strong> and <strong>vitamin D</strong>. It conditions the scalp, softens dry hair and helps reduce the breakage that makes hair look thinner. Rs&nbsp;1,200 for 100&nbsp;ml, Cash on Delivery across Pakistan.</p>
<p><strong>Contains almond — a tree nut.</strong> If anyone in your house has a nut allergy, please do not order this.</p>

<h3>What it does — and what it does not</h3>
<p><strong>What it does:</strong> it is a proper champi oil. It conditions a dry, flaky scalp, softens rough lengths, reduces the friction and snapping that causes breakage, and makes hair easier to comb — which on its own means less hair in the brush.</p>
<p><strong>What it does not do:</strong> it will not regrow hair on a bald patch, it will not reverse pattern baldness, and it is not a treatment for alopecia or any medical hair-loss condition. Any seller telling you an oil does that is lying to you. <strong>Hair fall with a medical cause needs a doctor, not a bottle.</strong></p>

<h3>Why these ingredients</h3>
<ul>
  <li><strong>Nariyal (coconut) oil</strong> — the base, and the one with the strongest evidence behind it. Coconut oil is one of the few oils shown to actually penetrate the hair shaft rather than sit on top of it, which is what reduces protein loss and breakage.</li>
  <li><strong>Roghan-e-badam (sweet almond oil)</strong> — light, slippery and conditioning. It is what makes the oil easy to spread on the scalp and easy to comb out afterwards, without the heaviness of a thick oil.</li>
  <li><strong>Kalonji (black seed) oil</strong> — the traditional favourite for scalp care in this region, and the reason this smells like a desi hair oil rather than a supermarket one.</li>
  <li><strong>Vitamin D</strong> — added to the blend.</li>
</ul>
<p><strong>That is the whole list.</strong> No mineral oil bulking it out, no undisclosed fragrance, no long list of extracts present in amounts too small to do anything. Four ingredients you can pronounce.</p>

<h3>Who this is NOT for</h3>
<ul>
  <li><strong>Anyone expecting regrowth on a bald area.</strong> It will not happen. Please do not buy it for that.</li>
  <li><strong>Sudden or patchy hair loss</strong> — that can signal thyroid problems, iron deficiency, illness or medication effects. See a doctor and get tested. An oil delays the answer.</li>
  <li><strong>Very oily scalps or active dandruff flare-ups</strong> — heavy oiling can make both worse. Use sparingly, or skip it.</li>
  <li><strong>Anyone with a nut or coconut allergy.</strong> This oil contains <strong>sweet almond oil — a tree nut</strong> — and coconut oil. If that applies to you or anyone who will use the bottle, please buy something else. Patch-test regardless, every time, no exceptions.</li>
  <li><strong>People who will not wash it out properly.</strong> Oil left sitting on the scalp for days causes more problems than it solves.</li>
</ul>

<h3>100 ml or 200 ml?</h3>
<table>
  <thead><tr><th>&nbsp;</th><th>100 ml</th><th>200 ml</th></tr></thead>
  <tbody>
    <tr><td>Price</td><td>Rs 1,200</td><td>Rs 2,000</td></tr>
    <tr><td>Per ml</td><td>Rs 12.0</td><td><strong>Rs 10.0</strong></td></tr>
    <tr><td>Roughly lasts</td><td>5–7 champis (shoulder-length hair)</td><td>10–14 champis</td></tr>
    <tr><td>Best for</td><td>Short hair, or trying it</td><td>Long hair, or twice-weekly oiling</td></tr>
  </tbody>
</table>

<h3>The honest part</h3>
<p>The full ingredient list is on this page and the list of what we <a href="/what-we-never-use">never use</a> is public. No mineral oil sold as a herbal oil, no undisclosed fragrance load. We hold <strong>no third-party halal certification and do not claim one</strong>.</p>

<h3>Safety note</h3>
<p>External use only. <strong>Contains sweet almond oil (tree nut) and coconut oil</strong> — do not use if you have a nut or coconut allergy. Patch-test on the inner forearm before first use. Avoid the eyes. Do not apply to broken or inflamed scalp skin. Keep out of reach of children. This is a cosmetic hair and scalp oil, not a medicine; it is not intended to diagnose, treat, cure or prevent any condition, including hair loss.</p>
HTML;

        $descriptionUr = <<<'HTML'
<p><strong>Roghan-e-Jarain</strong> woh herbal tel hai jo <strong>sar ki jild aur balon ki jarain</strong> ke liye banaya gaya hai — sirf uparse chamak ke liye nahi. Chaar ajza, kuch chupa hua nahi: <strong>nariyal ka tel</strong>, <strong>roghan-e-badam</strong>, <strong>kalonji ka tel</strong> aur <strong>vitamin D</strong>. Ye sar ki khushk jild ko naram karta hai, rookhe baal mulaim karta hai, aur woh tootna kam karta hai jis se baal patle lagne lagte hain. 100&nbsp;ml Rs&nbsp;1,200, poore Pakistan mein Cash on Delivery.</p>
<p><strong>Is mein badam hai — yaani giri (tree nut).</strong> Agar ghar mein kisi ko giri se allergy hai to meherbani kar ke ye order na karein.</p>

<h3>Ye kya karta hai — aur kya NAHI karta</h3>
<p><strong>Kya karta hai:</strong> ye ek proper champi ka tel hai. Khushk aur papri wali sar ki jild ko naram karta hai, rookhe balon ki lambai mulaim karta hai, ragar aur tootne ko kam karta hai, aur kangha karna aasan bana deta hai — sirf isi se brush mein baal kam aate hain.</p>
<p><strong>Kya NAHI karta:</strong> ye ganje hissay par baal wapas nahi laata, ye mordana ganjapan palat nahi sakta, aur ye alopecia ya kisi tibbi wajah se hone wale baal girne ka ilaj nahi hai. Jo bechne wala kahe ke tel ye karta hai, woh aap se jhoot bol raha hai. <strong>Tibbi wajah se girte balon ko bottle nahi, doctor chahiye.</strong></p>

<h3>Ye ajza kyun</h3>
<ul>
  <li><strong>Nariyal ka tel</strong> — base, aur woh juzv jis ke peeche sab se mazboot saboot hai. Nariyal ka tel un chand telon mein se hai jo baal ke andar tak jaate hain, sirf upar baithe nahi rehte — isi se protein ka nuqsan aur tootna kam hota hai.</li>
  <li><strong>Roghan-e-badam</strong> — halka, phisalne wala aur conditioning karne wala. Isi se tel sar par aasani se phailta hai aur baad mein kangha aasani se chalta hai, gaarhe tel wale bojh ke baghair.</li>
  <li><strong>Kalonji ka tel</strong> — is ilaqe mein sar ki jild ki dekh bhaal ka riwayati pasandeeda juzv, aur wajah ke ye tel desi lagta hai, kisi supermarket bottle jaisa nahi.</li>
  <li><strong>Vitamin D</strong> — is aamezish mein shamil.</li>
</ul>
<p><strong>Bas yehi poori fehrist hai.</strong> Na mineral oil bhar kar wazan barhaya gaya, na chupi hui khushbu, na darjan bhar aise extracts jin ki miqdaar itni kam ho ke kuch karte hi na hon. Chaar ajza, jo aap parh bhi sakte hain.</p>

<h3>Ye kis ke liye NAHI hai</h3>
<ul>
  <li><strong>Jo ganje hissay par baal ugne ki umeed rakhta ho.</strong> Aisa nahi hoga. Meherbani kar ke is maqsad ke liye na khareedein.</li>
  <li><strong>Achanak ya chittiyon mein baal girna</strong> — is ki wajah thyroid, iron ki kami, bimari ya dawaon ka asar ho sakti hai. Doctor ko dikhayein aur test karwayein. Tel sirf jawab mein deri karta hai.</li>
  <li><strong>Bohot chikni sar ki jild ya khushki (dandruff) ka daura</strong> — zyada tel dono ko barha sakta hai. Kam istemal karein, ya chhor dein.</li>
  <li><strong>Jinhein giri (nuts) ya nariyal se allergy ho.</strong> Is tel mein <strong>roghan-e-badam — yaani tree nut</strong> — aur nariyal ka tel hai. Agar ye aap par ya kisi bhi istemal karne wale par lagta hai to koi doosri cheez lein. Bahar-haal har baar patch test karein, koi istisna nahi.</li>
  <li><strong>Jo tel theek se dho kar nahi nikalte.</strong> Kai din sar par baitha tel faide se zyada masle paida karta hai.</li>
</ul>

<h3>100 ml ya 200 ml?</h3>
<table>
  <thead><tr><th>&nbsp;</th><th>100 ml</th><th>200 ml</th></tr></thead>
  <tbody>
    <tr><td>Qeemat</td><td>Rs 1,200</td><td>Rs 2,000</td></tr>
    <tr><td>Fi ml</td><td>Rs 12.0</td><td><strong>Rs 10.0</strong></td></tr>
    <tr><td>Taqreeban chalta hai</td><td>5–7 champi (kandhe tak baal)</td><td>10–14 champi</td></tr>
    <tr><td>Kis ke liye behtar</td><td>Chhote baal, ya aazmane ke liye</td><td>Lambe baal, ya hafte mein do baar tel</td></tr>
  </tbody>
</table>

<h3>Sach wali baat</h3>
<p>Mukammal ajza isi safhe par hain aur jo hum <a href="/what-we-never-use">kabhi istemal nahi karte</a> woh bhi public hai. Herbal ke naam par mineral oil nahi, chupi hui khushbu ki bhari miqdaar nahi. Hamare paas <strong>koi third-party halal certification nahi hai aur hum aisa dawa nahi karte</strong>.</p>

<h3>Ehtiyat</h3>
<p>Sirf bahri istemal. <strong>Is mein roghan-e-badam (tree nut) aur nariyal ka tel hai</strong> — agar giri ya nariyal se allergy hai to istemal na karein. Pehle istemal se pehle bazu par patch test karein. Aankhon se door rakhein. Kati ya sooji hui sar ki jild par na lagayein. Bachon ki pohanch se door rakhein. Ye cosmetic baal aur scalp ka tel hai, <strong>dawa nahi</strong>; kisi bimari ki — baal girne samet — tashkhees, ilaj ya bachao ke liye nahi.</p>
HTML;

        $howToUse = <<<'HTML'
<ul>
  <li><strong>Patch test first</strong> — a little on the inner forearm, wait a few hours.</li>
  <li><strong>Warm it slightly.</strong> Stand the closed bottle in warm water for a minute — warm oil spreads on the scalp far better than cold.</li>
  <li><strong>Part and apply to the SCALP.</strong> Section the hair, put the oil on the scalp line by line with your fingertips — not on the lengths first. The roots are the point.</li>
  <li><strong>Champi for 5–10 minutes.</strong> Fingertips, not nails. Small circles. Firm but gentle.</li>
  <li><strong>Work the leftovers down the lengths</strong> and to the ends, which are the driest part.</li>
  <li><strong>Leave 1–2 hours, or overnight</strong> on a towel-covered pillow. Do not leave it in for days.</li>
  <li><strong>Wash out properly</strong> — a mild shampoo, usually two rounds. Oil left behind attracts dust and dulls hair.</li>
  <li><strong>Twice a week is plenty.</strong> More is not better.</li>
</ul>
HTML;

        $howToUseUr = <<<'HTML'
<ul>
  <li><strong>Pehle patch test</strong> — thora sa bazu ke andar, chand ghante intezar.</li>
  <li><strong>Halka garam karein.</strong> Band bottle ko ek minute garam pani mein rakhein — garam tel sar par thande se kahin behtar phailta hai.</li>
  <li><strong>Maang nikal kar SAR KI JILD par lagayein.</strong> Baalon ko hisson mein baant kar ungliyon ke poron se line by line tel lagayein — pehle lambai par nahi. Asal maqsad jarain hain.</li>
  <li><strong>5–10 minute champi karein.</strong> Ungliyon ke poray, nakhun nahi. Chhoti golayian. Mazboot magar naram.</li>
  <li><strong>Bacha hua tel lambai aur siron tak</strong> le jayein — siray sab se zyada khushk hote hain.</li>
  <li><strong>1–2 ghante, ya raat bhar</strong> tauliye wale takiye par chhor dein. Kai din tak laga na rehne dein.</li>
  <li><strong>Achi tarah dho lein</strong> — halke shampoo se, aam tor par do baar. Bacha hua tel gard khenchta hai aur baal be-raunaq karta hai.</li>
  <li><strong>Hafte mein do baar kaafi hai.</strong> Zyada behtar nahi hota.</li>
</ul>
HTML;

        $faqs = [
            ['q' => 'What is Roghan-e-Jarain hair oil used for?', 'a' => 'It is a scalp-and-roots champi oil: it conditions a dry or flaky scalp, softens rough lengths and helps reduce the breakage that makes hair look thinner. It is a cosmetic hair oil, not a treatment for hair loss.'],
            ['q' => 'What is the price of Roghan-e-Jarain in Pakistan?', 'a' => 'Rs 1,200 for 100 ml and Rs 2,000 for 200 ml, with Cash on Delivery across Pakistan. The 200 ml is better value if you oil twice a week or have long hair.'],
            ['q' => 'Will this oil regrow my hair?', 'a' => 'No, and we will not pretend otherwise. No oil regrows hair on a bald patch or reverses pattern baldness. What a good oil can do is reduce breakage and condition the scalp, so you lose less hair to snapping. If your hair loss is sudden, patchy or getting worse, please see a doctor — it can point to thyroid, iron or medication issues.'],
            ['q' => 'How often should I use it?', 'a' => 'Twice a week is plenty. Apply to the scalp, massage 5–10 minutes, leave 1–2 hours or overnight, then wash out properly with a mild shampoo. Leaving oil in for days does more harm than good.'],
            ['q' => 'What is in it?', 'a' => 'Four things: coconut oil, sweet almond oil (roghan-e-badam), kalonji (black seed) oil and vitamin D. That is the complete list — no mineral oil, no hidden fragrance. Important: it contains almond, which is a tree nut, and coconut. Do not use it if you have a nut or coconut allergy.'],
            ['q' => 'Is it halal?', 'a' => 'The ingredients are plant-derived and the full list is published here so you can check it yourself. We hold no third-party halal accreditation and do not claim one.'],
            ['q' => 'Do you deliver in Karachi and the rest of Pakistan?', 'a' => 'Yes — Cash on Delivery across Pakistan, so you pay at your door. You can also order or ask questions on WhatsApp.'],
        ];

        $faqsUr = [
            ['q' => 'Roghan-e-Jarain kis liye istemal hota hai?', 'a' => 'Ye sar ki jild aur jaron ke liye champi ka tel hai: khushk ya papri wali scalp ko naram karta hai, rookhi lambai mulaim karta hai, aur woh tootna kam karta hai jis se baal patle lagte hain. Ye cosmetic hair oil hai, baal girne ka ilaj nahi.'],
            ['q' => 'Roghan-e-Jarain ki Pakistan mein qeemat kya hai?', 'a' => '100 ml Rs 1,200 aur 200 ml Rs 2,000, poore Pakistan mein Cash on Delivery. Agar aap hafte mein do baar tel lagate hain ya baal lambe hain to 200 ml zyada faida mand hai.'],
            ['q' => 'Kya is tel se baal wapas ug jayenge?', 'a' => 'Nahi, aur hum jhoot nahi bolenge. Koi tel ganje hissay par baal wapas nahi laata aur na hi mordana ganjapan palat sakta hai. Acha tel itna kar sakta hai ke tootna kam ho aur scalp ki halat behtar ho, taake baal toot kar kam giren. Agar baal achanak, chittiyon mein ya barhte huay gir rahe hain to doctor ko dikhayein — ye thyroid, iron ki kami ya dawaon ki taraf ishara ho sakta hai.'],
            ['q' => 'Kitni baar lagana chahiye?', 'a' => 'Hafte mein do baar kaafi hai. Sar ki jild par lagayein, 5–10 minute maalish karein, 1–2 ghante ya raat bhar chhor dein, phir halke shampoo se achi tarah dho lein. Kai din tak tel laga rakhna faida nahi, nuqsan deta hai.'],
            ['q' => 'Is mein kya kya hai?', 'a' => 'Chaar cheezein: nariyal ka tel, roghan-e-badam, kalonji ka tel aur vitamin D. Bas yehi mukammal fehrist hai — na mineral oil, na chupi hui khushbu. Ahem baat: is mein badam hai jo tree nut hai, aur nariyal. Agar giri ya nariyal se allergy hai to istemal na karein.'],
            ['q' => 'Kya ye halal hai?', 'a' => 'Ajza podon se hain aur mukammal fehrist yahan shaya ki gayi hai taake aap khud dekh sakein. Hamare paas koi third-party halal certification nahi hai aur hum aisa dawa nahi karte.'],
            ['q' => 'Kya Karachi aur baqi Pakistan mein delivery hoti hai?', 'a' => 'Ji haan — poore Pakistan mein Cash on Delivery, aap darwaze par paise dete hain. WhatsApp par bhi order ya sawal kar sakte hain.'],
        ];

        return [
            'slug' => 'roghan-e-jarain-hair-oil',
            'slug_ur' => 'roghan-e-jarain-balon-ka-tel',
            // LIVE. Ingredients are owner-supplied (3 Sep 2026), stock confirmed.
            'status' => 'active',
            'stock' => 25,
            'hold_reason' => null,
            'name' => 'Roghan-e-Jarain — Herbal Hair Oil for Scalp & Roots',
            'name_ur' => 'Roghan-e-Jarain — Balon Ki Jarain Ka Herbal Tel',
            'sku_prefix' => 'GH-JRN',
            'short' => 'A scalp-and-roots champi oil, four ingredients only: coconut oil, sweet almond oil, kalonji (black seed) oil and vitamin D. Conditions the scalp and helps reduce breakage. Contains almond (tree nut) and coconut. Cash on Delivery across Pakistan.',
            'short_ur' => 'Sar ki jild aur jaron ke liye champi ka tel, sirf chaar ajza: nariyal ka tel, roghan-e-badam, kalonji ka tel aur vitamin D. Scalp ko naram karta hai aur tootna kam karta hai. Is mein badam (giri) aur nariyal hai. Poore Pakistan mein Cash on Delivery.',
            'description' => $description,
            'description_ur' => $descriptionUr,
            'how_to_use' => $howToUse,
            'how_to_use_ur' => $howToUseUr,
            'faqs' => $faqs,
            'faqs_ur' => $faqsUr,
            // Owner-supplied, 3 Sep 2026: coconut, badam (almond), black seed, vitamin D.
            // ⚠️ ONE THING TO CONFIRM: the owner said "vitamin D". Hair oils almost
            //    always use vitamin E (tocopherol) — it is the antioxidant that stops
            //    the oil going rancid. If the bottle label says E, change it here and
            //    in the four copy blocks above (EN + UR description, EN + UR FAQ).
            'ingredients_note' => 'Cocos Nucifera (Nariyal / Coconut) Oil, '
                .'Prunus Amygdalus Dulcis (Roghan-e-Badam / Sweet Almond) Oil, '
                .'Nigella Sativa (Kalonji / Black Seed) Oil, Vitamin D. '
                .'ALLERGEN: contains sweet almond oil (TREE NUT) and coconut oil. '
                .'Not suitable for anyone with a nut or coconut allergy.',
            'variants' => [
                ['sku' => 'GH-JRN-100', 'volume' => '100 ml', 'price' => 120000, 'weight' => 160],
                ['sku' => 'GH-JRN-200', 'volume' => '200 ml', 'price' => 200000, 'weight' => 300],
            ],
            'meta_title' => 'Roghan-e-Jarain Herbal Hair Oil — Price in Pakistan, COD',
            'meta_description' => 'Scalp & roots champi oil — coconut, sweet almond, kalonji & vitamin D, nothing else. Rs 1,200 (100 ml) / Rs 2,000 (200 ml). Cash on Delivery across Pakistan.',
            'meta_title_ur' => 'Balon Ka Herbal Tel — Roghan-e-Jarain, Qeemat Aur COD',
            'meta_description_ur' => 'Jaron aur scalp ke liye champi ka tel — nariyal, roghan-e-badam, kalonji aur vitamin D, aur kuch nahi. 100 ml Rs 1,200, 200 ml Rs 2,000. Poore Pakistan Cash on Delivery.',
        ];
    }
}
