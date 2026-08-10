{{--
  Design system §3.15 — every empty state has four parts, always in this order:
  icon → what happened → what to do → the action. No dead ends.

  The icon is a line glyph in a muted neutral and is aria-hidden. Deliberately
  not an illustration: a cute drawing undercuts a brand whose entire position is
  seriousness about evidence.

  $heading, $body, $actionLabel, $actionHref, and optionally $secondaryLabel /
  $secondaryHref and $icon. `role="status"` is applied only when the state
  appeared in response to something the reader did (a search), so the result is
  announced without focus moving.
--}}
<div class="mx-auto max-w-[400px] py-12 text-center" @if ($announce ?? false) role="status" @endif>
    <div class="flex justify-center text-ink-400">
        <x-ui.icon :name="$icon ?? 'document'" :size="48" stroke-width="1.25" />
    </div>

    <h2 class="mt-4 text-title text-text-primary">{{ $heading }}</h2>

    <p class="mt-2 text-body text-text-secondary">{{ $body }}</p>

    @isset($actionHref)
        <div class="mt-6 flex flex-col items-center gap-4">
            <x-ui.button :href="$actionHref" variant="primary" size="md">{{ $actionLabel }}</x-ui.button>

            @isset($secondaryHref)
                <x-ui.button :href="$secondaryHref" variant="tertiary">{{ $secondaryLabel }}</x-ui.button>
            @endisset
        </div>
    @endisset
</div>
