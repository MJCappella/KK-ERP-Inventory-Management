<?php

namespace App\Enums;

enum TransferStatus: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'bg-amber-500 text-white border border-amber-500',
            self::COMPLETED => 'bg-emerald-500 text-white border border-emerald-500',
            self::CANCELLED => 'bg-rose-500 text-white border border-rose-500',
        };
    }
}
