<?php

namespace App\Models;

use App\Enums\OperationSilkUnit;
use App\Enums\OperationStencil;
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
            'stencil' => OperationStencil::class,
            'silk_unit' => OperationSilkUnit::class,
            'printing_in_date' => 'date',
            'printing_out_date' => 'date',
            'service_1_in_date' => 'date',
            'service_1_out_date' => 'date',
            'service_2_in_date' => 'date',
            'service_2_out_date' => 'date',
            'service_3_in_date' => 'date',
            'service_3_out_date' => 'date',
            'service_4_in_date' => 'date',
            'service_4_out_date' => 'date',
            'entry_date' => 'date',
            'exit_date' => 'date',
        ];
    }

    public function isOffset(): bool
    {
        return $this->operationType?->isOffset() ?? true;
    }

    public function isGeneral(): bool
    {
        return $this->operationType?->isGeneral() ?? false;
    }

    public function operationType(): BelongsTo
    {
        return $this->belongsTo(OperationType::class);
    }

    public function operationKind(): BelongsTo
    {
        return $this->belongsTo(OperationKind::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
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

    public function paperType(): BelongsTo
    {
        return $this->belongsTo(PaperType::class);
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

    public function service4(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_4_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(OperationLog::class)->latest();
    }

    public function movements(): HasMany
    {
        return $this->hasMany(OperationMovement::class);
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

    public function reportPaperDimension(): ?string
    {
        $name = $this->paperType?->name;

        if ($name && preg_match('/(\d+\*\d+)/', $name, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public function reportServicesLabel(): string
    {
        $services = collect([
            $this->service1?->name,
            $this->service2?->name,
            $this->service3?->name,
            $this->service4?->name,
        ])->filter()->values();

        return $services->isNotEmpty() ? $services->implode('+') : '';
    }

    public function reportTotalPullQuantity(): ?int
    {
        if ($this->pull_count === null || $this->quantity_per_sheet === null) {
            return null;
        }

        return (int) $this->pull_count * (int) $this->quantity_per_sheet;
    }

    public static function nextOperationNumber(OperationType $type): string
    {
        $prefix = $type->serial_prefix;
        $pattern = '/^' . preg_quote($prefix, '/') . '(\d+)$/i';

        $max = static::query()
            ->pluck('operation_number')
            ->map(function (string $number) use ($pattern) {
                if (preg_match($pattern, $number, $matches)) {
                    return (int) $matches[1];
                }

                return null;
            })
            ->filter()
            ->max();

        return $prefix . (($max ?? 0) + 1);
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

    public function assignedServiceIds(): array
    {
        return collect([
            $this->service_1_id,
            $this->service_2_id,
            $this->service_3_id,
            $this->service_4_id,
        ])->filter()->map(fn($val) => (int) $val)->values()->all();
    }

    public function hasEntryMovementForStatus(int $statusId): bool
    {
        return $this->movements()
            ->where('operation_status_id', $statusId)
            ->where('type', 'entry')
            ->exists();
    }
}
