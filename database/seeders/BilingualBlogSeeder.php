<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Illuminate\Database\Seeder;

/**
 * Bilingual (English + Roman Urdu) functional-query articles for the flagship
 * Lookman-e-Hayat oil. Two topics — joint pain and cuts & burns — each written
 * as a language PAIR that shares a translation_group_id so the foundation emits
 * a reciprocal hreflang cluster (en / ur-Latn / x-default) and sitemap
 * alternates automatically.
 *
 * ANTI-CANNIBALIZATION: the two members of each pair deliberately use DISTINCT
 * primary keywords, titles and slugs (English "for joint pain" vs Roman Urdu
 * "jodon ke dard ka tel"), so Google cannot make them compete even when it
 * reads Roman Urdu as English. The shared UUIDs are HARD-CODED (not generated)
 * so re-running the seeder never re-pairs or duplicates.
 *
 * DEPLOY-SAFE: firstOrCreate by slug — re-running never clobbers admin edits.
 *
 * Health-safe: traditional-use framing only, no cure/treatment claims. Both
 * burns pieces LEAD with the cool-under-water-and-see-a-doctor safety message
 * and state plainly that oil must never go on a serious or open burn.
 */
class BilingualBlogSeeder extends Seeder
{
    /** Fixed group UUIDs — one per topic, shared across its en + ur-Latn pair. */
    private const GROUP_JOINT_PAIN = 'a1f4b2c3-0d5e-4f6a-8b7c-9d0e1f2a3b4c';

    private const GROUP_CUTS_BURNS = 'b2e5c3d4-1e6f-4a7b-9c8d-0e1f2a3b4c5d';

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
                'topic_group' => 'joint-pain',
                'locale' => 'en',
                'translation_group_id' => self::GROUP_JOINT_PAIN,
                'title' => 'Lookman e Hayat Oil for Joint Pain: Uses & Price',
                'slug' => 'lookman-e-hayat-oil-for-joint-pain',
                'excerpt' => 'Lookman e Hayat oil for joint pain: how to use this herbal massage oil on knees, back & muscles, 50ml vs 100ml price in Pakistan, plus COD ordering.',
                'reading_time_minutes' => 5,
                'content' => $this->jointPainEn(),
            ],
            [
                'topic_group' => 'joint-pain',
                'locale' => 'ur-Latn',
                'translation_group_id' => self::GROUP_JOINT_PAIN,
                'title' => 'Jodon ke Dard ka Tel: Lookman e Hayat Maalish',
                'slug' => 'jodon-ke-dard-ka-tel',
                'excerpt' => 'Jodon ke dard ka tel — Lookman e Hayat herbal maalish tel ghutnon, kamar aur pathon ke liye kaise istemal karein, 50ml aur 100ml qeemat, COD order.',
                'reading_time_minutes' => 5,
                'content' => $this->jointPainUr(),
            ],
            [
                'topic_group' => 'cuts-burns',
                'locale' => 'en',
                'translation_group_id' => self::GROUP_CUTS_BURNS,
                'title' => 'Lookman e Hayat Oil for Cuts and Burns: Guide',
                'slug' => 'lookman-e-hayat-oil-for-cuts-and-burns',
                'excerpt' => 'Lookman e Hayat oil for cuts and burns — safety first: cool serious burns & see a doctor. How it is traditionally used for minor skin irritation, sizes & price.',
                'reading_time_minutes' => 5,
                'content' => $this->cutsBurnsEn(),
            ],
            [
                'topic_group' => 'cuts-burns',
                'locale' => 'ur-Latn',
                'translation_group_id' => self::GROUP_CUTS_BURNS,
                'title' => 'Jalne Kaatne par Lagane Wala Tel: Lookman',
                'slug' => 'jalne-kaatne-par-lagane-wala-tel',
                'excerpt' => 'Jalne kaatne par lagane wala tel? Pehle ehtiyat: shadeed jale ko thanday pani se thanda karein aur doctor ko dikhayein. Lookman e Hayat tel sirf mamooli jild ke liye.',
                'reading_time_minutes' => 5,
                'content' => $this->cutsBurnsUr(),
            ],
        ];
    }

    private function jointPainEn(): string
    {
        return <<<'HTML'
<div class="article-answer-box">
  <p>Lookman e Hayat oil is a traditional herbal massage oil that many people in Pakistan use to soothe tired joints, knees and muscles. Warm a little in your palms, massage gently into the area twice a day, and pair it with rest. It supports comfort during massage — it is not a medicine.</p>
</div>

<h2>What is Lookman e Hayat oil?</h2>
<p>Lookman e Hayat (also spelled Luqman-e-Hayat) is a herbal skin oil built from a blend of plant-based ingredients rooted in the desi hikmat tradition. At Glow Halal it is prepared as a light massage oil that absorbs into the skin, which is why families across Pakistan keep a bottle at home for everyday self-massage of the knees, shoulders, back and legs.</p>

<h2>How does Lookman e Hayat oil help with joint and knee pain?</h2>
<p>The oil is used the way maalish oils have always been used: the warmth and gentle pressure of massage help you relax stiff, tired muscles and improve the feeling of ease around a joint. Many customers reach for it after a long day on their feet, after exercise, or in cold weather when knees feel stiff. It is valued for comfort during massage — it does not treat or cure any medical joint condition.</p>

<h2>How do you use Lookman e Hayat oil for joint pain?</h2>
<ol>
  <li>Warm a small amount of oil between your palms, or place the closed bottle in warm water for a minute.</li>
  <li>Apply to the knee, joint or muscle and massage gently in circular motions for 5–10 minutes.</li>
  <li>Use twice a day — morning and before bed usually works best.</li>
  <li>Keep the area warm afterwards and rest the joint.</li>
  <li>If your skin is sensitive, do a small patch test on your forearm first.</li>
</ol>

<h2>Which areas can you massage?</h2>
<p>Knees, shoulders, lower back, elbows, calves and the soles of the feet are the most common. Use only on healthy, unbroken skin — never on cuts, wounds or a rash.</p>

<h2>Should you still see a doctor?</h2>
<p>Yes. Massage oil is for everyday comfort only. If your joint pain is severe, comes with swelling, redness or fever, does not settle, or follows an injury, please see a qualified doctor. Do not use this oil as a replacement for medical treatment.</p>

<h2>50ml vs 100ml: which size should you buy?</h2>
<table>
  <thead>
    <tr><th>Size</th><th>Price</th><th>Best for</th></tr>
  </thead>
  <tbody>
    <tr><td>50ml</td><td>Rs 1,800</td><td>Trying it for the first time, or one area (e.g. one knee)</td></tr>
    <tr><td>100ml</td><td>Rs 3,000</td><td>Daily whole-family massage — lasts longer, better value per ml</td></tr>
  </tbody>
</table>
<p>First time? Start with the <a href="/products/herbal-skin-oil-50ml">50ml bottle (Rs 1,800)</a>. For regular daily massage the <a href="/products/herbal-skin-oil-100ml">100ml bottle (Rs 3,000)</a> lasts longer and costs less per ml. See the full range on our <a href="/shop/oils">herbal oils shop</a>.</p>

<h2>Frequently asked questions</h2>
<h3>How long before I feel comfortable after massage?</h3>
<p>Massage feels relaxing straight away for most people. It is a comfort routine, not a treatment, so keep expectations realistic and be consistent.</p>
<h3>Can I use it every day?</h3>
<p>Yes, it is made for daily massage on healthy skin. Stop if you notice any redness or irritation.</p>
<h3>Is it safe for older people?</h3>
<p>It is a gentle external massage oil. Anyone with a medical condition, or who is on medication, should check with their doctor first.</p>
<h3>Does it stain clothes?</h3>
<p>Like most massage oils it can leave a mark, so let it absorb for a few minutes before dressing.</p>

<h2>How to order (Cash on Delivery)</h2>
<p>Order online with Cash on Delivery anywhere in Pakistan, or message us to order in seconds. <a href="https://wa.me/923001234567">Chat with us on WhatsApp</a> and we will arrange delivery to your door.</p>

<p class="article-disclaimer"><em>Disclaimer: Lookman e Hayat oil is a herbal cosmetic massage product, not a medicine. It is not intended to diagnose, treat, cure or prevent any disease. Results vary from person to person. For persistent or severe pain, please consult a qualified doctor. Keep out of reach of children and avoid contact with the eyes.</em></p>
HTML;
    }

    private function jointPainUr(): string
    {
        return <<<'HTML'
<div class="article-answer-box">
  <p>Lookman e Hayat tel ek purana herbal maalish wala tel hai jo bohot log Pakistan mein jodon, ghutnon aur pathon ki thakan ke liye istemal karte hain. Thora garam kar ke halke haath se din mein do baar maalish karein aur aaram karein. Ye dawai nahi, sirf maalish mein aaram deta hai.</p>
</div>

<h2>Lookman e Hayat tel kya hai?</h2>
<p>Lookman e Hayat (jise Luqman-e-Hayat bhi likha jata hai) ek herbal skin oil hai jo desi hikmat ki rivayat par bani jadi bootiyon ke amaiz se tayyar hota hai. Glow Halal par ye halka maalish wala tel hai jo jild mein jazb ho jata hai, isi liye ghar mein log ghutnon, kandhon, kamar aur tangon ki rozana maalish ke liye ek bottle rakhte hain.</p>

<h2>Jodon ke dard mein ye tel kaise madad karta hai?</h2>
<p>Ye tel usi tarah kaam aata hai jaise maalish ke tel hamesha se istemal hote aaye hain — halki garmahat aur maalish ka dabao aap ke akde hue, thake pathon ko dheela karne aur jod ke ird gird aaram ka ehsaas behtar karne mein madad karte hain. Bohot log din bhar ki thakan ke baad, warzish ke baad, ya sardi mein jab ghutne akde lagein tab istemal karte hain. Ye sirf maalish mein aaram ke liye hai, kisi bimari ka ilaj nahi.</p>

<h2>Jodon ke dard ke liye tel kaise istemal karein?</h2>
<ol>
  <li>Thora sa tel haathon ke darmiyan garam karein, ya band bottle ko ek minute garam pani mein rakh lein.</li>
  <li>Ghutne, jod ya path par lagayein aur 5–10 minute halke gol haath se maalish karein.</li>
  <li>Din mein do baar istemal karein — subah aur sone se pehle behtar rehta hai.</li>
  <li>Baad mein jagah ko garam rakhein aur jod ko aaram dein.</li>
  <li>Agar jild hassaas hai to pehle baazu par thora sa laga kar patch test kar lein.</li>
</ol>

<h2>Kin jagah maalish kar sakte hain?</h2>
<p>Ghutne, kandhe, kamar ka nichla hissa, kohniyan, pindliyan aur paon ke talwe sab se aam hain. Sirf sehatmand, be-zakhm jild par lagayein — kabhi kisi kaate, zakhm ya kharish wali jagah par na lagayein.</p>

<h2>Doctor ko dikhana zaroori hai?</h2>
<p>Ji haan. Maalish ka tel sirf rozana aaram ke liye hai. Agar dard shadeed ho, sujan, surkhi ya bukhar ke saath ho, theek na ho raha ho, ya kisi chot ke baad shuru hua ho, to zaroor qualified doctor ko dikhayein. Is tel ko medical ilaj ka mutabadil na samjhein.</p>

<h2>50ml ya 100ml — kaunsa lein?</h2>
<table>
  <thead>
    <tr><th>Size</th><th>Qeemat</th><th>Kis ke liye behtar</th></tr>
  </thead>
  <tbody>
    <tr><td>50ml</td><td>Rs 1,800</td><td>Pehli baar try karne ke liye ya ek hi jagah (jaise ek ghutna)</td></tr>
    <tr><td>100ml</td><td>Rs 3,000</td><td>Rozana poore ghar ki maalish — ziyada chalta hai, per ml sasta</td></tr>
  </tbody>
</table>
<p>Pehli baar le rahe hain? <a href="/products/herbal-skin-oil-50ml">50ml bottle (Rs 1,800)</a> se shuru karein. Rozana istemal ke liye <a href="/products/herbal-skin-oil-100ml">100ml bottle (Rs 3,000)</a> ziyada chalti hai aur per ml sasti parti hai. Poori range dekhne ke liye hamari <a href="/shop/oils">herbal oils shop</a> par jayein.</p>

<h2>Aam sawal jawab</h2>
<h3>Maalish ke baad kitni der mein aaram mehsoos hota hai?</h3>
<p>Ziyada tar logon ko maalish fauran sukoon deti hai. Ye ek aaram wala routine hai, ilaj nahi, is liye tawaqqu haqeeqi rakhein aur pabandi se karein.</p>
<h3>Kya rozana istemal kar sakte hain?</h3>
<p>Ji haan, ye sehatmand jild par rozana maalish ke liye hi hai. Agar surkhi ya jalan ho to istemal rok dein.</p>
<h3>Buzurg log istemal kar sakte hain?</h3>
<p>Ye halka bahri maalish wala tel hai. Jise koi bimari ho ya dawai chal rahi ho, wo pehle apne doctor se pooch lein.</p>
<h3>Kya ye kapron par daagh chhorta hai?</h3>
<p>Baqi maalish tel ki tarah nishan chhor sakta hai, is liye kapre pehanne se pehle isay kuch minute jazb hone dein.</p>

<h2>Order kaise karein (Cash on Delivery)</h2>
<p>Poore Pakistan mein Cash on Delivery par online order karein, ya seconds mein order dene ke liye humein message karein. <a href="https://wa.me/923001234567">WhatsApp par baat karein</a> aur ghar par delivery le lein.</p>

<p class="article-disclaimer"><em>Notice: Lookman e Hayat tel ek herbal cosmetic maalish product hai, dawai nahi. Ye kisi bimari ki tashkhees, ilaj ya rokthaam ke liye nahi hai. Nataij har shakhs mein alag hote hain. Musalsal ya shadeed dard ke liye qualified doctor se rujoo karein. Bachon ki pohanch se door rakhein aur aankhon se bachayein.</em></p>
HTML;
    }

    private function cutsBurnsEn(): string
    {
        return <<<'HTML'
<div class="article-answer-box">
  <p>Safety first: for a serious burn, cool it under cool running water for 10–20 minutes and see a doctor — never put oil on a serious or open burn. Lookman e Hayat oil is only traditionally used for minor skin dryness and irritation on intact skin. It does not heal wounds.</p>
</div>

<h2>What should you do first for a burn?</h2>
<p>Before anything else: for any burn that blisters, is larger than a coin, is deep, or is on the face, hands, joints or a child, get the area under cool (not ice-cold) running water for 10–20 minutes and seek medical help. Do not apply oil, toothpaste, or any home remedy to a serious or open burn — oil can trap heat and make things worse. This article is not first aid for a real injury; a doctor comes first.</p>

<h2>So where does Lookman e Hayat oil fit in?</h2>
<p>Lookman e Hayat is a herbal skin oil that people in Pakistan traditionally use on healthy, intact skin for everyday minor concerns — dryness, rough patches and mild everyday irritation. It is a cosmetic massage oil, not a wound treatment. It should never be applied to an open cut, a blistered or broken burn, or any bleeding skin.</p>

<h2>Can you use it on minor skin irritation?</h2>
<p>Many customers apply a small amount to dry, rough or mildly irritated skin (for example, after it has fully healed and closed) as part of a normal moisturising routine. Do a patch test first. If the skin is broken, painful, weeping, spreading or shows signs of infection, stop and see a doctor — do not rely on any oil.</p>

<h2>When must you NOT use this oil?</h2>
<ul>
  <li>On a serious, deep, large or blistered burn.</li>
  <li>On any open, bleeding or broken skin, or a fresh cut.</li>
  <li>On a burn or wound that is infected, spreading or very painful.</li>
  <li>On burns to the face, hands, feet, joints or genitals, or on children — see a doctor.</li>
</ul>

<h2>How is it used on minor, intact-skin concerns?</h2>
<ol>
  <li>Make sure the skin is clean, dry, healthy and fully closed (no open wound).</li>
  <li>Patch test a small amount on your forearm and wait 24 hours.</li>
  <li>If there is no reaction, apply a thin layer to the dry or rough area.</li>
  <li>Stop immediately if you feel stinging, burning or see redness.</li>
</ol>

<h2>50ml vs 100ml: which size should you buy?</h2>
<table>
  <thead>
    <tr><th>Size</th><th>Price</th><th>Best for</th></tr>
  </thead>
  <tbody>
    <tr><td>50ml</td><td>Rs 1,800</td><td>Trying it, or occasional use on small dry areas</td></tr>
    <tr><td>100ml</td><td>Rs 3,000</td><td>Regular whole-family skincare — better value per ml</td></tr>
  </tbody>
</table>
<p>New to it? Start with the <a href="/products/herbal-skin-oil-50ml">50ml bottle (Rs 1,800)</a>. For regular family use the <a href="/products/herbal-skin-oil-100ml">100ml bottle (Rs 3,000)</a> lasts longer. Explore the full range on our <a href="/shop/oils">herbal oils shop</a>.</p>

<h2>Frequently asked questions</h2>
<h3>Does Lookman e Hayat oil heal cuts or burns?</h3>
<p>No. It is a herbal cosmetic oil, not a medicine, and it does not heal wounds or speed recovery. Wounds and burns need proper first aid and, when needed, a doctor.</p>
<h3>Can I put it straight on a fresh burn to soothe it?</h3>
<p>No. Cool a burn with running water and see a doctor. Oil should not go on a serious or open burn.</p>
<h3>Is it okay on skin that has already healed?</h3>
<p>On fully healed, intact skin you can use it as a normal moisturising oil after a patch test. Stop if any irritation appears.</p>
<h3>Is it safe for children?</h3>
<p>Keep it out of reach of children and do not use it on a child's burn or wound — consult a doctor instead.</p>

<h2>How to order (Cash on Delivery)</h2>
<p>Order online with Cash on Delivery across Pakistan, or message us to order quickly. <a href="https://wa.me/923001234567">Chat with us on WhatsApp</a> for help choosing a size.</p>

<p class="article-disclaimer"><em>Disclaimer: Lookman e Hayat oil is a herbal cosmetic product, not a medicine. It is not intended to diagnose, treat, cure or prevent any disease, and it does not heal wounds or burns. For any burn, cut or skin injury, follow proper first aid and consult a qualified doctor or hospital. Do not apply oil to serious, open or blistered burns. Keep out of reach of children and avoid contact with the eyes.</em></p>
HTML;
    }

    private function cutsBurnsUr(): string
    {
        return <<<'HTML'
<div class="article-answer-box">
  <p>Pehle ehtiyat: agar shadeed jal jaye to 10–20 minute thanday behte pani ke neeche rakhein aur doctor ko dikhayein — tel kabhi shadeed ya khuli jali jagah par na lagayein. Lookman e Hayat tel sirf mamooli khushki aur halki jild ki kharish ke liye rivayati taur par istemal hota hai. Ye zakhm theek nahi karta.</p>
</div>

<h2>Jalne par sab se pehle kya karein?</h2>
<p>Har us jale par jis mein chhalay parein, jo sikke se bara ho, gehra ho, ya chehre, haathon, jodon ya bachay par ho — jagah ko thanday (barf jaisa thanda nahi) behte pani ke neeche 10–20 minute rakhein aur foran medical madad lein. Kisi shadeed ya khuli jali jagah par tel, toothpaste ya koi ghareloo cheez na lagayein — tel garmi ko band kar ke halat kharab kar sakta hai. Ye article kisi asli chot ka ilaj nahi; pehle doctor.</p>

<h2>To phir Lookman e Hayat tel kahan istemal hota hai?</h2>
<p>Lookman e Hayat ek herbal skin oil hai jise log Pakistan mein sehatmand, saabit jild par rozana mamooli baaton ke liye rivayati taur par istemal karte hain — khushki, rukhi jild aur halki rozana jalan. Ye ek cosmetic maalish tel hai, zakhm ka ilaj nahi. Isay kabhi khule kaate, chhalay wale ya tootay hue jale, ya khoon behti jild par na lagayein.</p>

<h2>Kya halki jild ki kharish par laga sakte hain?</h2>
<p>Bohot log thora sa tel rukhi, khushak ya halki kharish wali jild par (misaal ke taur par jab jild poori tarah theek aur band ho chuki ho) rozana moisturising ke tor par lagate hain. Pehle patch test karein. Agar jild tooti hui ho, dard ho, paani ris raha ho, phail rahi ho ya infection ke asaar hon to ruk jayein aur doctor ko dikhayein — kisi tel par bharosa na karein.</p>

<h2>Kab bilkul istemal NA karein?</h2>
<ul>
  <li>Shadeed, gehray, baray ya chhalay wale jale par.</li>
  <li>Kisi khule, khoon behte ya tootay hue jild ya taazay kaate par.</li>
  <li>Us jale ya zakhm par jo infected ho, phail raha ho ya bohot dard de.</li>
  <li>Chehre, haathon, paon, jodon ya bachon ke jale par — doctor ko dikhayein.</li>
</ul>

<h2>Saabit, sehatmand jild par kaise istemal karein?</h2>
<ol>
  <li>Yaqeen karein ke jild saaf, khushk, sehatmand aur poori tarah band hai (koi khula zakhm nahi).</li>
  <li>Baazu par thora sa laga kar patch test karein aur 24 ghante intzar karein.</li>
  <li>Agar koi reaction na ho to rukhi ya khushak jagah par patli tehh lagayein.</li>
  <li>Agar chubhan, jalan ya surkhi mehsoos ho to foran rok dein.</li>
</ol>

<h2>50ml ya 100ml — kaunsa lein?</h2>
<table>
  <thead>
    <tr><th>Size</th><th>Qeemat</th><th>Kis ke liye behtar</th></tr>
  </thead>
  <tbody>
    <tr><td>50ml</td><td>Rs 1,800</td><td>Try karne ke liye ya kabhi kabhaar chhoti khushak jagah par</td></tr>
    <tr><td>100ml</td><td>Rs 3,000</td><td>Rozana poore ghar ki skincare — per ml sasta</td></tr>
  </tbody>
</table>
<p>Pehli baar? <a href="/products/herbal-skin-oil-50ml">50ml bottle (Rs 1,800)</a> se shuru karein. Rozana istemal ke liye <a href="/products/herbal-skin-oil-100ml">100ml bottle (Rs 3,000)</a> ziyada chalti hai. Poori range hamari <a href="/shop/oils">herbal oils shop</a> par dekhein.</p>

<h2>Aam sawal jawab</h2>
<h3>Kya Lookman e Hayat tel kaate ya jale ko theek karta hai?</h3>
<p>Nahi. Ye ek herbal cosmetic tel hai, dawai nahi, aur ye zakhm theek nahi karta na recovery tez karta hai. Zakhm aur jale ko sahih first aid aur zaroorat par doctor chahiye.</p>
<h3>Kya taazay jale par foran sukoon ke liye laga sakte hain?</h3>
<p>Nahi. Jale ko behte pani se thanda karein aur doctor ko dikhayein. Shadeed ya khuli jali jagah par tel nahi lagana chahiye.</p>
<h3>Jo jild theek ho chuki ho us par theek hai?</h3>
<p>Poori tarah theek, saabit jild par patch test ke baad aam moisturising tel ki tarah istemal kar sakte hain. Koi jalan ho to rok dein.</p>
<h3>Kya bachon ke liye mehfooz hai?</h3>
<p>Bachon ki pohanch se door rakhein aur kisi bachay ke jale ya zakhm par istemal na karein — us ke bajaye doctor se rujoo karein.</p>

<h2>Order kaise karein (Cash on Delivery)</h2>
<p>Poore Pakistan mein Cash on Delivery par online order karein, ya jaldi order ke liye humein message karein. Size chunne mein madad ke liye <a href="https://wa.me/923001234567">WhatsApp par baat karein</a>.</p>

<p class="article-disclaimer"><em>Notice: Lookman e Hayat tel ek herbal cosmetic product hai, dawai nahi. Ye kisi bimari ki tashkhees, ilaj ya rokthaam ke liye nahi hai, aur ye zakhm ya jale ko theek nahi karta. Kisi bhi jale, kaate ya jild ki chot par sahih first aid karein aur qualified doctor ya hospital se rujoo karein. Shadeed, khule ya chhalay wale jale par tel na lagayein. Bachon ki pohanch se door rakhein aur aankhon se bachayein.</em></p>
HTML;
    }
}
