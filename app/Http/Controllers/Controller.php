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

    protected function getResourceName(): ?string
    {
        $className = class_basename($this);
        $name = str_replace('Controller', '', $className);
        $kebab = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $name));
        
        if ($kebab === 'category') return 'categories';
        if ($kebab === 'supplier') return 'suppliers';
        if ($kebab === 'paper-size') return 'paper-sizes';
        if ($kebab === 'client') return 'clients';
        if ($kebab === 'item') return 'items';
        if ($kebab === 'material') return 'materials';
        if ($kebab === 'paper-type') return 'paper-types';
        if ($kebab === 'service') return 'services';
        if ($kebab === 'stage') return 'stages';
        if ($kebab === 'user') return 'users';
        if ($kebab === 'operation') return 'operations';
        if ($kebab === 'operation-movement') return 'operation-movements';
        if ($kebab === 'operation-status') return 'operation-statuses';
        if ($kebab === 'operation-type') return 'operation-types';
        if ($kebab === 'operation-kind') return 'operation-kinds';

        return $kebab;
    }

    protected function authorizeRead(): void
    {
        $user = auth()->user();
        if (! $user) {
            abort(403, __('dobs.unauthorized_action'));
        }

        $resource = $this->getResourceName();
        if ($resource && $user->hasPermission($resource, 'read')) {
            return;
        }

        if ($user->isDataEntry() && ! in_array($resource, ['operations', 'operation-movements'], true)) {
            abort(403, __('dobs.unauthorized_action'));
        }
    }

    protected function authorizeCreate(): void
    {
        $user = auth()->user();
        if (! $user) {
            abort(403, __('dobs.unauthorized_action'));
        }

        $resource = $this->getResourceName();
        if ($resource && $user->hasPermission($resource, 'create')) {
            return;
        }

        if (! $user->canCreateRecords()) {
            abort(403, __('dobs.unauthorized_action'));
        }
    }

    protected function authorizeEdit(): void
    {
        $user = auth()->user();
        if (! $user) {
            abort(403, __('dobs.unauthorized_action'));
        }

        $resource = $this->getResourceName();
        if ($resource && $user->hasPermission($resource, 'update')) {
            return;
        }

        if (! $user->canEditRecords()) {
            abort(403, __('dobs.unauthorized_action'));
        }
    }

    protected function authorizeDelete(): void
    {
        $user = auth()->user();
        if (! $user) {
            abort(403, __('dobs.unauthorized_action'));
        }

        $resource = $this->getResourceName();
        if ($resource && $user->hasPermission($resource, 'delete')) {
            return;
        }

        if (! $user->canDeleteRecords()) {
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
