<?php

namespace App\Models;

use App\Enums\TransferStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_store_id',
        'destination_store_id',
        'user_id',
        'transfer_number',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => TransferStatus::class,
        ];
    }

    public function sourceStore(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'source_store_id');
    }

    public function destinationStore(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'destination_store_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransferItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'reference_id')
            ->where('reference_type', self::class);
    }

    public function getTotalQuantityAttribute(): int
    {
        return (int) $this->items()->sum('quantity');
    }

    /**
     * Scope query to transfers accessible by the given user.
     */
    public function scopeAccessibleBy($query, User $user)
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isBranchManager()) {
            return $query->where(function ($q) use ($user) {
                $q->whereHas('sourceStore', function ($sq) use ($user) {
                    $sq->where('branch_id', $user->branch_id);
                })->orWhereHas('destinationStore', function ($dq) use ($user) {
                    $dq->where('branch_id', $user->branch_id);
                });
            });
        }

        if ($user->isStoreManager()) {
            return $query->where(function ($q) use ($user) {
                $q->where('source_store_id', $user->store_id)
                  ->orWhere('destination_store_id', $user->store_id);
            });
        }

        return $query->whereRaw('1 = 0');
    }
}
