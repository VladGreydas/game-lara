<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Player extends Model
{
    use HasFactory;

    protected $fillable = [
        'nickname',
        'money',
        'exp',
        'max_exp',
        'lvl'
    ];

    public function createTrain(): void
    {
        $this->train()->create();
        $this->train->firstCreation();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function train() : HasOne
    {
        return $this->hasOne(Train::class);
    }
}
