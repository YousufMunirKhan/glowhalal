{{--
  Admin-authored rich text (Filament RichEditor output) rendered at the
  long-form measure.

  There is no typography plugin in this build, so the element styles are
  arbitrary child selectors on this wrapper. That keeps every value in the token
  system — --text-title-lg, --text-body, --text-secondary — instead of inventing
  a second scale inside a .prose class, and it means nothing here needs a change
  to resources/css/app.css.

  $html is trusted: it comes from the CMS, not from a visitor. Nothing on this
  site ever passes visitor input through here.
--}}
<div
    class="max-w-[var(--container-read)] text-body text-text-secondary
        [&>*+*]:mt-4
        [&_a]:text-text-primary [&_a]:underline [&_a]:decoration-1 [&_a]:underline-offset-[3px] hover:[&_a]:decoration-2
        [&_strong]:font-semibold [&_strong]:text-text-primary
        [&>h2]:mt-10 [&>h2]:text-title-lg [&>h2]:text-text-primary
        [&>h3]:mt-8 [&>h3]:text-title [&>h3]:text-text-primary
        [&>h4]:mt-6 [&>h4]:text-title-sm [&>h4]:text-text-primary
        [&>ul]:list-disc [&>ul]:ps-5 [&>ol]:list-decimal [&>ol]:ps-5 [&_li]:mt-2
        [&>blockquote]:border-s-4 [&>blockquote]:border-gold-surface [&>blockquote]:ps-4
        [&>blockquote]:text-lead [&>blockquote]:text-text-primary
        [&>figure>figcaption]:mt-2 [&>figure>figcaption]:text-meta [&>figure>figcaption]:text-text-muted
        [&_img]:rounded-sm
        [&>table]:w-full [&>table]:border-collapse [&_th]:border-b [&_th]:border-border-subtle [&_th]:py-2 [&_th]:text-start
        [&_td]:border-b [&_td]:border-border-subtle [&_td]:py-2">
    {!! $html !!}
</div>
