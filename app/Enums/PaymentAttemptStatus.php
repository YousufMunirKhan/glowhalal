<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PaymentAttemptStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case AwaitingVerification = 'awaiting_verification';
    case Authorized = 'authorized';
    case Paid = 'paid';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::AwaitingVerification => 'Awaiting verification',
            self::Authorized => 'Authorized',
            self::Paid => 'Paid',
            self::Failed => 'Failed',
            self::Cancelled => 'Cancelled',
            self::Refunded => 'Refunded',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'gray',
            self::AwaitingVerification => 'warning',
            self::Authorized => 'info',
            self::Paid => 'success',
            self::Failed => 'danger',
            self::Cancelled => 'gray',
            self::Refunded => 'gray',
        };
    }
}
