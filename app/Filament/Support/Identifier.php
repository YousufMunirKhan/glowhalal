<?php

namespace App\Filament\Support;

use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\TextColumn;

/**
 * Emails, phone numbers, order numbers and SKUs are single unbreakable tokens.
 *
 * Filament's default `overflow-wrap: break-word` on text entries means that as
 * soon as the column is narrower than the value, the browser breaks *inside*
 * the token — producing `ayesha.siddi / qui@example / .com` and `+923001 / 234567`.
 * A broken email is unreadable and un-copyable by eye, which is worse than one
 * that is visibly clipped.
 *
 * These helpers keep the value on one line, clip the overflow, and put the full
 * value behind a tooltip and the copy button. Applied on top of a layout that is
 * already wide enough, they are a guard against pathological values rather than
 * the primary fix — the primary fix is always giving the column enough room.
 */
final class Identifier
{
    private const STYLE = 'overflow-wrap:normal;word-break:normal;white-space:nowrap;'
        .'overflow:hidden;text-overflow:ellipsis;max-width:100%;display:block;';

    public static function entry(string $name, ?string $label = null): TextEntry
    {
        return TextEntry::make($name)
            ->label($label)
            ->copyable()
            ->tooltip(fn ($state) => filled($state) ? (string) $state : null)
            ->extraAttributes(['style' => self::STYLE])
            ->extraEntryWrapperAttributes(['style' => 'min-width:0;']);
    }

    public static function column(string $name, ?string $label = null): TextColumn
    {
        return TextColumn::make($name)
            ->label($label)
            ->copyable()
            ->tooltip(fn ($state) => filled($state) ? (string) $state : null)
            ->extraAttributes(['style' => self::STYLE]);
    }

    /** Style string for reuse on entries built elsewhere. */
    public static function style(): string
    {
        return self::STYLE;
    }
}
