<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'user_id',
        'invoice_number',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'payment_method',
        'customer_name',
        'customer_phone',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'reference_id')
            ->where('reference_type', self::class);
    }

    //scoped to specific users
    public function scopeAccessibleBy($query, User $user)
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isBranchManager()) {
            return $query->whereHas('store', function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            });
        }

        if ($user->isStoreManager()) {
            return $query->where('store_id', $user->store_id);
        }

        return $query->whereRaw('1 = 0');
    }
}
