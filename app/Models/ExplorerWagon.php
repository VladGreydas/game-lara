<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property $id
 * @property $wagon_id
 * @property Wagon $wagon
 */
class ExplorerWagon extends Model
{
    protected $fillable = ['wagon_id'];

    public function wagon(): BelongsTo
    {
        return $this->belongsTo(Wagon::class);
    }
}
