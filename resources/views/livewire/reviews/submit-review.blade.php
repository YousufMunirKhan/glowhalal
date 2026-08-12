<div class="mt-10 border-t border-border-subtle pt-8">
    @if ($submitted)
        {{-- Thank-you state — the review is pending moderation, said honestly. --}}
        <div class="rounded-md border border-border-subtle bg-surface p-6">
            <p class="text-title-sm text-text-primary">Shukriya! Aapka review bhej diya gaya. 🌿</p>
            <p class="mt-2 text-body text-text-secondary">
                Hum har review ko parhtay hain — approve honay ke baad ye is page par
                nazar aa jayega. (We review every submission before it is published.)
            </p>
        </div>
    @else
        <h3 class="text-title text-text-primary">Write a review</h3>
        <p class="mt-1 text-meta text-text-muted">
            Bought this product? Share your honest experience. Reviews are checked before they appear.
        </p>

        <form wire:submit="submit" class="mt-6 grid gap-5" novalidate>
            {{-- Star rating -------------------------------------------------- --}}
            <div>
                <label class="block text-meta font-semibold text-text-primary">Your rating</label>
                <div class="mt-2 flex items-center gap-1" role="radiogroup" aria-label="Rating out of 5">
                    @for ($i = 1; $i <= 5; $i++)
                        <button type="button"
                            wire:click="setRating({{ $i }})"
                            role="radio"
                            aria-checked="{{ $rating === $i ? 'true' : 'false' }}"
                            aria-label="{{ $i }} star{{ $i > 1 ? 's' : '' }}"
                            class="min-h-11 min-w-11 text-[1.75rem] leading-none transition-colors duration-[var(--motion-fast)]
                                {{ $i <= $rating ? 'text-text-gold' : 'text-text-muted hover:text-text-gold' }}">
                            {{ $i <= $rating ? '★' : '☆' }}
                        </button>
                    @endfor
                </div>
                @error('rating') <p class="mt-1 text-meta text-danger-600">{{ $message }}</p> @enderror
            </div>

            {{-- Name --------------------------------------------------------- --}}
            <div>
                <label for="rv-name" class="block text-meta font-semibold text-text-primary">Your name</label>
                <input id="rv-name" type="text" wire:model="author_name" maxlength="120"
                    class="mt-1 w-full rounded-sm border border-border-subtle bg-surface px-3 py-2 text-body
                        text-text-primary focus:border-text-gold focus:outline-none"
                    placeholder="e.g. Ayesha, Lahore">
                @error('author_name') <p class="mt-1 text-meta text-danger-600">{{ $message }}</p> @enderror
            </div>

            {{-- Title (optional) --------------------------------------------- --}}
            <div>
                <label for="rv-title" class="block text-meta font-semibold text-text-primary">
                    Title <span class="font-normal text-text-muted">(optional)</span>
                </label>
                <input id="rv-title" type="text" wire:model="title" maxlength="200"
                    class="mt-1 w-full rounded-sm border border-border-subtle bg-surface px-3 py-2 text-body
                        text-text-primary focus:border-text-gold focus:outline-none"
                    placeholder="Sum it up in a few words">
                @error('title') <p class="mt-1 text-meta text-danger-600">{{ $message }}</p> @enderror
            </div>

            {{-- Body --------------------------------------------------------- --}}
            <div>
                <label for="rv-body" class="block text-meta font-semibold text-text-primary">Your review</label>
                <textarea id="rv-body" wire:model="body" rows="4" maxlength="2000"
                    class="mt-1 w-full rounded-sm border border-border-subtle bg-surface px-3 py-2 text-body
                        text-text-primary focus:border-text-gold focus:outline-none"
                    placeholder="Aap ka tajurba kaisa raha? What did you use it for, and how was it?"></textarea>
                @error('body') <p class="mt-1 text-meta text-danger-600">{{ $message }}</p> @enderror
            </div>

            {{-- Honeypot: off-screen, not tab-reachable. Bots fill it; people don't. --}}
            <div aria-hidden="true" class="absolute -left-[9999px] h-0 w-0 overflow-hidden">
                <label>Website<input type="text" wire:model="website" tabindex="-1" autocomplete="off"></label>
            </div>

            <div>
                <button type="submit"
                    wire:loading.attr="disabled"
                    class="inline-flex min-h-11 items-center justify-center rounded-sm bg-gold-surface px-6 text-body
                        font-semibold text-ink-900 transition-[background-color] duration-[var(--motion-fast)]
                        ease-standard hover:bg-gold-surface-hover active:bg-gold-surface-active
                        disabled:cursor-not-allowed disabled:opacity-60">
                    <span wire:loading.remove wire:target="submit">Submit review</span>
                    <span wire:loading wire:target="submit">Submitting…</span>
                </button>
            </div>
        </form>
    @endif
</div>
