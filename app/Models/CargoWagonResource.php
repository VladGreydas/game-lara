<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Додайте цей імпорт
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $cargo_wagon_id
 * @property int $resource_id
 * @property int $quantity
 * @property-read CargoWagon $cargoWagon
 * @property-read Resource $resource
 */
class CargoWagonResource extends Model
{
    use HasFactory;

    protected $table = 'cargo_wagon_resources'; // Явно вказуємо назву таблиці

    protected $fillable = [
        'cargo_wagon_id',
        'resource_id',
        'quantity',
    ];

    /**
     * Get the cargo wagon that owns the resource record.
     */
    public function cargoWagon(): BelongsTo
    {
        return $this->belongsTo(CargoWagon::class);
    }

    /**
     * Get the resource definition.
     */
    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }
}
