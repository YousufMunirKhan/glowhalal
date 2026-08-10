<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AttributeType: string implements HasLabel
{
    case Select = 'select';
    case Color = 'color';
    case Size = 'size';
    case Text = 'text';

    public function getLabel(): string
    {
        return match ($this) {
            self::Select => 'Select list',
            self::Color => 'Colour swatch',
            self::Size => 'Size',
            self::Text => 'Free text',
        };
    }
}
