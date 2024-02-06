<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Route extends Model
{
    use HasFactory;

    public function startTown(): BelongsTo
    {
        return $this->belongsTo(Town::class, 'start_town_id');
    }

    public function endTown(): BelongsTo
    {
        return $this->belongsTo(Town::class, 'end_town_id');
    }
}
