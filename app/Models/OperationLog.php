<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationLog extends Model
{
    public const ACTION_CREATED = 'created';

    public const ACTION_UPDATED = 'updated';

    public const ACTION_STATUS_CHANGED = 'status_changed';

    public const ACTION_DELETED = 'deleted';

    protected $fillable = [
        'operation_id',
        'operation_number',
        'user_id',
        'action',
        'changes',
    ];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
        ];
    }

    public function operation(): BelongsTo
    {
        return $this->belongsTo(Operation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actionLabel(): string
    {
        return match ($this->action) {
            self::ACTION_CREATED => __('dobs.log_action_created'),
            self::ACTION_STATUS_CHANGED => __('dobs.log_action_status_changed'),
            self::ACTION_DELETED => __('dobs.log_action_deleted'),
            default => __('dobs.log_action_updated'),
        };
    }

    /**
     * @return list<array{field: string, from: string, to: string}>
     */
    public function changeEntries(): array
    {
        if (! in_array($this->action, [self::ACTION_UPDATED, self::ACTION_STATUS_CHANGED], true)) {
            return [];
        }

        $entries = [];

        foreach ($this->loggedChanges() as $field => $pair) {
            if (! is_array($pair) || ! array_key_exists('from', $pair) || ! array_key_exists('to', $pair)) {
                continue;
            }

            $entries[] = [
                'field' => __('dobs.log_field_'.$field),
                'from' => $this->formatValue($field, $pair['from']),
                'to' => $this->formatValue($field, $pair['to']),
            ];
        }

        return $entries;
    }

    /**
     * @return list<string>
     */
    public function changeLines(): array
    {
        if ($this->action === self::ACTION_CREATED) {
            return [__('dobs.log_operation_registered')];
        }

        if ($this->action === self::ACTION_DELETED) {
            $number = $this->operation_number ?? __('dobs.dash');

            return [__('dobs.log_operation_deleted_detail', ['number' => $number])];
        }

        $lines = [];

        foreach ($this->changeEntries() as $entry) {
            $lines[] = __('dobs.log_change_line', [
                'field' => $entry['field'],
                'from' => $entry['from'],
                'to' => $entry['to'],
            ]);
        }

        return $lines !== [] ? $lines : [__('dobs.log_no_field_details')];
    }

    /**
     * @return array<string, array{from: mixed, to: mixed}>
     */
    private function loggedChanges(): array
    {
        $changes = $this->getAttribute('changes');

        return is_array($changes) ? $changes : [];
    }

    private function formatValue(string $field, mixed $value): string
    {
        if ($value === null || $value === '') {
            return __('dobs.dash');
        }

        if (in_array($field, ['operation_status_id', 'client_id', 'item_id', 'printing_supplier_id', 'ctp_supplier_id', 'paper_type_id', 'material_id', 'service_1_id', 'service_2_id', 'service_3_id'], true)) {
            return $this->resolveRelationName($field, $value);
        }

        if ($field === 'operation_date' && $value) {
            try {
                return \Illuminate\Support\Carbon::parse($value)->format('Y-m-d');
            } catch (\Throwable) {
                return (string) $value;
            }
        }

        if ($field === 'operation_time' && is_string($value)) {
            return substr($value, 0, 5);
        }

        return (string) $value;
    }

    private function resolveRelationName(string $field, mixed $id): string
    {
        $id = (int) $id;

        return match ($field) {
            'operation_status_id' => OperationStatus::find($id)?->name ?? (string) $id,
            'client_id' => Client::find($id)?->name ?? (string) $id,
            'item_id' => Item::find($id)?->name ?? (string) $id,
            'printing_supplier_id', 'ctp_supplier_id' => Supplier::find($id)?->name ?? (string) $id,
            'paper_type_id' => PaperType::find($id)?->name ?? (string) $id,
            'material_id' => Material::find($id)?->name ?? (string) $id,
            'service_1_id', 'service_2_id', 'service_3_id' => Service::find($id)?->name ?? (string) $id,
            default => (string) $id,
        };
    }
}
