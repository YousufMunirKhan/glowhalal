# Glow Halal — Visual Design System & Page Layout Specification

**Version:** 1.0
**Derived from:** `docs/ux-research.md` (v1) — §2 trust hierarchy, §4 information architecture, §5 user flows, §6 mobile-first constraints, §7 content requirements
**Stack this is built for:** Laravel 13.24 · Livewire 4.3.5 · Tailwind CSS 4 (`@tailwindcss/vite`, CSS-first `@theme` configuration) · Blade server-rendered storefront
**Scope:** Storefront only. Filament 5.7.6 admin is out of scope and uses its own design language.
**Audience:** Frontend Developer building directly from this document.

---

## How to read this document

This is a **specification**, not a style guide and not a mood board. Every value in it is a decision that has already been made. Where two values would both have worked, one was picked and the other was discarded — do not re-open those. Where a value is genuinely conditional, the condition is stated explicitly.

**Three rules govern every ambiguity:**

1. **Mobile-first is literal.** Every component is specified at 375 px first. Desktop is the variant. If a spec below gives one number without a breakpoint qualifier, that number is the mobile value and it holds until the first stated breakpoint.
2. **Evidence over adjectives** (research §2). If a design choice makes a claim look *marketed*, it is the wrong choice. Trust content is styled like a record, not like a promotion.
3. **Contrast is a build error, not a review comment.** Every foreground/background pairing used in production must appear in the contrast matrix in §1.3 with a PASS mark. If a pairing is not in the matrix, it is not approved.

**Verification note on the token code:** all `@theme` syntax in §1.4 was verified against the current Tailwind CSS 4 documentation before writing. Tailwind 4 has no `tailwind.config.js` theme object; there is no `--duration-*` and no `--z-index-*` theme namespace (those ship as plain CSS custom properties consumed via `duration-[var(--motion-base)]` / `z-[var(--z-modal)]`). Font sizes support the `--text-*--line-height`, `--text-*--letter-spacing` and `--text-*--font-weight` modifiers. Namespaces are reset with `--namespace-*: initial`.

---

## 0. Decision log — the six choices that shape everything else

| # | Decision | Why | Consequence you will feel while building |
|---|---|---|---|
| D1 | **Brand gold `#C9A961` is never a text colour on any light surface, at any size.** | Measured 2.25:1 on white. That fails AA normal text (4.5:1) **and** fails AA large text (3:1). Research §6.3 asked for verification rather than assumption; verification says it fails at every size. | There is no `--color-gold-500`. The gold tokens are named by *permitted role*, so `text-gold-surface` reads as obviously wrong. |
| D2 | **No numeric gold ramp ships.** | A `gold-500`/`gold-600` ramp invites a developer to guess a text colour from it. | Gold tokens: `gold-surface`, `gold-surface-hover`, `gold-surface-active`, `gold-surface-light`, `gold-tint`, `gold-display-only`, `gold-text`, `gold-text-strong`, `gold-on-dark`. Nine names, each one a licence. |
| D3 | **Tailwind's default colour, font-size and font-weight namespaces are reset to `initial`.** | Prevents `text-xs` (12 px, below the 14 px floor), `bg-red-500` (unapproved pairing) and `font-bold` (700 — a weight we do not ship, so it would faux-bold). | You cannot reach for an off-system value by accident. If a utility does not exist, that is the system saying no. |
| D4 | **The catalogue grid does not exist below 8 SKUs.** | Research §4.3 Rules 1 and 9. Three tiles in a grid is the single visual that says "dead store". | `/shop` has two entirely different layouts. Both are specified in §4.2. Build the editorial one first. |
| D5 | **Trust content uses a "document plate" treatment, not a badge treatment.** | Research §2.3: crescent badges and seal graphics now read as decoration or as scam signalling. | Certificates, INCI lists and lab reports share one visual chassis: hairline border, mono metadata, dated, selectable, no gradient, no glow, no icon flourish. Specified in §6. |
| D6 | **Dark theme ships, but as a secondary theme (P2).** | Persona A's stated shopping window is 10pm–1am in bed; an OLED-dark store is a real comfort win. It also *unlocks* the brand gold — `#C9A961` measures 8.32:1 on `#121212`, so gold finally becomes a legal text colour. | Product photography is shot on white and keeps a white plate in dark mode. Never invert a product image. |

---

## 1. Design tokens

### 1.1 Contrast methodology

All ratios below were computed from WCAG 2.1 relative luminance over sRGB, `(L1 + 0.05) / (L2 + 0.05)`, rounded down to two decimals. Thresholds applied:

| Content type | Threshold | Applies to |
|---|---|---|
| Normal text | **4.5:1** | Anything under 24 px, or under 18.66 px at weight 600+ |
| Large text | **3:1** | 24 px+ at any weight, or 18.66 px+ at weight 600+ |
| Non-text UI | **3:1** | Input borders, focus rings, toggle states, icons that carry meaning, chart/status colour |
| Decoration | none | Dividers, ornament, background texture, anything that carries no information |

Where a ratio is marked **FAIL**, the value is still a legitimate token — it is simply not licensed for that role. The token name says which role it *is* licensed for.

---

### 1.2 Palette

#### Brand gold — nine named roles, no numeric ramp

| Token | Hex | Licensed role | Explicitly forbidden |
|---|---|---|---|
| `--color-gold-surface` | `#C9A961` | Button/chip **backgrounds** (with ink text), swatch fills, 2 px accent rules, the logo sparkle | Any text on a light surface. Any border that indicates state or boundary (2.25:1 fails the 3:1 non-text bar). |
| `--color-gold-surface-hover` | `#B8974E` | Hover fill for gold surfaces | As above |
| `--color-gold-surface-active` | `#A8863F` | Pressed fill for gold surfaces | As above |
| `--color-gold-surface-light` | `#E4C87F` | Gradient light stop, tinted highlight fill | As above |
| `--color-gold-tint` | `#FBF6EA` | Background wash for evidence blocks and the certificate plate | Not a text colour (it is a surface) |
| `--color-gold-display-only` | `#9C7C33` | **Display text ≥ 24 px only**, on `#FFFFFF` or `#FAF9F7`. Also the page-load progress bar. | Anything under 24 px. Anything on `--color-gold-tint` or `--color-ink-100`. |
| `--color-gold-text` | `#8A6D28` | Small gold-flavoured text on **white / `#FAF9F7` only** | On `#F0EFEC` it drops to 4.25:1 — use `gold-text-strong` there |
| `--color-gold-text-strong` | `#6A5320` | Small gold text on any light surface including tints; AAA-grade | — |
| `--color-gold-on-dark` | `#C9A961` | The brand gold as **text**, on `#121212` / `#1C1C1C` / `#0F0F0F` only | Any light surface |

`--color-gold-on-dark` is deliberately the same hex as `--color-gold-surface`. Two names, one value, because the *permission* differs. This is the mechanism that makes misuse visible in a diff.

#### Ink (neutrals)

| Token | Hex | Role |
|---|---|---|
| `--color-ink-950` | `#0F0F0F` | Overlay scrim base, dark section backgrounds, deepest text |
| `--color-ink-900` | `#1A1A1A` | **Body text.** Primary text everywhere. Button label on gold. |
| `--color-ink-800` | `#2E2E2E` | Form labels, table headers, emphasis text |
| `--color-ink-700` | `#4A4A4A` | Secondary body copy, long-form prose meta |
| `--color-ink-600` | `#5C5C5C` | Tertiary text, breadcrumbs, captions |
| `--color-ink-500` | `#6E6E6E` | Placeholder text, disabled-but-readable meta (lowest legal small-text neutral) |
| `--color-ink-400` | `#8A8A8A` | **Interactive borders** (inputs, checkboxes, radio, focusable outlines). Lowest neutral that clears 3:1 on white. |
| `--color-ink-300` | `#B5B5B5` | Decorative rules only. Never a boundary a user must perceive. |
| `--color-ink-200` | `#D9D9D9` | Skeleton fill, decorative dividers |
| `--color-ink-100` | `#F0EFEC` | Warm sunken surface — section bands, table zebra, secondary-button hover |
| `--color-ink-50` | `#FAF9F7` | Raised card surface on white, input backgrounds when needed |
| `--color-white` | `#FFFFFF` | Page background, product-image plate |
| `--color-black` | `#000000` | Reserved. Do not use for text — use `ink-900`. |

#### Semantic — status and verdict

These do double duty. In commerce they are success/warning/error/info. In the Ingredient Index and INCI tables they are **halal verdicts**, which is a heavier job — so every verdict is always icon + text, never colour alone.

| Token | Hex | Commerce role | Verdict role |
|---|---|---|---|
| `--color-success-600` | `#1B7A4B` | Success fill, in-stock, order confirmed | **Halal** — plant/mineral/synthetic verified |
| `--color-success-700` | `#14603A` | Success text on white or `success-50` | Halal verdict text |
| `--color-success-50` | `#EAF5EF` | Success surface | Halal row tint |
| `--color-warning-600` | `#8F5A00` | Warning text and icon | **Mushbooh** (doubtful) — source not documented |
| `--color-warning-500` | `#B87400` | Warning border / non-text only (3.79:1) | Mushbooh rule/icon at ≥24 px |
| `--color-warning-50` | `#FDF3E3` | Warning surface | Mushbooh row tint |
| `--color-danger-600` | `#B3261E` | Error fill, out of stock | **Not halal** |
| `--color-danger-700` | `#8C1D18` | Error text on white or `danger-50` | Not-halal verdict text, `Remove` link |
| `--color-danger-50` | `#FCEDEC` | Error surface | Not-halal row tint |
| `--color-info-600` | `#1D5FA8` | Links, info fill, **focus ring** | Neutral/informational annotation |
| `--color-info-50` | `#EAF1F9` | Info surface, "certification in progress" plate |
| `--color-whatsapp` | `#075E54` | WhatsApp action fill (authentic WhatsApp dark green; the bright `#25D366` fails with white text) |

#### Dark theme surfaces

| Token | Hex | Role |
|---|---|---|
| `--color-dark-bg` | `#121212` | Page background |
| `--color-dark-surface` | `#1C1C1C` | Card / raised surface |
| `--color-dark-border` | `#3A3A3A` | Interactive border on dark |
| `--color-dark-text` | `#F2F0EC` | Primary text on dark |
| `--color-dark-text-muted` | `#A8A29A` | Secondary text on dark |
| `--color-dark-focus` | `#7FB2E8` | Focus ring on dark |

---

### 1.3 Contrast matrix — every approved pairing, measured

Read this as the allow-list. **A pairing that is not on this list is not approved for production.**

#### Text on light surfaces

| Foreground | Background | Ratio | AA normal (4.5) | AA large (3.0) | Verdict / licensed use |
|---|---|---|---|---|---|
| `#1A1A1A` ink-900 | `#FFFFFF` | **17.41:1** | PASS | PASS | Body text everywhere |
| `#1A1A1A` ink-900 | `#FAF9F7` ink-50 | **16.54:1** | PASS | PASS | Text on raised cards |
| `#1A1A1A` ink-900 | `#F0EFEC` ink-100 | **15.14:1** | PASS | PASS | Text on sunken bands |
| `#1A1A1A` ink-900 | `#FBF6EA` gold-tint | **16.14:1** | PASS | PASS | Text on evidence plates |
| `#2E2E2E` ink-800 | `#FFFFFF` | **13.58:1** | PASS | PASS | Form labels, table headers |
| `#4A4A4A` ink-700 | `#FFFFFF` | **8.86:1** | PASS | PASS | Secondary prose |
| `#5C5C5C` ink-600 | `#FFFFFF` | **6.69:1** | PASS | PASS | Captions, breadcrumbs |
| `#6E6E6E` ink-500 | `#FFFFFF` | **5.10:1** | PASS | PASS | Placeholder text — the floor for small neutral text |
| `#8A8A8A` ink-400 | `#FFFFFF` | **3.45:1** | **FAIL** | PASS | **Non-text only** — input borders, checkbox outlines |
| `#B5B5B5` ink-300 | `#FFFFFF` | **2.05:1** | **FAIL** | **FAIL** | Decoration only — never a perceivable boundary |
| `#D9D9D9` ink-200 | `#FFFFFF` | **1.41:1** | **FAIL** | **FAIL** | Skeleton fill, decorative hairline only |

#### Gold — the constraint, measured

| Foreground | Background | Ratio | AA normal (4.5) | AA large (3.0) | Verdict |
|---|---|---|---|---|---|
| `#C9A961` gold-surface | `#FFFFFF` | **2.25:1** | **FAIL** | **FAIL** | **Never text on white — not even at 24 px.** This is D1. |
| `#C9A961` gold-surface | `#F0EFEC` ink-100 | **1.95:1** | **FAIL** | **FAIL** | Never text |
| `#C9A961` gold-surface | `#FBF6EA` gold-tint | **2.08:1** | **FAIL** | **FAIL** | Never text |
| `#E4C87F` gold-surface-light | `#FFFFFF` | **1.63:1** | **FAIL** | **FAIL** | Never text |
| `#9C7C33` gold-display-only | `#FFFFFF` | **3.93:1** | **FAIL** | PASS | **Display type ≥ 24 px only**, plus the progress bar (non-text, 3:1) |
| `#9C7C33` gold-display-only | `#FAF9F7` ink-50 | **3.73:1** | **FAIL** | PASS | Display type ≥ 24 px only |
| `#8A6D28` gold-text | `#FFFFFF` | **4.88:1** | PASS | PASS | Small gold text on white |
| `#8A6D28` gold-text | `#FAF9F7` ink-50 | **4.64:1** | PASS | PASS | Small gold text on raised cards |
| `#8A6D28` gold-text | `#F0EFEC` ink-100 | **4.25:1** | **FAIL** | PASS | **Use `gold-text-strong` here instead** |
| `#8A6D28` gold-text | `#FBF6EA` gold-tint | **4.52:1** | PASS | PASS | Passes, but with a 0.02 margin — prefer `gold-text-strong` |
| `#6A5320` gold-text-strong | `#FFFFFF` | **7.31:1** | PASS | PASS | AAA-grade gold text |
| `#6A5320` gold-text-strong | `#F0EFEC` ink-100 | **6.36:1** | PASS | PASS | Gold text on sunken bands |
| `#6A5320` gold-text-strong | `#FBF6EA` gold-tint | **6.78:1** | PASS | PASS | Gold text on evidence plates |

#### Text on gold surfaces (buttons)

| Foreground | Background | Ratio | AA normal | AA large | Verdict |
|---|---|---|---|---|---|
| `#1A1A1A` ink-900 | `#C9A961` gold-surface | **7.73:1** | PASS | PASS | **The primary button.** Ink label on gold — per research §6.3. |
| `#1A1A1A` ink-900 | `#B8974E` gold-surface-hover | **6.28:1** | PASS | PASS | Primary button hover |
| `#1A1A1A` ink-900 | `#A8863F` gold-surface-active | **5.10:1** | PASS | PASS | Primary button pressed |
| `#1A1A1A` ink-900 | `#E4C87F` gold-surface-light | **11.49:1** | PASS | PASS | Gradient light end — still safe |
| `#FFFFFF` white | `#C9A961` gold-surface | **2.25:1** | **FAIL** | **FAIL** | **Never white text on gold.** |

#### Status and verdict colours

| Foreground | Background | Ratio | AA normal | AA large | Verdict |
|---|---|---|---|---|---|
| `#1B7A4B` success-600 | `#FFFFFF` | **5.34:1** | PASS | PASS | Halal verdict icon + text on white |
| `#FFFFFF` | `#1B7A4B` success-600 | **5.34:1** | PASS | PASS | White label on halal badge fill |
| `#14603A` success-700 | `#FFFFFF` | **7.60:1** | PASS | PASS | Halal verdict text, AAA |
| `#14603A` success-700 | `#EAF5EF` success-50 | **6.80:1** | PASS | PASS | Halal verdict on tinted row |
| `#8F5A00` warning-600 | `#FFFFFF` | **5.78:1** | PASS | PASS | Mushbooh verdict text |
| `#8F5A00` warning-600 | `#FDF3E3` warning-50 | **5.26:1** | PASS | PASS | Mushbooh verdict on tinted row |
| `#B87400` warning-500 | `#FFFFFF` | **3.79:1** | **FAIL** | PASS | Non-text only — rules, icons ≥24 px |
| `#B3261E` danger-600 | `#FFFFFF` | **6.54:1** | PASS | PASS | Error border, out-of-stock icon |
| `#FFFFFF` | `#B3261E` danger-600 | **6.54:1** | PASS | PASS | White label on error fill |
| `#8C1D18` danger-700 | `#FFFFFF` | **9.12:1** | PASS | PASS | Error message text, `Remove` action |
| `#8C1D18` danger-700 | `#FCEDEC` danger-50 | **8.01:1** | PASS | PASS | Error text on tinted surface |
| `#1D5FA8` info-600 | `#FFFFFF` | **6.45:1** | PASS | PASS | Links, focus ring (non-text 3:1 also PASS) |
| `#FFFFFF` | `#1D5FA8` info-600 | **6.45:1** | PASS | PASS | White label on info fill |
| `#FFFFFF` | `#075E54` whatsapp | **7.67:1** | PASS | PASS | WhatsApp button label |

#### Focus ring adjacency

| Ring | Adjacent surface | Ratio | Non-text (3.0) | Verdict |
|---|---|---|---|---|
| `#1D5FA8` info-600 | `#FFFFFF` page | **6.45:1** | PASS | Default focus ring |
| `#1D5FA8` info-600 | `#C9A961` gold button fill | **2.87:1** | **FAIL** | **This is why `outline-offset: 2px` is mandatory** — the 2 px gap renders page-white between fill and ring, and the ring is then measured against white at 6.45:1. Never set `outline-offset: 0` on a gold surface. |
| `#7FB2E8` dark-focus | `#121212` dark-bg | **8.42:1** | PASS | Focus ring in dark theme |

#### Dark theme

| Foreground | Background | Ratio | AA normal | AA large | Verdict |
|---|---|---|---|---|---|
| `#F2F0EC` dark-text | `#121212` dark-bg | **16.46:1** | PASS | PASS | Body text on dark |
| `#F2F0EC` dark-text | `#1C1C1C` dark-surface | **14.98:1** | PASS | PASS | Body text on dark cards |
| `#A8A29A` dark-text-muted | `#121212` dark-bg | **7.40:1** | PASS | PASS | Secondary text on dark |
| `#C9A961` gold-on-dark | `#121212` dark-bg | **8.32:1** | PASS | PASS | **Gold becomes a legal text colour on dark.** |
| `#C9A961` gold-on-dark | `#1C1C1C` dark-surface | **7.57:1** | PASS | PASS | Gold text on dark cards |
| `#E4C87F` gold-surface-light | `#1A1A1A` ink-900 | **10.66:1** | PASS | PASS | Gold display type on dark sections |

> **The one-line rule to remember:** on light, gold is a *surface*; on dark, gold is *ink*. The token names encode this and there is no token that lets you do it the other way round.

---

### 1.4 Ready-to-paste Tailwind 4 `@theme` CSS — Part 1 of 3

**File:** `resources/css/app.css`. Parts 1, 2 and 3 concatenate into that single file, in order. This **replaces** the current contents (which still carry the Laravel starter's `Instrument Sans` default — delete that).

```css
/* ============================================================
   GLOW HALAL — DESIGN SYSTEM
   Tailwind CSS 4 · CSS-first configuration
   Part 1/3 — imports, sources, dark variant, colour tokens
   ============================================================ */

@import 'tailwindcss';

@source '../views';
@source '../../app/Livewire';
@source '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php';
@source '../../storage/framework/views/*.php';

/* Dark theme is driven by a resolved [data-theme] attribute written to <html>
   by the inline head script in §1.5. The script always resolves "system" to a
   concrete light|dark value, so CSS never needs a prefers-color-scheme branch. */
@custom-variant dark (&:where([data-theme='dark'], [data-theme='dark'] *));

@theme {
  /* --- Reset Tailwind's default palette -------------------------------
     Decision D3. Removing the stock ramps means bg-red-500 / text-gray-400
     simply do not exist, so an unapproved pairing cannot be typed by
     accident. `transparent`, `current` and `inherit` are hardcoded in the
     utilities and survive the reset; white and black do not, so they are
     redeclared below. */
  --color-*: initial;

  --color-white: #ffffff;
  --color-black: #000000;

  /* --- Brand gold — nine named roles, deliberately no numeric ramp (D2)
     Contrast, measured on #FFFFFF unless noted:
       gold-surface        2.25:1  FAIL normal · FAIL large  -> surface only
       gold-surface-hover  2.77:1  FAIL       · FAIL         -> surface only
       gold-surface-active 3.42:1  FAIL       · PASS large   -> surface only anyway
       gold-tint           1.08:1  FAIL       · FAIL         -> surface only
       gold-surface-light  1.63:1  FAIL       · FAIL         -> surface only
       gold-tint           surface only (it is a background)
       gold-display-only   3.93:1  FAIL normal · PASS large  -> >=24px only
       gold-text           4.88:1  PASS       · PASS         -> white/ink-50 only
       gold-text-strong    7.31:1  PASS AAA   · PASS         -> any light surface
       gold-on-dark        8.32:1 on #121212  PASS AAA       -> dark surfaces only
  */
  --color-gold-surface: #c9a961;
  --color-gold-surface-hover: #b8974e;
  --color-gold-surface-active: #a8863f;
  --color-gold-surface-light: #e4c87f;
  --color-gold-tint: #fbf6ea;
  --color-gold-display-only: #9c7c33;
  --color-gold-text: #8a6d28;
  --color-gold-text-strong: #6a5320;
  --color-gold-on-dark: #c9a961;

  /* --- Ink (neutrals) — ratios on #FFFFFF ---------------------------- */
  --color-ink-950: #0f0f0f; /* 19.17:1  PASS AAA */
  --color-ink-900: #1a1a1a; /* 17.41:1  PASS AAA — body text */
  --color-ink-800: #2e2e2e; /* 13.58:1  PASS AAA — labels */
  --color-ink-700: #4a4a4a; /*  8.86:1  PASS AAA */
  --color-ink-600: #5c5c5c; /*  6.69:1  PASS AA  */
  --color-ink-500: #6e6e6e; /*  5.10:1  PASS AA  — small-text floor */
  --color-ink-400: #8a8a8a; /*  3.45:1  FAIL text · PASS non-text — borders */
  --color-ink-300: #b5b5b5; /*  2.05:1  FAIL both — decoration only */
  --color-ink-200: #d9d9d9; /*  1.41:1  FAIL both — skeletons, hairlines */
  --color-ink-100: #f0efec; /*  surface */
  --color-ink-50: #faf9f7;  /*  surface */

  /* --- Status / halal verdict --------------------------------------- */
  --color-success-600: #1b7a4b; /* 5.34:1 on white PASS · white on it 5.34:1 PASS */
  --color-success-700: #14603a; /* 7.60:1 on white PASS AAA · 6.80:1 on success-50 PASS */
  --color-success-50: #eaf5ef;

  --color-warning-600: #8f5a00; /* 5.78:1 on white PASS · 5.26:1 on warning-50 PASS */
  --color-warning-500: #b87400; /* 3.79:1 on white FAIL normal · PASS non-text */
  --color-warning-50: #fdf3e3;

  --color-danger-600: #b3261e; /* 6.54:1 on white PASS · white on it 6.54:1 PASS */
  --color-danger-700: #8c1d18; /* 9.12:1 on white PASS AAA · 8.01:1 on danger-50 PASS */
  --color-danger-50: #fcedec;

  --color-info-600: #1d5fa8;   /* 6.45:1 on white PASS · focus ring */
  --color-info-50: #eaf1f9;

  --color-whatsapp: #075e54;   /* white on it 7.67:1 PASS AAA */
  --color-whatsapp-hover: #05463f;

  /* --- Dark theme surfaces ------------------------------------------- */
  --color-dark-bg: #121212;
  --color-dark-surface: #1c1c1c;
  --color-dark-border: #3a3a3a;
  --color-dark-text: #f2f0ec;       /* 16.46:1 on dark-bg PASS AAA */
  --color-dark-text-muted: #a8a29a; /*  7.40:1 on dark-bg PASS AAA */
  --color-dark-focus: #7fb2e8;      /*  8.42:1 on dark-bg PASS */
}
```

---

### 1.4 Ready-to-paste Tailwind 4 `@theme` CSS — Part 2 of 3

```css
/* ============================================================
   Part 2/3 — type, spacing, radii, shadows, breakpoints, easing
   ============================================================ */

@theme {
  /* --- Font families -------------------------------------------------- */
  --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto,
    'Helvetica Neue', Arial, sans-serif;
  --font-display: 'Playfair Display', 'Iowan Old Style', 'Palatino Linotype',
    Georgia, 'Times New Roman', serif;
  --font-mono: ui-monospace, 'SFMono-Regular', 'SF Mono', Menlo, Consolas,
    'Roboto Mono', 'Liberation Mono', monospace;
  /* Reserved for the P2 Urdu toggle — loaded only on lang="ur" documents. */
  --font-urdu: 'Noto Nastaliq Urdu', 'Jameel Noori Nastaleeq', serif;

  /* --- Font weights — only what we actually ship (D3) -----------------
     Resetting the namespace removes font-bold (700). We ship 400 and 600
     only, so font-bold would have produced a synthesised faux-bold on
     both faces. `strong`/`b` are remapped to 600 in the base layer. */
  --font-weight-*: initial;
  --font-weight-normal: 400;
  --font-weight-semibold: 600;

  /* --- Font sizes — full reset, then the Glow Halal scale -------------
     The reset deletes text-xs (12px), which structurally enforces the
     14px floor demanded by Persona C (research §1, §6.3).
     Display sizes are fluid via clamp(); body sizes never are, so a user
     who has raised their browser text size gets the full increase on the
     text that actually matters. */
  --text-*: initial;

  --text-meta: 0.875rem;              /* 14px — absolute floor, metadata only */
  --text-meta--line-height: 1.45;

  --text-body: 1rem;                  /* 16px — minimum body, prevents iOS zoom */
  --text-body--line-height: 1.6;

  --text-lead: 1.125rem;              /* 18px — intro paragraphs, PDP prose */
  --text-lead--line-height: 1.6;

  --text-title-sm: 1.125rem;          /* 18px semibold — card titles, h4 */
  --text-title-sm--line-height: 1.35;
  --text-title-sm--font-weight: 600;

  --text-title: 1.25rem;              /* 20px — h3, section subheads */
  --text-title--line-height: 1.3;
  --text-title--font-weight: 600;

  --text-title-lg: 1.5rem;            /* 24px — h2 (sans, functional sections) */
  --text-title-lg--line-height: 1.25;
  --text-title-lg--font-weight: 600;

  --text-price: 1.375rem;             /* 22px — PDP price */
  --text-price--line-height: 1.2;
  --text-price--font-weight: 600;

  --text-price-sm: 1rem;              /* 16px — product-card price */
  --text-price-sm--line-height: 1.25;
  --text-price-sm--font-weight: 600;

  --text-overline: 0.875rem;          /* 14px caps — eyebrow labels */
  --text-overline--line-height: 1.2;
  --text-overline--letter-spacing: 0.1em;
  --text-overline--font-weight: 600;

  --text-inci: 0.9375rem;             /* 15px mono — INCI names read ~1px small */
  --text-inci--line-height: 1.55;

  /* Display — serif, fluid. 375px -> min, ~1280px -> max. */
  --text-display-sm: clamp(1.5rem, 1.15rem + 1.5vw, 1.75rem);   /* 24 -> 28 */
  --text-display-sm--line-height: 1.22;
  --text-display-sm--font-weight: 600;

  --text-display: clamp(1.75rem, 1.2rem + 2.4vw, 2.5rem);       /* 28 -> 40 */
  --text-display--line-height: 1.12;
  --text-display--font-weight: 600;

  --text-display-lg: clamp(2.125rem, 1.3rem + 3.5vw, 3.5rem);   /* 34 -> 56 */
  --text-display-lg--line-height: 1.06;
  --text-display-lg--font-weight: 600;

  /* --- Letter spacing ------------------------------------------------- */
  --tracking-display: -0.015em;  /* Playfair tightens well at large sizes */
  --tracking-normal: 0em;
  --tracking-label: 0.01em;
  --tracking-caps: 0.1em;

  /* --- Spacing — 4px base, Tailwind's default step, kept deliberately -- */
  --spacing: 0.25rem;

  /* Layout constants consumed via var(); no utilities generated. */
  --gutter-mobile: 1rem;      /* 16px  — <480px  */
  --gutter-tablet: 1.5rem;    /* 24px  — >=768px */
  --gutter-desktop: 2rem;     /* 32px  — >=1024px */
  --section-y-mobile: 3rem;   /* 48px  */
  --section-y-desktop: 5rem;  /* 80px  */

  /* --- Containers — generates max-w-* utilities ----------------------- */
  --container-page: 75rem;    /* 1200px — the site's outer bound */
  --container-wide: 90rem;    /* 1440px — full-bleed editorial media only */
  --container-read: 44rem;    /* 704px  — long-form prose measure (~72ch) */
  --container-form: 30rem;    /* 480px  — checkout / contact form column */

  /* --- Radii — tight and editorial; pills are for badges only --------- */
  --radius-*: initial;
  --radius-xs: 0.125rem;  /* 2px  — swatch outlines, inline chips */
  --radius-sm: 0.25rem;   /* 4px  — buttons, inputs, cards, the default */
  --radius-md: 0.375rem;  /* 6px  — toasts, dropdown panels */
  --radius-lg: 0.75rem;   /* 12px — modals, evidence plates */
  --radius-xl: 1rem;      /* 16px — bottom-sheet top corners */

  /* --- Shadows — max 2 layers, max 24px blur, never animated ---------
     Low-end Android repaints large-blur shadows expensively. */
  --shadow-*: initial;
  --shadow-xs: 0 1px 2px rgb(15 15 15 / 0.06);
  --shadow-sm: 0 1px 3px rgb(15 15 15 / 0.08), 0 1px 2px rgb(15 15 15 / 0.04);
  --shadow-md: 0 4px 12px rgb(15 15 15 / 0.08);
  --shadow-lg: 0 8px 24px rgb(15 15 15 / 0.1);
  --shadow-bar: 0 -1px 3px rgb(15 15 15 / 0.08);   /* sticky bottom bars */
  --shadow-sheet: 0 -8px 24px rgb(15 15 15 / 0.12); /* bottom sheets */

  /* --- Breakpoints — Tailwind defaults plus xs for small Android ------
     360x800 is the most common Android class in this market (research
     §6.2); xs=480 is the first point at which a 2-up card grid is legible. */
  --breakpoint-xs: 30rem;   /* 480px  */
  /* sm 640 / md 768 / lg 1024 / xl 1280 / 2xl 1536 remain as shipped */

  /* --- Easing — generates ease-* utilities ---------------------------- */
  --ease-standard: cubic-bezier(0.2, 0, 0, 1);
  --ease-enter: cubic-bezier(0.05, 0.7, 0.1, 1);
  --ease-exit: cubic-bezier(0.3, 0, 0.8, 0.15);
  --ease-pop: cubic-bezier(0.34, 1.2, 0.64, 1); /* cart badge only */

  /* --- Animations ----------------------------------------------------- */
  --animate-skeleton: skeleton-pulse 1.4s ease-in-out infinite;
  --animate-badge-pop: badge-pop 320ms var(--ease-pop);
  --animate-toast-in: toast-in 240ms var(--ease-enter);
  --animate-sheet-in: sheet-in 280ms var(--ease-enter);

  /* Opacity-only pulse. Deliberately NOT a gradient sweep: a moving
     background-position forces a full repaint of the skeleton area on
     every frame, which stutters on the Infinix/Tecno class of device
     Persona B is browsing on. Opacity is composited. */
  @keyframes skeleton-pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.55; }
  }
  @keyframes badge-pop {
    0% { transform: scale(1); }
    45% { transform: scale(1.18); }
    100% { transform: scale(1); }
  }
  @keyframes toast-in {
    from { opacity: 0; transform: translate3d(0, 16px, 0); }
    to { opacity: 1; transform: translate3d(0, 0, 0); }
  }
  @keyframes sheet-in {
    from { transform: translate3d(0, 100%, 0); }
    to { transform: translate3d(0, 0, 0); }
  }
}
```

---

### 1.4 Ready-to-paste Tailwind 4 `@theme` CSS — Part 3 of 3

Tailwind 4 has **no theme namespace for z-index or transition-duration**. Those ship as plain custom properties in `:root` and are consumed with `z-[var(--z-modal)]` and `duration-[var(--motion-base)]` (the shorthand forms `z-(--z-modal)` / `duration-(--motion-base)` also work). This is not a workaround — it is the documented mechanism.

```css
/* ============================================================
   Part 3/3 — runtime semantic layer, z-index, motion, base styles
   ============================================================ */

@layer base {
  :root {
    /* --- Z-INDEX LADDER ---------------------------------------------
       Eleven rungs. Nothing in the app may use a raw z-index integer.
       Gaps of 10 exist so a one-off can slot in without a renumber. */
    --z-below: -1;          /* decorative wash behind content       */
    --z-base: 0;            /* normal flow                          */
    --z-raised: 10;         /* hovered card, image-zoom lens        */
    --z-dropdown: 40;       /* select menus, autocomplete lists     */
    --z-sticky-cta: 60;     /* PDP sticky buy bar, checkout bar     */
    --z-header: 100;        /* sticky site header                   */
    --z-bottom-nav: 110;    /* mobile bottom navigation bar         */
    --z-fab: 120;           /* floating WhatsApp button             */
    --z-overlay: 300;       /* scrim behind sheets and drawers      */
    --z-sheet: 310;         /* bottom sheets, mobile menu           */
    --z-modal: 410;         /* dialogs, certificate lightbox        */
    --z-toast: 500;         /* toasts — must clear every sheet      */
    --z-skiplink: 600;      /* skip-to-content, always topmost      */

    /* --- MOTION DURATIONS ------------------------------------------- */
    --motion-instant: 100ms; /* press feedback, checkbox tick        */
    --motion-fast: 150ms;    /* hover, focus, colour change          */
    --motion-base: 200ms;    /* the default for anything unspecified */
    --motion-toast: 240ms;   /* toast enter                          */
    --motion-sheet: 280ms;   /* bottom sheet enter                   */
    --motion-slow: 300ms;    /* accordion, large reveal              */
    --motion-exit: 160ms;    /* every exit — exits are always faster */

    /* --- CART RECONCILIATION ---------------------------------------- */
    --debounce-cart: 300ms;  /* research §5.2 Flow C — server write debounce */
    --min-spinner: 120ms;    /* minimum spinner display, prevents flicker    */

    /* --- SEMANTIC COLOUR (theme-switchable) -------------------------- */
    --surface: #ffffff;
    --surface-raised: #faf9f7;
    --surface-sunken: #f0efec;
    --surface-evidence: #fbf6ea;
    --surface-inverse: #1a1a1a;

    --text-primary: #1a1a1a;      /* 17.41:1 on --surface  PASS AAA */
    --text-secondary: #4a4a4a;    /*  8.86:1 on --surface  PASS AAA */
    --text-muted: #5c5c5c;        /*  6.69:1 on --surface  PASS AA  */
    --text-on-inverse: #f2f0ec;   /* 14.98:1 on --surface-inverse PASS AAA */
    --text-gold: #6a5320;         /*  7.31:1 on --surface  PASS AAA */

    --border-strong: #8a8a8a;     /*  3.45:1 on --surface  PASS non-text */
    --border-subtle: #d9d9d9;     /*  decorative only */
    --border-accent: #c9a961;     /*  decorative only — never a state indicator */

    --focus-ring: #1d5fa8;        /*  6.45:1 on --surface  PASS non-text */

    /* Safe-area passthroughs for iOS home-indicator devices */
    --safe-bottom: env(safe-area-inset-bottom, 0px);
    --safe-top: env(safe-area-inset-top, 0px);

    /* Fixed-chrome heights — read by sticky offsets and scroll-margin */
    --h-announcement: 36px;
    --h-header: 56px;
    --h-bottom-nav: 56px;
    --h-buy-bar: 72px;
  }

  [data-theme='dark'] {
    --surface: #121212;
    --surface-raised: #1c1c1c;
    --surface-sunken: #0f0f0f;
    --surface-evidence: #1c1c1c;
    --surface-inverse: #f2f0ec;

    --text-primary: #f2f0ec;      /* 16.46:1 on --surface  PASS AAA */
    --text-secondary: #a8a29a;    /*  7.40:1 on --surface  PASS AAA */
    --text-muted: #a8a29a;        /*  7.40:1 on --surface  PASS AAA */
    --text-on-inverse: #1a1a1a;   /* 16.46:1 on --surface-inverse PASS AAA */
    --text-gold: #c9a961;         /*  8.32:1 on --surface  PASS AAA — gold is
                                     legal as ink here, and only here */

    --border-strong: #3a3a3a;
    --border-subtle: #2a2a2a;
    --border-accent: #c9a961;

    --focus-ring: #7fb2e8;        /*  8.42:1 on --surface  PASS non-text */
  }

  @media (min-width: 768px) {
    :root { --h-header: 72px; --h-announcement: 40px; }
  }
}

/* Map the runtime semantics into utilities. `inline` resolves the var()
   reference at the point of use, so bg-surface follows the cascade and
   flips with [data-theme] without any dark: variant on the element. */
@theme inline {
  --color-surface: var(--surface);
  --color-surface-raised: var(--surface-raised);
  --color-surface-sunken: var(--surface-sunken);
  --color-surface-evidence: var(--surface-evidence);
  --color-surface-inverse: var(--surface-inverse);
  --color-text-primary: var(--text-primary);
  --color-text-secondary: var(--text-secondary);
  --color-text-muted: var(--text-muted);
  --color-text-on-inverse: var(--text-on-inverse);
  --color-text-gold: var(--text-gold);
  --color-border-strong: var(--border-strong);
  --color-border-subtle: var(--border-subtle);
  --color-border-accent: var(--border-accent);
  --color-focus-ring: var(--focus-ring);
}

@layer base {
  html {
    -webkit-text-size-adjust: 100%;
    scroll-behavior: smooth;
    scroll-padding-top: calc(var(--h-header) + var(--h-announcement) + 8px);
  }

  body {
    background-color: var(--surface);
    color: var(--text-primary);
    font-family: var(--font-sans);
    font-size: 1rem;      /* 16px floor — never below */
    line-height: 1.6;
    font-synthesis-weight: none; /* fail loudly rather than faux-bolding */
    text-rendering: optimizeLegibility;
  }

  /* We ship 400 and 600 only. Browser default for strong is 700, which
     would synthesise. Remap it. */
  strong, b, th { font-weight: 600; }

  h1, h2, h3, h4 { text-wrap: balance; }
  p { text-wrap: pretty; }

  /* Tabular figures everywhere a PKR amount can appear, so digits do not
     jitter when an optimistic cart total re-renders. */
  .tnum, [data-price], input[inputmode='numeric'], table td, table th {
    font-variant-numeric: tabular-nums;
  }

  /* One focus treatment, applied globally, never removed per-component.
     The 2px offset is load-bearing: on a gold fill the ring measures
     2.87:1 (FAIL) against the gold itself, but the offset renders page
     background in the gap, where it measures 6.45:1 (PASS). */
  :focus-visible {
    outline: 3px solid var(--focus-ring);
    outline-offset: 2px;
    border-radius: var(--radius-xs);
  }
  :focus:not(:focus-visible) { outline: none; }

  ::selection { background: #e4c87f; color: #1a1a1a; } /* 11.49:1 PASS AAA */

  /* Reduced motion: strip movement, keep state changes legible. */
  @media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
      animation-duration: 0.01ms !important;
      animation-iteration-count: 1 !important;
      transition-duration: 0.01ms !important;
      scroll-behavior: auto !important;
    }
  }
}

/* --- Project utilities ------------------------------------------------ */

@utility container-page {
  width: 100%;
  max-width: var(--container-page);
  margin-inline: auto;
  padding-inline: var(--gutter-mobile);
  @media (min-width: 768px) { padding-inline: var(--gutter-tablet); }
  @media (min-width: 1024px) { padding-inline: var(--gutter-desktop); }
}

/* Logical properties throughout, so the P2 Urdu/RTL retrofit is a
   dir="rtl" attribute rather than a stylesheet rewrite. */
@utility section-y {
  padding-block: var(--section-y-mobile);
  @media (min-width: 768px) { padding-block: var(--section-y-desktop); }
}

@utility evidence-plate {
  background: var(--surface-evidence);
  border: 1px solid var(--border-subtle);
  border-inline-start: 3px solid var(--border-accent);
  border-radius: var(--radius-lg);
  padding: 1.25rem;
}

@utility tap-safe {
  min-block-size: 48px;
  min-inline-size: 48px;
}
```

---

### 1.5 Theme resolution (light / dark / system)

**Storage key:** `glowhalal:theme`, value `light` | `dark` | `system`. Absent means `system`.

**Mechanism:** an inline, render-blocking script in `<head>` — before the stylesheet link — resolves the preference to a concrete value and writes it to `<html data-theme="…">`. Because the attribute is always concrete, the CSS never needs a `prefers-color-scheme` branch and there is no flash of wrong theme.

```html
<!-- First element in <head>, inline, not deferred, not bundled. ~380 bytes. -->
<script>
(function () {
  var stored = null;
  try { stored = localStorage.getItem('glowhalal:theme'); } catch (e) {}
  var pref = stored || 'system';
  var dark = pref === 'dark' || (pref === 'system' &&
    window.matchMedia('(prefers-color-scheme: dark)').matches);
  document.documentElement.setAttribute('data-theme', dark ? 'dark' : 'light');
  document.documentElement.setAttribute('data-theme-pref', pref);
})();
</script>
```

**Runtime behaviour:** when the preference is `system`, subscribe to `matchMedia('(prefers-color-scheme: dark)')` `change` and re-resolve. When the user picks explicitly, write the key and re-resolve immediately.

**Toggle control — anatomy.** A 3-option segmented radio group, not a binary switch, because "system" must be selectable and a two-state switch cannot express it.

| Property | Value |
|---|---|
| Placement | Footer, first item in the utility row. **Not** in the header — header space at 375 px is spent on cart and search, which carry more revenue. |
| Container | height 40 px, `--radius-sm`, `1px solid var(--border-subtle)`, `background: var(--surface-raised)`, `display:flex`, `padding:3px` |
| Option | min 44 × 34 px tap area with 7 px vertical padding extending the hit box to 48 px, `--text-meta` (14 px), weight 600 |
| Selected | `background: var(--surface-inverse)`, `color: var(--text-on-inverse)` — 16.5:1 **PASS AAA**. Never gold: at 3.42:1 `gold-surface-active` would fail the 3:1 non-text bar as a state indicator, and colour alone can't carry state anyway |
| Unselected | `color: var(--text-muted)` — 6.69:1 **PASS AA** |
| Semantics | `role="radiogroup"` with `aria-label="Colour theme"`; each option `role="radio"` + `aria-checked`; arrow-key roving tabindex |
| Labels | `Light` · `Dark` · `System` — text labels, with the icon optional and `aria-hidden`. Icon-only fails for Persona C |
| Motion | `background-color` and `color` transition `var(--motion-fast)`; the whole document gets no transition (a full-page colour cross-fade is expensive on low-end Android) |

**Dark-theme content rules — non-negotiable:**

1. **Product photography keeps a white plate.** Wrap every product image in `background: #FFFFFF; border-radius: var(--radius-sm)` in both themes. Never `filter: invert()`, never a dark plate. The product must look identical to what arrives in the box — this is a counterfeit-fear mitigation, not an aesthetic preference.
2. **Certificate scans keep a white plate** for the same reason, plus a paper-white surround reads as a document.
3. Gold becomes ink (`--text-gold: #C9A961`, 8.32:1 **PASS AAA**). This is the only context in the system where the brand gold is a text colour.
4. Shadows are near-invisible on dark; replace elevation with `--border-subtle` hairlines. Do not raise shadow opacity.

---

### 1.6 Token governance — making misuse visible

The naming scheme is the primary enforcement mechanism. These CI checks are the backstop. Add them to the lint step; each is a one-line grep over `resources/views` and `app/Livewire`.

| # | Forbidden pattern | Regex | Why |
|---|---|---|---|
| G1 | Gold as text on light | `text-gold-surface(-hover\|-active\|-light)?\b` | 2.25:1 FAIL. There is no legal use of these as text. |
| G2 | Gold display token below 24 px | `text-gold-display-only` co-occurring with `text-(meta\|body\|lead\|title-sm\|title)\b` on one element | 3.93:1 passes large only |
| G3 | White on gold | `bg-gold-surface[^ ]*` co-occurring with `text-white` | 2.25:1 FAIL |
| G4 | Raw z-index | `\bz-\[?\d` | Must use `z-[var(--z-*)]` |
| G5 | Raw hex in markup | `#[0-9a-fA-F]{6}` in `.blade.php` | All colour flows through tokens |
| G6 | Sub-floor type | `text-\[1[0-3]px\]\|text-\[0\.[0-8]` | 14 px floor |
| G7 | Focus removal | `outline-none` without an adjacent `focus-visible:` rule | Focus is global and must not be locally deleted |
| G8 | Disabled primary CTA | `disabled` on a `btn-primary` in a product context | Research §5.2: out-of-stock shows `Notify me`, never a dead button |

**Storybook-free review rule:** any PR that introduces a new foreground/background pairing must add a row to the §1.3 matrix with a measured ratio. A pairing with no row is not approved.

---

## 2. Typography

### 2.1 Typeface selection

| Role | Face | Weights shipped | Licence | Why this one |
|---|---|---|---|---|
| **Display / editorial** | **Playfair Display** | 600 only | SIL OFL 1.1 | High-contrast transitional serif in the same family of forms as the logo wordmark. Its hairlines are its weakness at small sizes — which is irrelevant here, because D1 already restricts display type to ≥24 px. The constraint and the typeface agree. |
| **UI / body** | **Inter** | 400, 600 | SIL OFL 1.1 | Largest x-height per unit of vertical space of the realistic options, which is what makes 16 px genuinely readable on a 360 px Android screen. Has true tabular figures (`tnum`), which PKR prices need. Unambiguous 1/l/I — matters when a customer is reading a certificate number. |
| **INCI / metadata** | **System monospace stack** | — | — | **0 KB.** INCI names are the one place a monospace is functionally right (they are label text, read character by character), and no webfont can justify its bytes for that. Falls through `ui-monospace → SF Mono → Roboto Mono → Consolas → monospace`. |
| **Urdu (P2)** | **Noto Nastaliq Urdu** | 400 | SIL OFL 1.1 | See §2.4. Not loaded on English pages under any circumstances. |

**The wordmark is an inline SVG, not text.** ~1.5 KB, no font dependency, renders identically before any font loads, and gives crisp gold at every DPI. This matters more than it sounds: it means brand identity is *never* waiting on a network request, and it means the escape hatch below costs nothing visually at the top of the page.

#### Payload budget

| File | Format | Subset | Budget |
|---|---|---|---|
| `inter-400.woff2` | WOFF2 | Latin + Latin-Ext punctuation | ≤ 24 KB |
| `inter-600.woff2` | WOFF2 | Latin + Latin-Ext punctuation | ≤ 24 KB |
| `playfair-600.woff2` | WOFF2 | Latin, **uppercase + lowercase + digits + basic punctuation only** (headings never contain symbols) | ≤ 26 KB |
| **Total font payload** | | | **≤ 74 KB** |

Research §6.1 sets "2 weights max". This ships three files. The deviation is deliberate and bounded: the two *body* weights are the budgeted pair, and Playfair is a third file carrying roughly 26 KB for the editorial voice that separates this brand from a template. **The escape hatch is pre-authorised:** if the measured homepage exceeds 600 KB, or LCP on throttled 4G exceeds 2.5 s, drop `playfair-600.woff2` and render display type in Inter 600 at `--tracking-display`. Do not negotiate this at review time — measure, then act.

#### Self-hosting and loading

Fonts live in `public/fonts/`, served same-origin. **No Google Fonts, no Bunny, no CDN** — a third-party font host is a second DNS lookup and TLS handshake on a congested Pakistani cell, and it is the single most common cause of a 4-second first paint.

```css
/* resources/css/fonts.css — imported first in app.css */

@font-face {
  font-family: 'Inter';
  src: url('/fonts/inter-400.woff2') format('woff2');
  font-weight: 400;
  font-style: normal;
  font-display: swap;
  unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+2000-206F,
    U+2074, U+20A8, U+20B9, U+20BC, U+2122, U+2191, U+2193, U+2212, U+2215;
}
@font-face {
  font-family: 'Inter';
  src: url('/fonts/inter-600.woff2') format('woff2');
  font-weight: 600;
  font-style: normal;
  font-display: swap;
  unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+2000-206F,
    U+2074, U+20A8, U+20B9, U+20BC, U+2122, U+2191, U+2193, U+2212, U+2215;
}
@font-face {
  font-family: 'Playfair Display';
  src: url('/fonts/playfair-600.woff2') format('woff2');
  font-weight: 600;
  font-style: normal;
  font-display: swap;
  unicode-range: U+0020-007E, U+00A0-00FF, U+2018-201D, U+2013-2014, U+2026;
}

/* Metric-matched fallbacks so the swap does not shift layout.
   Generate the exact override values with the Fallback Font Generator
   (screenspan.org/tools) against the shipped subsets and paste the result;
   the values below are the expected magnitudes, not a substitute for
   measuring. Verify CLS < 0.02 on the homepage after wiring these up. */
@font-face {
  font-family: 'Inter Fallback';
  src: local('Arial');
  size-adjust: 107%;
  ascent-override: 90%;
  descent-override: 22%;
  line-gap-override: 0%;
}
@font-face {
  font-family: 'Playfair Fallback';
  src: local('Georgia');
  size-adjust: 105%;
  ascent-override: 96%;
  descent-override: 22%;
  line-gap-override: 0%;
}
```

Insert `'Inter Fallback'` and `'Playfair Fallback'` as the first fallback in `--font-sans` and `--font-display` respectively.

**Preload exactly two files** — `inter-400` and `inter-600`. Playfair is not preloaded: it is only needed below the first heading, and preloading it competes with the LCP hero image for bandwidth.

```html
<link rel="preload" href="/fonts/inter-400.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/fonts/inter-600.woff2" as="font" type="font/woff2" crossorigin>
```

Serve all three with `Cache-Control: public, max-age=31536000, immutable` and hashed filenames.

---

### 2.2 The type scale

Display sizes are fluid (`clamp()`); body sizes are fixed. That split is deliberate: Persona C raises her browser text size, and fixed `rem` body type gives her the full increase, whereas a `vw`-driven size would partly ignore it. Nothing a user needs to *read* is fluid.

| Token / utility | Face | Mobile (375) | Desktop (≥1280) | Line-height | Weight | Tracking |
|---|---|---|---|---|---|---|
| `text-display-lg` | Playfair | **34 px** | **56 px** (max at 1005 px) | 1.06 | 600 | −0.015em |
| `text-display` | Playfair | **28 px** | **40 px** (max at 866 px) | 1.12 | 600 | −0.015em |
| `text-display-sm` | Playfair | **24 px** | **28 px** (max at 640 px) | 1.22 | 600 | −0.015em |
| `text-title-lg` | Inter | 24 px | 24 px | 1.25 | 600 | 0 |
| `text-price` | Inter | 22 px | 22 px | 1.20 | 600 | 0 · `tnum` |
| `text-title` | Inter | 20 px | 20 px | 1.30 | 600 | 0 |
| `text-title-sm` | Inter | 18 px | 18 px | 1.35 | 600 | 0 |
| `text-lead` | Inter | 18 px | 18 px | 1.60 | 400 | 0 |
| `text-price-sm` | Inter | 16 px | 16 px | 1.25 | 600 | 0 · `tnum` |
| `text-body` | Inter | **16 px** | 16 px | 1.60 | 400 | 0 |
| `text-inci` | Mono | 15 px | 15 px | 1.55 | 400 | 0 |
| `text-overline` | Inter | 14 px | 14 px | 1.20 | 600 | +0.10em, uppercase |
| `text-meta` | Inter | **14 px** | 14 px | 1.45 | 400 | 0 |

**The floor is 14 px and it is enforced by the absence of a smaller token** (`--text-*: initial` deleted `text-xs`). 14 px is licensed only for: breadcrumbs, image captions, badge labels, table cell metadata, form helper text, timestamps, footer legal copy. It is **never** used for a product description, an error message, an ingredient annotation, or anything a user must act on.

**16 px is also an input floor**, not just a reading floor — anything below it triggers zoom-on-focus in iOS Safari, which throws the checkout layout sideways mid-form.

#### Vertical rhythm

Headings carry space-before, not space-after, so a heading always binds to the content under it:

| Element | `margin-block-start` | `margin-block-end` |
|---|---|---|
| `h1` / display | 0 (first in section) | 12 px mobile · 16 px desktop |
| `h2` | 40 px mobile · 56 px desktop | 12 px |
| `h3` | 32 px | 8 px |
| `h4` | 24 px | 8 px |
| `p` | 0 | 16 px (last child 0) |
| `ul` / `ol` | 0 | 16 px; item gap 8 px; marker inset 20 px |
| Table | 16 px | 24 px |

Prose measure is capped at `--container-read` (704 px, ~72ch). Never let body copy run the full 1200 px container.

---

### 2.3 Where each style is used

| Context | Token | Notes |
|---|---|---|
| Homepage h1 | `text-display` | 28 px at 375. Two lines maximum — copy must be written to fit. |
| Section headings (editorial: About, Journal, Founder) | `text-display-sm` | Serif signals *voice* |
| Section headings (functional: cart, checkout, INCI, filters) | `text-title-lg` | Sans signals *utility*. Never serif on a form. |
| Product name, PDP | `text-display-sm` (24 px) | |
| Product name, card | `text-title-sm` (18 px) | Clamp to 2 lines, `text-wrap: balance` |
| PDP price | `text-price` (22 px), `tnum`, `--text-primary` | Always `PKR 4,850` — currency prefix, thin space, comma groups, no decimals |
| Card price | `text-price-sm` (16 px), `tnum` | |
| Body copy, PDP descriptions, About | `text-body` | |
| Article intro, PDP "what it does" opener | `text-lead` | |
| INCI names | `text-inci`, mono, `--text-primary` | Selectable, never inside an image |
| INCI annotations (`[Plant — shea]`) | `text-meta`, `--text-secondary` | |
| Certificate number | `text-inci`, mono, `--text-primary`, `letter-spacing: 0.02em` | Tap-to-copy |
| Eyebrow labels ("THE FOUNDING COLLECTION") | `text-overline` | `--text-gold` (`#6A5320`, 7.31:1 **PASS AAA**) — this is the one small-text gold use in the system |
| Button labels | 16 px / 600 | 15 px on the `sm` size only |
| Form labels | 15 px / 600, `--text-secondary` at `#2E2E2E` → 13.58:1 **PASS AAA** | |
| Helper text | `text-meta`, `--text-muted` → 6.69:1 **PASS AA** | |
| Error text | `text-meta` at **weight 600**, `danger-700` → 9.12:1 **PASS AAA** | Weight, icon and text — never colour alone |
| Breadcrumbs | `text-meta`, `--text-muted` | |
| Footer legal / NTN block | `text-meta`, `--text-secondary` | Persona C reads this — do not drop it to muted |

---

### 2.4 Urdu and Arabic script — what to do now, before it is needed

Urdu is a P2 deliverable (research §8, item 21: an Urdu toggle for trust-critical pages — the halal explainer, delivery instructions, "What We Never Use"). **Nothing Urdu ships in v1.** But three decisions must be taken now, because retrofitting them later is expensive and doing them now is free:

1. **Write every layout in logical properties.** `margin-inline-start` not `margin-left`; `padding-inline` not `padding-x`; `text-align: start` not `left`; `border-inline-start` not `border-left`; `inset-inline-end` not `right`. Tailwind's `ms-*`, `me-*`, `ps-*`, `pe-*`, `start-*`, `end-*` and `text-start`/`text-end` utilities all do this. **A `dir="rtl"` retrofit then costs an attribute, not a stylesheet rewrite.** This is mandatory in v1 even though no RTL page exists yet.
2. **Never load the Urdu face globally.** Noto Nastaliq Urdu is 150–400 KB depending on subset — five times the entire Latin font budget. It loads only on documents carrying `lang="ur"`, from a separate stylesheet, and it is `unicode-range`-scoped so a stray Arabic character on an English page cannot trigger the download:
   ```css
   @font-face {
     font-family: 'Noto Nastaliq Urdu';
     src: url('/fonts/noto-nastaliq-urdu-400.woff2') format('woff2');
     font-weight: 400;
     font-display: swap;
     unicode-range: U+0600-06FF, U+0750-077F, U+08A0-08FF, U+FB50-FDFF, U+FE70-FEFF;
   }
   ```
3. **Nastaliq, not Naskh, and budget the line-height.** Urdu readers find Naskh (the default Arabic style in most Noto faces) visibly wrong for Urdu. Nastaliq is also steeply sloped and needs far more vertical room: set `line-height: 2.0` minimum and `font-size: 1.125rem` (18 px) for Urdu body text, versus 1.6/16 px for English. Reserve that space in any component that may hold Urdu — do not build fixed-height text containers on trust pages.

**Numerals stay Western Arabic** (`4,850`, not `۴,۸۵۰`) in prices, phone numbers and certificate numbers even in Urdu mode. Pakistani commerce reads Western digits, and a certificate number must be transcribable into the certifier's own directory search.

---

## 3. Component library

Every component below is specified at 375 px first. Where no breakpoint is stated, the value holds at every width.

Three rules apply to all of them and are not repeated in each entry:

- **Touch target ≥ 48 × 48 px**, with ≥ 8 px clear space between adjacent targets. Where a control looks smaller than 48 px, it carries a transparent `::after { position:absolute; inset:-Npx }` that extends the hit box. The *visual* size and the *tap* size are separate numbers.
- **Focus is global** (`:focus-visible`, 3 px `--focus-ring`, 2 px offset, §1.4 Part 3). Components never restyle or remove it. The 2 px offset is what makes the ring legal on gold fills.
- **No hover state carries information.** Every hover affordance has a tap equivalent (research §6.2).

---

### 3.1 Buttons

#### Sizing

| Size | Height | Padding (inline) | Label | Icon | Min width | Use |
|---|---|---|---|---|---|---|
| `sm` | 40 px visual / **48 px hit box** | 16 px | 15 px / 600 | 16 px | 72 px | Inside cards, filter chips, toast actions |
| `md` (default) | 48 px | 24 px | 16 px / 600 | 18 px | 96 px | Everything not otherwise specified |
| `lg` | 56 px | 32 px | 16 px / 600 | 20 px | 140 px | Sticky buy bar, Place Order, homepage hero CTA |

Shared: `--radius-sm` (4 px) · `gap: 8px` between icon and label · `white-space: nowrap` · `font-family: var(--font-sans)` · `transition: background-color var(--motion-fast) var(--ease-standard), border-color var(--motion-fast) var(--ease-standard)` — **never transition `box-shadow` or `transform` on hover**, only on press.

Full-width on mobile is the default for any primary action inside a form or a buy block. Auto-width applies from `xs` (480 px) up.

#### Variants and states

**Primary** — the only gold-filled control in the system. One per viewport, ideally one per page section.

| State | Background | Label | Border | Measured |
|---|---|---|---|---|
| Default | `gold-surface` `#C9A961` | `ink-900` `#1A1A1A` | none | **7.73:1 PASS AAA** |
| Hover | `gold-surface-hover` `#B8974E` | `ink-900` | none | **6.28:1 PASS AA** |
| Active | `gold-surface-active` `#A8863F` | `ink-900` | none | **5.10:1 PASS AA** + `transform: scale(0.98)` for `--motion-instant` |
| Focus | unchanged | unchanged | `outline: 3px #1D5FA8; outline-offset: 2px` | ring vs page white **6.45:1 PASS**; ring vs gold is 2.87:1 FAIL, which is exactly why the offset is mandatory |
| Disabled | `ink-200` `#D9D9D9` | `ink-500` `#6E6E6E` | none | 3.61:1 — FAIL AA; WCAG exempts inactive controls, **but see the rule below** |
| Loading | `gold-surface` | 18 px spinner, `ink-900`, 2 px stroke, `animate-spin` | none | `aria-busy="true"`, width locked to the measured default width so the bar does not reflow |
| Success | `success-600` `#1B7A4B` | `#FFFFFF` + check glyph | none | **5.34:1 PASS AA** — held 900 ms, then morphs to the quantity stepper |

> **Disabled is almost always the wrong answer here.** Research §5.2: an out-of-stock product shows `Notify me`, never a dead button. A checkout button with an incomplete form stays *enabled* and, on press, moves focus to the first invalid field and announces the error. The disabled style exists for genuinely inert cases (a submitted form mid-flight) and nothing else.

**Secondary** — `Buy Now`, "View the certificate", "Continue shopping". Deliberately quieter than primary because Add to Cart is the higher-AOV path (research §5.2 Flow B).

| State | Background | Label | Border |
|---|---|---|---|
| Default | `transparent` | `ink-900` — **17.41:1 PASS AAA** | `1.5px solid #1A1A1A` |
| Hover | `ink-100` `#F0EFEC` | `ink-900` — **15.14:1 PASS AAA** | `1.5px solid #1A1A1A` |
| Active | `ink-200` `#D9D9D9` | `ink-900` — **12.33:1 PASS AAA** | `1.5px solid #1A1A1A` |
| Disabled | `transparent` | `ink-400` `#8A8A8A` | `1.5px solid #D9D9D9` |

**Tertiary / text** — inline links inside content blocks, "See all 8 ingredients we exclude".

| State | Label | Decoration |
|---|---|---|
| Default | `--text-gold` `#6A5320` — **7.31:1 PASS AAA** | `underline`, `text-underline-offset: 3px`, `text-decoration-thickness: 1px` |
| Hover | `ink-900` `#1A1A1A` — **17.41:1 PASS AAA** | thickness `2px` |
| Active | `ink-900` | thickness `2px` |

Underline is present at rest, not on hover. A gold-ish text link without an underline would be relying on colour alone.

**WhatsApp** — a distinct variant because it is a conversion tool, not a support link (research §3.2).

| State | Background | Label | Measured |
|---|---|---|---|
| Default | `whatsapp` `#075E54` | `#FFFFFF` + WhatsApp glyph | **7.67:1 PASS AAA** |
| Hover / Active | `#05463F` | `#FFFFFF` | **10.74:1 PASS AAA** |

Always `href="https://wa.me/92XXXXXXXXXX?text=…"` with a **pre-filled, context-specific message** (`Hi, I have a question about the Nourishing Face Cream`). `target="_blank" rel="noopener"`. Next to every WhatsApp button, the number also appears as **selectable text** — Persona C saves it to contacts.

**Destructive** — text-only. There is no filled red button anywhere in this system.

| State | Label | Notes |
|---|---|---|
| Default | `danger-700` `#8C1D18` — **9.12:1 PASS AAA** | 15 px / 600, no underline, trash glyph optional |
| Hover | `#7A1914` — **10.62:1 PASS AAA** | |

`Remove` in the cart is never adjacent to `Proceed to Checkout` — minimum 24 px separation and never on the same horizontal band (research §6.2).

**Inverse** — for the ink-950 sections (founder block, footer). Primary inverse is the gold fill unchanged (`ink-900` label on gold reads **7.73:1** regardless of the surface behind it). Secondary inverse: transparent fill, `1.5px solid #F2F0EC` border, `#F2F0EC` label — **16.46:1 PASS AAA** on `#121212`. Focus ring switches to `--focus-ring` which is already `#7FB2E8` on dark — **8.42:1 PASS**.

**Icon button** — 48 × 48, transparent, 24 px glyph in `ink-900`, `--radius-sm`, hover `ink-100`. **Always** carries `aria-label`. Used for: menu, search, cart, gallery close, sheet dismiss, copy-to-clipboard.

#### Button accessibility notes

- Real `<button>` / `<a>` elements. No `<div role="button">`.
- Loading state: keep the accessible name stable (`aria-busy="true"`, do not swap the label text for "Loading"), so a screen-reader user does not lose their place.
- Success state: announce via the toast's `role="status"` region, not by mutating the button's label — the button is not a live region.
- Minimum 120 ms spinner display (`--min-spinner`) so a fast response does not produce a flash.
- Icon-only buttons in the bottom nav still carry a visible text label beneath the glyph (see §5.2). Icon-only navigation fails Persona C.

---

### 3.2 Form inputs

The checkout is six fields (research §5.3) and every one of them is a chance to lose the order. These specs are tuned for that, not for a generic form.

#### Anatomy (top to bottom)

```
Label                     15px/600  #2E2E2E   13.58:1 PASS AAA
[ Input                                    ]  52px tall
Helper or error text      14px      per state
```

Field group vertical gap: **20 px**. Label→input gap: **6 px**. Input→helper gap: **6 px**.

#### Base input

| Property | Value | Rationale |
|---|---|---|
| Height | **52 px** (mobile and desktop) | Above the 48 px floor with room for a 2 px error border without reflow |
| Font size | **16 px** | Below this, iOS Safari zooms on focus and breaks the checkout layout |
| Text colour | `ink-900` `#1A1A1A` — **17.41:1 PASS AAA** | |
| Placeholder | `ink-500` `#6E6E6E` — **5.10:1 PASS AA** | Placeholders are *examples*, never labels |
| Background | `--surface` `#FFFFFF` | |
| Border | `1.5px solid #8A8A8A` (`ink-400`) — **3.45:1 vs white, PASS non-text** | `ink-300` at 2.05:1 would fail 1.4.11 |
| Radius | `--radius-sm` (4 px) | |
| Padding | `14px 16px` | |
| Width | 100 %, capped by `--container-form` (480 px) | |

#### States

| State | Border | Background | Helper text | Extra |
|---|---|---|---|---|
| Default | `1.5px #8A8A8A` | white | `--text-muted` `#5C5C5C` — **6.69:1 PASS AA** | — |
| Hover | `1.5px #5C5C5C` — **6.69:1 PASS** | white | unchanged | Pointer devices only |
| Focus | `1.5px #1A1A1A` + global focus ring | white | unchanged | Ring at 2 px offset, **6.45:1 PASS** |
| Filled | `1.5px #8A8A8A` | white | unchanged | No special styling — "filled" is not a state worth signalling |
| **Error** | **`2px #B3261E`** — **6.54:1 PASS non-text** | white | `danger-700` `#8C1D18` **9.12:1 PASS AAA**, 14 px / **600**, preceded by a 16 px alert glyph | `aria-invalid="true"`, `aria-describedby` → the error node; error node is `role="alert"` |
| **Success** | `2px #1B7A4B` — **5.34:1 PASS** | white | `success-700` `#14603A` **7.60:1 PASS AAA** + check glyph | Only used where verification actually occurred (OTP accepted, city matched) — never as generic "this field is fine" noise |
| Disabled | `1.5px #D9D9D9` | `ink-100` `#F0EFEC` | `ink-500` | `cursor: not-allowed`; avoid — prefer omitting the field |
| Read-only | `1.5px #D9D9D9` | `ink-50` `#FAF9F7` | — | Used for the locked `+92` prefix |

Error state uses **three** simultaneous signals — 2 px border, glyph, weight-600 text — so it never depends on red alone.

#### Validation timing

Per research §5.3: **validate on blur, not per keystroke.** Re-validate on input only *after* a field has already errored once, so the error clears as soon as it is fixed. Never validate a field the user has not yet reached. Never block submission on a field that has not been touched — on submit, validate everything at once, move focus to the first invalid field, and scroll it to `scroll-margin-top: calc(var(--h-header) + 16px)`.

#### Field-specific specifications

| Field | Type / attributes | Notes |
|---|---|---|
| **Full name** | `type="text"` `autocomplete="name"` `autocapitalize="words"` | Single field. No first/last split. |
| **Mobile number** | `type="tel"` `inputmode="numeric"` `autocomplete="tel"` `maxlength="12"` | `+92` rendered as a **read-only 56 px prefix segment** inside the same bordered box, separated by a 1 px `ink-200` rule. Input accepts 10 digits, displays as `3XX XXX XXXX`. Placeholder `300 1234567`. Validation on blur: exactly 10 digits, first digit `3`. Error copy: *"Pakistani mobile numbers start with 3 and have 10 digits."* |
| **City** | Searchable combobox | `role="combobox"` `aria-expanded` `aria-controls` `aria-autocomplete="list"`; listbox of `role="option"`. Filters on 2+ characters. Options 48 px tall. Free text allowed with an "Other — type your city" fallback so an unlisted town cannot block the order. **Postal code is derived from this server-side and never shown** (research §5.3). |
| **Complete address** | `<textarea rows="2">` `autocomplete="street-address"` | Auto-grows to a max of 5 rows. Placeholder: `House 12, Street 4, Block B, Gulberg III`. Helper: *"Add a nearby landmark — it helps the courier find you."* Unstructured free text; **no multi-field address form**. |
| **Email** | `type="email"` `inputmode="email"` `autocomplete="email"` | Label reads **"Email (optional)"** — the word optional is in the label, not only in helper text. Helper: *"For your order receipt. We don't send marketing unless you ask."* |
| **Payment** | Radio group | See below. |
| **OTP** | 4 × `inputmode="numeric"` `autocomplete="one-time-code"` `maxlength="1"` | 56 × 64 px boxes, 12 px gap, 24 px centred text. Auto-advance on entry, backspace moves back, paste of a 4-digit string fills all four. Resend link disabled for 30 s with a live countdown in a `role="status"` region. |

#### Payment radio group (research §3.2 — COD is the market, not an option)

```
┌────────────────────────────────────────────┐
│ (●) Cash on Delivery              [ COD ]  │   ← pre-selected, listed first
│     Pay the courier when it arrives        │
├────────────────────────────────────────────┤
│ ( ) Card / Easypaisa / JazzCash             │
│     You'll be redirected to pay securely   │
└────────────────────────────────────────────┘
```

| Property | Value |
|---|---|
| Row height | 72 px, entire row is the tap target |
| Selected row | `2px solid #1A1A1A` border, `--surface-raised` background, radio dot `ink-900` |
| Unselected row | `1.5px solid #8A8A8A`, white |
| Title | 16 px / 600 `ink-900` |
| Subtitle | 14 px `--text-muted` — **6.69:1 PASS AA** |
| Semantics | `<fieldset>` + visually-hidden `<legend>Payment method</legend>`; native `input[type=radio]`, 24 px, arrow-key navigation |

**COD is `checked` in the markup, first in DOM order, and never labelled "alternative", "other" or "offline".** Do not gate it behind an order minimum.

#### Other controls

- **Checkbox** — 24 × 24 visual, 48 × 48 hit box, `1.5px #8A8A8A`, checked `bg #1A1A1A` with a white tick (**17.41:1 PASS AAA**), `--radius-xs`. Used for the checkout order bump (research §5.2 Flow B) and marketing opt-in.
- **Quantity stepper** — `[−] 2 [+]`. Buttons **48 × 48** (research §6.2 sets 44 px as the floor; we spend the extra 4 px because this is the documented classic mis-tap). Value field 48 px wide, 16 px, `tnum`, `role="spinbutton"` with `aria-valuenow`/`aria-valuemin="1"`/`aria-valuemax`. `−` at quantity 1 becomes the Remove action and is announced as such, rather than being disabled.
- **Search input** — 48 px tall, 20 px search glyph inset-inline-start 14 px, text padding-inline-start 44 px, clear (`×`) button 48 × 48 appearing once non-empty, `type="search"` `enterkeyhint="search"`.

---

### 3.3 Product card

**Where it is used:** the `/shop` grid (only at ≥8 SKUs — research §4.3 Rule 9), "You may also like" rails, search results, the Ingredient Index "products containing this" rail. **It is not used on the homepage below 4 SKUs** — that is the editorial feature block in §4.1.

#### Anatomy

```
┌──────────────────────────┐
│ ┌──────────────────────┐ │  Image plate — always #FFFFFF, both themes
│ │ [halal chip]         │ │  1:1, object-fit: contain, 8% inset padding
│ │                      │ │
│ │        image         │ │
│ │                      │ │
│ └──────────────────────┘ │
│ Nourishing Face Cream    │  18px/600, 2-line clamp, balance
│ Dry & sensitive skin     │  14px, --text-muted
│ PKR 4,850                │  16px/600, tnum, --text-primary
│ [    + Add     ]         │  44px, full width
└──────────────────────────┘
```

| Property | Value |
|---|---|
| Card background | `--surface`; **no border, no shadow at rest** |
| Image plate | `#FFFFFF` in both themes, `--radius-sm`, `aspect-ratio: 1/1`, `object-fit: contain`, 8 % inset padding. Never crop a product. Never invert in dark mode. |
| Gaps | image→name 10 px · name→descriptor 4 px · descriptor→price 6 px · price→action 12 px |
| Grid | 2-up at 375 px (card = 165.5 px wide: `(375 − 32 gutter − 12 gap) / 2`) · 2-up to 767 · 3-up 768–1023 · 4-up ≥1024 · gap 12 px mobile / 24 px desktop |
| Hover (pointer only) | image `transform: scale(1.02)` over `--motion-base`; name gains underline. No shadow change. |
| Whole-card link | The image + name are one `<a>` covering the block via a stretched `::after`; the action button sits **above** it in stacking order with its own `position: relative` |

#### Action zone — four mutually exclusive states

| Condition | Control | Behaviour |
|---|---|---|
| In stock, **no variants** | `[+ Add]` primary `sm`, full width | One-click add (research §5.2 Flow A). On success the control **morphs into a quantity stepper** so a second tap cannot blind-add. |
| In stock, **has shades** | `[Choose shade]` secondary `sm` | Opens the shade bottom sheet (§3.12). **Never silently add a default shade** — a wrong shade becomes a COD refusal at the door, the most expensive failure in the system. |
| Out of stock | `[Notify me]` secondary `sm` | Opens a one-field email capture. **Never a disabled button.** Image gets a 4 px `ink-100` band across the plate bottom reading `Out of stock` in 14 px `ink-800`. |
| Coming soon | `[Notify me]` secondary `sm` + progress chip | Chip reads e.g. `In halal certification` (research §4.3 Rule 5) |

#### Ratings

Per research §T9: **the star average does not render until there are ≥5 reviews.** Below that threshold the rating row is absent entirely — not "no reviews yet", not an empty star row. When it renders: 16 px stars in `ink-900` (filled) and `ink-300` (empty), plus the numeric average and count in 14 px `--text-muted`, e.g. `4.6 (12)`. Stars are decorative; the accessible name is `Rated 4.6 out of 5 from 12 reviews`.

#### Accessibility

- Card is an `<article>`; the product name is the `<h3>` and the link's accessible name.
- Price is announced with the currency: mark up as `PKR 4,850` in text, not `4,850` with a separate symbol.
- The `+ Add` button's accessible name is `Add Nourishing Face Cream to cart`, not `Add`.
- The halal chip is a real text element, not an image (see §3.4).
- Focus order within a card: link → action button. The stretched `::after` must not swallow the button's focus.

---

### 3.4 Category card

Only rendered once categories are revealed at ≥8 SKUs (research §4.3 Rule 6: categories with `product_count = 0` are **hidden, not greyed out**; Rule 7: a category holding exactly one product redirects to that PDP).

| Property | Value |
|---|---|
| Aspect | 3:2 mobile, 4:3 desktop |
| Image | Editorial lifestyle or grouped-product shot; **never** a stock photo of a non-South-Asian model (research §2.3) |
| Overlay | Bottom-anchored linear gradient, `rgb(15 15 15 / 0)` → `rgb(15 15 15 / 0.72)` over the lower 55 % |
| Label | 20 px / 600, `#FFFFFF`. On the 0.72 scrim over a mid-tone image, measured worst case **7.9:1 PASS AAA**. The scrim is mandatory — this is the "never place text over a photograph without a scrim" rule from research §6.3, applied to white as well as gold. |
| Count | 14 px `#F2F0EC`, e.g. `6 products` |
| Radius | `--radius-sm` |
| Grid | 2-up mobile, 3-up ≥768 |
| Hover | Image `scale(1.03)` over `--motion-slow`; scrim opacity → 0.80 |

---

### 3.5 Badges and chips

This is where the brand either differentiates or joins the noise. Research §2.3 is blunt: a generic crescent badge with no issuer name now reads as decoration, because uncertified sellers use it universally. So the system ships **no crescent, no seal, no ribbon, no starburst**. Every badge in Glow Halal carries either a *fact* or a *name*.

Four families, visually distinct on purpose.

#### A. Evidence chip — the halal certification badge

The one badge that matters. It is a **fact carrier**, so it is rectangular, bordered, and contains a number.

```
┌────────────────────────────────────┐
│ ✓  SANHA Certified  ·  HC-24019    │
└────────────────────────────────────┘
```

| Property | Value |
|---|---|
| Shape | Rectangle, `--radius-xs` (2 px). **Never a pill** — pills read as marketing, rectangles read as stamps |
| Height | 28 px (`sm`, on cards) · 36 px (`md`, on PDP and homepage trust strip) |
| Background | `--surface` white |
| Border | `1px solid #8A8A8A` (`ink-400`) — **3.45:1 PASS non-text** |
| Icon | 16 px check in `success-600` `#1B7A4B` — **5.34:1 on white PASS** |
| Issuer text | 14 px / 600 `ink-900` — **17.41:1 PASS AAA** |
| Separator | 1 px × 14 px `ink-300` rule, 8 px margin each side |
| Certificate number | `text-inci` mono, 14 px, `ink-800` — **13.58:1 PASS AAA**, `user-select: all` |
| Link | The whole chip links to `/halal-certification` |

**The issuer name and the number are mandatory.** If either is unavailable, this component does not render — it renders the in-progress variant instead (§3.6). A chip that says only "Halal Certified" is forbidden by this system; it is the exact anti-pattern research §2.3 identifies.

**In-progress variant:** same frame, icon becomes a 16 px clock in `info-600`, text reads `Certification in progress · SANHA · expected Nov 2026`, border stays `ink-400`. Background `info-50` `#EAF1F9`, text `info-600` `#1D5FA8` — **5.66:1 PASS AA**. Deliberately informational blue, **not** warning amber: an honest in-progress state is not an error and must not be styled as one (research §T1 honesty gate, §T4).

#### B. "Free from" chip — always with the INCI name

The differentiator. Competitors write "Alcohol-Free". Glow Halal writes the name that is printed on the back of the box the customer already owns.

```
┌─────────────────────────────┐
│ ✕  Alcohol Denat.           │
└─────────────────────────────┘
```

| Property | Value |
|---|---|
| Shape | Pill, `--radius-full`, height 32 px, padding-inline 12 px |
| Background | `ink-100` `#F0EFEC` |
| Text | 14 px / 600 `ink-800` `#2E2E2E` — **11.81:1 PASS AAA** |
| Glyph | 14 px `✕` in `ink-600` `#5C5C5C` — **6.69:1 PASS AA** |
| Content | **The INCI name**, in the mono face, not the marketing word. `Alcohol Denat.` not `Alcohol-Free`. `CI 75470 (Carmine)` not `No insect dyes`. |
| Behaviour | Each chip links to its row anchor on `/what-we-never-use` |
| Layout | Wrapping flex row, 8 px gap. On a PDP, show **all** exclusions — do not truncate to "+3 more". Persona A screenshots this. |

#### C. Source chip — for annotated INCI rows

Three categories only: **Plant / Mineral / Synthetic** (research §T3). There is no fourth.

| Chip | Background | Text | Ratio | Verdict |
|---|---|---|---|---|
| `Plant` | `success-50` `#EAF5EF` | `success-700` `#14603A` | **6.80:1** | PASS AA |
| `Mineral` | `info-50` `#EAF1F9` | `info-600` `#1D5FA8` | **5.66:1** | PASS AA |
| `Synthetic` | `ink-100` `#F0EFEC` | `ink-800` `#2E2E2E` | **11.81:1** | PASS AAA |
| `Animal` | white, `1px dashed #8A8A8A` | `ink-600` `#5C5C5C` | **6.69:1** | PASS AA — **see below** |

Height 24 px, `--radius-xs`, text 14 px / 600, padding-inline 8 px. The 14 px floor holds even here — there is no "it's just a chip" exemption.

> **The empty Animal slot is a designed element, not an omission.** On every INCI list and on the `/what-we-never-use` legend, render all four chips — and render `Animal` as an *empty, dashed, greyed* chip labelled `Animal — 0 ingredients`. Making the absence visible is worth more than three positive claims. It is the single cheapest way to render "the skeptic reads the negative space" (research §2) as pixels.

#### D. Verdict chip — Ingredient Index and INCI tables

Carries a halal ruling, so it is the heaviest-weight chip and **always icon + text**, never colour alone.

| Verdict | Background | Text | Icon | Ratio | Verdict |
|---|---|---|---|---|---|
| `Halal` | `success-50` `#EAF5EF` | `success-700` `#14603A` | check | **6.80:1** | PASS AA |
| `Mushbooh` | `warning-50` `#FDF3E3` | `warning-600` `#8F5A00` | half-filled circle | **5.26:1** | PASS AA |
| `Not halal` | `danger-50` `#FCEDEC` | `danger-700` `#8C1D18` | cross | **8.01:1** | PASS AAA |
| `Depends on source` | `warning-50` `#FDF3E3` | `warning-600` `#8F5A00` | branching arrow | **5.26:1** | PASS AA |

Height 28 px, `--radius-xs`, 14 px / 600, 16 px icon, 6 px gap. Each carries a `title`-free visually-hidden clarifier, e.g. `Mushbooh — doubtful; source not documented`.

#### E. Utility badges

| Badge | Spec |
|---|---|
| **Cart count** | 20 px circle, `ink-900` fill, `#FFFFFF` 14 px / 600 — **17.41:1 PASS AAA**. Inset to the cart glyph's top-inline-end. `aria-label="3 items in cart"`; the glyph itself is `aria-hidden`. Animates with `animate-badge-pop` on change. Not gold: at 2.25:1 a gold badge would be an unreadable count. |
| **Out of stock** | Full-width band across the image plate bottom, `ink-100` background, `ink-800` 14 px / 600 — **11.81:1 PASS AAA** |
| **Discount** | `danger-600` `#B3261E` fill, `#FFFFFF` 14 px / 600 — **6.54:1 PASS AA**. Use sparingly: research §3.1 flags discount-led merchandising as the incumbent's mistake and a threat to premium positioning. |
| **"Founding customer"** | Outline chip, `1px #8A8A8A` border on white, `ink-800` text — **13.58:1 PASS AAA**. Required label on early testimonials (research §T9). |
| **"Verified purchase"** | Same frame as Founding customer, with a 14 px check in `success-600` |

**Forbidden badge forms, permanently:** crescent moon glyph, padlock/SSL graphic, "100 % Natural", "Chemical Free", "17 people viewing", countdown timer, any starburst or ribbon. Research §2.3 — several of these are actively pattern-matched to scam sites in this market.

---

### 3.6 Certificate display block

Research §T1 ranks this the single highest-value element on the site: *everyone shows a badge, almost nobody shows the certificate.* The design job is to make it read as a **record**, not a graphic.

#### Anatomy — mobile (375 px), stacked

```
┌───────────────────────────────────────────┐
│ ┃ [ Certified ]              status chip  │  ← 3px gold rule, inline-start
│ ┃                                         │
│ ┃  ┌─────────────────┐                    │
│ ┃  │                 │  ← certificate scan │
│ ┃  │   photograph    │     3:4, white plate│
│ ┃  │   of the        │     tap to enlarge  │
│ ┃  │   document      │                     │
│ ┃  └─────────────────┘                    │
│ ┃  [ View full size ] [ Download PDF 1.2MB ]│
│ ┃                                         │
│ ┃  Issuing body      SANHA Pakistan  ↗    │
│ ┃  ─────────────────────────────────────  │
│ ┃  Certificate no.   HC-24019      [copy] │
│ ┃  ─────────────────────────────────────  │
│ ┃  Standard          PS 5319-2014         │
│ ┃                    PS 4992 / OIC-SMIIC 2│
│ ┃  ─────────────────────────────────────  │
│ ┃  Scope             Cosmetics & personal │
│ ┃                    care — 3 products    │
│ ┃  ─────────────────────────────────────  │
│ ┃  Issued            12 March 2026        │
│ ┃  ─────────────────────────────────────  │
│ ┃  Expires           11 March 2029        │
│ ┃                    valid for 31 months  │
│ ┃                                         │
│ ┃  [ Verify on pnac.gov.pk  ↗ ]           │
└───────────────────────────────────────────┘
```

Desktop ≥768: two columns — scan 320 px fixed on the inline-start, definition list filling the rest. Same content, same order.

#### Specification

| Element | Spec |
|---|---|
| Container | `evidence-plate` utility: `--surface-evidence` `#FBF6EA`, `1px solid #D9D9D9`, **`border-inline-start: 3px solid #C9A961`**, `--radius-lg`, padding 20 px mobile / 32 px desktop |
| Gold rule | The 3 px inline-start rule is the **only** gold in the block, and it is purely decorative (2.08:1 on the tint — decoration is exempt). It must never be the sole indicator of anything. |
| Status chip | Top-inline-end. `Certified` = `success-50`/`success-700` **6.80:1 PASS**. `Certification in progress` = `info-50`/`info-600` **5.66:1 PASS**. `Renewal due` (<90 days to expiry) = `warning-50`/`warning-600` **5.26:1 PASS**. |
| Scan thumbnail | 3:4, `#FFFFFF` plate with `1px solid #D9D9D9`, `--radius-sm`, 2 px inner white padding so it reads as paper. `loading="lazy"`, ≤40 KB. `alt` describes the document, not the design: `Halal certificate HC-24019 issued to Glow Halal by SANHA Pakistan, valid to 11 March 2029`. |
| Enlarge | Tap opens the certificate lightbox (§3.11) — pinch-zoomable, 1600 px wide source (~300 KB) fetched **on demand only**. |
| PDF link | Secondary `sm` button. **State the file size in the label** — Persona B pays for data. `Download PDF (1.2 MB)`. `download` attribute set. |
| Definition list | Real `<dl>`. Term: 14 px / 600 `ink-800` **13.58:1 PASS AAA**, fixed 128 px column on desktop, stacked on mobile. Value: 16 px `ink-900` **17.41:1 PASS AAA**. Row separator `1px #D9D9D9`, 12 px vertical padding. |
| Certificate number | `text-inci` mono, `letter-spacing: 0.02em`, `user-select: all`, with a 48 × 48 copy button. Copy fires a toast: `Certificate number copied`. |
| Dates | Written out in full — `12 March 2026`, never `12/03/26` (ambiguous across Pakistani and US conventions). |
| **Expiry** | Always shown, with a computed relative line beneath it. Research §T1: *"Nobody does this, and it is a spectacular credibility signal."* |
| Verify link | Secondary `sm` button whose label **names the destination domain**: `Verify on pnac.gov.pk ↗`. `target="_blank" rel="noopener"`. Showing where the link goes is the trust move; a bare "Verify" is not. |
| Text rendering | **Every field is real text.** Nothing here is baked into an image — it must be crawlable, selectable, screenshot-able and translatable. |

#### The three states this component must render

**1. Certified** — as drawn above.

**2. Certification in progress** — the honesty gate (research §T1, non-negotiable). Same plate, same border, same typography. Changes:
- Status chip → `Certification in progress` in info blue.
- The scan slot is **replaced, not emptied**: it shows the submitted application reference or the certifier's acknowledgement, or — if neither exists — a 3:4 `--surface-raised` panel with a centred 32 px document glyph in `ink-400` and the caption `Certificate will be published here on issue`.
- Definition list becomes: Certifying body · Application submitted (date) · Standard applied · Expected decision (month) · What we publish meanwhile.
- The final row is a link: `Read the full ingredient dossier →` pointing at `/what-we-never-use` and the PDP INCI lists.
- **Design intent, stated for the developer so it is not "fixed" later: this state must look as deliberate and well-made as the certified state.** It carries no warning colour, no reduced opacity, no dashed borders. A brand that volunteers an inconvenient truth earns credibility; styling that truth as a defect throws the credibility away.

**3. Expired or lapsed** — the certified variant **must not render**. The component falls back to state 2 with the chip reading `Renewal in progress` and the previous certificate still linked, labelled `Previous certificate (expired 11 March 2029)`. Never silently show a lapsed certificate as current.

#### Placement (research §T1)

| Location | Variant |
|---|---|
| `/halal-certification` | Full block, state 1/2/3 |
| Homepage, section 4 | Full block, but with the definition list trimmed to issuer / number / standard / expiry, plus `See the full certificate →` |
| Every PDP, section 5 of 8 | Full block |
| Every product card | Evidence chip only (§3.5 A) |
| Footer, every page | Certificate number as text, `SANHA Certified · HC-24019 · expires 11 Mar 2029` |
| Checkout header | Certificate number as text alongside `Secure order — Cash on Delivery available` |

#### Accessibility

- `<section aria-labelledby>` pointing at a heading, e.g. `Halal certification`.
- The scan is a `<figure>` with a `<figcaption>`; the caption duplicates the key facts as text so the information does not live only in the image.
- Copy button: `aria-label="Copy certificate number HC-24019"`; success announced through the toast region.
- The relative expiry line (`valid for 31 months`) is computed server-side in Blade, not client-side — it must be present in the initial HTML for crawlers and for a screenshot taken before JS runs.

---

### 3.7 The "What We Never Use" INCI table

Research §T2 — the signature content asset, and the reason `/what-we-never-use` sits in the **primary navigation** rather than the footer.

#### Desktop (≥768 px) — a real table

Four columns, `<table>` with `<caption>` and `<th scope="col">`.

| Column | Width | Type | Colour |
|---|---|---|---|
| Ingredient | 18 % | 16 px / 600 `ink-900` | **17.41:1 PASS AAA** |
| INCI / label name | 26 % | `text-inci` mono 15 px `ink-900`, multiple names on separate lines | **17.41:1 PASS AAA** |
| Why it's a problem | 34 % | 16 px `ink-700` | **8.86:1 PASS AAA** |
| What we use instead | 22 % | 16 px `ink-900`, INCI substitutes in mono | **17.41:1 PASS AAA** |

Header row: `ink-100` `#F0EFEC` background, 14 px / 600 uppercase `ink-800` with `--tracking-caps` — **11.81:1 PASS AAA**. Row separator `1px #D9D9D9`. Row padding 16 px. **No zebra striping** — the mono column already gives the eye its anchor, and stripes add visual noise to something that should read as a reference document.

#### Mobile (<768 px) — stacked cards, never a scrolling table

A horizontally scrolling data table is unusable at 375 px and, worse, un-screenshot-able. Each row becomes a card:

```
┌──────────────────────────────────────────┐
│ Denatured alcohol                        │  18px/600 ink-900
│                                          │
│ ON THE LABEL                             │  14px/600 caps, ink-600
│ Alcohol Denat.                           │  15px mono ink-900
│ SD Alcohol 40                            │
│                                          │
│ WHY IT'S A PROBLEM                       │
│ Intoxicant-derived; the most common      │  16px ink-700
│ halal disqualifier in cosmetics.         │
│                                          │
│ WHAT WE USE INSTEAD                      │
│ Glycerin-based, alcohol-free solvent     │  16px ink-900
│ system                                   │
└──────────────────────────────────────────┘
```

Card: `--surface` white, `1px solid #D9D9D9`, `--radius-sm`, padding 16 px, 12 px gap between cards. Field labels 14 px / 600 uppercase `ink-600` — **6.69:1 PASS AA**, `--tracking-caps`, 12 px above / 4 px below.

**Implementation note:** author it once as a semantic `<table>` and restyle to cards with `display: block` on `tr`/`td` plus `::before { content: attr(data-label) }` for the field labels. One markup, two layouts, and the screen-reader experience stays tabular.

#### Hard constraints (research §T2, §T3)

1. **No accordion. No "read more". No lazy loading. No virtualisation.** The full table renders in the initial HTML.
2. **Screenshot-able in one or two vertical scrolls.** At 375 px, eight stacked cards is roughly 2,400 px — acceptable. If the list grows past ~12 entries, add an anchored index at the top; do not collapse rows.
3. **INCI names are selectable mono text**, never inside an image (research §2.3: an ingredient list as a flat image defeats the entire purpose).
4. Each row has a stable `id` so a `Free from` chip elsewhere on the site can deep-link to it. Anchored rows get a 2 s `--surface-evidence` background highlight on arrival, then fade over `--motion-slow`.
5. A `Share this list` action sits at the top of the page: copies a clean plain-text version to the clipboard and offers the native share sheet where available.

#### Homepage condensed variant

Six rows (research §2.2 item 6), two columns only — **Ingredient** and **INCI / label name** — followed by a `See all 8 and why →` tertiary link to the full page. Same card treatment on mobile, at 12 px padding.

---

### 3.8 Annotated INCI list (PDP)

Research §T3. This is what Persona A came for; it is expanded by default and is never behind a disclosure.

#### Layout

```
INGREDIENTS                    23 total · 0 animal-derived
────────────────────────────────────────────────────────
Aqua                    [Mineral]     ✓ Halal   (i)
Glycerin                [Plant — palm]✓ Halal   (i)
Butyrospermum Parkii    [Plant — shea]✓ Halal   (i)
Tocopherol              [Plant — vit E] ✓ Halal (i)
Phenoxyethanol          [Synthetic]   ✓ Halal   (i)
...
────────────────────────────────────────────────────────
Legend:  [Plant] [Mineral] [Synthetic] [Animal — 0 ingredients]
[ Copy ingredient list ]  [ Share as image ]
```

| Element | Spec |
|---|---|
| Row | 48 px min height, `1px #D9D9D9` bottom rule, 12 px vertical padding. Flex: INCI name (grows) · source chip · verdict mark · info button |
| INCI name | `text-inci` mono 15 px `ink-900` — **17.41:1 PASS AAA**, `user-select: text` |
| Source chip | §3.5 C, with the specific origin appended: `Plant — shea`, `Plant — palm`, `Mineral`, `Synthetic` |
| Verdict mark | 16 px check in `success-600` + the word `Halal` at 14 px `success-700` — **7.60:1 PASS AAA**. Icon and word together; never the icon alone |
| Info button | 44 × 44 `(i)` icon button opening a plain-language "what it does" popover (`role="dialog"`, dismiss on Esc / outside tap / scroll). Popover body 16 px `ink-900`, max-width 280 px, `--shadow-md` |
| Count line | 14 px `--text-muted`: `23 total · 0 animal-derived · 0 alcohol`. Server-computed. **The zeroes are the message.** |
| Legend | The four source chips including the empty dashed `Animal — 0 ingredients` (§3.5 C) |
| Order | Exactly as printed on the pack (descending concentration). Do not re-sort alphabetically — Persona A is cross-checking against the physical box. |

#### The two share actions

- **`Copy ingredient list`** — copies a clean plain-text block: product name, full INCI list with annotations, certificate number, and the site URL. Toast confirms.
- **`Share as image`** — server-rendered PNG (1080 × variable, Instagram/WhatsApp-friendly), containing the same content plus the wordmark and certificate number. This is the "small feature with disproportionate word-of-mouth value" from research §T3: Persona A's default behaviour is to screenshot this and send it to a more religiously-knowledgeable friend. Give her a clean asset instead of a cropped screenshot.

Both actions sit **above** the list as well as below it, so a user who has just finished reading does not have to scroll back.

#### Accessibility

- Semantic `<table>` with `<caption class="sr-only">Full ingredient list with source and halal status</caption>`; columns are Ingredient, Source, Halal status.
- Verdict is conveyed by text, not by the icon or colour.
- The info popovers are keyboard reachable in DOM order and return focus to their trigger on close.
- The list must be complete in the server-rendered HTML — no client-side fetch. A screenshot taken before JS executes must show everything.

---

### 3.9 Breadcrumbs

| Property | Value |
|---|---|
| Markup | `<nav aria-label="Breadcrumb"><ol>` with `<li>` items |
| Type | 14 px `--text-muted` `#5C5C5C` — **6.69:1 PASS AA** |
| Current page | 14 px / 600 `ink-900` — **17.41:1 PASS AAA**, **not a link**, `aria-current="page"` |
| Separator | 14 px `›` in `ink-300`, `aria-hidden`, 8 px margin each side |
| Height | 32 px band, 44 px tap targets on the links via vertical padding |
| Mobile overflow | Single line, `overflow-x: auto`, `scrollbar-width: none`, scrolled to the **end** on load so the current page is visible; 24 px fade mask on the inline-start edge. **Never truncate with an ellipsis** — a shortened trail is worse than a scrollable one |
| Structured data | `BreadcrumbList` JSON-LD emitted alongside |
| Placement | Directly under the header on PDP, category, Journal post, and Ingredient Index detail. **Not** on the homepage, cart, or checkout |

---

### 3.10 Pagination

Used on `/journal`, `/ingredient-index`, and `/shop` once it is a grid.

| Property | Value |
|---|---|
| Control size | 48 × 48 px, 8 px gap |
| Default | `--surface`, `1px solid #D9D9D9`, 16 px `ink-900` — **17.41:1 PASS AAA** |
| Hover | `ink-100` background |
| Current | `ink-900` fill, `#FFFFFF` 16 px / 600 — **17.41:1 PASS AAA**, `aria-current="page"` |
| Disabled prev/next | `ink-300` glyph, `1px #F0EFEC` border, `aria-disabled="true"` (still focusable, so the sequence is not silently broken) |
| Mobile shape | `[‹] 1 … 4 [5] 6 … 12 [›]` — first, last, current ±1, ellipses as inert `<span>` |
| Desktop | Same, plus a `Page 5 of 12` label at 14 px `--text-muted` |
| Markup | `<nav aria-label="Pagination">` + `<ul>`; `rel="prev"`/`rel="next"` on the adjacent links |
| Page size | Journal 6 mobile / 9 desktop · Ingredient Index 25 · Shop 12 mobile / 24 desktop |

**Numbered pagination, not infinite scroll.** The Ingredient Index and Journal are the SEO moat (research §4.3 Rule 4) — every entry needs a crawlable URL. Where a "Load more" affordance is wanted for feel, it may be added *in addition*, but the numbered links must remain in the HTML.

---

### 3.11 Modals, dialogs and the certificate lightbox

Modals are used sparingly. There is **no confirmation dialog anywhere in this system** — destructive actions use undo instead (§3.13).

Approved modal uses, and only these: the certificate lightbox, the product image lightbox, the ingredient "what it does" popover (non-modal), the "Notify me" capture, and the shade sheet (which is a bottom sheet, §3.12).

| Property | Value |
|---|---|
| Backdrop | `rgb(15 15 15 / 0.60)`, `z-[var(--z-overlay)]`, fades over `--motion-base` |
| Panel | `--surface`, `--radius-lg`, `--shadow-lg`, `z-[var(--z-modal)]`, max-width 560 px, max-height `calc(100dvh - 32px)`, internal scroll |
| Mobile | Full-width minus 16 px inset; a modal taller than 70 dvh becomes a bottom sheet instead |
| Enter | opacity 0→1 + `translate3d(0, 8px, 0)` → 0 over `--motion-base` `ease-enter` |
| Exit | opacity only, `--motion-exit` |
| Close | 48 × 48 icon button, top-inline-end, 8 px inset |
| Semantics | `role="dialog"` `aria-modal="true"` `aria-labelledby` (the heading). Focus moves to the panel on open, is trapped inside, and returns to the trigger on close. Esc closes. Backdrop click closes. `overflow: hidden` on `<body>` with scroll-position restore |

#### Certificate lightbox — a specific case

- Full-viewport `#0F0F0F` background at 0.94 opacity.
- Image is the 1600 px scan, **fetched only when the lightbox opens**. Show a skeleton in its place while loading.
- Pinch-zoom and double-tap-to-zoom enabled (`touch-action: pinch-zoom`); the page's zoom lock does not apply here.
- Below the image: the certificate number and issuer as selectable text in `#F2F0EC` on `#0F0F0F` — **16.84:1 PASS AAA** — plus the `Download PDF` button.
- Close button 48 × 48 in the top-inline-end, `#F2F0EC` glyph, with a `rgb(15 15 15 / 0.6)` circular backing so it stays visible over pale paper.

---

### 3.12 Shade-selection bottom sheet

Research §5.2 Flow A flags this as a friction point to solve *before* the first shaded SKU ships: a one-click add that silently picks a default shade produces a refusal at the door, which is the most expensive failure the business has. It is also the component that answers Persona A's anxiety #4 and Persona B's anxiety #3 — both are buying on phone screens with inaccurate colour profiles.

A **bottom sheet, not a modal** — research §6.2: top-anchored UI is the hardest region to reach one-handed.

#### Anatomy

```
        ▁▁▁▁▁▁▁▁                    ← 36×4 grab handle, ink-300
┌──────────────────────────────────┐
│ Choose your shade            [✕] │  20px/600
│ Rose Tint Lip Balm               │  14px --text-muted
├──────────────────────────────────┤
│  ●        ●        ●        ●    │  56px swatches, 4-up
│ Dusk    Rosewood  Clay    Henna  │  14px/600 ink-900
│ cool    neutral   warm    warm   │  14px --text-muted
│                          ⓘ out   │
│                                  │
│ ─────────────────────────────    │
│ Not sure? See all four on medium │  tertiary link
│ and deep South Asian skin →      │
│ 💬 Ask us on WhatsApp            │  whatsapp text button
├──────────────────────────────────┤
│ [        Add to cart         ]   │  56px lg primary, sticky
│  PKR 1,950 · Cash on Delivery    │  14px --text-muted
└──────────────────────────────────┘
```

#### Specification

| Element | Spec |
|---|---|
| Sheet | `--surface`, `border-radius: var(--radius-xl) var(--radius-xl) 0 0`, `--shadow-sheet`, `z-[var(--z-sheet)]`, max-height 85 dvh, internal scroll, `padding-bottom: calc(16px + var(--safe-bottom))` |
| Backdrop | `rgb(15 15 15 / 0.60)`, `z-[var(--z-overlay)]` |
| Grab handle | 36 × 4 px, `ink-300`, `--radius-full`, 12 px above the title. Decorative (`aria-hidden`) — dismissal must also be possible via the close button and Esc |
| Enter | `animate-sheet-in` — `translate3d(0,100%,0)` → 0 over `--motion-sheet` `ease-enter`. Backdrop fades in parallel over `--motion-base` |
| Exit | `translate3d(0,100%,0)` over `--motion-exit` |
| Drag dismiss | Follows the finger; dismisses past 96 px travel **or** velocity > 0.5 px/ms; otherwise springs back over `--motion-base` |
| **Swatch** | **56 × 56 px circle**, `1px solid rgb(15 15 15 / 0.12)` inner hairline so pale shades stay visible on white. Selected: `3px solid #1A1A1A` ring at 3 px offset (**17.41:1 PASS** as a non-text indicator) **plus** a 20 px white check glyph centred on the swatch with a 1 px ink outline for legibility on light shades |
| Swatch grid | 4-up at 375 px (56 px swatch + 24 px gutter), 5-up ≥480, wrapping. Column width 76 px, row gap 20 px |
| **Shade name** | 14 px / 600 `ink-900` — **17.41:1 PASS AAA**. **Always visible, never a tooltip.** Colour alone can never identify a shade |
| Undertone | 14 px `--text-muted` — **6.69:1 PASS AA**: `cool` / `neutral` / `warm` / `olive` |
| Out of stock shade | Swatch at 40 % opacity with a 2 px `ink-600` diagonal strike; label gains `Out of stock` in 14 px `ink-600`; tapping it swaps the sheet CTA to `Notify me` for that shade. **Never removed from the grid** — an absent shade reads as a smaller range |
| Help row | Tertiary link to the shade guide + a WhatsApp text button pre-filled with `Hi, which shade of Rose Tint Lip Balm would suit me?` |
| Sticky footer | `--surface` with `--shadow-bar`, `lg` primary button full width, disabled-in-appearance-only until a shade is chosen: it stays enabled and, if pressed with no selection, moves focus to the first swatch and announces `Choose a shade first` |
| Price line | Beneath the CTA, 14 px `--text-muted`, updates if the shade changes price |

#### Behaviour

- Opening from a **product card** (`Choose shade`): on confirm, adds to cart, closes, fires the standard add-to-cart toast, and the card's control becomes a stepper.
- Opening from the **PDP** (tapping the shade row): on confirm, closes and updates the PDP shade row, gallery image, and price — it does **not** add to cart. The PDP's own Add to Cart remains the commit action.
- The chosen shade is carried into the cart line, the checkout summary, the order confirmation, and the WhatsApp confirmation message. A shade that appears in the cart but not in the confirmation message is a refusal waiting to happen.

#### Accessibility

- `role="dialog" aria-modal="true" aria-labelledby="shade-sheet-title"`.
- Swatch group is a `radiogroup`; each swatch is a `radio` with an accessible name of `Rosewood, neutral undertone` (and `, out of stock` when applicable). Arrow keys move within the group, roving `tabindex`.
- Focus moves to the currently-selected swatch on open, or the first swatch if none; returns to the trigger on close.
- The visual check mark is backed by `aria-checked`, not by colour or ring alone.
- `prefers-reduced-motion`: the sheet appears without translation; the backdrop still fades.

---

### 3.13 Toasts

| Property | Value |
|---|---|
| Position | Bottom-centred, `inset-inline: 16px`, bottom offset = `calc(var(--h-bottom-nav) + var(--safe-bottom) + 12px)`, or `calc(var(--h-buy-bar) + var(--safe-bottom) + 12px)` on the PDP. **A toast never covers the primary CTA.** |
| Width | `min(100% − 32px, 420px)` |
| Height | 56 px single-line, auto for two |
| Background | `ink-900` `#1A1A1A` |
| Text | 16 px `#F2F0EC` — **14.98:1 PASS AAA** |
| Action | Tertiary, 16 px / 600, `gold-on-dark` `#C9A961` — **7.73:1 PASS AAA**. This is a legal gold text use because the surface is ink |
| Radius / shadow | `--radius-md`, `--shadow-lg` |
| Enter / exit | `animate-toast-in` (`--motion-toast`, `ease-enter`) / opacity + 8 px drop over `--motion-exit` |
| Duration | 3 s default · **5 s when the toast carries an Undo** · persists while hovered or focused |
| Stacking | **Maximum one toast.** A new toast replaces the current one; it does not queue or stack. On a 375 px screen a stack is an obstruction |
| Dismiss | Swipe in any direction, or tap the action. No close `×` — it competes with the action for a small target |
| Semantics | A single persistent `<div role="status" aria-live="polite" aria-atomic="true">` region in the layout; content is swapped into it. **Never** `role="alert"` for routine confirmations |
| `z-index` | `z-[var(--z-toast)]` — above every sheet |

**Copy patterns:**

| Trigger | Message | Action |
|---|---|---|
| Add to cart | `Added — Nourishing Face Cream` | `View cart` |
| Remove from cart | `Removed — Nourishing Face Cream` | `Undo` (5 s) |
| Quantity reconciliation mismatch | `Price updated — please review` | `Review` |
| Copy certificate number | `Certificate number copied` | — |
| Copy ingredient list | `Ingredient list copied` | — |
| Notify-me signup | `We'll message you when it's back` | — |
| Network retry queued | `Saved — we'll sync when you're back online` | — |

---

### 3.14 Loading skeletons

Research §6.1 is explicit: **skeleton screens, not spinners**, for content; spinners only for a scoped in-place action (a cart line, a button).

| Property | Value |
|---|---|
| Fill | `ink-200` `#D9D9D9` |
| Radius | Matches the element it stands in for — text lines `--radius-xs`, images `--radius-sm` |
| Animation | `animate-skeleton`: opacity 1 → 0.55 → 1 over 1.4 s. **Opacity only.** No gradient sweep, no `background-position` animation — those force a full repaint of the skeleton area every frame, which visibly stutters on the Infinix/Tecno class of device Persona B uses. Opacity is composited on the GPU |
| Text lines | Height = the line-height of the text they replace (e.g. 26 px for `text-body`), 8 px gap, final line at 60 % width |
| Reduced motion | Static `ink-200`, no pulse |
| Semantics | Container carries `aria-busy="true"`; the skeleton itself is `aria-hidden="true"`. On resolve, `aria-busy` flips and focus is untouched |

**Shape-matched skeletons required for:** the product grid card, the PDP gallery + buy block, the cart line item, the Ingredient Index result row, the Journal card, and the certificate lightbox image. A skeleton whose geometry does not match the real content causes a layout shift on resolve, which is worse than no skeleton — the whole point is to hold the space.

**Never render a full-page loading overlay.** Research §5.2 Flow C names this explicitly for the cart; the rule is generalised here to the whole site.

---

### 3.15 Empty states

Every empty state has four parts and they are always in this order: **illustration or icon → what happened → what to do → the action.** No dead ends.

| State | Heading | Body | Primary action | Secondary |
|---|---|---|---|---|
| **Empty cart** | `Your cart is empty` | `Three formulas, each one certified before it shipped.` | `See the collection` | `Our halal certification` |
| **Search: no results** | `No matches for "retinol"` | `We may not stock it, or it may be spelled differently on the pack.` | `Search the Ingredient Index` | `Ask on WhatsApp` (pre-filled with the query) |
| **Ingredient Index: no match** | `We haven't reviewed "<term>" yet` | `Tell us and we'll research it — we publish every verdict, including the ones that are inconvenient for us.` | `Request this ingredient` (one-field email capture) | `Ask on WhatsApp` |
| **Order tracking: not found** | `We can't find that order number` | `Check the number in your confirmation message, or send it to us and we'll look it up.` | `Try again` | `WhatsApp us` |
| **Reviews: fewer than 5** | *No empty state* | — | — | Research §T9: render the "be one of our first 50 reviewers" offer instead. **Never** an empty star row, never "0 reviews", never a fabricated review |
| **Category with 0 products** | *Does not exist* | — | — | Research §4.3 Rule 6: the route 404s or redirects. **Never** a greyed-out "coming soon" category |
| **Offline / request failed** | `You're offline` | `Your cart is saved. We'll sync it when you're back.` | `Retry` | — |

| Property | Value |
|---|---|
| Container | Centred, max-width 400 px, padding-block 48 px |
| Icon | 48 px line icon in `ink-400` — decorative, `aria-hidden`. **No cute illustrations** — they undercut a brand whose entire position is seriousness about evidence |
| Heading | `text-title` (20 px / 600) `ink-900` — **17.41:1 PASS AAA** |
| Body | `text-body` (16 px) `--text-secondary` `#4A4A4A` — **8.86:1 PASS AAA** |
| Actions | 16 px gap above; primary `md`, secondary tertiary-style beneath |
| Semantics | Heading is a real `<h2>`; the region carries `role="status"` when it appears in response to a user action (search, filter) so the result is announced |

---

## 4. Page-by-page layout specifications

### 4.0 How the above-the-fold budgets were calculated

Every page below carries a measured fold budget. The reference is deliberately harsh:

| | |
|---|---|
| Viewport width | **375 px** (iPhone SE / iPhone 13 mini class) |
| Usable height at first paint | **600 px** — a 667 px device minus Safari's ~67 px of chrome |
| Fixed chrome consumed | Announcement bar 36 px + header 56 px = **92 px** |
| Content budget above the fold | **508 px** |

The research reference device is 390 × 844, and 360 × 800 is the common Android class (§6.2). Both are *taller* than this budget, so a layout that fits 375 × 600 fits those comfortably. Where a stack lands within 20 px of the 600 px line, that is called out.

Desktop specs assume a 1280 px viewport with `--container-page` (1200 px) and `--gutter-desktop` (32 px), giving a 1136 px content column.

---

### 4.1 Homepage — `/`

The homepage's job is **not** to sell. Research §5.1: the first visit is a trust audit, days before the purchase. Every decision below serves the question *"Says who?"*.

#### Above the fold at 375 × 600

| y | Element | Height |
|---|---|---|
| 0 | Announcement bar — `Free delivery over PKR 3,000 · Cash on Delivery` | 36 |
| 36 | Header — `[☰] GLOW HALAL [🔍] [🛒]` | 56 |
| 92 | Hero image — product on white, `clamp(200px, 53vw, 300px)` → **200 px** at 375 | 200 |
| 292 | *gap* | 20 |
| 312 | **H1** `text-display` 28 px/1.12 — `Certified halal cosmetics. Every ingredient named.` (2 lines) | 63 |
| 375 | *gap* | 8 |
| 383 | Subline `text-body` 16 px/1.6 (2 lines) | 51 |
| 434 | *gap* | 16 |
| 450 | **Trust strip** — 3 stacked rows × 22 px + 2 × 8 px gaps | 82 |
| 532 | *gap* | 16 |
| 548 | Primary CTA `lg` — `See the collection` | 52 |
| **600** | **fold** | |

The CTA's baseline lands exactly on the fold line at the worst-case viewport and is fully visible on 390 × 844 and 360 × 800. This is intentional: the trust strip outranks the CTA, because a user who is not convinced will not press the button anyway.

**Trust strip content — the exact three items** (research §2.2 is prescriptive here):

```
✓ SANHA Certified · HC-24019        ← evidence chip, links to /halal-certification
✓ Full INCI list on every product   ← links to /what-we-never-use
✓ Cash on Delivery                  ← links to /shipping-returns
```

Not `100% Halal | Cruelty-Free | Premium Quality`. Research: *"that is what every competitor says and it is noise."* Each row: 16 px check in `success-600` (**5.34:1 PASS**) + 14 px / 600 `ink-900` (**17.41:1 PASS AAA**), 44 px tap height.

Hero image: `<picture>` with a 3:2 mobile crop and a 16:7 desktop crop, AVIF → WebP → JPEG, `fetchpriority="high"`, `loading="eager"`, `decoding="async"`, ≤120 KB. It is the LCP element — nothing may be lazily inserted above it.

#### Full mobile section order

| # | Section | Notes |
|---|---|---|
| 1 | Announcement bar | Persistent, not dismissible. First of three free-shipping statements (research §1) |
| 2 | Header | §5.1 |
| 3 | Hero + trust strip + CTA | Above |
| 4 | **Certificate evidence block** | §3.6, trimmed variant. `section-y`. **This is the second thing on the page for a reason** |
| 5 | **What We Never Use — 6 rows** | §3.7 condensed. `--surface-sunken` band. `See all 8 and why →` |
| 6 | Scarcity-as-rigor statement | Pull quote, `text-display-sm` serif, `--surface` : *"We launch a product only after it passes halal certification and third-party testing. That takes months. It's why we have three products instead of thirty."* (research §4.3 Rule 2, near-verbatim) |
| 7 | **The Founding Collection** — 3 full-bleed editorial feature blocks | One product per block. **Never a 3-tile grid** (Rule 1). Spec below |
| 8 | **In development** | 3 cards: name, one-line description, target month, progress chip (`Formulation complete` / `In halal certification`), `Notify me` capture (Rule 5) |
| 9 | **Founder block** | Photo, name, city, one-paragraph origin moment, signature image, `Read the full story →`. `--surface-inverse` band — and note that on ink, gold becomes a legal text colour for the pull quote (**7.73:1 PASS AAA**) |
| 10 | How delivery works | 3 steps + courier logos (TCS / Leopards / M&P) + `Have the exact cash ready — we tell you the amount` + free-shipping threshold (second statement) |
| 11 | Founding-customer testimonials | Max 3, each with `Founding customer` badge, first name, city, photo where real. If fewer than 3 exist, render the "first 50 reviewers" offer instead (§T9) |
| 12 | Journal teasers | 2 most recent — a liveness signal independent of catalogue size (Rule 8) |
| 13 | Instagram embed | **Lazy-loaded, below the fold, never blocking LCP.** Deferred to P2 |
| 14 | Footer | §5.4, with the full legitimacy block |

#### Editorial feature block (section 7) — the anti-empty-store component

This replaces the product grid below 4 SKUs (research §4.3 Rules 1 and 9).

```
MOBILE (375)                        DESKTOP (≥768, alternating sides)
┌──────────────────────────┐        ┌─────────────┬─────────────┐
│  full-bleed image        │        │             │ VOLUME ONE  │
│  375 × 420 (4:5)         │        │    image    │ Nourishing  │
│                          │        │   3:4 crop  │ Face Cream  │
├──────────────────────────┤        │             │ 23 ingred.  │
│ VOLUME ONE · 01          │        │             │ 0 animal    │
│ Nourishing Face Cream    │        │             │ PKR 4,850   │
│ 23 ingredients. 0 animal-│        │             │ [Add] [→]   │
│ derived. 0 alcohol.      │        └─────────────┴─────────────┘
│ PKR 4,850                │
│ [ Add to cart ] [ View → ]│
└──────────────────────────┘
```

| Element | Spec |
|---|---|
| Image | 4:5 mobile (375 × 420), 3:4 desktop, full-bleed to the viewport edge on mobile — it must break the container to read as editorial rather than as a card |
| Eyebrow | `text-overline` 14 px caps, `--text-gold` `#6A5320` — **7.31:1 PASS AAA** |
| Name | `text-display-sm` 24 px serif `ink-900` |
| **Ingredient highlight** | 16 px `ink-900`: `23 ingredients. 0 animal-derived. 0 alcohol.` — the counts are the differentiator and belong above the price |
| Price | `text-price` 22 px, `tnum` |
| Actions | Primary `md` `Add to cart` + secondary `md` `View details` |
| Spacing | 48 px between blocks mobile, 80 px desktop; alternating image side on desktop |

#### Desktop homepage (≥1024)

- Hero becomes 2-column inside `--container-page`: copy 5/12 (with H1 at `text-display-lg` 56 px), image 7/12 at 16:9, min-height 520 px.
- Trust strip becomes a **horizontal 3-item row** with 1 px `ink-200` dividers, 16 px text, centred beneath the hero and spanning the full container.
- Certificate block goes 2-column (scan 320 px + definition list).
- "What We Never Use" renders as the real 4-column table.
- Feature blocks alternate image side.
- "In development" becomes a 3-up grid.
- Founder block: photo 5/12, text 7/12.

---

### 4.2 Shop / catalogue — `/shop`

**This page has two entirely different layouts.** Which one renders is decided by `Product::published()->count()`, not by a config flag. Build State A first; State B is dormant until the catalogue justifies it.

#### State A — under 8 SKUs: "The Founding Collection" (research §4.3 Rule 9)

No grid. No filters. No sort. No pagination. The same editorial feature-block format as the homepage, with a curation statement at the top.

**Above the fold at 375 × 600:**

| y | Element | Height |
|---|---|---|
| 0 | Announcement + header | 92 |
| 92 | **H1** `text-display-sm` 24 px — `The Founding Collection` | 30 |
| 122 | *gap* | 8 |
| 130 | Curation statement `text-body` 16 px/1.6 (3 lines) — *"Three formulas. Each one certified before it shipped, with every ingredient published."* | 77 |
| 207 | *gap* | 24 |
| 231 | Feature block 1 — full-bleed image 375 × 280 | 280 |
| 511 | *gap* | 12 |
| 523 | Product name `text-display-sm` 24 px | 30 |
| 553 | *gap* | 4 |
| 557 | Price `text-price` 22 px | 26 |
| **583** | fold at 600 — the first product's price is visible | |

Then: feature blocks 2 and 3, the "In development" section, and a closing trust band (`Every product here has a published certificate and a full INCI list`) linking to `/halal-certification` and `/what-we-never-use`.

Category routes (`/shop/skincare`, `/shop/makeup/lips`, …) exist in routing and the database from day one but are **not linked anywhere** while their `product_count` is 0 (Rule 6 — hide, do not grey out), and a category holding exactly one product **301-redirects to that PDP** (Rule 7).

#### State B — 8+ SKUs: the real grid

**Above the fold at 375 × 600:**

| y | Element | Height |
|---|---|---|
| 0 | Announcement + header | 92 |
| 92 | Breadcrumb `Home › Shop` | 32 |
| 124 | **H1** `text-title-lg` 24 px — `Shop` (or the category name) | 30 |
| 154 | Result count `text-meta` — `12 products` | 22 |
| 176 | *gap* | 12 |
| 188 | Filter + sort bar — `[⚙ Filter] [Sort: Newest ▾]`, 48 px, sticky below the header on scroll | 48 |
| 236 | *gap* | 16 |
| 252 | Card row 1 — image plate 165.5 × 165.5 | 165 |
| 417 | Card name (2 lines) | 40 |
| 457 | Card price | 24 |
| 481 | `[+ Add]` 44 px | 44 |
| 525 | card padding | 12 |
| **537** | fold at 600 — row 1 complete, row 2 begins | |

| Element | Spec |
|---|---|
| Grid | 2-up <768 (gap 12) · 3-up 768–1023 (gap 20) · 4-up ≥1024 (gap 24) |
| **Minimum tiles** | **Never render fewer than 4 tiles** (Rule 1). If a filtered result returns 1–3, the layout switches to the feature-block format rather than showing a sparse grid |
| Filter (mobile) | Bottom sheet (§3.12 chassis): category, price range, "free from" attributes, skin type. Sticky `[Show 8 products]` footer. Applied filters render as removable chips above the grid |
| Filter (desktop) | 240 px inline-start rail, sticky at `top: calc(var(--h-header) + 24px)`, always expanded |
| Sort | Native `<select>` on mobile (the OS picker is faster and more accessible than a custom listbox); custom listbox on desktop. Options: Newest, Price low→high, Price high→low. **No "Best selling"** at low volume — it advertises thin sales data |
| Pagination | §3.10, 12 per page mobile / 24 desktop |
| Loading | Shape-matched card skeletons, same count as the incoming page |

A **"free from" filter is a first-class filter here**, not a nice-to-have: `Alcohol Denat.` · `Carmine (CI 75470)` · `Gelatin` · `Lanolin` · `Animal glycerin`. Filtering by INCI exclusion is the feature no competitor offers, and it belongs at the top of the filter sheet, above category.

---

### 4.3 Product detail page — `/product/{slug}`

#### Reconciling the two lists in the research

Research gives the PDP twice and the numbers do not match, so the reconciliation is stated here once and is binding:

- **§2.2** lists 14 ordered items, starting with the gallery and buy controls.
- **§4.3 Rule 3** calls for "8-section PDPs (ingredients, sourcing, certification, lab report, wudu note, how-to-use, FAQ, reviews)" and says each PDP should take 3–4 minutes to read.

These describe the same page. §2.2 items 1–5 are the **buy block** (not content sections); items 6–12 plus sourcing are the **8 content sections**; items 13–14 are a **utility block** that closes the page. Final structure:

```
BUY BLOCK          gallery · name · price · wudu chip · shade · CTAs · trust row
SECTION 1          What it does — including the full wudu statement
SECTION 2          Full annotated INCI list        ← the reason she came
SECTION 3          What's not in it
SECTION 4          Sourcing & how it's made
SECTION 5          Halal certification
SECTION 6          Third-party lab testing
SECTION 7          How to use
SECTION 8          Reviews
UTILITY            Shipping & returns · FAQ        (§2.2 items 13–14)
```

#### Above the fold at 375 × 600

| y | Element | Height |
|---|---|---|
| 0 | Announcement + header | 92 |
| 92 | Breadcrumb `Home › Shop › Nourishing Face Cream` | 32 |
| 124 | **Gallery** — 1:1 capped at `min(100vw, 340px)` | 340 |
| 464 | Dot indicators (4 dots, 8 px, 44 px tap band) | 24 |
| 488 | *gap* | 12 |
| 500 | **Product name** `text-display-sm` 24 px (1 line) | 30 |
| 530 | *gap* | 4 |
| 534 | **Price** `text-price` 22 px `tnum` — `PKR 4,850` | 26 |
| 560 | *gap* | 8 |
| 568 | **Wudu chip** — 28 px | 28 |
| **596** | fold at 600 | |

The gallery is capped at 340 px rather than the full 375 px square specifically to pull the name, price and wudu note above the fold. Nothing else in the buy block earns that space.

#### Buy block, in full

| Element | Spec |
|---|---|
| **Gallery** | Horizontal swipe carousel, `scroll-snap-type: x mandatory`. **Minimum 4 images** (research §2.2): product, texture/swatch, on-skin on South Asian skin, and the **packaging back showing the ingredient panel** — that last one is a trust asset, not a product shot. Dots, not arrows (research §6.2: never rely on tiny arrow buttons). Tap opens the lightbox (§3.11). Images 1:1, white plate, `object-fit: contain`, ≤80 KB each; first `eager`, rest `lazy` |
| Name | `text-display-sm` 24 px serif, `<h1>` |
| Price | `text-price` 22 px `tnum`. Round PKR numbers only — `PKR 4,850`, never `PKR 4,847` (research §3.2) |
| **Wudu chip** | Full-width row, `--surface-sunken`, 28–56 px, 16 px `ink-900`. Both variants use **identical styling** — see §6.3 |
| Shade row | If applicable: selected swatch + name + `Change`, opening §3.12 |
| CTAs | Primary `lg` `Add to cart` full-width; secondary `lg` `Buy now` beneath it, full-width. Buy Now is deliberately quieter (research §5.2 Flow B) |
| **Trust row** | Directly beneath the CTAs. 3 items, 44 px each, stacked at 375 px: `Halal Certified · HC-24019` (links to §5) · `Cash on Delivery` · `Delivery 3–5 days` |
| WhatsApp | Text button: `Ask about this product on WhatsApp`, pre-filled `Hi, I have a question about the Nourishing Face Cream` |
| Free-shipping line | 14 px `--text-muted`: `Free delivery on orders over PKR 3,000` — the **second** of the three required statements |

#### The 8 content sections

| # | Section | Contents |
|---|---|---|
| **1** | **What it does** | 2–3 short paragraphs, `text-lead` opener then `text-body`. Leads with **exclusions before inclusions** (the Iba lesson, research §3.1). Closes with the **full wudu statement** in an `evidence-plate`: either *"This cream absorbs fully and leaves no occlusive film. Wudu is unaffected."* or the honest-limitation variant |
| **2** | **Full annotated INCI list** | §3.8, **expanded by default**, complete in server-rendered HTML, with both share actions. This is section 2 of 8 and it is not negotiable — Persona A came for it and will not scroll past six marketing sections to find it |
| **3** | **What's not in it** | The condensed "never use" table (§3.7) filtered to this product's category, plus the wrapping row of `Free from` chips carrying INCI names (§3.5 B). Link: `See all 8 exclusions and why →` |
| **4** | **Sourcing & how it's made** | Where, by whom, under what conditions. Named supplier certificates where they exist. Facility photographs if available. This is where the counterfeit fear gets answered (research §7.1 item 6) |
| **5** | **Halal certification** | Full certificate block (§3.6), including the scan |
| **6** | **Third-party lab testing** | Link to the **actual PDF**, not a summary. Heavy-metals screen (lead, mercury, arsenic), microbial results, the **batch number the report covers**, and the testing lab's name. `evidence-plate` treatment, file size in the link label |
| **7** | **How to use** | Numbered steps, 16 px, with an amount-per-use note and a patch-test line |
| **8** | **Reviews** | §T9 rules apply: **no star average below 5 reviews**. Below threshold, render the "be one of our first 50 reviewers — 20% off your next order" offer. `Verified purchase` and `Founding customer` badges from day one |

**Utility block:** Shipping & returns — the return policy in **one plain sentence, visible without a click** (research §2.2) — then an FAQ accordion (single-expand, 48 px headers, `aria-expanded`, chevron rotates over `--motion-fast`).

Sections 1–8 are separated by 40 px and a 1 px `ink-200` rule; each has an `<h2>` at `text-title-lg` (sans — these are functional, not editorial) and a stable `id`. On desktop ≥1024 a sticky section-nav rail sits in the inline-start column at `top: calc(var(--h-header) + 24px)` listing all 8, with scroll-spy on the current section.

#### Sticky buy bar (mobile)

Appears once the primary Add to Cart scrolls out of view; **replaces the bottom nav on this page** rather than stacking above it, so bottom occlusion stays at 72 px instead of 128 px.

| Element | Spec |
|---|---|
| Height | 72 px + `var(--safe-bottom)`, `--surface`, `--shadow-bar`, `z-[var(--z-sticky-cta)]` |
| Inline-start | Price 18 px / 600 `tnum`, with `Cash on Delivery` at 14 px `--text-muted` beneath |
| Inline-end | Primary `lg` `Add to cart`, flex-1, min 180 px |
| Buy Now | **Not** in the bar — it stays in the buy block. Two competing CTAs at 375 px produces mis-taps, and Add to Cart is the higher-AOV path |
| Enter | `translate3d(0,100%,0)` → 0 over `--motion-base`; no shadow animation |
| WhatsApp | The floating WhatsApp FAB sits 12 px above the bar on this page (§5.3) |

#### Desktop PDP (≥1024)

Two columns inside `--container-page`: gallery 7/12 (main image 1:1 with a 4-thumbnail vertical strip on the inline-start), buy block 5/12 sticky at `top: calc(var(--h-header) + 24px)` until the content sections begin. Sections 1–8 then run **full width beneath both columns**, not squeezed into the narrow column — the INCI list and certificate block need the width. No sticky bar on desktop; the buy block's own stickiness covers it.

---

### 4.4 Cart — `/cart`

Implements research §5.2 Flow C exactly. Behaviour and motion are in §7.2; this is the layout.

#### Above the fold at 375 × 600

| y | Element | Height |
|---|---|---|
| 0 | Announcement + header | 92 |
| 92 | **H1** `text-title-lg` — `Your cart (2)` | 30 |
| 122 | *gap* | 12 |
| 134 | **Free-shipping progress block** | 72 |
| 206 | *gap* | 12 |
| 218 | Line item 1 | 120 |
| 338 | *gap* | 12 |
| 350 | Line item 2 | 120 |
| 470 | *gap* | 12 |
| 482 | Summary block begins (subtotal row visible) | — |
| 528 | Sticky checkout bar occupies 528–600 | 72 |
| **600** | fold | |

#### Line item

```
┌────────────────────────────────────────────┐
│ ┌──────┐  Nourishing Face Cream            │  16px/600
│ │ img  │  Shade: Rosewood                  │  14px --text-muted
│ │ 88px │  PKR 4,850                        │  16px/600 tnum
│ └──────┘  [−] 2 [+]        PKR 9,700   ⟳   │  48px stepper · line total
│           Remove                            │  14px danger-700
└────────────────────────────────────────────┘
```

| Element | Spec |
|---|---|
| Height | 120 px minimum |
| Image | 88 × 88, white plate, `--radius-sm`, links to the PDP |
| Name | 16 px / 600 `ink-900`, 2-line clamp |
| Shade | 14 px `--text-muted` — **mandatory when the product has shades.** A shade shown in the cart but missing from the confirmation is a doorstep refusal |
| Unit price | 16 px / 600 `tnum` |
| Stepper | §3.2, 48 × 48 buttons |
| Line total | 16 px / 600 `tnum`, inline-end aligned |
| Per-line spinner | 16 px, `ink-400`, inline-end of the line total, visible only during reconciliation (research §5.2: *spinner scoped to the affected line*) |
| Remove | Text-only `danger-700` (**9.12:1 PASS AAA**), placed on the row **below** the stepper and ≥24 px from any primary control |
| Separator | 1 px `ink-200` between items |

#### Free-shipping progress block

The strongest AOV lever on the page (research §5.2) and therefore visually prominent — but not gold-shouting.

| Element | Spec |
|---|---|
| Container | `--surface-sunken`, `--radius-sm`, padding 16 px |
| Message | 16 px `ink-900`: `PKR 550 away from free delivery` — or, on threshold, `✓ You've got free delivery` in `success-700` (**7.60:1 PASS AAA**) |
| Bar track | 8 px, `ink-200`, `--radius-full` |
| Bar fill | 8 px, `ink-900` (**not gold** — at 2.25:1 a gold fill would fail the 3:1 non-text bar and the progress would be unreadable). Width transitions over `--motion-slow` `ease-standard` |
| Semantics | `role="progressbar"` with `aria-valuenow`/`aria-valuemin`/`aria-valuemax` and an `aria-valuetext` of the message |

#### Summary and sticky bar

```
Subtotal (2 items)              PKR 9,700
Delivery                             Free
────────────────────────────────────────
TOTAL                          PKR 9,700     20px/600
┌────────────────────────────────────────┐
│ 💵 Have PKR 9,700 ready for the courier│   evidence-plate, 16px
└────────────────────────────────────────┘
[      Proceed to Checkout      ]            sticky, lg
```

Subtotal/Delivery rows 16 px, `ink-700`. TOTAL row 20 px / 600 `ink-900` with a 1 px `ink-900` rule above. The **exact cash amount** appears here and again on checkout and again on confirmation (research §3.2).

Sticky bar: 72 px + `var(--safe-bottom)`, `--surface`, `--shadow-bar`, `z-[var(--z-sticky-cta)]`, primary `lg` full width. The bottom nav is suppressed on this page.

Desktop ≥1024: two columns — line items 7/12, summary 5/12 sticky at `top: calc(var(--h-header) + 24px)`. No sticky bar.

**Empty cart:** §3.15.

---

### 4.5 Checkout — `/checkout`

Six fields. Research §5.3: *"Do not add a seventh without deleting one."*

#### Chrome changes on this page

- **No announcement bar.** Replaced by a 32 px trust strip: `SANHA Certified · HC-24019` + `Secure order — Cash on Delivery available`.
- **No bottom nav, no WhatsApp FAB, no search.** Every exit affordance except the logo is removed.
- Header reduces to 56 px: back arrow, wordmark, and a 44 px `Need help?` WhatsApp text link.

#### Above the fold at 375 × 600

| y | Element | Height |
|---|---|---|
| 0 | Trust strip | 32 |
| 32 | Header | 56 |
| 88 | **Order summary** — always expanded, never an accordion | 140 |
| 228 | *gap* | 16 |
| 244 | `Delivery details` `text-title` | 26 |
| 270 | *gap* | 12 |
| 282 | Field 1 — Full name (label 20 + input 52 + helper gap) | 78 |
| 360 | *gap* | 20 |
| 380 | Field 2 — Mobile number | 78 |
| 458 | *gap* | 20 |
| 478 | Field 3 — City | 78 |
| **556** | fold at 600 — three fields and the whole summary are visible | |

#### Order summary block

`--surface-sunken`, `--radius-sm`, padding 16 px. Per line: 56 px thumbnail, name, shade, `× 2`, line total. Then subtotal, delivery, TOTAL. **Visible without expanding** (research §5.3) — there is no "show order details" toggle. Buy Now arrivals get extra prominence here (research §5.2 Flow B friction point: the cart step, where a user normally re-reads the total, was skipped).

#### Field order and the order bump

1. Full name · 2. Mobile number · 3. City · 4. Complete address · 5. Email (optional) · 6. Payment (COD pre-selected).

**No postal code. No CNIC. Guest checkout only** — no account creation anywhere on this page. "Save my details" is offered on the *confirmation* page, after the money is committed.

**Order bump** — sits between the payment radios and the Place Order bar. A single bordered checkbox row, 72 px, `1.5px solid #8A8A8A`, `--surface-raised`:

```
┌──────────────────────────────────────────┐
│ ☐  Add the Rose Tint Lip Balm            │
│    PKR 1,950  (save 15%)   [thumb 48px]  │
└──────────────────────────────────────────┘
```

Research §5.2 calls this "the highest-ROI single component in the checkout". One offer only, never a carousel. Checking it updates the summary and the cash total optimistically.

#### Place Order bar

Sticky, 88 px + `var(--safe-bottom)`:

```
Have PKR 9,700 ready for the courier        14px --text-muted
[            Place Order            ]        lg primary, full width
```

The exact cash amount is restated immediately adjacent to the button (research §5.3). Beneath, 14 px: the return policy in one sentence, and `We'll send an order confirmation on WhatsApp`.

#### COD verification step (research §5.4)

After Place Order, before confirmation — the one place the design deliberately adds friction, because RTO runs 18–45 %.

Full-screen step (not a modal — a modal invites dismissal):

| Element | Spec |
|---|---|
| Heading | `text-title-lg`: `Confirm your number` |
| Body | 16 px: `We've sent a 4-digit code to +92 300 1234567.` + a `Change number` tertiary link |
| Input | 4-box OTP (§3.2) |
| Resend | Disabled 30 s with a live countdown in `role="status"` |
| Fallback | After two failed attempts: `Having trouble? Confirm on WhatsApp instead` — a WhatsApp deep link. **Never strand a paying customer at a code box** |
| Progress | `Step 2 of 2` at 14 px, so the extra step reads as bounded |

#### Confirmation — `/order/{id}/confirmation`

Order number (mono, selectable) · **the exact cash amount** · expected delivery window · courier name and logo · tracking link · WhatsApp button · `Save my details for next time` (the only account prompt in the flow) · printable invoice link (Persona C wants a physical proof of purchase).

Desktop ≥1024: two columns — form 7/12, summary 5/12 sticky. Place Order moves inline beneath the form; no sticky bar.

---

### 4.6 About Us — `/about`

Research §7.1: not a brand essay — **the primary evidence page**, and for Persona C often the deciding page. 800–1,200 words, first person, real photography only.

#### Above the fold at 375 × 600

| y | Element | Height |
|---|---|---|
| 0 | Announcement + header | 92 |
| 92 | **H1** `text-display` 28 px/1.12 (2 lines) — `I read the label on my own foundation.` | 63 |
| 155 | *gap* | 16 |
| 171 | **Origin-moment pull quote** `text-lead` 18 px/1.6 (4 lines) | 115 |
| 286 | *gap* | 20 |
| 306 | **Founder photograph** — full-bleed 375 × 280 (4:3) | 280 |
| **586** | fold at 600 | |

The origin moment is above the fold and the founder's face is at the fold. That ordering is deliberate: the specific, dated, personal moment (*"In 2024 I turned over my foundation and read 'Alcohol Denat.' I had been wearing it to Jummah for two years."*) is what makes the photograph mean something.

#### Section order (research §7.1, all ten, in order)

| # | Section | Treatment |
|---|---|---|
| 1 | Origin moment | `text-lead` pull quote, 4 px `gold-surface` inline-start rule (decorative), `--surface-evidence` |
| 2 | Founder, fully identified | Full-bleed 4:3 photo · name `text-display-sm` · city · credentials 16 px · **signature image** (SVG or transparent PNG, max 200 × 72, `alt` = the name) |
| 3 | The problem | Prose + inline `Free from` chips carrying INCI names, linking to `/what-we-never-use` |
| 4 | Our standard | 3 short statements in `evidence-plate` cards: *Every ingredient sourced with a halal certificate from the supplier · Every batch tested · Every INCI name published* |
| 5 | Certification in detail | Full certificate block (§3.6) with scan |
| 6 | How products are made | Facility photographs, named manufacturer, city. 2-up image grid on desktop |
| 7 | **What we're not** | `--surface-sunken` band, `text-lead`: *"We're new. We have three products. We're not a big company. What we are is careful."* Set at the same visual weight as every other section — this is a credibility asset, not a disclaimer |
| 8 | Company details | `evidence-plate` `<dl>`: registered name, legal form, full address, NTN, STRN, SECP number. Mono values, selectable |
| 9 | Direct contact from the founder | Personal email as a `mailto:` **and** as selectable text, with the explicit invitation |
| 10 | CTA | Primary `lg` back to the collection |

Prose measure capped at `--container-read` (704 px). Desktop: photo and text go 2-column for sections 2 and 6; everything else stays single-column and centred.

---

### 4.7 Contact Us — `/contact`

Research §7.2: the purpose is **proof of reachability**, and the form is the least important element on the page. Ordering follows §7.2 exactly.

#### Above the fold at 375 × 600

| y | Element | Height |
|---|---|---|
| 0 | Announcement + header | 92 |
| 92 | **H1** `text-title-lg` — `Talk to a person` | 30 |
| 122 | *gap* | 8 |
| 130 | Intro 16 px/1.6 (2 lines) — *"You'll usually be talking to the founder."* | 51 |
| 181 | *gap* | 20 |
| 201 | **WhatsApp card** | 132 |
| 333 | *gap* | 12 |
| 345 | **Phone card** | 108 |
| 453 | *gap* | 12 |
| 465 | **Email card** | 92 |
| **557** | fold at 600 — three live channels above the fold, no form in sight | |

#### Channel cards

Each is an `evidence-plate` with a 24 px icon, the channel name at `text-title-sm`, the destination as **selectable text**, a tap-to-act button, and stated hours.

| Card | Contents |
|---|---|
| **WhatsApp** (first, most prominent) | Number as selectable mono text · WhatsApp `md` button · `We reply within 2 hours, 10am–8pm PKT` |
| **Phone** | Number as selectable mono text · `tel:` button · hours · **landline listed alongside mobile where one exists** (research §T6: a landline implies fixed premises) |
| **Email** | Brand-domain address only — `hello@glowhalal.com`, **never a Gmail address** · `mailto:` button · `We reply within one working day` |

Then, in order: **physical address** with a lazy-loaded map embed and an explicit note on whether it is open to visitors (*"This is our office, not a shop — please message before visiting"*, so an unannounced visitor is not a broken promise); **company registration details** in an `evidence-plate` `<dl>` (registered name, NTN, SECP); **response-time commitment**; **the form, last** — four fields maximum (name, contact, subject dropdown, message), max-width `--container-form`, with a confirmation state naming a real timeframe; **quick links** (Track order, Shipping & returns, FAQ) as three 56 px rows; **social links** with follower counts only if they are respectable; and a **founder photo with a short note**.

> Restated from research §7.2 because it is an operational commitment, not a visual one: **only publish channels that will actually be staffed.** A phone number that rings out is worse than no phone number, and Persona C will call before ordering.

---

### 4.8 Journal index — `/journal`

Named "Journal", not "Blog" (research §4.1 sitemap). This is content inventory — a liveness signal that does not depend on catalogue size (§4.3 Rules 4 and 8).

#### Above the fold at 375 × 600

| y | Element | Height |
|---|---|---|
| 0 | Announcement + header | 92 |
| 92 | **H1** `text-title-lg` — `Journal` | 30 |
| 122 | *gap* | 8 |
| 130 | Intro (2 lines) — *"What we're learning about halal formulation, published as we go."* | 51 |
| 181 | *gap* | 24 |
| 205 | Featured post image 3:2 (375 × 250) | 250 |
| 455 | *gap* | 12 |
| 467 | Category overline + date | 20 |
| 487 | Title `text-title` 20 px (2 lines) | 52 |
| 539 | *gap* | 8 |
| 547 | Excerpt (1 line) | 26 |
| **573** | fold at 600 | |

Featured post full-width; subsequent posts in a 1-up list mobile, 2-up ≥768, 3-up ≥1024. Card: 3:2 image (≤50 KB, lazy), category overline in `--text-gold` (**7.31:1 PASS AAA**), title `text-title-sm` 2-line clamp, excerpt 2-line clamp `--text-secondary`, `date · N min read` at 14 px `--text-muted`. Pagination per §3.10, 6 mobile / 9 desktop.

---

### 4.9 Journal post — `/journal/{slug}`

#### Above the fold at 375 × 600

| y | Element | Height |
|---|---|---|
| 0 | Announcement + header | 92 |
| 92 | Breadcrumb | 32 |
| 124 | Category overline | 20 |
| 144 | *gap* | 8 |
| 152 | **H1** `text-display` 28 px/1.12 (3 lines) | 97 |
| 249 | *gap* | 12 |
| 261 | Byline · date · read time | 22 |
| 283 | *gap* | 16 |
| 299 | Hero image 3:2 (375 × 250) | 250 |
| **549** | fold at 600 | |

Body measure `--container-read` (704 px). `text-body` 16 px/1.6, `text-lead` for the opening paragraph. Rhythm per §2.2. In-article components available: `evidence-plate` callouts, the INCI table (§3.7), verdict chips (§3.5 D), full-bleed images with 14 px captions in `--text-muted`, and pull quotes at `text-display-sm` serif with a 4 px `gold-surface` inline-start rule.

Post footer: author block with founder photo · share row (WhatsApp first, then Facebook, then copy-link — WhatsApp is how this content actually travels here) · 2 related posts · a single product CTA where relevant. Reading-progress bar: 3 px, `gold-display-only` `#9C7C33` — **3.93:1 PASS** as a non-text indicator — fixed at the header's bottom edge, `transform: scaleX()` only.

Desktop ≥1024: a sticky table-of-contents rail in the inline-start column for posts with 4+ headings.

---

### 4.10 Ingredient Index — `/ingredient-index`

Research §T2 calls this the moat: *"a genuine SEO and authority moat, it costs nothing but content, and it partially solves the empty-store problem."* A visitor who lands on a searchable halal-ingredient database does not perceive an empty store — she perceives an authority.

**Search-first, not browse-first.** The search field is the hero.

#### Above the fold at 375 × 600

| y | Element | Height |
|---|---|---|
| 0 | Announcement + header | 92 |
| 92 | **H1** `text-title-lg` — `Halal Ingredient Index` | 30 |
| 122 | *gap* | 8 |
| 130 | Intro (2 lines) — *"Type any ingredient from the back of a pack. We publish the verdict and the reasoning."* | 51 |
| 181 | *gap* | 16 |
| 197 | **Search input** 52 px — placeholder `Try "Alcohol Denat." or "CI 75470"` | 52 |
| 249 | *gap* | 12 |
| 261 | Verdict filter chips — `All · Halal · Mushbooh · Not halal` | 36 |
| 297 | *gap* | 12 |
| 309 | Result count — `312 ingredients` | 22 |
| 331 | *gap* | 12 |
| 343 | Result row 1 | 92 |
| 435 | Result row 2 | 92 |
| **527** | fold at 600 — two full results visible | |

#### Result row

```
┌──────────────────────────────────────────┐
│ Alcohol Denat.            [ Not halal ]  │  15px mono · verdict chip
│ Denatured alcohol · SD Alcohol 40        │  14px --text-muted
│ Intoxicant-derived. Common in toners…    │  16px ink-700, 1-line clamp
└──────────────────────────────────────────┘
```

92 px, 1 px `ink-200` separator, whole row links to the detail page. Verdict chip per §3.5 D, inline-end.

| Element | Spec |
|---|---|
| Search | Sticky beneath the header once scrolled past. `type="search"`, `enterkeyhint="search"`, 48 × 48 clear button |
| Live filtering | Livewire `wire:model.live.debounce.300ms` — the same 300 ms budget as the cart, so there is one debounce value in the codebase. Results region is `aria-live="polite"` and announces `312 ingredients` → `4 ingredients` |
| Filter chips | 36 px pills, `--radius-full`. Selected: `ink-900` fill, `#FFFFFF` text (**17.41:1 PASS AAA**). Unselected: `1px #8A8A8A` border, `ink-800` text (**13.58:1 PASS AAA**). `role="radiogroup"` |
| Progressive enhancement | The form submits to `?q=` and renders server-side without JS. This page is the SEO moat — it must work for a crawler and on a dropped connection |
| A–Z index | Beneath the results: a 26-letter jump row, 44 × 44 targets, each linking to a crawlable `?letter=` URL |
| Pagination | §3.10, 25 per page. Numbered and crawlable |
| Empty state | §3.15 — including the `Request this ingredient` capture. Every "we don't know yet" is a content lead |

#### Ingredient detail — `/ingredient-index/{slug}`

One crawlable page per ingredient (this is where the SEO value actually lives). Structure: H1 = INCI name in mono · verdict chip · alternative names as `Free from`-style chips · **Why** (the reasoning — research §3.1: *"'No carmine' is a fact. 'No carmine, because carmine is crushed cochineal insects and lip products are inevitably ingested' is a conversion."*) · typical source · common product categories it appears in · what Glow Halal uses instead · `Products verified free from this` rail · last-reviewed date in `--text-muted` (a dated verdict reads as maintained) · `FAQPage`/`DefinedTerm` JSON-LD.

---

## 5. Navigation

### 5.1 Header

#### Mobile (<768 px) — 56 px, sticky, always visible

```
┌─────────────────────────────────────────────┐
│ Free delivery over PKR 3,000 · COD available│  36px announcement
├─────────────────────────────────────────────┤
│ [☰]        GLOW HALAL         [🔍]  [🛒 2]  │  56px
└─────────────────────────────────────────────┘
```

| Element | Spec |
|---|---|
| Announcement bar | 36 px, `ink-900` background, `#F2F0EC` 14 px centred — **14.98:1 PASS AAA**. Persistent, not dismissible. The **first** of three free-shipping statements (research §1) |
| Header | `--surface`, `1px solid #D9D9D9` bottom border, `z-[var(--z-header)]` |
| **No hide-on-scroll** | The header stays put. Auto-hide saves 56 px and costs the persistent cart badge and the scroll listener's frame budget — a bad trade on a low-end device |
| Menu button | 48 × 48, `aria-expanded`, `aria-controls`, `aria-label="Menu"` |
| Wordmark | Inline SVG, 132 × 24, centred, links to `/`. `<title>Glow Halal</title>` inside the SVG |
| Search | 48 × 48 icon button opening a full-screen search overlay |
| Cart | 48 × 48 with the count badge (§3.5 E) |

#### Desktop (≥768 px) — 72 px

```
┌──────────────────────────────────────────────────────────────────┐
│ Free delivery over PKR 3,000     ☎ +92 300 1234567 · WhatsApp   │  40px
├──────────────────────────────────────────────────────────────────┤
│ GLOW HALAL   Shop · What We Never Use · Certification ·          │  72px
│              Ingredient Index · About · Journal      [🔍] [🛒 2] │
└──────────────────────────────────────────────────────────────────┘
```

The desktop announcement bar gains a **phone number and WhatsApp link on the inline-end**. Reachability on every page is Persona C's first check.

**Primary nav — six items, and the order is the positioning:**

`Shop` · `What We Never Use` · `Certification` · `Ingredient Index` · `About` · `Journal`

Research §T2 is explicit: **"What We Never Use" is a top-level nav item, not a footer link, and that placement is itself a positioning statement.** It sits second, immediately after Shop, ahead of Certification — because it is the asset no competitor has.

`Lab Testing`, `Verify a batch`, `Contact` and `Track order` live in the footer and in the mobile drawer. They are not dropped; they are ranked below six items that earn the header.

| Element | Spec |
|---|---|
| Nav link | 16 px `ink-900` (**17.41:1 PASS AAA**), 24 px gap, 72 px tall hit area |
| Hover | 2 px `gold-surface` underline, `transform: scaleX()` from centre over `--motion-fast`. Decorative — it is not the state indicator |
| **Current page** | 2 px **`ink-900`** underline **plus** weight 600. Gold at 2.25:1 would fail the 3:1 non-text bar as a state indicator, and colour alone cannot carry state regardless |
| Shop dropdown | At launch, lists **individual product names**, not categories (research §4.2: with 3 SKUs a "Skincare" link leading to one item is a dead end that broadcasts emptiness). Flip to categories at ~8 SKUs |

### 5.2 Mobile menu — a bottom sheet

Research §6.2: *"Menu opens as a bottom sheet, not a top-anchored drawer — top-left is the hardest point on the screen to reach one-handed."*

Uses the §3.12 sheet chassis: slides from the bottom to 92 % of viewport height, grab handle, backdrop, drag-to-dismiss, focus trap, Esc, focus returned to the menu button on close.

Contents, in the order research §4.2 specifies:

```
        ▁▁▁▁▁▁▁▁
┌──────────────────────────────────────┐
│ Menu                            [✕]  │
├──────────────────────────────────────┤
│ SHOP                                 │  overline, --text-gold
│   Nourishing Face Cream          →   │  56px rows — real product
│   Rose Tint Lip Balm             →   │  names, not categories
│   Cleansing Oil                  →   │
│   Shop all                       →   │
├──────────────────────────────────────┤
│ WHY GLOW HALAL                       │
│   Our Halal Certification        →   │
│   What We Never Use              →   │
│   Ingredient Index               →   │
│   Lab Testing                    →   │
├──────────────────────────────────────┤
│   About the Founder              →   │
│   Journal                        →   │
│   Contact                        →   │
│   Track your order               →   │
├──────────────────────────────────────┤
│ [ 💬  WhatsApp us ]                  │  whatsapp button, full width
│ Cash on Delivery available           │  14px --text-muted, centred
└──────────────────────────────────────┘
```

Rows 56 px, 16 px `ink-900`, 1 px `ink-200` separators, 16 px chevron in `ink-400`. Group headers `text-overline` 14 px caps in `--text-gold` `#6A5320` (**7.31:1 PASS AAA**), 32 px above / 8 px below. Current page: `--surface-sunken` row background + weight 600 + `aria-current="page"`.

### 5.3 Bottom navigation and the WhatsApp affordance

```
┌───────┬───────┬───────────┬───────┐
│ ⌂     │ ▤     │ 💬        │ 🛒 2  │  56px + safe-area
│ Home  │ Shop  │ WhatsApp  │ Cart  │  14px labels — always visible
└───────┴───────┴───────────┴───────┘
```

WhatsApp in the nav bar is unusual in Western design and exactly right here (research §4.2).

| Property | Value |
|---|---|
| Height | 56 px + `var(--safe-bottom)`, `--surface`, 1 px `ink-200` top border, `z-[var(--z-bottom-nav)]` |
| Item | 25 % width, 24 px glyph + 14 px label. **Labels always visible** — icon-only navigation fails Persona C |
| Active | `ink-900` glyph and label at weight 600 + a 2 px `ink-900` top rule on that cell. Inactive `ink-600` (**6.69:1 PASS AA**) |
| Semantics | `<nav aria-label="Primary">`, `aria-current="page"` on the active item |
| **Suppressed on** | PDP (replaced by the sticky buy bar), cart, checkout, and the OTP step |

**The floating WhatsApp FAB and the bottom-nav WhatsApp item are mutually exclusive.** The FAB (56 px circle, `--color-whatsapp` fill, white glyph — **7.67:1 PASS AAA**, `--shadow-md`, `z-[var(--z-fab)]`, inset-inline-end 16 px) renders **only** where the bottom nav is absent: the PDP (12 px above the buy bar), the cart, and all desktop viewports. Never both at once — research requires WhatsApp on every page, not twice on the same page.

### 5.4 Footer

Five blocks. On mobile they stack; blocks 2–4 are collapsible accordions, but **block 1 is never collapsible** — it is the anti-scam proof (research §T6).

| Block | Contents |
|---|---|
| **1. Company legitimacy** — always expanded | Registered business name and legal form · complete physical address including city · **NTN** · **STRN** if registered · **SECP incorporation number** · landline and mobile as selectable text · `hello@glowhalal.com` (brand domain, never Gmail) · `SANHA Certified · HC-24019 · expires 11 Mar 2029`. `evidence-plate`, mono values, `user-select: all`. Persona C looks for these explicitly; Persona A checks them subconsciously |
| **2. Why Glow Halal** | Our Halal Certification · What We Never Use · Ingredient Index · Lab Testing · Verify a batch |
| **3. Shop** | Individual products · Shop all · Track your order |
| **4. Help** | Contact · FAQ · Shipping & returns · Privacy · Terms |
| **5. Utility row** | Founder avatar (48 px) with a one-line quote and signature (research §T5) · courier logos (TCS, Leopards, M&P) · social links · **theme toggle** (§1.5) · `Free delivery over PKR 3,000` — the **third** of the three required statements · `© 2026 Glow Halal` |

Footer surface `--surface-sunken` `#F0EFEC`; text `--text-secondary` `#4A4A4A` — **7.71:1 PASS AAA** on that surface. Legal and NTN copy at 14 px stays at `--text-secondary`, **not** muted — Persona C reads it. Headers `text-overline` in `--text-gold-strong` `#6A5320` (**6.36:1 on `#F0EFEC`, PASS AA**). Links 16 px, 44 px tap height, underline on hover.

Accordion headers on mobile: 56 px, `aria-expanded`, chevron rotating over `--motion-fast`. On desktop all blocks are expanded in a 4-column grid with the utility row spanning beneath.

---

## 6. The trust layer, visually

This is the brand's entire differentiator, so it gets a design language of its own rather than being decorated versions of ordinary components.

### 6.1 The governing idea: a document aesthetic, not a badge aesthetic

Research §2 gives two principles — *evidence over adjectives*, and *the skeptic reads the negative space*. Translated into visual decisions:

**Evidence is styled like a record, not a promotion.** The visual vocabulary is borrowed from documents — hairline rules, definition lists, monospace identifiers, spelled-out dates, stated file sizes, named destinations, "last reviewed" stamps. It is deliberately *under*-designed relative to the rest of the site. A certificate block that looks designed looks authored; a certificate block that looks filed looks true.

The four moves that produce this, and they apply everywhere trust content appears:

| Move | Concretely |
|---|---|
| **Monospace for identifiers** | Certificate numbers, INCI names, NTN, SECP, batch codes, order numbers. Mono says *transcribed*, proportional says *typeset*. It is also functionally right: these strings are read character by character |
| **Hairline definition lists, never cards-in-cards** | `<dl>` with 1 px `ink-200` row rules. No nested elevation, no shadow stacking. Two levels of surface maximum on any trust block |
| **Dates on everything, spelled out** | `Issued 12 March 2026 · Expires 11 March 2029 · Last reviewed 2 August 2026`. Never `12/03/26`. A dated claim reads as maintained; an undated claim reads as marketing |
| **Named destinations** | `Verify on pnac.gov.pk ↗`, `Download PDF (1.2 MB)`, `Tested by [lab name]`. Naming where a link goes and what it costs to open is a trust signal that a generic "Learn more" throws away |

**Selectable, copyable, screenshot-able — always.** Every trust fact is real text in the server-rendered HTML. Nothing is baked into an image, injected by JS, or lazily fetched. The test is concrete: *a screenshot taken with JS disabled must contain the complete evidence.* Research §5.1 documents Persona A screenshotting the ingredient list and sending it to a more knowledgeable friend — that screenshot is a marketing channel, and it must not be a picture of a spinner.

**Restraint on gold.** Gold appears in trust blocks exactly once: a 3 px decorative inline-start rule. It is never the certificate's frame, never the badge fill, never the check mark. The reason is not only contrast — it is that gilding evidence makes it look sold.

### 6.2 Rendering the negative space

The strongest trust asset is what is *absent*, and absence is hard to see. Three components make it visible:

| Component | How the absence is rendered |
|---|---|
| **The empty Animal chip** (§3.5 C) | The INCI legend shows four source chips. Three are filled: Plant, Mineral, Synthetic. The fourth — `Animal — 0 ingredients` — renders as an empty, dashed, grey outline. The eye reads the gap. This is the cheapest high-value component in the system |
| **The count line** (§3.8) | `23 total · 0 animal-derived · 0 alcohol`, server-computed, directly under the INGREDIENTS heading. A number is checkable; an adjective is not |
| **"Free from" chips carry INCI names** (§3.5 B) | `✕ Alcohol Denat.` not `Alcohol-Free`; `✕ CI 75470 (Carmine)` not `No insect dyes`. The chip matches the string printed on the back of the box she already owns, which is what makes it usable rather than decorative |

### 6.3 Honest limitations must look as good as the good news

Three components have a "bad news" state, and in all three the honest state is the more persuasive one (research §T1, §T4). The design rule is one line, and it is the most important rule in this section:

> **The limitation state uses the same chassis, the same typography, the same spacing, and the same visual weight as the positive state. Only the words and one status chip change. No warning colour, no reduced opacity, no dashed border, no italic apology.**

| Component | Positive state | Honest-limitation state |
|---|---|---|
| **Wudu note** (PDP buy block + section 1) | `--surface-sunken` row, 16 px `ink-900`: *"This cream absorbs fully and leaves no occlusive film. Wudu is unaffected."* | **Identical row, identical styling**: *"This is a long-wear formula. We recommend removing before wudu. We would rather tell you than let you assume."* No amber, no warning triangle. Research §T4: *"That second variant is worth more than the first."* |
| **Certificate block** (§3.6) | `Certified` chip, scan, full `<dl>` | `Certification in progress` chip in **info blue, not warning amber**, application reference in place of the scan, expected month, and a link to the interim ingredient dossier |
| **Reviews** (PDP content section 8) | Star average + count, at ≥5 reviews | **No stars at all** below 5 — plus the "be one of our first 50 reviewers" offer. Research §T9: a lone 5-star review reads as fake and is worse than no rating |

A fourth case worth naming: **out of stock** shows `Notify me`, never a disabled button. The same principle — an inconvenient fact is rendered as a usable path, not as a dead end.

### 6.4 Trust density per page — what appears where

| Page | Trust elements, in order of appearance |
|---|---|
| Homepage | Trust strip (cert number, INCI promise, COD) → certificate block → "never use" 6 rows → scarcity-as-rigor statement → founder block → delivery/COD → founding-customer testimonials → footer legitimacy block |
| PDP | Wudu chip (above fold) → trust row → annotated INCI (section 2) → "what's not in it" (3) → sourcing (4) → certificate (5) → lab report (6) → reviews (8) |
| Cart | Exact cash amount → free-shipping progress → return policy sentence |
| Checkout | Certificate number in the trust strip → COD pre-selected → exact cash amount adjacent to Place Order → return policy in one sentence, no click |
| About | Origin moment → founder photo + signature → certificate block → facility photos → "what we're not" → NTN/SECP block → founder's direct email |
| Contact | WhatsApp → phone → email → address → registration details → response commitment |
| Ingredient Index | Verdict chips with reasoning → last-reviewed dates → "request this ingredient" |
| Every page | Footer legitimacy block · certificate number · WhatsApp affordance |

### 6.5 The anti-pattern list, as a build rule

From research §2.3. These are not stylistic preferences — several are actively pattern-matched to scam sites in this market and will cost conversions.

| Never build | Because |
|---|---|
| Crescent/halal badge with no issuer name | Universally used by uncertified sellers; now reads as decoration |
| "100 % Natural" / "Chemical Free" | Scientifically illiterate; erodes credibility with the educated Persona A |
| Padlock / SSL badge graphics | A 2010-era signal; a modern buyer reads the browser, not your badge |
| Countdown timers, "17 people viewing" | Pattern-matched to scam sites in Pakistan. **Actively harmful here** |
| Stock photography of non-South-Asian models | Instantly reads as a dropshipping template |
| Ingredient list as a flat image | Not selectable, not searchable, not screenshot-friendly at quality — defeats the entire purpose |
| Contact page with only a web form | Reads as unreachable, especially to Persona C |
| "Certified by international standards" (unnamed) | Vagueness is precisely the tell Persona C is scanning for |
| Fabricated or unlabelled reviews | Pakistani beauty shoppers spot these and discuss them in Facebook groups |

---

## 7. Motion and interaction

### 7.1 The performance contract

Every animation in this system obeys four rules. They exist because Persona B is on an Infinix/Tecno-class device on a throttled connection, and a janky interface reads as a broken one.

1. **Animate `transform` and `opacity` only.** Never `width`, `height`, `top`, `left`, `margin`, `box-shadow`, `filter`, or `background-position`. Those trigger layout or paint on every frame; transform and opacity are composited.
2. **No scroll-linked JavaScript animation.** No parallax, no scroll-driven counters, no JS scroll listeners driving style. Where a scroll-linked effect is genuinely wanted (the reading-progress bar), use CSS `animation-timeline: scroll()` with a `scaleX` transform and accept that unsupporting browsers simply get no bar.
3. **`will-change` is applied on interaction start and removed on completion**, never left in a stylesheet. A permanent `will-change` promotes a layer permanently and costs memory on a 2 GB device.
4. **Exits are faster than entrances.** `--motion-exit` (160 ms) for everything leaving; the entrance duration for everything arriving. Waiting for something to leave feels like lag.

Durations and easings are the tokens in §1.4 Part 3. The default for anything unspecified is `--motion-base` (200 ms) with `--ease-standard`.

| Interaction | Duration | Easing | Property |
|---|---|---|---|
| Button hover / focus | `--motion-fast` 150 ms | `ease-standard` | `background-color`, `border-color` |
| Button press | `--motion-instant` 100 ms | `ease-standard` | `transform: scale(0.98)` |
| Toast enter / exit | 240 / 160 ms | `ease-enter` / `ease-exit` | `opacity`, `translate3d` |
| Bottom sheet enter / exit | 280 / 160 ms | `ease-enter` / `ease-exit` | `translate3d` |
| Modal enter / exit | 200 / 160 ms | `ease-enter` / `ease-exit` | `opacity`, `translate3d` |
| Backdrop fade | 200 / 160 ms | `ease-standard` | `opacity` |
| Cart badge | 320 ms | `ease-pop` | `transform: scale` |
| Accordion | 300 ms | `ease-standard` | `grid-template-rows: 0fr → 1fr` (the only layout-ish animation permitted, because it has no JS-measured height and no reflow of siblings) |
| Free-shipping bar | 300 ms | `ease-standard` | `transform: scaleX` |
| Skeleton pulse | 1400 ms loop | `ease-in-out` | `opacity` |
| Image hover zoom | 200 ms | `ease-standard` | `transform: scale(1.02)` |
| Page-load progress bar | — | linear | `transform: scaleX`, 3 px, `gold-display-only` `#9C7C33` (**3.93:1 PASS** non-text) |

**Reduced motion** (`prefers-reduced-motion: reduce`) is handled globally in `@layer base` (§1.4 Part 3): all durations collapse to 0.01 ms. Additionally: the skeleton pulse becomes a static fill, the cart badge does not pop, the sheet appears without translation (the backdrop still fades so the state change remains perceptible), and smooth scrolling becomes instant. **No state change is ever lost** — only the movement is.

### 7.2 The cart: optimistic UI in detail

Research §5.2 Flow C, specified as a sequence a developer can implement without inventing anything.

#### One-click add (Flow A)

```
t=0      Tap [+ Add]
         · button scale(0.98) for 100ms
t=0      Cart badge increments IMMEDIATELY and plays animate-badge-pop
         Button label swaps to an 18px spinner (min display --min-spinner 120ms)
t=0      POST fires
t≈120ms  Server responds
         · success  -> button shows ✓ Added (success-600) for 900ms,
                       then morphs into the [− 1 +] stepper
         · failure  -> badge rolls back, button returns to [+ Add],
                       inline 14px danger-700 message beneath the card
t≈150ms  Toast: "Added — Nourishing Face Cream   [View cart]"  (3s)
```

**Never navigate. Never open a drawer on a single add.** Research §5.2: a full-screen drawer interrupts browsing, which is the opposite of what a one-click add is for.

The `[+ Add]` → `[− 1 +]` morph is not cosmetic: it is what stops a second tap from blind-adding a second unit.

#### Quantity change on the cart page (Flow C)

```
t=0      Tap [+]
         · quantity value updates instantly              (optimistic)
         · line total recalculates                       (optimistic)
         · subtotal and TOTAL recalculate                (optimistic)
         · free-shipping progress bar advances           (optimistic)
         · cart badge updates                            (optimistic)
         · 16px spinner appears on THAT LINE ONLY
t=0      300ms debounce timer starts (--debounce-cart)
         Further taps within 300ms reset the timer — three rapid taps
         send ONE request carrying the final quantity, not three
t≈300ms  PATCH fires
t≈450ms  Server responds
         · match     -> spinner clears, nothing else moves
         · mismatch  -> values correct themselves AND an inline notice
                        appears: "Price updated — please review"
         · network   -> queue and retry; toast "Saved — we'll sync when
                        you're back online". Values are NOT reverted
```

Four rules from the research, restated as build constraints:

- **Never a full-page loading overlay.** The spinner is scoped to the affected line.
- **Never silently correct a price.** A silent change is the fastest way to lose a Pakistani buyer's trust — show `Price updated — please review` inline, at the line, in `--text-muted` with a `warning-600` glyph.
- **Quantity edits survive a dropped connection.** Queue and retry; connections drop routinely (research §6.1). Do not surface an error where a retry will do.
- **Remove uses a 5-second Undo toast, never a confirm dialog.** The row animates out over `--motion-base` (opacity + `translate3d`), the totals recalculate, and Undo restores it at its original index.

#### Livewire notes (implementation, not design)

`wire:model.live.debounce.300ms` on the quantity input; `wire:loading` scoped with `wire:target` to the specific line's action so the spinner cannot leak to the whole component; `wire:key` on every line so DOM diffing does not reorder rows mid-animation. The 300 ms figure is a single shared token (`--debounce-cart`) also used by the Ingredient Index search, so there is one number to tune.

### 7.3 Feedback patterns — which mechanism for which event

| Event | Mechanism | Not this |
|---|---|---|
| Add to cart | Optimistic badge + button morph + toast | A modal, a drawer, a redirect |
| Remove from cart | Row exit + Undo toast (5 s) | A confirm dialog |
| Quantity change | Per-line spinner, optimistic totals | A page overlay |
| Form field invalid | Inline message on blur, `role="alert"` | A toast, a summary box at the top only |
| Form submit invalid | Focus + scroll to the first invalid field, then inline messages | A disabled submit button |
| Copy to clipboard | Toast, 3 s | A tooltip that vanishes before it is read |
| Search / filter | Skeleton rows + `aria-live` count announcement | A spinner over the results |
| Page navigation | 3 px top progress bar | A full-page spinner |
| Offline | Persistent toast + queued writes | An error dialog |
| Order placed | Full-page confirmation + WhatsApp message | A toast |

### 7.4 Touch and gesture

| Gesture | Where | Notes |
|---|---|---|
| Horizontal swipe | PDP gallery, image lightbox | `scroll-snap-type: x mandatory`. **Never rely on arrow buttons** (research §6.2) |
| Vertical drag | Bottom sheets | Dismiss past 96 px or velocity > 0.5 px/ms; otherwise spring back over `--motion-base` |
| Swipe any direction | Toasts | Dismiss |
| Pinch / double-tap zoom | Certificate lightbox, product lightbox | Explicitly enabled — the certificate must be inspectable |
| Long press | — | **Not used.** No functionality is behind a long press |
| Pull to refresh | — | **Not used.** It conflicts with sheet drag gestures |

Tap feedback is `scale(0.98)` over `--motion-instant` on buttons and cards. `-webkit-tap-highlight-color: transparent` is set globally, which means every tappable element **must** provide its own press state — a tap with no feedback on a slow connection reads as an unresponsive site, and the user taps again, which is how double-orders happen.

---

## 8. Responsive rules

### 8.1 Breakpoints and what changes at each

80.2 % of Pakistani internet traffic is smartphone (research §6). Desktop is a scaled-up afterthought, not a parallel deliverable — build and review every page at 375 px before opening a desktop viewport.

| Token | Width | Device class | What changes |
|---|---|---|---|
| *(base)* | 320–479 | Small Android (360 × 800), iPhone SE (375 × 667) | Single column. Gutter 16 px. Bottom nav. Sheets instead of modals. Full-width buttons. Stacked trust strip. Tables become cards |
| `xs` | ≥480 | Large phones, small phones landscape | Product grid goes 2-up with real breathing room. Shade swatches 5-up. Buttons become auto-width |
| `sm` | ≥640 | Phablet / small tablet portrait | Display type reaches its clamp maximum for `display-sm`. Feature-block images gain side margins |
| `md` | ≥768 | Tablet | Gutter 24 px. Header 72 px with the full 6-item nav. Bottom nav disappears; WhatsApp FAB takes over. Product grid 3-up. **INCI tables become real tables**. Certificate block goes 2-column |
| `lg` | ≥1024 | Laptop | Gutter 32 px. Product grid 4-up. PDP 2-column with a sticky buy block. Cart and checkout 2-column. Shop filter rail inline. Sticky PDP section-nav appears |
| `xl` | ≥1280 | Desktop | `--container-page` (1200 px) reached; content stops growing and the page centres. Display type at maximum |
| `2xl` | ≥1536 | Large desktop | No layout change. Only full-bleed editorial media grows, capped at `--container-wide` (1440 px) |

**One-way rule:** every layout is authored mobile-first with `min-width` queries. There are no `max-width` queries in this system. If a rule needs `max-width`, the mobile default was written wrong.

### 8.2 Thumb-reach zones

Research §6.2, mapped to concrete pixel bands. Reference devices: **390 × 844** (iPhone 13/14 class) and **360 × 800** (the common Android class here).

| Zone | % of height | 844 px device | 800 px device | What lives here |
|---|---|---|---|---|
| **Hard** | 0–15 % | 0–127 px | 0–120 px | Announcement bar, logo, search. Static and low-frequency only. **Never a primary action** |
| **Stretch** | 15–55 % | 127–464 px | 120–440 px | Content, images, reading. Gallery, INCI list, prose |
| **Natural** ⭐ | 55–80 % | 464–675 px | 440–640 px | Price, shade selector, key decision information. The best real estate on the screen |
| **Easy** ⭐ | 80–100 % | 675–844 px | 640–800 px | Add to Cart, Place Order, bottom nav, sheet CTAs |

Applied rules:

- **Every primary CTA sits in the Easy zone**, either natively or via a sticky bar. This is why the PDP buy bar, the cart bar, the checkout bar and the shade sheet's footer all exist.
- **Primary actions align to the inline-end** (right in LTR) — right-handed majority. Under RTL this flips automatically via logical properties.
- **Destructive actions are never in the Easy zone** and never share a horizontal band with a primary action. `Remove` sits on its own row, ≥24 px clear.
- **Menu opens upward as a sheet**, because the menu button itself is unavoidably in the Hard zone but its *contents* need not be.
- Minimum tap target **48 × 48 px**; minimum **8 px** between adjacent targets; quantity steppers at 48 px (research floor is 44 px — the extra 4 px is spent because this is the documented classic mis-tap).
- **No hover state carries meaning.** Every hover affordance has a tap equivalent.
- Full functionality in portrait. Nothing requires landscape.

### 8.3 Images — sizing, formats and art direction

Research §6.1 sets hard byte budgets. These are per-image ceilings, not averages.

| Slot | Aspect | Rendered widths | `srcset` | `sizes` | Max bytes | Loading |
|---|---|---|---|---|---|---|
| **Homepage hero** | 3:2 mobile / 16:7 desktop (art-directed `<picture>`) | 375 → 1440 | 400, 800, 1200, 1600 | `(min-width:1024px) 1200px, 100vw` | **120 KB** | `eager`, `fetchpriority="high"` |
| PDP gallery main | 1:1 | 340 → 660 | 400, 800, 1200 | `(min-width:768px) 560px, 100vw` | **80 KB** | first `eager`, rest `lazy` |
| PDP gallery thumb | 1:1 | 64 | 128 | — | 12 KB | `lazy` |
| Product card | 1:1 | 165 → 280 | 300, 600 | `(min-width:1024px) 280px, (min-width:480px) 45vw, 90vw` | **45 KB** | `lazy` |
| Editorial feature block | 4:5 mobile / 3:4 desktop (art-directed) | 375 → 720 | 400, 800, 1200 | `(min-width:768px) 50vw, 100vw` | **80 KB** | `lazy`, first block `eager` |
| Category card | 3:2 mobile / 4:3 desktop | 165 → 380 | 400, 800 | `(min-width:768px) 33vw, 50vw` | 50 KB | `lazy` |
| Founder photo | 4:3 mobile / 4:5 desktop (art-directed) | 375 → 480 | 400, 800 | `(min-width:1024px) 480px, 100vw` | **70 KB** | `lazy` (homepage), `eager` on `/about` |
| Certificate thumbnail | 3:4 | 240 → 320 | 240, 480 | `320px` | 40 KB | `lazy` |
| Certificate full scan | native | 1600 | 1600 | — | **300 KB** | **on demand only** — fetched when the lightbox opens |
| Journal card | 3:2 | 343 → 380 | 400, 800 | `(min-width:1024px) 360px, 100vw` | 50 KB | `lazy` |
| Journal hero | 3:2 | 375 → 704 | 400, 800, 1200 | `(min-width:768px) 704px, 100vw` | 90 KB | `eager` |
| Facility photo | 3:2 | 375 → 560 | 400, 800 | `(min-width:768px) 50vw, 100vw` | 70 KB | `lazy` |
| OG / Twitter card | 1200 × 630 | fixed | — | — | 200 KB | — |

**Formats:** AVIF first, WebP second, JPEG fallback, via `<picture><source type>`. PNG only for the signature and logo assets that need transparency; the wordmark is SVG.

**Art direction** (a genuinely different crop, not just a different size) is required in exactly four places, and is a `<picture>` with `media` on the sources: the homepage hero (3:2 → 16:7), the editorial feature block (4:5 → 3:4), the founder photo (4:3 → 4:5), and the category card (3:2 → 4:3). Everywhere else, one crop with `srcset` is correct and cheaper.

**Mandatory on every `<img>`:** explicit `width` and `height` attributes (or a CSS `aspect-ratio`) so nothing shifts on load — the CLS budget is 0.02 for the homepage. Plus `decoding="async"`, and a real `alt` (empty `alt=""` only for genuinely decorative images).

**A 9:16 Reels crop is exported for every product** but is not used on the site. Research §3.2: product photography must be Reels-native because discovery happens on Instagram — that is an asset-pipeline requirement, not a layout one.

**Never** autoplay video, and never load a video poster above 100 KB. Data cost is a real constraint for Persona B (research §6.1); a heavy site is a site she cannot afford to browse.

### 8.4 Performance budget — the numbers this design is accountable to

Carried unchanged from research §6.1. If a design decision in this document breaks one of these, the budget wins and the decision gets revised.

| Metric | Budget |
|---|---|
| Homepage total weight | < 600 KB |
| Hero image | < 120 KB |
| Product image | < 80 KB each |
| JS bundle (initial) | < 150 KB gzipped |
| Web fonts | ≤ 74 KB total, 3 files, `font-display: swap`, Latin-subset (§2.1) |
| LCP on 4G | < 2.5 s |
| Time to Interactive | < 4 s |
| CLS (homepage) | < 0.02 |

Structural requirements that follow: server-rendered Blade for first paint (**the PDP is never behind a client-side SPA**); `srcset` on every image; lazy-load everything below the fold including the Instagram embed; skeletons rather than spinners; optimistic UI on every cart action; queue-and-retry rather than error states on network failure; and `content-visibility: auto` with a `contain-intrinsic-size` hint on the below-fold homepage sections (7 onward) and on PDP sections 4–8.

### 8.5 Other mobile requirements

Research §6.4, restated as a checklist because each of these is individually easy to forget:

- Correct `inputmode` and `autocomplete` on every field — `tel`, `name`, `email`, `street-address`, `one-time-code`.
- **No zoom-on-focus**: 16 px minimum on every input. This is why `--text-meta` (14 px) is forbidden inside form controls.
- `tel:` and `wa.me` links open the native app; WhatsApp links carry pre-filled context text.
- Native share sheet on the PDP and on Journal posts.
- Visible focus states for external-keyboard users — never removed (lint rule G7).
- Full functionality in portrait; nothing requires landscape.
- `<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">` — `viewport-fit=cover` is required for the `env(safe-area-inset-bottom)` values the sticky bars depend on. **Never** `user-scalable=no` or `maximum-scale=1`.
- `100dvh`, not `100vh`, for any full-height surface — mobile browser chrome resizes on scroll and `vh` produces a jump.
- Test at 200 % browser text size (Persona C): no clipping, no horizontal scroll, no overlapping sticky bars. Fixed-height text containers are forbidden for this reason — use `min-height`.

---

## 9. Handoff

### 9.1 Stylesheet file structure

```
resources/css/
├── fonts.css          @font-face declarations + metric-matched fallbacks (§2.1)
└── app.css            @import 'tailwindcss' + Parts 1–3 of §1.4, in order
public/fonts/
├── inter-400.woff2    ≤24 KB
├── inter-600.woff2    ≤24 KB
└── playfair-600.woff2 ≤26 KB
```

Everything else is Blade components and Livewire components consuming the utilities. **There is no `tailwind.config.js`** — Tailwind 4 is configured entirely from `app.css`, and adding a JS config would silently shadow half of this document.

### 9.2 Build order

Foundations before components, components before pages. Each step is verifiable on its own.

| # | Step | Done when |
|---|---|---|
| 1 | `app.css` Parts 1–3 + `fonts.css` + the inline theme script | `bg-surface`, `text-gold-text-strong`, `ease-enter` and `z-[var(--z-modal)]` all resolve; no `text-xs`, no `font-bold`, no `bg-red-500` exists |
| 2 | Layout shell — announcement bar, header, mobile menu sheet, bottom nav, footer | Navigable at 375 px, with "What We Never Use" in the primary nav |
| 3 | Primitives — buttons, inputs, badges, chips, toasts, skeletons, empty states | Every state in §3.1–3.2 and §3.5 renders; the global focus ring is visible on a gold button |
| 4 | Trust components — certificate block (all **three** states), INCI table, annotated INCI list | The in-progress certificate state looks as finished as the certified state |
| 5 | Homepage | Fold budget in §4.1 verified in a 375 × 600 viewport |
| 6 | PDP — buy block, 8 sections, sticky bar, shade sheet | INCI list complete with JS disabled |
| 7 | Cart — optimistic UI, 300 ms debounce, per-line spinners, Undo | Three rapid `+` taps produce one request |
| 8 | Checkout — 6 fields, order bump, OTP step, confirmation | No postal code, no CNIC, no account creation; COD pre-selected |
| 9 | About, Contact, Journal, Ingredient Index | Contact shows three live channels above the fold at 375 px |
| 10 | Dark theme (P2) | Product images keep white plates |

### 9.3 Definition of done — per page

A page is not done until all nine are true:

1. Renders correctly at **360 × 800** and **375 × 667** with no horizontal scroll.
2. Every foreground/background pairing on it appears in the §1.3 matrix with a **PASS**.
3. Keyboard-navigable end to end; focus ring visible on every interactive element; focus order matches visual order.
4. Works with **JavaScript disabled** for all content and all trust evidence (interactive extras may degrade).
5. Every tap target ≥ 48 × 48 px with ≥ 8 px separation.
6. Legible and unbroken at **200 % browser text size**.
7. Meets the §8.4 performance budget, measured on throttled 4G — not on a desktop connection.
8. `prefers-reduced-motion` honoured; no state change is lost.
9. No lint rule from §1.6 triggers.

### 9.4 Open items this document deliberately does not decide

These need real inputs before they can be specified, and inventing them here would be worse than naming the gap:

| Item | Blocked on | Fallback until then |
|---|---|---|
| Certificate number, issuer, standard, dates | The actual certificate | Build state 2 (in progress) as the default. Research §T1's honesty gate applies: **the copy must not assume a certificate that does not yet exist** |
| Founder name, photograph, signature, city | The founder | Every founder slot is a required field, not an optional one — the component should fail loudly in dev if unpopulated, because anonymity is itself a scam signal in this market (research §T5) |
| NTN, STRN, SECP, registered address, landline | Company registration | Footer block 1 renders only the fields that exist; it never renders a placeholder |
| Free-shipping threshold (PKR 3,000 used throughout) | Unit economics | Single config value, surfaced in all three required places |
| Lab reports, batch codes | Testing | PDP section 6 hides entirely rather than showing "coming soon" |
| Shade names, swatch hex values, undertones | The first shaded SKU | The sheet (§3.12) is built ahead of it, per research §5.2 |
| Real product photography | Photoshoot | **No stock photography of non-South-Asian models under any circumstances** (research §2.3) |

### 9.5 Where this document may be wrong

Research §0 is explicit that the personas are synthesized hypotheses, not validated findings, and §9 names the cheapest studies that would upgrade them. The design decisions most exposed to that uncertainty:

- **The above-the-fold hierarchy on the homepage** — research §9 proposes a first-click test across three variants (generic badges / certificate-number-forward / founder-forward). This document commits to certificate-number-forward. That is an argument, not a finding.
- **Six primary nav items with "What We Never Use" second** — a strong positioning bet on unvalidated personas.
- **The 340 px gallery cap on the PDP**, which trades product imagery for getting price and the wudu note above the fold.
- **Dark theme as P2** — justified by Persona A's stated 10pm–1am shopping window, which is itself synthesized.

Re-open these after the 8–10 interviews in research §9 run. Nothing else in this document should change on the strength of an opinion.

---

**Document status:** v1.0 — complete specification, ready for implementation.
**Derived from:** `docs/ux-research.md` v1.
**No application code was written to the project as part of this deliverable.** The CSS in §1.4 and §2.1 is specification content, to be applied by the Frontend Developer to `resources/css/app.css` and `resources/css/fonts.css`.

