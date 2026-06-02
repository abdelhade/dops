<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationItem extends Model
{
    protected $table = 'operation_items';
    protected $guarded = [];

    public function operation(): BelongsTo
    {
        return $this->belongsTo(Operation::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
