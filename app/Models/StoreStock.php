<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'product_id',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= ($this->product?->reorder_level ?? 10);
    }

    public function isOutOfStock(): bool
    {
        return $this->quantity <= 0;
    }

    /**
     * Scope query to store stocks accessible by the given user.
     */
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
