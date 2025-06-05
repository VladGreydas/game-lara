<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CargoWagon extends Model
{
    protected $fillable = ['wagon_id', 'capacity'];

    public function wagon(): BelongsTo
    {
        return $this->belongsTo(Wagon::class);
    }
}
