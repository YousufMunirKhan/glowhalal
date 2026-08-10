<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Installs real legal/service page content (Terms, Privacy, Shipping & Returns,
 * Disclaimer) at the owner's request.
 *
 * DEPLOY-SAFE: content is written ONLY when the page body is currently blank, so
 * re-running this (or a full db:seed) NEVER overwrites edits the owner later
 * makes in Admin → Content → Pages. Every one of these pages is fully editable
 * there via the Filament PageResource.
 *
 * ⚠️ NOT LEGAL ADVICE. This is a solid, standard e-commerce baseline tailored to
 * a Pakistan-based reseller of cosmetic/herbal products; a qualified Pakistani
 * lawyer should review it before you rely on it. Fill in the bracketed business
 * details (registered name / NTN) if/when you decide to publish them.
 */
class LegalPagesSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->pages() as $slug => $data) {
            $page = Page::withTrashed()->firstOrNew(['slug' => $slug]);

            $page->fill([
                'title' => ($page->exists && $page->title) ? $page->title : $data['title'],
                'template' => 'default',
                'status' => 'published',
                'published_at' => $page->published_at ?? now(),
                'is_system' => true,
                'show_in_footer' => true,
                'show_in_header' => false,
                'position' => $data['position'],
            ]);

            // Only fill a blank body — never clobber owner edits.
            if (blank($page->content)) {
                $page->content = $data['content'];
            }

            if ($page->trashed()) {
                $page->restore();
            }

            $page->save();
        }
    }

    /** @return array<string, array{title:string, position:int, content:string}> */
    private function pages(): array
    {
        $email = 'hello@glowhalal.com';
        $addr = '2nd Floor, 14-C Main Boulevard, Gulberg III, Lahore 54660, Pakistan';
        $phone = '+92 42 3577 1120';
        $mobile = '+92 300 1234567';

        return [
            'terms' => [
                'title' => 'Terms & Conditions',
                'position' => 40,
                'content' => $this->terms($email, $addr),
            ],
            'privacy' => [
                'title' => 'Privacy Policy',
                'position' => 30,
                'content' => $this->privacy($email),
            ],
            'shipping-returns' => [
                'title' => 'Shipping & Returns',
                'position' => 10,
                'content' => $this->shipping($email),
            ],
            'disclaimer' => [
                'title' => 'Disclaimer',
                'position' => 45,
                'content' => $this->disclaimer($email),
            ],
        ];
    }

    private function terms(string $email, string $addr): string
    {
        return <<<HTML
<p>These Terms &amp; Conditions ("Terms") govern your access to and use of the Glow Halal website and your purchase of any product from us. By browsing this website or placing an order, you agree to these Terms. Please read them carefully. If you do not agree, please do not use this website or place an order.</p>

<h2>1. Who we are</h2>
<p>"Glow Halal", "we", "us" and "our" refer to the Glow Halal online store, operating from {$addr}, Pakistan. You can reach us at <a href="mailto:{$email}">{$email}</a>. "You" and "your" mean the person browsing this website or placing an order.</p>

<h2>2. We are a seller, not a manufacturer</h2>
<p>Glow Halal is a <strong>retailer and reseller</strong>. Several products we offer — including herbal, ayurvedic and traditional oils and similar items — are <strong>manufactured, formulated and packaged by third parties</strong>, not by us. We sell these products in their original manufacturer packaging. We do not manufacture, compound, alter or make any medical or therapeutic representation about them beyond the information provided by the manufacturer and published on the product page.</p>

<h2>3. Product information</h2>
<p>We take care to describe products accurately, but ingredient lists, weights, colours and packaging are provided by manufacturers and may change without notice. Product photographs are for illustration; actual colour and packaging may vary slightly. Where we publish an ingredient list, it reflects the information available to us; you should always read the label on the product you receive before use.</p>

<h2>4. Health, safety and product use — please read</h2>
<p><strong>Products sold on this website are cosmetic and personal-care items for external use only. They are not medicines.</strong> They are not intended to diagnose, treat, cure or prevent any disease or condition, and no statement on this website should be read as medical advice.</p>
<ul>
  <li>Always read the label and follow the manufacturer's directions.</li>
  <li>Do a patch test before first use, and stop use immediately if you experience redness, irritation, or any adverse reaction.</li>
  <li>Keep products away from the eyes and out of reach of children. Do not use on broken skin, open wounds or serious burns.</li>
  <li>If you are pregnant, nursing, have a medical condition, or have sensitive or allergy-prone skin, consult a qualified doctor or dermatologist before use.</li>
</ul>
<p><strong>You use all products at your own risk.</strong> To the maximum extent permitted by applicable law, Glow Halal is <strong>not responsible or liable for any side effect, allergic reaction, irritation, injury, loss or damage</strong> arising from the use, misuse, or reaction to any product, including any reaction to an ingredient. As a reseller, our responsibility is limited to supplying the genuine product as described; the composition and safety of the product are the responsibility of its manufacturer. If you have a serious reaction, seek medical attention promptly.</p>

<h2>5. Orders and acceptance</h2>
<p>Your order is an offer to buy. A confirmation (by SMS, WhatsApp, email or call) means we have received your order; it does not guarantee acceptance. We may decline or cancel any order — for example if a product is out of stock, an address cannot be reached, pricing was listed in error, or an order appears fraudulent — and where you have already paid for a cancelled order, we will refund it.</p>

<h2>6. Prices and payment</h2>
<p>Prices are in Pakistani Rupees (PKR) and include applicable taxes unless stated otherwise. Delivery charges, where they apply, are shown before you confirm. We accept <strong>Cash on Delivery (COD)</strong> and bank transfer. For COD, please keep the exact amount ready; the confirmation message states the total to pay. We may correct any obvious pricing error even after an order is placed.</p>

<h2>7. Delivery</h2>
<p>We deliver across Pakistan. Orders are normally delivered <strong>within 7 (seven) working days</strong> of order confirmation; major cities are often faster. Working days exclude Sundays and public holidays. Delivery timeframes are estimates and are not guaranteed, as final delivery is carried out by third-party courier companies. We are not liable for delays caused by couriers, weather, incorrect or incomplete addresses, or events beyond our reasonable control. Risk in the goods passes to you on delivery.</p>

<h2>8. Returns, cancellations and refunds</h2>
<p>Please see our <a href="/shipping-returns">Shipping &amp; Returns</a> policy, which forms part of these Terms. For hygiene and safety reasons, opened or used cosmetic and personal-care products cannot be returned unless they arrived damaged, defective or incorrect.</p>

<h2>9. Limitation of liability</h2>
<p>To the maximum extent permitted by applicable law:</p>
<ul>
  <li>Products and this website are provided on an "as is" and "as available" basis, without warranties of any kind except those that cannot be excluded by law.</li>
  <li>We are not liable for any indirect, incidental, special or consequential loss, or for loss of profit, data, goodwill or opportunity.</li>
  <li>Our total liability to you for any claim connected with a product or order will not exceed the amount you actually paid for the product giving rise to the claim.</li>
</ul>
<p>Nothing in these Terms excludes or limits any liability that cannot lawfully be excluded or limited under the laws of Pakistan.</p>

<h2>10. Indemnity</h2>
<p>You agree to indemnify and hold Glow Halal harmless from any claim, loss or expense arising from your misuse of a product or your breach of these Terms.</p>

<h2>11. Accounts and sign-in</h2>
<p>You may check out as a guest, or sign in with Google for convenience. You are responsible for the accuracy of the details you provide and for activity under your account. We may suspend accounts that are misused.</p>

<h2>12. Intellectual property</h2>
<p>The Glow Halal name, logo, website design, text and original images are our property or used with permission and may not be copied or used without our written consent.</p>

<h2>13. Changes to these Terms</h2>
<p>We may update these Terms from time to time. The version published on this page applies to your use of the website and to any order placed after it is posted. The "last updated" date below reflects the current version.</p>

<h2>14. Governing law and jurisdiction</h2>
<p>These Terms are governed by the laws of the Islamic Republic of Pakistan. The courts at Lahore have exclusive jurisdiction over any dispute, save that we may seek relief in any competent court where necessary.</p>

<h2>15. Severability</h2>
<p>If any part of these Terms is found to be unenforceable, the remaining provisions continue in full force.</p>

<h2>16. Contact</h2>
<p>Questions about these Terms? Email <a href="mailto:{$email}">{$email}</a> or use our <a href="/contact">contact page</a>.</p>
HTML;
    }

    private function privacy(string $email): string
    {
        return <<<HTML
<p>This Privacy Policy explains what personal information Glow Halal collects, how we use it, and the choices you have. By using this website or placing an order, you agree to this policy.</p>

<h2>1. Information we collect</h2>
<ul>
  <li><strong>Order &amp; contact details</strong> you provide: name, phone number, delivery address, and (if given) email.</li>
  <li><strong>Sign-in details</strong>, if you choose "Sign in with Google": your name, email address and profile picture, as shared by Google. Signing in is optional — you can shop and pay by Cash on Delivery as a guest.</li>
  <li><strong>Order history</strong> and communications with us (e.g. WhatsApp or SMS about your order).</li>
  <li><strong>Usage &amp; device data</strong> collected, with your consent, through cookies and Google Analytics — such as pages viewed and general location — to understand and improve the store.</li>
</ul>

<h2>2. How we use your information</h2>
<ul>
  <li>To process, confirm, deliver and support your orders (including Cash-on-Delivery confirmation by SMS/WhatsApp/call).</li>
  <li>To provide customer service and respond to your questions.</li>
  <li>To improve our products, website and service.</li>
  <li>To meet legal, tax and record-keeping obligations, and to prevent fraud.</li>
  <li>To send you marketing messages about new products and offers <strong>only if you have opted in</strong> (see "Marketing" below). We do not use your Google sign-in email for marketing unless you separately consent.</li>
</ul>

<h2>3. Cookies</h2>
<p>We use <strong>essential cookies</strong> needed to run the store (for example, your cart and sign-in). With your consent — given through our cookie banner — we also use <strong>analytics cookies</strong> (Google Analytics) to measure and improve the site. If you decline, analytics cookies are not set. You can change your choice at any time by clearing the site's cookies in your browser.</p>

<h2>4. When we share information</h2>
<p>We do not sell your personal information. We share it only as needed:</p>
<ul>
  <li><strong>Courier partners</strong> (such as TCS, Leopards and M&amp;P) — your name, address and phone number, to deliver your order.</li>
  <li><strong>Google</strong> — for optional sign-in and, with your consent, analytics, under Google's own privacy terms.</li>
  <li><strong>Service providers</strong> who help us operate (e.g. hosting), under confidentiality obligations.</li>
  <li><strong>Authorities</strong>, where required by law.</li>
</ul>

<h2>5. Marketing</h2>
<p>We only send marketing messages to people who have <strong>opted in</strong>. You can unsubscribe at any time using the link in an email or by messaging us. Opting out of marketing does not stop essential messages about an order you have placed.</p>

<h2>6. Data retention</h2>
<p>We keep order and contact information for as long as needed to fulfil orders, provide support, and meet legal and accounting requirements, then delete or anonymise it.</p>

<h2>7. Security</h2>
<p>We take reasonable measures to protect your information. No method of transmission or storage is completely secure, but we work to keep your data safe and to limit who can access it.</p>

<h2>8. Your rights</h2>
<p>You may ask us to access, correct or delete the personal information we hold about you, or to stop marketing to you. Email <a href="mailto:{$email}">{$email}</a> and we will respond within a reasonable time.</p>

<h2>9. Children</h2>
<p>This website is intended for adults. We do not knowingly collect information from children. If you believe a child has provided us information, contact us and we will remove it.</p>

<h2>10. Changes</h2>
<p>We may update this policy; the version on this page is the one that applies. The "last updated" date below reflects the current version.</p>

<h2>11. Contact</h2>
<p>Questions about your privacy? Email <a href="mailto:{$email}">{$email}</a> or use our <a href="/contact">contact page</a>.</p>
HTML;
    }

    private function shipping(string $email): string
    {
        return <<<HTML
<h2>Where we deliver</h2>
<p>We deliver nationwide across Pakistan through trusted courier partners (such as TCS, Leopards and M&amp;P).</p>

<h2>Delivery time</h2>
<p>Orders are normally delivered <strong>within 7 (seven) working days</strong> of order confirmation. Major cities — Lahore, Karachi, Islamabad, Rawalpindi and Faisalabad — are usually faster. Working days exclude Sundays and public holidays. Timeframes are estimates: final delivery is handled by the courier, and we cannot guarantee an exact day.</p>

<h2>Delivery charges</h2>
<ul>
  <li><strong>Free delivery</strong> on orders over <strong>PKR 3,000</strong>.</li>
  <li>Below that, a flat delivery charge of <strong>PKR 250</strong> applies nationwide.</li>
</ul>

<h2>Payment</h2>
<p>We offer <strong>Cash on Delivery (COD)</strong> and bank transfer. For COD, please keep the exact amount ready — the confirmation message tells you the total to pay, including delivery.</p>

<h2>Order tracking</h2>
<p>Once your order is dispatched, we share the courier tracking details by SMS or WhatsApp so you can follow it to your door.</p>

<h2>Returns</h2>
<p>Because our products are cosmetic and personal-care items, hygiene and safety come first:</p>
<ul>
  <li><strong>Damaged, defective or wrong item:</strong> if your order arrives damaged, faulty, or is not what you ordered, contact us within <strong>7 days of delivery</strong> with a photo and your order details, and we will arrange a replacement or refund.</li>
  <li><strong>Unopened items:</strong> unopened products in their original, sealed packaging may be returned within <strong>7 days of delivery</strong>.</li>
  <li><strong>Opened or used items cannot be returned</strong> for hygiene reasons, unless they were damaged, defective or incorrect on arrival.</li>
</ul>

<h2>Refunds</h2>
<p>Approved refunds are processed after we receive and check the returned item, normally within 7–10 working days, to your original payment method or by bank transfer for COD orders. Delivery charges are non-refundable except where the return is due to our error.</p>

<h2>Cancellations</h2>
<p>You can cancel an order before it is dispatched — just message us as soon as possible. Once an order is handed to the courier it can no longer be cancelled, but you may refuse a COD parcel at the door.</p>

<h2>Contact</h2>
<p>Need help with delivery or a return? Email <a href="mailto:{$email}">{$email}</a> or use our <a href="/contact">contact page</a>.</p>
HTML;
    }

    private function disclaimer(string $email): string
    {
        return <<<HTML
<p>Please read this disclaimer carefully before using any product purchased from Glow Halal.</p>

<h2>We are a seller, not a manufacturer</h2>
<p>Glow Halal is a retailer. Many products we sell — including herbal, ayurvedic and traditional oils and similar items — are made and packaged by third-party manufacturers. We supply the genuine product as received from the manufacturer; we do not formulate it and we do not make medical or therapeutic claims about it.</p>

<h2>Not a medicine, not medical advice</h2>
<p>Products on this website are cosmetic and personal-care items for <strong>external use only</strong>. They are not medicines and are not intended to diagnose, treat, cure or prevent any disease or condition. Nothing on this website is medical advice. Any traditional uses described are general information, not a recommendation for your situation.</p>

<h2>Use at your own risk</h2>
<ul>
  <li>Always read the manufacturer's label and directions, and do a patch test before first use.</li>
  <li>Stop use immediately if any redness, irritation or reaction occurs, and seek medical attention if a reaction is serious.</li>
  <li>Consult a doctor or dermatologist before use if you are pregnant or nursing, have a medical or skin condition, or are prone to allergies.</li>
  <li>Keep away from eyes, broken skin and open wounds, and out of reach of children.</li>
</ul>
<p>To the maximum extent permitted by applicable law, <strong>Glow Halal accepts no responsibility or liability for any side effect, allergic reaction, irritation, injury, loss or damage</strong> resulting from the use or misuse of any product. Individual results vary. By purchasing, you accept that you use products at your own risk. Nothing here excludes any liability that cannot lawfully be excluded under the laws of Pakistan.</p>

<h2>Ingredients and halal information</h2>
<p>We publish the ingredient information available to us and the list of ingredients we choose not to stock. We hold <strong>no third-party halal certification</strong> and make no certification claim. Please read the label on the product you receive and make your own decision.</p>

<h2>Contact</h2>
<p>Questions? Email <a href="mailto:{$email}">{$email}</a> or use our <a href="/contact">contact page</a>.</p>
HTML;
    }
}
