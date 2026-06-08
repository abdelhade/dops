<?php

namespace App\Models;

use App\Models\Concerns\PreventsDeletionWhenRelated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model implements PreventsDeletionWhenRelated
{
    protected $guarded = [];

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function printingOperations(): HasMany
    {
        return $this->hasMany(Operation::class, 'printing_supplier_id');
    }

    public function ctpOperations(): HasMany
    {
        return $this->hasMany(Operation::class, 'ctp_supplier_id');
    }

    public function hasRelatedRecords(): bool
    {
        return $this->items()->exists()
            || $this->printingOperations()->exists()
            || $this->ctpOperations()->exists();
    }
}
