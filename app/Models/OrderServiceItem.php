<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'ac_unit_id',
    'room_name',
    'ac_type',
    'service_category',
    'quantity',
    'price_per_unit',
    'subtotal',
])]
class OrderServiceItem extends Model
{
    use HasFactory;

    /**
     * Relasi ke pesanan induk (Order).
     *
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relasi opsional ke unit AC terdaftar di masjid.
     *
     * @return BelongsTo<AcUnit, $this>
     */
    public function acUnit(): BelongsTo
    {
        return $this->belongsTo(AcUnit::class);
    }

    /**
     * Attributes cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'price_per_unit' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }
}
