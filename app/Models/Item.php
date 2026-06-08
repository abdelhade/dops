<?php

namespace App\Models;

use App\Models\Concerns\PreventsDeletionWhenRelated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model implements PreventsDeletionWhenRelated
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

    public function assignedOperations(): HasMany
    {
        return $this->hasMany(Operation::class);
    }

    public function hasRelatedRecords(): bool
    {
        return $this->operations()->exists()
            || $this->assignedOperations()->exists();
    }
}
