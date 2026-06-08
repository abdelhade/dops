<?php

namespace App\Models;

use App\Models\Concerns\PreventsDeletionWhenRelated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaperSize extends Model implements PreventsDeletionWhenRelated
{
    protected $guarded = [];

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function hasRelatedRecords(): bool
    {
        return $this->items()->exists();
    }
}
