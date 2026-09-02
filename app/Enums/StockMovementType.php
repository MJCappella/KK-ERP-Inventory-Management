<?php

namespace App\Enums;

enum StockMovementType: string
{
    case INBOUND = 'inbound';
    case SALE = 'sale';
    case TRANSFER_OUT = 'transfer_out';
    case TRANSFER_IN = 'transfer_in';
    case ADJUSTMENT = 'adjustment';

    public function label(): string
    {
        return match ($this) {
            self::INBOUND => 'Stock Inbound / Receive',
            self::SALE => 'POS Sale',
            self::TRANSFER_OUT => 'Transfer Out (Sent)',
            self::TRANSFER_IN => 'Transfer In (Received)',
            self::ADJUSTMENT => 'Manual Adjustment',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::INBOUND => 'INBOUND',
            self::SALE => 'SALE',
            self::TRANSFER_OUT => 'TRANSFER OUT',
            self::TRANSFER_IN => 'TRANSFER IN',
            self::ADJUSTMENT => 'ADJUSTMENT',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::INBOUND => 'bg-emerald-500 text-white border border-emerald-500',
            self::SALE => 'bg-sky-500 text-white border border-blue-500',
            self::TRANSFER_OUT => 'bg-yellow-500 text-white border border-amber-500',
            self::TRANSFER_IN => 'bg-indigo-500 text-white border border-indigo-500',
            self::ADJUSTMENT => 'bg-amber-400 text-white border border-amber-400',
        };
    }
}
