<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Item extends Model
{
    protected $guarded = [];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function paperSize(): BelongsTo
    {
        return $this->belongsTo(PaperSize::class, 'paper_size_id');
    }

    public function operations(): BelongsToMany
    {
        return $this->belongsToMany(Operation::class, 'operation_items')
                    ->withPivot('quantity', 'unit_price', 'notes')
                    ->withTimestamps();
    }
}
