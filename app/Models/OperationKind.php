<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\PreventsDeletionWhenRelated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OperationKind extends Model implements PreventsDeletionWhenRelated
{
    protected $fillable = [
        'name',
        'sort_order',
        'description',
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
