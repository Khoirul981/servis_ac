<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['masjid_id', 'room_name', 'ac_type', 'brand'])]
class AcUnit extends Model
{
    use HasFactory;

    /**
     * Get the masjid that owns the AC unit.
     *
     * @return BelongsTo<Masjid, $this>
     */
    public function masjid(): BelongsTo
    {
        return $this->belongsTo(Masjid::class);
    }
}
