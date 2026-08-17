<?php

namespace Database\Seeders;

use App\Enums\ContentLanguage;
use App\Enums\SocialCtaType;
use App\Enums\SocialPillar;
use App\Enums\SocialPlatform;
use App\Enums\SocialPostStatus;
use App\Enums\SocialTargetStatus;
use App\Models\SocialPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * 30 days of X (Twitter) captions for the MANUAL copy-paste flow.
 *
 * Context (18 Aug 2026): the auto-publish engine works end-to-end but X's API
 * now bills per-use credits ("HTTP 402 credits depleted") and the owner chose
 * not to pay — so posts are drafted here, surfaced by the 08:00 PKT
 * social:due-digest, and the owner pastes each into the X app and hits the
 * "Mark posted" action. If credits are ever bought, uncommenting the X_* keys
 * in .env turns the same queue fully automatic with no other change.
 *
 * Editorial rules baked into every caption (do not "improve" them away):
 *  - No cure/treatment claims; cosmetic and traditional-use framing only.
 *  - No "halal certified" (day 14 says the opposite on purpose — the brand's
 *    honesty positioning IS the differentiator).
 *  - Reseller language: stock, never make/formulate.
 *  - Hashtags sparingly (0-2, relevant only) and INSIDE the caption text, so
 *    one copy grabs everything; the separate hashtags column stays empty to
 *    avoid double-adding on paste.
 *  - ≤ 280 chars each (free-account cap) — verified by the length guard below.
 *  - The only phone number that may ever appear: 0301 2973886.
 *
 * Scheduling: one post/day starting tomorrow, 20:00 PKT (evening peak per
 * docs/twitter-x-plan-aug2026.md). Idempotent — keyed on the internal title,
 * and a re-run never touches a post the owner has already edited or posted.
 */
class XContentCalendarSeeder extends Seeder
{
    public function run(): void
    {
        $start = Carbon::today('Asia/Karachi')->addDay();

        foreach ($this->captions() as $i => [$lang, $pillar, $cta, $caption]) {
            $day = $i + 1;

            if (mb_strlen($caption) > 280) {
                $this->command?->error("Day {$day} caption is over 280 chars — fix before seeding.");

                continue;
            }

            $post = SocialPost::firstOrCreate(
                ['title' => sprintf('X calendar — day %02d', $day)],
                [
                    'status' => SocialPostStatus::Scheduled,
                    'pillar' => $pillar,
                    'language' => $lang,
                    'caption_base' => $caption,
                    'cta_type' => $cta,
                    'compliance_checked' => true,
                    // app.timezone IS Asia/Karachi (verified in prod tinker,
                    // 18 Aug 2026) — do NOT convert to UTC here. Eloquent's
                    // datetime cast renders a Carbon in the instance's own
                    // timezone on save but re-parses the stored string in the
                    // APP timezone on read, so a ->utc() here lands every post
                    // five hours early. The create_social_posts migration's
                    // "Stored UTC" comment is wrong.
                    'scheduled_at' => $start->copy()->addDays($day - 1)->setTime(20, 0),
                ],
            );

            $post->targets()->firstOrCreate(
                ['platform' => SocialPlatform::X],
                ['status' => SocialTargetStatus::Pending],
            );
        }

        $this->command?->info('X content calendar seeded (30 days, manual flow).');
    }

    /**
     * @return list<array{ContentLanguage, SocialPillar, SocialCtaType, string}>
     */
    private function captions(): array
    {
        $ru = ContentLanguage::RomanUrdu;
        $en = ContentLanguage::English;

        return [
            [$ru, SocialPillar::HowTo, SocialCtaType::None,
                'Til ka tel (sesame oil) subcontinent mein sadiyon se maalish ke liye istemal hota aa raha hai. Halka hai, jald jazb hota hai, aur rooki jild ko naram rakhta hai. Hamare Lookman-e-Hayat oil ka 97% yehi til ka tel hai — poori list website par chhapi hai. #HerbalPakistan'],
            [$en, SocialPillar::BehindTheScenes, SocialCtaType::None,
                'Every product we sell lists its FULL ingredients — INCI names, everything. And we also publish the list of ingredients we will never stock. No secrets, no "special formula" stories. That\'s the whole brand. #SkincarePakistan'],
            [$ru, SocialPillar::CustomerQa, SocialCtaType::None,
                'Online herbal products lete waqt 3 sawal zaroor poochein: 1) Poori ingredient list kahan hai? 2) Seller ka asli number/address hai? 3) Return policy likhi hai? Jo seller in teeno ka jawab na de, wahan se kuch na lein.'],
            [$en, SocialPillar::HowTo, SocialCtaType::DmToOrder,
                'Guggul (Commiphora mukul) is a tree resin used in traditional massage oils for centuries. It\'s the other 3% of our Lookman-e-Hayat oil — the full breakdown is public on our site. Questions? DM us.'],
            [$ru, SocialPillar::BehindTheScenes, SocialCtaType::WhatsappOrder,
                'Pakistan mein online shopping ka sab se bara darr: paisa pehle, maal baad mein. Isi liye hum sirf Cash on Delivery karte hain — maal haath mein aaye, tab paisa dein. Order WhatsApp par: 0301 2973886 #Karachi'],
            [$ru, SocialPillar::ComfortMassage, SocialCtaType::None,
                'Champi ka sahi tareeqa: tel ko halka garam karein (garam nahi, halka garam), ungliyon ke poron se scalp par gol dairon mein lagayein, 10 minute. Zor se ragarna balon ko todta hai. Hafte mein 2 baar kafi hai.'],
            [$en, SocialPillar::CustomerQa, SocialCtaType::None,
                'Herbal doesn\'t mean magic. No oil "removes" scars in a week — anyone promising that is selling you a story. What good oils do: soften skin, support massage, smell like your nani\'s house. That\'s enough. #HerbalCare'],
            [$ru, SocialPillar::BehindTheScenes, SocialCtaType::None,
                'Neem ka istemal jild ki dekhbhal mein sadiyon purana hai. Acne-prone jild wale aksar neem sabun pasand karte hain. Hum jald neem soap stock karne wale hain — kis size mein chahiye, batayein? #Karachi'],
            [$en, SocialPillar::CustomerQa, SocialCtaType::None,
                'Shilajit sellers won\'t tell you: it can interact with medication, overdoing it causes side effects, and half the market is fake. Before you buy from ANYONE, research it. We\'ll share how to spot the real thing this month. #Salajeet'],
            [$ru, SocialPillar::CustomerQa, SocialCtaType::None,
                'Asli salajeet ki pehchan ke 5 gharelu tareeqay hum agle hafte share karenge. Tab tak ek mashwara: jo salajeet "guarantee shuda taqat" ka wada kare, wo pehchan khud hi ho gayi — nakli ya dhoka. #Salajeet'],
            [$en, SocialPillar::BehindTheScenes, SocialCtaType::None,
                'Sidr (beri) leaves have been used for hair and skin washing in this region for centuries. We\'re bringing a sidr leaf soap soon — nobody else in Pakistan stocks one. Watch this space.'],
            [$ru, SocialPillar::HowTo, SocialCtaType::None,
                'Naya oil ya cream lagane se pehle patch test karein: kalai ke andar thora sa lagayein, 24 ghante dekhein. Kharish ya lali ho to istemal na karein. Ye rule HAR brand par lagta hai — hamara bhi.'],
            [$ru, SocialPillar::BehindTheScenes, SocialCtaType::WhatsappOrder,
                'Lookman-e-Hayat Herbal Oil: 50ml Rs 1,200 · 100ml Rs 2,200. Til 97% + guggul 3%. Maalish aur rozana jild ki dekhbhal ke liye. Poore Pakistan COD, 7 din return. WhatsApp: 0301 2973886 #Karachi'],
            [$en, SocialPillar::BehindTheScenes, SocialCtaType::None,
                'We don\'t claim "halal certified" — because we hold no certificate, and pretending otherwise would be a lie. What we do instead: publish every ingredient so YOU can check. Honesty > badges. #GlowHalal'],
            [$ru, SocialPillar::BehindTheScenes, SocialCtaType::None,
                'Kalonji (black seed) ka zikr tibb ki kitabon mein sadiyon se hai. Balon ke liye kalonji oil subcontinent ka purana totka hai. Hum jald pure kalonji oil stock kar rahe hain. Kaun kaun use karta hai? 👇'],
            [$en, SocialPillar::SeasonalSkincare, SocialCtaType::None,
                'Karachi humidity + sweat + sunscreen = clogged skin. Light oils after washing at night (not before going out) is how oil-users manage it. Heavy greasy layers in this weather? Skip them. #Karachi'],
            [$ru, SocialPillar::BehindTheScenes, SocialCtaType::None,
                'Amla ko balon ka purana saathi kaha jata hai — dadi nani ke zamane se. Amla oil hum jald la rahe hain. Sawal: aap amla oil khud lagate hain ya mehndi mein mila kar? Har ghar ka apna tareeqa hota hai 😄'],
            [$en, SocialPillar::CustomerQa, SocialCtaType::DmToOrder,
                'Q: "Will your oil remove my scars?" A: No — and no oil will. Moisturised skin can LOOK smoother over time, but "scar removal in days" is a marketing lie. We\'d rather lose a sale than lie to you. DM your questions.'],
            [$ru, SocialPillar::SeasonalSkincare, SocialCtaType::None,
                'Multani mitti ka face pack garmiyon ka classic hai: mitti + arq-e-gulab, 10 minute, dho lein. Rooki jild wale hafte mein 1 baar se zyada na karein. Simple cheezain aksar behtreen hoti hain.'],
            [$en, SocialPillar::BehindTheScenes, SocialCtaType::None,
                'Why "Glow Halal"? Because we check every ingredient\'s source — animal-derived glycerin, carmine, alcohol — and publish what we found. Halal-conscious by transparency, not by a sticker. #SkincarePakistan'],
            [$ru, SocialPillar::CustomerQa, SocialCtaType::None,
                'Ashwagandha aaj kal har jagah hai — lekin kya aap jante hain iske side effects bhi hain? Neend, dawaon ke sath interaction, dosage ka masla. Kisi bhi herbal cheez ko "bilkul safe" samajhna ghalti hai. Research karein, phir lein.'],
            [$en, SocialPillar::BehindTheScenes, SocialCtaType::WhatsappOrder,
                'How ordering works: WhatsApp us → we confirm by SMS → courier arrives in 2-4 working days (Karachi/Lahore/Islamabad) → you pay cash at the door. Rs 300 delivery, free over Rs 5,000. Simple. 0301 2973886 #Karachi'],
            [$ru, SocialPillar::HowTo, SocialCtaType::None,
                'Aloe vera taza patton se nikaal kar lagana behtareen hai — lekin har ghar mein paudha nahi hota. Bazaari aloe products mein asal aloe kitna hai? Ingredient list parhein. 1% wale "aloe" cream se paudha behtar hai.'],
            [$en, SocialPillar::CustomerQa, SocialCtaType::None,
                'Whitening creams promising "gora rang in 7 days" often contain mercury or steroids — dangerous, and sold openly. We will NEVER stock a whitening product. Your skin tone was never the problem. #SkincarePakistan'],
            [$ru, SocialPillar::HowTo, SocialCtaType::None,
                'Balon mein tel raat bhar rakhna zaroori nahi — 1-2 ghante kafi hain, phir dho lein. Zyada dair scalp par mail jama karti hai. Purane tareeqay achhe hain, lekin har purani baat sahi nahi hoti.'],
            [$en, SocialPillar::HowTo, SocialCtaType::None,
                'Read any label: ingredients are listed by QUANTITY, highest first. If "aqua" is #1 and the herb is #9, you\'re buying scented water. This one rule will save you thousands of rupees. #HerbalCare'],
            [$ru, SocialPillar::BehindTheScenes, SocialCtaType::None,
                'Hamari har review asli hai — WhatsApp par customer se li gayi, jaisi ki waisi. Na paisay de kar likhwai, na khud likhi. Kam reviews sahi, jhooti nahi. Yehi farq hai.'],
            [$en, SocialPillar::HowTo, SocialCtaType::None,
                'Sesame (til) oil is one of the most studied traditional oils — light texture, absorbs well, rich in natural antioxidants. It\'s 97% of our flagship oil. Sometimes the boring ingredient is the best one.'],
            [$ru, SocialPillar::SeasonalSkincare, SocialCtaType::None,
                'Sardiyon ki taiyari abhi se: rooki jild walon ke liye Oct-Nov mushkil mahine hote hain. Halka tel roz raat, garam pani se kam nahana, aur loofah ka kam istemal — jild shukriya ada karegi.'],
            [$ru, SocialPillar::BehindTheScenes, SocialCtaType::None,
                'Ek mahina ho gaya X par! Jo seekha: yahan log sawal poochte hain, hype nahi khareedte. Yehi hamara style hai. Sawal poochte rahein — jild, balon, ingredients ke bare mein. Jawab dena hamara kaam hai. #GlowHalal'],
        ];
    }
}
