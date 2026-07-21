<?php

namespace App\Models;

use App\Models\Concerns\PreventsDeletionWhenRelated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OperationStatus extends Model implements PreventsDeletionWhenRelated
{
    protected $fillable = [
        'name',
        'color',
        'sort_order',
        'days',
        'is_end',
        'is_phase',
        'description',
    ];

    protected $casts = [
        'is_end' => 'boolean',
        'is_phase' => 'boolean',
    ];

    public function operations(): HasMany
    {
        return $this->hasMany(Operation::class);
    }

    public function hasRelatedRecords(): bool
    {
        return $this->operations()->exists();
    }
}
