<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OperationStatus extends Model
{
    protected $fillable = [
        'name',
        'color',
        'sort_order',
        'days',
        'is_end',
        'description',
    ];

    protected $casts = [
        'is_end' => 'boolean',
    ];

    public function operations(): HasMany
    {
        return $this->hasMany(Operation::class);
    }
}
