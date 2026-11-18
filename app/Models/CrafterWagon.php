<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property $id
 * @property $wagon_id
 * @property $craft_slots
 * @property Wagon $wagon
 */
class CrafterWagon extends Model
{
    protected $fillable = ['wagon_id', 'craft_slots'];

    public function upgradeFields(array $fields, int $lvl): void
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
