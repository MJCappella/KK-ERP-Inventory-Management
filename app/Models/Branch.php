<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'location',
        'phone',
    ];

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function sales(): HasManyThrough
    {
        return $this->hasManyThrough(Sale::class, Store::class);
    }

    public function stockMovements(): HasManyThrough
    {
        return $this->hasManyThrough(StockMovement::class, Store::class);
    }

    /**
     * Scope query to branches accessible by the given user.
     */
    public function scopeAccessibleBy($query, User $user)
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isBranchManager()) {
            return $query->where('id', $user->branch_id);
        }

        if ($user->isStoreManager() && $user->store) {
            return $query->where('id', $user->store->branch_id);
        }

        return $query->whereRaw('1 = 0');
    }
}
