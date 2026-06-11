<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OperationTypeMode;
use App\Models\Concerns\PreventsDeletionWhenRelated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OperationType extends Model implements PreventsDeletionWhenRelated
{
    protected $fillable = [
        'name',
        'slug',
        'form_mode',
        'serial_prefix',
        'sort_order',
        'is_system',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'form_mode' => OperationTypeMode::class,
            'is_system' => 'boolean',
        ];
    }

    public function operations(): HasMany
    {
        return $this->hasMany(Operation::class);
    }

    public function hasRelatedRecords(): bool
    {
        return $this->operations()->exists();
    }

    public function label(): string
    {
        return $this->name;
    }

    public function isOffset(): bool
    {
        return $this->form_mode === OperationTypeMode::Offset;
    }

    public function isGeneral(): bool
    {
        return $this->form_mode === OperationTypeMode::General;
    }

    public static function resolveFromRequest(?string $slug = null, ?int $id = null): self
    {
        if ($id) {
            return static::query()->findOrFail($id);
        }

        return static::query()
            ->where('slug', $slug ?: 'offset')
            ->first() ?? static::query()->where('slug', 'offset')->firstOrFail();
    }
}
