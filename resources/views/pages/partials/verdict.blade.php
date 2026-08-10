{{--
  Verdict chip — WITHHELD FOR NOW (owner decision).

  The site does not display a halal/haram religious ruling until each ruling can
  cite a fatwa or a recognised halal standard (e.g. OIC/SMIIC 1, GSO 2055-1,
  MS 2200). Stating "haram" without a sourced authority is exactly what we will
  not do.

  This partial therefore no longer renders the halal/haram/mashbooh verdict from
  $status. It renders ONLY a neutral FORMULATION-POLICY label when the caller
  passes one — e.g. "Never used" on the exclusion table, which is a statement
  about our own formulation choice, not a religious ruling. With no $label
  (i.e. it would have been a bare ruling), it renders nothing.

  To restore sourced verdicts later: reintroduce the status→tone map here, gated
  on a per-ingredient ruling-source field, so a verdict only shows when its
  citation is present.

  $status — App\Enums\HalalStatus (currently ignored; kept in the call sites)
  $label  — optional formulation-policy label, e.g. "Never used"
--}}
@php
    $label = $label ?? null;
@endphp

@if ($label)
    <x-ui.status-chip tone="neutral" icon="cross">{{ $label }}</x-ui.status-chip>
@endif
