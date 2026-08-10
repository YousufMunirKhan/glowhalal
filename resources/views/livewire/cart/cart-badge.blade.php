{{--
  Header cart control + live badge.

  The count is ordinary server-rendered text (never wire:text), so the correct
  number is in the initial HTML on every page; Livewire re-renders it when
  'cart-updated' fires. The control is a <button>, not an <a href="/cart">: its
  job is to open the drawer. "View cart" (the full page) lives inside the drawer.
--}}

@php $count = $this->count; @endphp

<button type="button"
    x-data
    x-on:click="$dispatch('open-cart-drawer')"
    class="tap-safe relative -me-3 grid place-items-center rounded-sm text-text-primary
        hover:bg-black/5 transition-[background-color] duration-[var(--motion-fast)] ease-standard"
    aria-haspopup="dialog"
    aria-label="{{ $count === 1 ? '1 item in cart, open cart' : $count . ' items in cart, open cart' }}">
    <x-ui.icon name="cart" />
    @if ($count > 0)
        {{-- Gold fill with ink-900 digits: 7.73:1 PASS AAA, the same approved
             pair as the primary button. --}}
        <span aria-hidden="true"
            class="tnum absolute end-1.5 top-1.5 grid h-5 min-w-5 place-items-center rounded-full
                bg-gold-surface px-1 text-meta font-semibold text-ink-900">{{ $count }}</span>
    @endif
</button>
