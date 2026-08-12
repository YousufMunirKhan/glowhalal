<?php

use App\Models\Product;
use Illuminate\Database\Migrations\Migration;

/**
 * SEO quick win for the 50 ml Lookman-e-Hayat page — the URL Google already
 * elected to rank for the head term (docs SERP analysis, Aug 2026).
 *
 * Two changes, 50 ml ONLY (deliberately not the 100 ml — concentrating the
 * face/pimples relevance on the elected page also helps de-duplicate the two
 * near-identical PDPs):
 *
 *  1. The exact-match spelling the #1 Daraz result ranks on — "lookmaan e hayat
 *     oil" (double-a) — added to visible body copy and the image alt text. It
 *     was on NO page before; the title is left alone (60-char cap / no stuffing).
 *
 *  2. The two open "People also ask" questions no competitor answers — "can I
 *     use it on the face?" and "is it good for pimples?" — appended to the FAQ,
 *     which renders visibly AND feeds the FAQPage JSON-LD. Answers are honest
 *     and claim-free (it is a massage/skin oil, NOT an acne treatment) per the
 *     content-honesty rules — no cure claim, no certification claim.
 *
 * Idempotent and non-destructive: guards on every field, touches nothing else
 * (prices, other FAQs, the 100 ml row all untouched). Safe to re-run.
 */
return new class extends Migration
{
    public function up(): void
    {
        $product = Product::where('slug', 'herbal-skin-oil-50ml')->first();

        if (! $product) {
            return; // fresh/partial DB without the owner products — nothing to do.
        }

        // 1a) Spelling variant in the description body (natural, in the opener).
        $desc = (string) $product->description;
        if (stripos($desc, 'lookmaan') === false) {
            $anchor = '(Lookman E Hayat Tel)';
            $withVariant = '(also spelled lookmaan e hayat oil, Lookman E Hayat Tel or Luqman-e-Hayat Tel)';

            $product->description = str_contains($desc, $anchor)
                ? str_replace($anchor, $withVariant, $desc)
                : $desc."\n<p>You may also see this oil spelled <strong>lookmaan e hayat oil</strong>, "
                    ."lukman e hayat tel or luqman-e-hayat — it is the same traditional oil.</p>";
        }

        // 1b) Spelling variant in the primary image alt text.
        $image = $product->images()->where('is_primary', true)->first()
            ?? $product->images()->first();
        if ($image && stripos((string) $image->alt_text, 'lookmaan') === false) {
            $image->alt_text = 'Lookman-e-Hayat (lookmaan e hayat / Luqman-e-Hayat) herbal oil 50 ml bottle';
            $image->save();
        }

        // 2) Append the two missing PAA answers — only if not already present.
        $faqs = $product->faqs ?? [];
        $existing = strtolower(implode(' | ', array_column($faqs, 'q')));

        $additions = [];

        if (! str_contains($existing, 'chehre') && ! str_contains($existing, 'face')) {
            $additions[] = [
                'q' => 'Kya Lookman-e-Hayat tel chehre par laga sakte hain? (Can I use it on the face?)',
                'a' => 'Yes — it can be used on the face as a light massage or overnight moisturising oil, '
                    .'and many people use it on old, fully-healed marks. Use only a small amount, patch-test '
                    .'on your inner forearm first, and keep it well away from the eyes. If your skin is oily '
                    .'or acne-prone, use it sparingly at night and stop if you notice any breakouts. Never '
                    .'apply it to fresh, broken or unhealed skin.',
            ];
        }

        if (! str_contains($existing, 'pimple') && ! str_contains($existing, 'muhaanse') && ! str_contains($existing, 'keel')) {
            $additions[] = [
                'q' => 'Kya ye keel-muhaanse (pimples) ke liye theek hai? (Is it good for pimples / acne?)',
                'a' => 'It is a traditional massage and skin oil, not an acne treatment, and we make no claim '
                    .'that it clears pimples. Sesame oil is fairly rich, so on acne-prone skin a heavy layer '
                    .'can sometimes make breakouts worse. If you want to try it, use a tiny amount at night, '
                    .'patch-test first, and stop if your skin reacts. For persistent acne, a doctor or '
                    .'dermatologist is the right person to see.',
            ];
        }

        if ($additions !== []) {
            $product->faqs = array_merge($faqs, $additions);
        }

        $product->save();
    }

    public function down(): void
    {
        // One-way content patch; nothing to roll back safely without clobbering
        // later admin edits. Intentionally a no-op.
    }
};
