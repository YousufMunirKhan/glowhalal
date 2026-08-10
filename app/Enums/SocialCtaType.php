<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SocialCtaType: string implements HasLabel
{
    case None = 'none';
    case WhatsappOrder = 'whatsapp_order';
    case ShopNow = 'shop_now';
    case LearnMore = 'learn_more';
    case DmToOrder = 'dm_to_order';

    public function getLabel(): string
    {
        return match ($this) {
            self::None => 'No call to action',
            self::WhatsappOrder => 'Order on WhatsApp',
            self::ShopNow => 'Shop now',
            self::LearnMore => 'Learn more',
            self::DmToOrder => 'DM to order',
        };
    }
}
