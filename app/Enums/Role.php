<?php

namespace App\Enums;

enum Role: string
{
    case ADMIN = 'admin';
    case BRANCH_MANAGER = 'branch_manager';
    case STORE_MANAGER = 'store_manager';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::BRANCH_MANAGER => 'Branch Manager',
            self::STORE_MANAGER => 'Store Manager',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::ADMIN => 'bg-purple-400 text-white border-purple-500',
            self::BRANCH_MANAGER => 'bg-blue-400 text-white border-blue-500',
            self::STORE_MANAGER => 'bg-emerald-400 text-white border-emerald-500',
        };
    }
}
