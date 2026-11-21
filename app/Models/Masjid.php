<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'address', 'pic_name', 'pic_phone', 'last_service_date'])]
class Masjid extends Model
{
    use HasFactory;

    /**
     * Get the AC units owned by this masjid.
     *
     * @return HasMany<AcUnit, $this>
     */
    public function acUnits(): HasMany
    {
        return $this->hasMany(AcUnit::class);
    }

    /**
     * Get all service orders for this masjid.
     *
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Attributes cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_service_date' => 'date',
        ];
    }
}
