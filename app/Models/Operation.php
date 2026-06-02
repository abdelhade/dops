<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Operation extends Model
{
    protected $guarded = [];

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'operation_items')
                    ->withPivot('quantity', 'unit_price', 'notes')
                    ->withTimestamps();
    }
}
