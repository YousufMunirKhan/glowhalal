<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PageTemplate: string implements HasLabel
{
    case Default = 'default';
    case FullWidth = 'full_width';
    case Contact = 'contact';
    case Faq = 'faq';

    public function getLabel(): string
    {
        return match ($this) {
            self::Default => 'Default',
            self::FullWidth => 'Full width',
            self::Contact => 'Contact',
            self::Faq => 'FAQ',
        };
    }
}
