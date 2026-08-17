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
 * v2 (owner feedback, 18 Aug 2026): every caption now ends with 2-3 hashtags
 * INSIDE the caption text (one copy = everything), and every post makes one
 * concrete point — a product, a price, a usable tip, or a direct question.
 * The v1 set had deliberately sparse tags and a few abstract musings; the
 * owner called those out, and the owner posts these daily, so the owner wins.
 *
 * Still non-negotiable in every caption (do not "improve" away):
 *  - No cure/treatment claims; cosmetic and traditional-use framing only.
 *  - No "halal certified" (day 14 openly says we hold no certificate).
 *  - Reseller language: stock, never make/formulate.
 *  - ≤ 280 chars including tags (free-account cap) — guarded below.
 *  - The only phone number that may ever appear: 0301 2973886.
 *  - Tags stay RELEVANT (#Karachi, niche herbs) — no trend-hijacking.
 *
 * Re-running is safe: a post whose X target was already posted (or skipped)
 * is never touched; pending ones are refreshed with the current caption.
 * Scheduling: one post/day, 20:00 PKT (app timezone IS Asia/Karachi — no UTC
 * conversion here; see the v1 five-hours-early bug in git history).
 */
class XContentCalendarSeeder extends Seeder
{
    public function run(): void
    {
        $start = Carbon::today('Asia/Karachi')->addDay();

        foreach ($this->captions() as $i => [$lang, $pillar, $cta, $caption]) {
            $day = $i + 1;

            if (mb_strlen($caption) > 280) {
                $this->command?->error("Day {$day} caption is ".mb_strlen($caption)." chars — over the 280 cap, fix before seeding.");

                continue;
            }

            $post = SocialPost::firstOrNew(['title' => sprintf('X calendar — day %02d', $day)]);

            // Never rewrite a post that already went out — the archive should
            // show what was actually published.
            $alreadyOut = $post->exists && $post->targets()
                ->where('platform', SocialPlatform::X)
                ->whereIn('status', [SocialTargetStatus::PostedManually, SocialTargetStatus::PostedApi])
                ->exists();

            if ($alreadyOut) {
                continue;
            }

            $post->forceFill([
                'status' => SocialPostStatus::Scheduled,
                'pillar' => $pillar,
                'language' => $lang,
                'caption_base' => $caption,
                'cta_type' => $cta,
                'compliance_checked' => true,
                'scheduled_at' => $post->scheduled_at
                    ?? $start->copy()->addDays($day - 1)->setTime(20, 0),
            ])->save();

            $post->targets()->firstOrCreate(
                ['platform' => SocialPlatform::X],
                ['status' => SocialTargetStatus::Pending],
            );
        }

        $this->command?->info('X content calendar seeded/refreshed (30 days, manual flow).');
    }

    /**
     * @return list<array{ContentLanguage, SocialPillar, SocialCtaType, string}>
     */
    private function captions(): array
    {
        $ru = ContentLanguage::RomanUrdu;
        $en = ContentLanguage::English;

        return [
            [$ru, SocialPillar::BehindTheScenes, SocialCtaType::WhatsappOrder,
                'Til ka tel — hamare Lookman-e-Hayat oil ka 97%. Halka, jald jazb hone wala, rooki jild ke liye behtareen. 50ml Rs 1,200, COD poore Pakistan mein. Order: WhatsApp 0301 2973886 #Karachi #Pakistan #HerbalPakistan'],
            [$ru, SocialPillar::BehindTheScenes, SocialCtaType::LearnMore,
                'Har product ki POORI ingredient list hamari website par chhapi hai — koi "khaas formula" ka parda nahi. Aur jo cheezein hum kabhi stock nahi karenge, unki list bhi public hai. Khud check karein. #SkincarePakistan #Pakistan #GlowHalal'],
            [$ru, SocialPillar::CustomerQa, SocialCtaType::None,
                'Online herbal products lete waqt 3 sawal: 1) Ingredient list kahan hai? 2) Seller ka asli number/address? 3) Return policy likhi hai? Teeno ka jawab na mile to mat lein. Hamare teeno jawab website par hain. #OnlineShoppingPakistan #Karachi #Pakistan'],
            [$en, SocialPillar::HowTo, SocialCtaType::DmToOrder,
                'Guggul (Commiphora mukul) — the tree resin that makes up the other 3% of our Lookman-e-Hayat oil. Used in traditional massage oils for centuries. Full breakdown is public on our site. Questions? DM us. #HerbalPakistan #SkincarePakistan'],
            [$ru, SocialPillar::BehindTheScenes, SocialCtaType::WhatsappOrder,
                'Paisa pehle wala dhoka nahi: hum SIRF Cash on Delivery karte hain. Maal haath mein aaye, tab payment dein. Karachi/Lahore/Islamabad 2-4 din mein. Order: WhatsApp 0301 2973886 #Karachi #Pakistan #OnlineShoppingPakistan'],
            [$ru, SocialPillar::ComfortMassage, SocialCtaType::WhatsappOrder,
                'Champi ka sahi tareeqa: tel halka garam karein, ungliyon ke poron se 10 minute gol dairon mein maalish. Zor se ragarna balon ko todta hai. Hafte mein 2 baar kafi hai. Maalish ka tel chahiye? 0301 2973886 #HairCare #Karachi #Pakistan'],
            [$ru, SocialPillar::CustomerQa, SocialCtaType::None,
                '"7 din mein daagh khatam" — aisa wada karne wala aap ko jhoot bech raha hai. Acha tel jild ko naram rakhta hai aur maalish mein kaam aata hai, bas. Hum jhoota wada nahi karte — isi liye customer wapas aata hai. #SkincarePakistan #Pakistan'],
            [$ru, SocialPillar::BehindTheScenes, SocialCtaType::None,
                'Neem sabun jald aa raha hai Glow Halal par — acne-prone jild walon ka purana pasandida. Launch se pehle demand dekhni hai: kaun kaun lega? Reply karein 👇 #Karachi #Pakistan #SkincarePakistan'],
            [$en, SocialPillar::CustomerQa, SocialCtaType::None,
                'Shilajit 101: it can interact with medicines, overdoing it has side effects, and much of the market is fake. Research before buying from anyone — including us. Honest sellers survive scrutiny. #Salajeet #Pakistan'],
            [$ru, SocialPillar::CustomerQa, SocialCtaType::None,
                'Asli salajeet ki 2 fori pehchan: 1) Garam pani mein poora ghul jaye, talchhat na chhore. 2) "Guaranteed taqat" ka wada kare to seedha nakli ya dhoka. Salajeet hum jald stock kar rahe hain — sirf asli. #Salajeet #Pakistan #Karachi'],
            [$ru, SocialPillar::BehindTheScenes, SocialCtaType::None,
                'Beri (sidr) ke patte — is khitte mein sadiyon se jild aur balon ki dhulai ke liye istemal hote aaye hain. Hum Pakistan ka pehla sidr leaf soap la rahe hain. Intezar karein. #Karachi #Pakistan #HerbalPakistan'],
            [$ru, SocialPillar::HowTo, SocialCtaType::None,
                'Har naya oil ya cream lagane se pehle: kalai par thora sa lagayein, 24 ghante dekhein. Lali ya kharish ho to na lagayein. Ye rule hamare products par bhi lagta hai — har jild alag hoti hai. #SkinCare #SkincarePakistan #Pakistan'],
            [$ru, SocialPillar::BehindTheScenes, SocialCtaType::WhatsappOrder,
                'Lookman-e-Hayat Herbal Oil: 50ml Rs 1,200 | 100ml Rs 2,200. Til 97% + guggul 3% — poori list website par. COD poore Pakistan, 7 din return. Order: WhatsApp 0301 2973886 #Karachi #Pakistan #HerbalPakistan'],
            [$ru, SocialPillar::BehindTheScenes, SocialCtaType::None,
                'Hum "halal certified" ka dawa NAHI karte — kyunke certificate hamare paas nahi hai. Jhoota badge lagana asaan tha. Hum ne mushkil rasta chuna: har ingredient public. Aap khud check karein. #GlowHalal #Pakistan #SkincarePakistan'],
            [$ru, SocialPillar::BehindTheScenes, SocialCtaType::None,
                'Kalonji ka tel — tibb ki kitabon se le kar dadi ke totkon tak, balon ke liye subcontinent ka purana bharosa. Hum pure kalonji oil la rahe hain. Aap kalonji kis kaam ke liye istemal karte hain? Reply karein 👇 #Kalonji #Pakistan #HairCare'],
            [$ru, SocialPillar::SeasonalSkincare, SocialCtaType::None,
                'Karachi ki humidity mein chipchipi jild? Din mein heavy oil aur cream skip karein; raat ko munh dho kar sirf halka tel — bas itna. Zyada products = zyada masla. #Karachi #SkinCare #Pakistan'],
            [$ru, SocialPillar::BehindTheScenes, SocialCtaType::None,
                'Amla — balon ka purana saathi. Koi seedha tel lagata hai, koi mehndi mein mila kar. Aap ke ghar mein kaunsa tareeqa chalta hai? Reply karein 👇 Amla oil jald hamare paas bhi aa raha hai. #HairCare #Pakistan #Karachi'],
            [$ru, SocialPillar::CustomerQa, SocialCtaType::None,
                'Sawal: "Kya aap ka oil daagh khatam kar dega?" Jawab: Nahi — koi oil nahi karta. Naram, moisturised jild waqt ke sath behtar NAZAR aa sakti hai, magar "dinon mein daagh gayab" jhoot hai. Hum sale se pehle sach bolte hain. #SkincarePakistan #Pakistan'],
            [$ru, SocialPillar::SeasonalSkincare, SocialCtaType::None,
                'Garmi ka classic: multani mitti + arq-e-gulab, 10 minute, phir dho lein. Rooki jild wale hafte mein sirf 1 baar karein. Sasta, saada, sadiyon se aazmuda. #SkinCare #Pakistan #Karachi'],
            [$ru, SocialPillar::BehindTheScenes, SocialCtaType::None,
                '"Glow Halal" naam kyun? Kyunke hum har ingredient ka source check karte hain — animal glycerin, carmine, alcohol — aur jo mila wo publish kar dete hain. Sticker se nahi, transparency se halal-conscious. #GlowHalal #Pakistan #Karachi'],
            [$ru, SocialPillar::CustomerQa, SocialCtaType::None,
                'Ashwagandha har jagah hai — lekin side effects bhi hain: neend par asar, dawaon se interaction, dosage ke masail. "Herbal = 100% safe" sab se bara myth hai. Research karein, phir lein. #HerbalPakistan #Pakistan'],
            [$ru, SocialPillar::BehindTheScenes, SocialCtaType::WhatsappOrder,
                'Order kaise hota hai: WhatsApp karein → SMS se confirm → 2-4 din mein courier (bare shehr) → darwaze par cash dein. Delivery Rs 300, Rs 5,000 se upar FREE. 0301 2973886 #Karachi #Pakistan #OnlineShoppingPakistan'],
            [$ru, SocialPillar::HowTo, SocialCtaType::None,
                'Bazaari "aloe vera" cream mein asal aloe kitna hota hai? Ingredient list mein aloe agar aakhir mein likha hai to na hone ke barabar hai. List parhna seekh lein — hazaron rupay bachenge. #SkincarePakistan #Pakistan'],
            [$ru, SocialPillar::CustomerQa, SocialCtaType::None,
                '"7 din mein rang gora" wali creams mein aksar mercury ya steroids hote hain — jild ko tabah kar dete hain. Hum whitening product KABHI stock nahi karenge. Aap ka rang kabhi masla tha hi nahi. #SkincarePakistan #Pakistan #Karachi'],
            [$ru, SocialPillar::HowTo, SocialCtaType::None,
                'Balon mein tel raat bhar rakhna zaroori nahi — 1-2 ghante kafi hain, phir dho lein. Zyada dair scalp par mail jamati hai. Har purani baat sahi nahi hoti. #HairCare #Pakistan'],
            [$ru, SocialPillar::HowTo, SocialCtaType::None,
                'Label ka usool: ingredients zyada miqdar se kam ki taraf likhe jate hain. "Aqua" pehle number par aur jari booti nauvein par? Aap khushbudar pani khareed rahe hain. Ye ek rule aap ke paise bachayega. #SkincarePakistan #HerbalPakistan #Pakistan'],
            [$ru, SocialPillar::BehindTheScenes, SocialCtaType::None,
                'Hamari har review WhatsApp par asli customer se aayi hai — jaisi likhi gayi, waisi lagayi. Na paise de kar likhwai, na khud banayi. Kam magar sachi. Yehi hamara tareeqa hai. #GlowHalal #Pakistan #Karachi'],
            [$ru, SocialPillar::BehindTheScenes, SocialCtaType::WhatsappOrder,
                'Til ka tel duniya ke sab se zyada studied riwayati telon mein se hai — halka, jald jazb, qudrati antioxidants. Isi liye hamare flagship oil ka 97% yehi hai. Order: WhatsApp 0301 2973886 #HerbalPakistan #Pakistan #Karachi'],
            [$ru, SocialPillar::SeasonalSkincare, SocialCtaType::None,
                'Sardiyan aane wali hain — rooki jild walon ke liye Oct-Nov mushkil mahine hote hain. Abhi se aadat banayein: raat ko halka tel, garam pani kam, loofah kam. Jild shukriya ada karegi. #SkinCare #Pakistan #Karachi'],
            [$ru, SocialPillar::CustomerQa, SocialCtaType::None,
                'Jild ya balon ke bare mein koi sawal? Ingredient ka naam samajh nahi aa raha? Reply karein — seedha, imandar jawab denge. Bechne ke liye nahi, batane ke liye. 👇 #SkincarePakistan #Karachi #Pakistan'],
            [$ru, SocialPillar::BehindTheScenes, SocialCtaType::WhatsappOrder,
                'Ek mahina X par mukammal! Aap ke sawalon ne raunaq lagayi. Jild, balon ya kisi ingredient par sawal ho to kabhi bhi reply karein. Order karna ho to WhatsApp: 0301 2973886 #GlowHalal #Karachi #Pakistan'],
        ];
    }
}
