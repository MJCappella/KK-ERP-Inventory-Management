<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'name',
        'code',
        'location',
        'phone',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(StoreStock::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function outgoingTransfers(): HasMany
    {
        return $this->hasMany(Transfer::class, 'source_store_id');
    }

    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(Transfer::class, 'destination_store_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    // Get the stock quantity for a specific product in this store.
    public function getStockForProduct(int $productId): int
    {
        return (int) ($this->stocks()->where('product_id', $productId)->value('quantity') ?? 0);
    }

    // Scope query to stores accessible by the given user.
    public function scopeAccessibleBy($query, User $user)
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isBranchManager()) {
            return $query->where('branch_id', $user->branch_id);
        }

        if ($user->isStoreManager()) {
            return $query->where('id', $user->store_id);
        }

        return $query->whereRaw('1 = 0');
    }
}
