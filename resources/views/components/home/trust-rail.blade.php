{{--
  The trust rail — three claims, each with a destination where it can be
  checked. It sits directly under the hero on its own #1C1C1C band so it reads
  as a ledger line under the argument rather than as decoration inside it.

  NOT "100% Halal | Cruelty-Free | Premium Quality", which is what every
  competitor says and is therefore noise. And NOT a third-party approval line:
  Glow Halal holds no halal accreditation from any body and this rail must
  never imply otherwise — no seal, no badge, no reference number, no issuing
  organisation, no "approval pending". Each row is a fact about what is on this
  website.

  Measured:
    #F2F0EC dark-text        on #1C1C1C  14.98:1  PASS AAA  labels
    #7CD9A4 success-on-dark  on #1C1C1C  10.10:1  PASS AAA  check glyphs
  The glyph is redundant with the label, so it carries no meaning on its own.
--}}

@php
    $items = [
        [
            'label' => 'Every ingredient published',
            'detail' => 'Full INCI list, every product',
            'href' => '/halal-ingredients',
        ],
        [
            'label' => 'Every exclusion published',
            'detail' => 'The 8 we will not formulate with',
            'href' => '/what-we-never-use',
        ],
        [
            'label' => 'Cash on Delivery',
            'detail' => 'Nationwide, no account needed',
            'href' => '/shipping-returns',
        ],
    ];
@endphp

<section data-surface="dark" aria-label="What this site publishes" class="bg-dark-surface">
    <div class="container-page">
        <ul class="md:flex md:divide-x md:divide-border-subtle">
            @foreach ($items as $item)
                <li class="border-t border-border-subtle first:border-t-0 md:flex-1 md:border-t-0">
                    <a href="{{ $item['href'] }}"
                        class="flex min-h-16 items-center gap-3 py-3 text-dark-text no-underline
                            hover:underline hover:underline-offset-[3px] md:min-h-20 md:px-5">
                        <x-ui.icon name="check" :size="18" stroke-width="2.5"
                            class="shrink-0 text-success-on-dark" />
                        <span>
                            <span class="block text-body font-semibold leading-tight">{{ $item['label'] }}</span>
                            <span class="mt-0.5 block text-meta text-dark-text-muted">{{ $item['detail'] }}</span>
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</section>
