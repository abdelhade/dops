<?php

namespace App\Http\Controllers;

use App\Models\Concerns\PreventsDeletionWhenRelated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\RedirectResponse;
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

    protected function destroyRecord(Model $model, string $indexRoute, string $flashDeletedKey): RedirectResponse
    {
        $this->authorizeDelete();

        if ($model instanceof PreventsDeletionWhenRelated && $model->hasRelatedRecords()) {
            return redirect()
                ->route($indexRoute)
                ->with('error', __('dobs.cannot_delete_has_related'));
        }

        $model->delete();

        return redirect()
            ->route($indexRoute)
            ->with('success', __($flashDeletedKey));
    }
}
