<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Operation extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'operation_date' => 'date',
            'job_size' => 'decimal:2',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function printingSupplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'printing_supplier_id');
    }

    public function ctpSupplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'ctp_supplier_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function service1(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_1_id');
    }

    public function service2(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_2_id');
    }

    public function service3(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_3_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(OperationLog::class)->latest();
    }

    public function operationStatus(): BelongsTo
    {
        return $this->belongsTo(OperationStatus::class);
    }

    /** @deprecated Legacy multi-item pivot; kept for older records. */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'operation_items')
            ->withPivot('quantity', 'unit_price', 'notes')
            ->withTimestamps();
    }

    public function formattedOperationTime(): ?string
    {
        if (! $this->operation_time) {
            return null;
        }

        $time = is_string($this->operation_time)
            ? $this->operation_time
            : $this->operation_time->format('H:i:s');

        return substr($time, 0, 5);
    }
}
