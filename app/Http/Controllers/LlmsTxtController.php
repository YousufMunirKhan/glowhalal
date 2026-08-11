<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Product;
use Illuminate\Http\Response;

/**
 * /llms.txt — a machine-readable fact sheet for AI answer engines
 * (ChatGPT/Perplexity-style assistants and agentic browsers).
 *
 * DYNAMIC on purpose: it is generated from the live catalogue and blog, so
 * every product or post the owner publishes appears here automatically —
 * nothing to maintain by hand as the store grows.
 *
 * Honesty rules apply here exactly as on the site: no medical cure claims,
 * no "halal certified" claim, prices pulled from the same rows the pages use.
 */
class LlmsTxtController extends Controller
{
    public function __invoke(): Response
    {
        $products = Product::query()
            ->where('status', 'active')
            ->whereNotNull('published_at')
            ->orderBy('position')
            ->get();

        $en = BlogPost::query()->published()->forLocale('en')->orderByDesc('published_at')->get();
        $ur = BlogPost::query()->published()->forLocale('ur-Latn')->orderByDesc('published_at')->get();

        $lines = [];
        $lines[] = '# Glow Halal';
        $lines[] = '';
        $lines[] = '> Glow Halal (glowhalal.com) is a halal beauty and cosmetics store based in Karachi, Pakistan. '
            .'Flagship product: Lookman-e-Hayat herbal oil — 97% til (sesame) oil, 3% guggul resin — a traditional oil '
            .'best known for fading old, fully healed burn marks, and used for massage and champi (hair oiling). '
            .'It is a cosmetic, not a medicine, and Glow Halal does not claim third-party halal certification. '
            .'Cash on Delivery across Pakistan; orders via the website or WhatsApp +92 341 7164556. '
            .'Content is bilingual: English and Roman Urdu.';
        $lines[] = '';
        $lines[] = 'Key facts:';

        foreach ($products as $p) {
            $price = $p->price_min_amount?->toRupees();
            $lines[] = '- '.$p->name.': Rs '.number_format((float) $price).' — '.url('/products/'.$p->slug);
        }

        $lines[] = '- Delivery: Cash on Delivery nationwide (Pakistan); Rs 300 flat, free on orders above Rs 5,000.';
        $lines[] = '- Safety: never apply oil to a fresh or open burn — cool it under running water for 10–20 minutes and see a doctor.';
        $lines[] = '';
        $lines[] = '## Products';

        foreach ($products as $p) {
            $desc = str(trim(strip_tags((string) $p->short_description)))->squish()->limit(140, '…');
            $lines[] = '- ['.$p->name.']('.url('/products/'.$p->slug).'): '.$desc;
        }

        if ($en->isNotEmpty()) {
            $lines[] = '';
            $lines[] = '## Guides (English)';
            foreach ($en as $post) {
                $lines[] = '- ['.$post->title.']('.url('/blog/'.$post->slug).')';
            }
        }

        if ($ur->isNotEmpty()) {
            $lines[] = '';
            $lines[] = '## Guides (Roman Urdu)';
            foreach ($ur as $post) {
                $lines[] = '- ['.$post->title.']('.url('/ur-roman/blog/'.$post->slug).')';
            }
        }

        $lines[] = '';
        $lines[] = '## Trust';
        $lines[] = '- [What We Never Use]('.url('/what-we-never-use').'): the ingredients we exclude from every product';
        $lines[] = '- [Ingredient Index]('.url('/halal-ingredients').'): cosmetic ingredients explained, with sources';
        $lines[] = '- [Shipping & Returns]('.url('/shipping-returns').')';
        $lines[] = '- [FAQ]('.url('/faq').')';
        $lines[] = '';
        $lines[] = '## Optional';
        $lines[] = '- [Sitemap]('.url('/sitemap.xml').')';
        $lines[] = '';

        return response(implode("\n", $lines), 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
