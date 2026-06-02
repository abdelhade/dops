<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller
{
    use AuthorizesRequests, ValidatesRequests;

    protected function authorizeCreate(): void
    {
        if (! auth()->user()?->canCreateRecords()) {
            abort(403, __('dobs.unauthorized_action'));
        }
    }

    protected function authorizeEdit(): void
    {
        if (! auth()->user()?->canEditRecords()) {
            abort(403, __('dobs.unauthorized_action'));
        }
    }

    protected function authorizeDelete(): void
    {
        if (! auth()->user()?->canDeleteRecords()) {
            abort(403, __('dobs.unauthorized_action'));
        }
    }

    protected function authorizeManageUsers(): void
    {
        if (! auth()->user()?->canManageUsers()) {
            abort(403, __('dobs.unauthorized_action'));
        }
    }
}
