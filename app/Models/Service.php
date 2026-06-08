<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\PreventsDeletionWhenRelated;
use Illuminate\Database\Eloquent\Model;

class Service extends Model implements PreventsDeletionWhenRelated
{
    protected $guarded = [];

    public function hasRelatedRecords(): bool
    {
        return Operation::query()
            ->where(function ($query) {
                $query->where('service_1_id', $this->id)
                    ->orWhere('service_2_id', $this->id)
                    ->orWhere('service_3_id', $this->id);
            })
            ->exists();
    }
}
