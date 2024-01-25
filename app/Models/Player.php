<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
