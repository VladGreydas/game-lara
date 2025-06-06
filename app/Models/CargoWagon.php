<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CargoWagon extends Model
{
    protected $fillable = ['wagon_id', 'capacity'];

    public function incrementEach(array $fields, int $lvl): void
    {
        foreach ($fields as $field => $valuePerLvl) {
            $this->increment($field, $valuePerLvl * $lvl);
        }
    }

    public function wagon(): BelongsTo
    {
        return $this->belongsTo(Wagon::class);
    }
}
