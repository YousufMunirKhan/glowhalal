<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * /about — the brand story page, neutral version at the owner's request:
 * no founder name, no persona, no invented anecdotes. Every sentence here is
 * verifiable fact about how the store actually operates. The honesty section
 * IS the marketing — in a market full of cure-claim sellers, saying what we
 * don't do is the strongest trust signal we own (brand plan §trust-assets).
 *
 * DEPLOY-SAFE like LegalPagesSeeder: content fills only when blank, so owner
 * edits in Admin → Content → Pages are never overwritten by re-seeding.
 */
class AboutPageSeeder extends Seeder
{
    public function run(): void
    {
        $page = Page::withTrashed()->firstOrNew(['slug' => 'about']);

        $page->fill([
            'title' => ($page->exists && $page->title) ? $page->title : 'About Glow Halal',
            'template' => 'default',
            'status' => 'published',
            'published_at' => $page->published_at ?? now(),
            'is_system' => true,
            'show_in_footer' => true,
            'show_in_header' => false,
            'position' => 5,
        ]);

        if (blank($page->content)) {
            $page->content = $this->content();
        }

        if ($page->trashed()) {
            $page->restore();
        }

        $page->save();

        if (! $page->seoMeta) {
            $page->seoMeta()->create([
                'meta_description' => 'Glow Halal is a Karachi-based halal personal care brand — no medical claims, COD across Pakistan. Har product par poori ingredient list, koi jhoothe wade nahi.',
            ]);
        }
    }

    private function content(): string
    {
        return <<<'HTML'
<p><strong>Glow Halal — halal beauty, honestly made.</strong> Karachi se, poore Pakistan tak — Cash on Delivery.</p>

<h2>Hum kaun hain</h2>
<p>Glow Halal ek Karachi-based halal personal care brand hai. Hum riwayati jari bootiyon par mabni products chunte hain — aaj herbal oils, aur aage soaps, creams, hair oils aur sirf DRAP-listed supplements bhi. Har product hum pehle khud parakhte hain: <strong>hum sirf wohi bechte hain jo hum apne ghar mein rakh saken.</strong></p>

<h2>Hamare 3 usool</h2>
<ul>
  <li><strong>Poori ingredient list, har product par.</strong> Kya andar hai aur kya hum kabhi istemal nahi karte — dono hamari website par publish hote hain, taake aap khud parakh saken.</li>
  <li><strong>Koi medical claim nahi.</strong> Hamare products cosmetics hain, dawai nahi. Yeh kisi bimari ka ilaj nahi karte, aur jo cheez "ilaj" ya "guarantee" ka wada kare, wo aap ko hum se nahi milegi. Serious skin ya health ke maslay par hamesha doctor se rujoo karein.</li>
  <li><strong>Koi jhoot nahi.</strong> Na fake reviews, na kharide hue followers, na banawti "certified" stamps. Halal hamari pehchan aur ingredient ka meyar hai — hum kisi third-party halal certification ka dawa nahi karte, kyunki hamare paas koi certificate nahi hai. Jab hoga, tab kahenge.</li>
</ul>

<h2>Hum seller hain — honest seller</h2>
<p>Hamare kuch products doosre manufacturers banate hain aur hum unhe original packaging mein bechte hain. Aise har product par hum wohi maloomat aage dete hain jo manufacturer ke label par hai — na kam, na zyada. Saaf baat, saaf sauda.</p>

<h2>Kaise order karein</h2>
<p>WhatsApp <a href="https://wa.me/923012973886">0301 2973886</a> par message karein ya seedha website se order karein — <strong>koi advance payment nahi.</strong> Parcel aap ke haath mein aata hai, aap check karte hain, phir payment hoti hai. Delivery poore Pakistan mein, aam tor par 2–7 working days. Tafseel ke liye <a href="/shipping-returns">Shipping &amp; Returns</a> dekhein.</p>

<p>Koi sawal ho to <a href="/contact">contact page</a> se poochein — jawab ek asli insaan deta hai.</p>
HTML;
    }
}
