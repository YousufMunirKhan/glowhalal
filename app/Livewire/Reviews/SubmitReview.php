<?php

namespace App\Livewire\Reviews;

use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Customer review submission form on the product page.
 *
 * Every submission is created as `pending` and only appears on the site after
 * the owner approves it in Admin → Reviews — the deliberate defence against
 * spam and abuse on a public, no-account-required form (the store avoids
 * CAPTCHAs on purpose, §6.2, so the guards here are a honeypot + a per-IP rate
 * limit + moderation). No fabricated reviews ever enter this way: the text is
 * whatever the customer types, attributed to the name they give.
 */
class SubmitReview extends Component
{
    // Public Livewire props are attacker-modifiable; the product is pinned.
    #[Locked]
    public int $productId;

    public int $rating = 0;

    public string $author_name = '';

    public string $title = '';

    public string $body = '';

    /** Honeypot — real users never see or fill this; bots do. */
    public string $website = '';

    public bool $submitted = false;

    public function mount(Product $product): void
    {
        $this->productId = $product->id;

        // Prefill the name for a signed-in customer; still editable.
        if ($user = auth()->user()) {
            $this->author_name = (string) ($user->name ?? '');
        }
    }

    protected function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'between:1,5'],
            'author_name' => ['required', 'string', 'min:2', 'max:120'],
            'title' => ['nullable', 'string', 'max:200'],
            'body' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    protected function messages(): array
    {
        return [
            'rating.required' => 'Please choose a star rating.',
            'rating.between' => 'Please choose a star rating.',
            'body.required' => 'Please write a few words about the product.',
            'body.min' => 'Please write at least a few words about the product.',
        ];
    }

    public function setRating(int $rating): void
    {
        $this->rating = max(1, min(5, $rating));
    }

    public function submit(): void
    {
        // Honeypot tripped → almost certainly a bot. Show the thank-you state but
        // save nothing, so the bot cannot tell it failed.
        if ($this->website !== '') {
            $this->submitted = true;

            return;
        }

        $key = 'review-submit:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, maxAttempts: 3)) {
            $this->addError('body', 'You have already submitted a few reviews. Please try again later.');

            return;
        }

        $data = $this->validate();
        RateLimiter::hit($key, decaySeconds: 3600);

        ProductReview::create([
            'product_id' => $this->productId,
            'user_id' => auth()->id(),
            'order_id' => null,   // not order-matched → no "Verified purchase" badge
            'author_name' => trim($data['author_name']),
            'rating' => $data['rating'],
            'title' => filled($data['title'] ?? null) ? trim($data['title']) : null,
            'body' => trim($data['body']),
            'status' => 'pending',
        ]);

        $this->reset(['rating', 'title', 'body']);
        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.reviews.submit-review');
    }
}
