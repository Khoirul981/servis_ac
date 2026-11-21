<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'order_number',
    'masjid_id',
    'technician_id',
    'status',
    'service_date',
    'service_time',
    'ac_count',
    'base_amount',
    'additional_amount',
    'additional_notes',
    'payment_method',
    'paid_at',
    'dkm_complaint',
])]
class Order extends Model
{
    use HasFactory;

    public const STATUS_PESANAN_BARU = 'pesanan_baru';
    public const STATUS_DIJADWALKAN = 'dijadwalkan';
    public const STATUS_DIKERJAKAN = 'dikerjakan';
    public const STATUS_MENUNGGU_BAYAR = 'menunggu_bayar';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_BATAL = 'batal';

    /**
     * Get the masjid for this order.
     *
     * @return BelongsTo<Masjid, $this>
     */
    public function masjid(): BelongsTo
    {
        return $this->belongsTo(Masjid::class);
    }

    /**
     * Get the assigned technician.
     *
     * @return BelongsTo<User, $this>
     */
    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    /**
     * Get the photos attached to this order.
     *
     * @return HasMany<OrderPhoto, $this>
     */
    public function photos(): HasMany
    {
        return $this->hasMany(OrderPhoto::class);
    }

    /**
     * Calculate grand total (base + additional).
     */
    public function grandTotal(): float
    {
        return (float) ($this->base_amount + $this->additional_amount);
    }

    /**
     * Get human-friendly Indonesian status label.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PESANAN_BARU => 'Pesanan Masuk',
            self::STATUS_DIJADWALKAN => 'Dijadwalkan',
            self::STATUS_DIKERJAKAN => 'Sedang Dikerjakan',
            self::STATUS_MENUNGGU_BAYAR => 'Menunggu Pembayaran',
            self::STATUS_SELESAI => 'Selesai & Lunas',
            self::STATUS_BATAL => 'Dibatalkan',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    /**
     * Attributes cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'service_date' => 'date',
            'paid_at' => 'datetime',
            'base_amount' => 'decimal:2',
            'additional_amount' => 'decimal:2',
            'ac_count' => 'integer',
        ];
    }
}
