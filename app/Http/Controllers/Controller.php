<?php

namespace App\Http\Controllers;

use App\Models\Concerns\PreventsDeletionWhenRelated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;

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

    /**
     * @param  class-string<Model>  $modelClass
     * @return array{deleted: int, skipped: int, deleted_ids: list<int>}
     */
    protected function bulkDestroyRecords(string $modelClass, array $ids): array
    {
        $this->authorizeDelete();

        $deleted = 0;
        $skipped = 0;
        $deletedIds = [];

        /** @var Collection<int, Model&PreventsDeletionWhenRelated|null> $records */
        $records = $modelClass::query()->whereIn('id', $ids)->get();

        foreach ($records as $record) {
            if ($record instanceof PreventsDeletionWhenRelated && $record->hasRelatedRecords()) {
                $skipped++;

                continue;
            }

            $record->delete();
            $deleted++;
            $deletedIds[] = (int) $record->getKey();
        }

        return [
            'deleted' => $deleted,
            'skipped' => $skipped,
            'deleted_ids' => $deletedIds,
        ];
    }
}
