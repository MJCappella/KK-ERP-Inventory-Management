<?php

namespace App\Models;

use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'branch_id',
        'store_id',
        'phone',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
            'is_active' => 'boolean',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(Transfer::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::ADMIN;
    }

    public function isBranchManager(): bool
    {
        return $this->role === Role::BRANCH_MANAGER;
    }

    public function isStoreManager(): bool
    {
        return $this->role === Role::STORE_MANAGER;
    }

    /**
     * Check if user has permission to view or manage a specific store.
     */
    public function canAccessStore(int|Store $store): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $storeId = $store instanceof Store ? $store->id : $store;

        if ($this->isStoreManager()) {
            return $this->store_id === $storeId;
        }

        if ($this->isBranchManager()) {
            if (! $this->branch_id) {
                return false;
            }
            $storeObj = $store instanceof Store ? $store : Store::find($storeId);

            return $storeObj && $storeObj->branch_id === $this->branch_id;
        }

        return false;
    }

    /**
     * Check if user has permission to view or manage a specific branch.
     */
    public function canAccessBranch(int|Branch $branch): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $branchId = $branch instanceof Branch ? $branch->id : $branch;

        if ($this->isBranchManager()) {
            return $this->branch_id === $branchId;
        }

        if ($this->isStoreManager()) {
            return $this->store && $this->store->branch_id === $branchId;
        }

        return false;
    }

    /**
     * Get allowed store IDs collection or query for the user
     */
    public function accessibleStoreIds(): array
    {
        if ($this->isAdmin()) {
            return Store::pluck('id')->toArray();
        }

        if ($this->isBranchManager()) {
            return Store::where('branch_id', $this->branch_id)->pluck('id')->toArray();
        }

        if ($this->isStoreManager() && $this->store_id) {
            return [$this->store_id];
        }

        return [];
    }
}
