<?php

use App\Models\BlogPost;
use App\Models\Product;
use Illuminate\Database\Migrations\Migration;

/**
 * CTR optimisation for the pages that already rank but barely get clicked.
 *
 * GSC (to 27 Aug 2026) showed ~1,250 impressions but almost no clicks: the
 * "uses/benefits/price" blog alone had 653 impressions at avg position ~5.7
 * with 0.6% CTR, and several brand queries sat on page 1 with zero clicks. The
 * bottleneck is the search snippet, not the ranking — so this sets tighter,
 * keyword-front-loaded, price-forward meta titles + descriptions (matched to
 * the actual queries: "uses for skin", "benefits", "price") on the top pages.
 * Content and H1s are untouched. All copy is claim-free (no cure/efficacy) per
 * the content-honesty rule. Idempotent — updateOrCreate on the seoMeta.
 */
return new class extends Migration
{
    public function up(): void
    {
        // slug => [meta_title, meta_description]
        $blogs = [
            'lookman-e-hayat-oil-uses-benefits-price' => [
                'Lookman e Hayat Oil: Uses, Benefits & Price (2026)',
                'Lookman-e-Hayat oil ke uses for skin, hair & massage, its til + guggul benefits, aur Pakistan mein asal price (50ml Rs 1,200). Aik honest, poori guide.',
            ],
            'lookman-e-hayat-oil-for-cuts-and-burns' => [
                'Lookman e Hayat Oil for Cuts & Burns: Safe Use Guide',
                'How Lookman-e-Hayat oil is traditionally used on the skin — plus the safety rules for cuts and burns you must know first. Honest, doctor-first advice.',
            ],
            'lookman-e-hayat-oil-for-joint-pain' => [
                'Lookman e Hayat Oil for Joint Pain & Massage (Malish)',
                'Lookman-e-Hayat herbal oil for massage and tired joints: how to warm and apply it, how often, and safety tips. Til + guggul oil, COD in Pakistan.',
            ],
            'sidr-leaves-benefits-skin-hair' => [
                'Sidr (Lote Tree) Leaves: Benefits for Skin & Hair',
                'What sidr / lote tree (beri) leaves are traditionally used for on skin and hair, how to use them, and what research suggests. A practical, honest guide.',
            ],
            'lookman-e-hayat-oil-price-in-pakistan' => [
                'Lookman e Hayat Oil Price in Pakistan 2026 (50 & 100ml)',
                'Lookman-e-Hayat oil price in Pakistan: 50ml Rs 1,200, 100ml Rs 2,200 — original, Cash on Delivery nationwide. See both sizes and how to order.',
            ],
        ];

        foreach ($blogs as $slug => [$title, $desc]) {
            $post = BlogPost::where('slug', $slug)->where('locale', 'en')->first();
            $post?->seoMeta()->updateOrCreate([], ['meta_title' => $title, 'meta_description' => $desc]);
        }

        $products = [
            'herbal-skin-oil-50ml' => [
                'Lookman e Hayat Oil 50ml – Rs 1,200 COD Pakistan',
                'Original Lookman-e-Hayat Tel (lookmaan e hayat oil) 50ml — til + guggul herbal oil for skin, massage & hair. Rs 1,200, Cash on Delivery. Order now.',
            ],
            'herbal-skin-oil-100ml' => [
                'Lookman e Hayat Oil 100ml – Rs 2,200 COD Pakistan',
                'Original Lookman-e-Hayat Tel 100ml — the value size. Til + guggul herbal oil for skin, massage & hair. Rs 2,200, Cash on Delivery nationwide.',
            ],
        ];

        foreach ($products as $slug => [$title, $desc]) {
            $product = Product::where('slug', $slug)->first();
            $product?->seoMeta()->updateOrCreate([], ['meta_title' => $title, 'meta_description' => $desc]);
        }
    }

    public function down(): void
    {
        // One-way SEO copy change; nothing to roll back safely.
    }
};
