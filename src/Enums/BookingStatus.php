<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Canceled = 'canceled';
    case Completed = 'completed';
    case NoShowed = 'no_showed';

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::Pending => in_array($target, [
                self::Confirmed,
                self::Canceled,
            ], true),

            self::Confirmed => in_array($target, [
                self::Completed,
                self::NoShowed,
                self::Canceled,
            ], true),

            self::Canceled,
            self::Completed,
            self::NoShowed => false,
        };
    }
}