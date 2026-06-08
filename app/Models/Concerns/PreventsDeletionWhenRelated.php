<?php

namespace App\Models\Concerns;

interface PreventsDeletionWhenRelated
{
    public function hasRelatedRecords(): bool;
}
