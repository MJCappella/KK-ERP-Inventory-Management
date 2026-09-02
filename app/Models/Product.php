<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'barcode',
        'category',
        'description',
        'unit',
        'cost_price',
        'selling_price',
        'reorder_level',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'reorder_level' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function storeStocks(): HasMany
    {
        return $this->hasMany(StoreStock::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function transferItems(): HasMany
    {
        return $this->hasMany(TransferItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function getTotalStockAttribute(): int
    {
        return (int) $this->storeStocks()->sum('quantity');
    }

    public function getMarginPercentageAttribute(): float
    {
        if ($this->cost_price <= 0) {
            return 0.0;
        }
        return round((($this->selling_price - $this->cost_price) / $this->cost_price) * 100, 1);
    }

    public function isLowStock(?int $storeId = null): bool
    {
        if ($storeId) {
            $qty = (int) ($this->storeStocks()->where('store_id', $storeId)->value('quantity') ?? 0);
            return $qty <= $this->reorder_level;
        }

        return $this->total_stock <= $this->reorder_level;
    }
}
