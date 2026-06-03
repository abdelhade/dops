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
        'description',
    ];

    public function operations(): HasMany
    {
        return $this->hasMany(Operation::class);
    }
}
