<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperationMovement extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'datetime' => 'datetime',
        ];
    }

    public function operation()
    {
        return $this->belongsTo(Operation::class);
    }

    public function operationStatus()
    {
        return $this->belongsTo(OperationStatus::class);
    }
}
