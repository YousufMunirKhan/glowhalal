<?php

use App\Models\BlogPost;
use App\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Fixes from the 11-agent adversarial SEO audit of the bilingual PDP launch
 * (16 Aug 2026). Every change below was a CONFIRMED live finding:
 *
 * 1. UR blog "asli…kahan se lein — Qeemat": the title/H1 suffix collided with
 *    the UR 50ml PDP's primary keyword ("…50ml qeemat"). Suffix dropped; the
 *    blog keeps its reserved phrase, the PDP now owns "qeemat" outright and
 *    gains it in the H1.
 * 2. EN PDPs used the exact "Lookman e Hayat Tel {size}" strings (the UR
 *    pages' keyword roots) in meta descriptions / short descriptions —
 *    blurring the deliberate EN="Oil" / UR="Tel" split. Tel→Oil there.
 *    (The EN H1 parenthetical "(Luqman-e-Hayat Tel)" is deliberately KEPT —
 *    it captures the Luqman spelling variant; revisit only if GSC shows the
 *    EN PDP outranking the UR PDP on "tel" queries.)
 * 3. UR copy fixes: "daawa" idiom in the no-cure disclaimer; UR 100ml meta +
 *    lead no longer open with "Asli …" (the blog's head phrase); UR titles
 *    trimmed to SERP length; 50ml meta description no longer truncates
 *    mid-word; the jalne-kaatne pillar post is now linked from both PDPs; the
 *    guide links moved out of the uses section (no implied therapeutic use).
 *
 * All guarded/idempotent: string replacements only fire when the audited
 * string is present, so local (stale data) and prod both land correctly and
 * re-running is harmless.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->fixBlogTitle();
        $this->fixEnglishTelStrings();
        $this->rewriteUrProduct100();
        $this->rewriteUrProduct50();
    }

    public function down(): void
    {
        // Content-fix migration — no structural change, nothing to reverse.
    }

    private function fixBlogTitle(): void
    {
        $post = BlogPost::withoutGlobalScopes()
            ->where('slug', 'asli-lookman-e-hayat-tel-kahan-se-lein')
            ->where('locale', 'ur-Latn')
            ->first();

        if (! $post) {
            return;
        }

        if (str_contains((string) $post->title, 'Qeemat')) {
            $post->title = trim(preg_replace('/\s*[—–-]\s*Qeemat\s*$/u', '', $post->title));
        }

        // Meta description led with the per-size price list — the 50ml PDP's
        // job now. Refocused on the post's own intent (authenticity + where to
        // buy), wherever that description actually lives.
        $newDesc = 'Asli Lookman e Hayat Tel pehchanne aur mehfooz tareeqe se khareedne ka guide — sealed pack, batch aur expiry check, aur poore Pakistan mein Cash on Delivery.';

        foreach (['excerpt', 'meta_description'] as $column) {
            if (Schema::hasColumn('blog_posts', $column) && str_contains((string) $post->{$column}, 'Qeemat: 50ml')) {
                $post->{$column} = $newDesc;
            }
        }

        BlogPost::withoutEvents(fn () => $post->save());

        if ($post->seoMeta && str_contains((string) $post->seoMeta->meta_description, 'Qeemat: 50ml')) {
            $post->seoMeta->update(['meta_description' => $newDesc]);
        }

        if ($post->seoMeta && str_contains((string) $post->seoMeta->meta_title, 'Qeemat')) {
            $post->seoMeta->update([
                'meta_title' => trim(preg_replace('/\s*[—–-]\s*Qeemat\s*$/u', '', $post->seoMeta->meta_title)),
            ]);
        }
    }

    private function fixEnglishTelStrings(): void
    {
        // "Tel {size}" belongs to the UR pages now; EN keeps "Oil {size}".
        // Product NAME is untouched — the "(Luqman-e-Hayat Tel)" H1 stays.
        $swap = [
            'Lookman e Hayat Tel 100ml' => 'Lookman e Hayat Oil 100ml',
            'Lookman e Hayat Tel 50ml' => 'Lookman e Hayat Oil 50ml',
            'Lookman-e-Hayat Tel 100ml' => 'Lookman-e-Hayat Oil 100ml',
            'Lookman-e-Hayat Tel 50ml' => 'Lookman-e-Hayat Oil 50ml',
        ];

        Product::query()
            ->whereIn('slug', ['herbal-skin-oil-50ml', 'herbal-skin-oil-100ml'])
            ->with('seoMeta')
            ->get()
            ->each(function (Product $product) use ($swap) {
                $short = strtr((string) $product->short_description, $swap);

                if ($short !== $product->short_description) {
                    Product::withoutEvents(fn () => $product->forceFill(['short_description' => $short])->save());
                }

                if ($product->seoMeta) {
                    $desc = strtr((string) $product->seoMeta->meta_description, $swap);

                    if ($desc !== $product->seoMeta->meta_description) {
                        $product->seoMeta->update(['meta_description' => $desc]);
                    }
                }
            });
    }

    private function rewriteUrProduct100(): void
    {
        $product = Product::query()->where('slug', 'herbal-skin-oil-100ml')->first();

        if (! $product || blank($product->slug_ur)) {
            return;
        }

        Product::withoutEvents(fn () => $product->forceFill([
            // ≤65 chars with the " | Glow Halal" suffix; keyword front-loaded.
            'meta_title_ur' => 'Lookman e Hayat Tel 100ml Online Order',
            // Opens on the page's own keyword, not the blog's "Asli …" phrase.
            'meta_description_ur' => 'Lookman e Hayat Tel 100ml online order — Pakistan bhar mein Cash on Delivery. Sealed asli imported pack, til 97% + guggul 3%, Karachi se dispatch.',
            'short_description_ur' => 'Lookman e Hayat Tel 100ml — til (sesame) 97% + guggal 3%. Riwayati maalish ka tel, sealed asli imported pack, poore Pakistan mein Cash on Delivery. Sirf bairooni istemal.',
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
<h2>Zaroori ehtiyat</h2>
<ul>
<li>Taza jale, kate ya khule zakham par kabhi na lagayen — pehle 10–20 minute thanda paani daalein aur doctor se rujoo karein.</li>
<li>Aankhon aur munh se door rakhein. Sirf bairooni (external) istemal ke liye hai.</li>
<li>Pehli dafa istemal se pehle inner forearm par patch test karein aur 24 ghante intezar karein.</li>
<li>Bachon ki pohanch se door, thandi aur khushk jagah par rakhein. Pack par likhi expiry check karein.</li>
</ul>
<p><em>Ye aik riwayati herbal tel hai, koi dawa nahi — hum kisi bimari ke ilaj ka koi daawa nahi karte. Sehat ke kisi bhi masle ke liye doctor se mashwara karein.</em></p>
<p>Mazeed parhein: <a href="/ur-roman/blog/jodon-ke-dard-ka-tel">jodon ke dard ke liye riwayati tel ka istemal</a>, <a href="/ur-roman/blog/jalne-kaatne-par-lagane-wala-tel">jalne kaatne par lagane wale tel ki hifazati guide</a>, aur <a href="/ur-roman/blog/asli-lookman-e-hayat-tel-kahan-se-lein">asli Lookman e Hayat Tel kahan se lein</a>.</p>
HTML,
        ])->save());
    }

    private function rewriteUrProduct50(): void
    {
        $product = Product::query()->where('slug', 'herbal-skin-oil-50ml')->first();

        if (! $product || blank($product->slug_ur)) {
            return;
        }

        Product::withoutEvents(fn () => $product->forceFill([
            // H1 now carries "Qeemat" — the PDP owns the price intent outright.
            'name_ur' => 'Lookman e Hayat Tel 50ml — Qeemat aur Chota Pack',
            'meta_title_ur' => 'Lookman e Hayat Tel 50ml Qeemat – Order Karein',
            // Complete sentence inside the 158-char render limit (the old one
            // cut off at "…mein deliver").
            'meta_description_ur' => 'Lookman e Hayat Tel 50ml chota pack Rs 1,200 — try karne ke liye behtareen size. Sealed asli pack, poore Pakistan mein Cash on Delivery.',
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
<p>Mazeed parhein: <a href="/ur-roman/blog/jalne-kaatne-par-lagane-wala-tel">jalne kaatne par lagane wale tel ki hifazati guide</a>.</p>
HTML,
        ])->save());
    }
};
