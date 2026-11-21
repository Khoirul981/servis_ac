<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['order_id', 'photo_url', 'photo_type', 'caption'])]
class OrderPhoto extends Model
{
    use HasFactory;

    public const TYPE_SEBELUM = 'sebelum';
    public const TYPE_SESUDAH = 'sesudah';
    public const TYPE_SPAREPART = 'sparepart';

    /**
     * Get the order that owns this photo.
     *
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get full public URL of the photo.
     */
    public function getUrlAttribute(): string
    {
        if (str_starts_with($this->photo_url, 'http://') || str_starts_with($this->photo_url, 'https://')) {
            return $this->photo_url;
        }

        return Storage::url($this->photo_url);
    }
}
