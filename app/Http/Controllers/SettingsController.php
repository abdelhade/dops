<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingsRequest;
use App\Models\AppSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        $this->authorizeManageUsers();

        return view('settings.edit', [
            'deletePasswordConfigured' => AppSetting::isDeletePasswordConfigured(),
        ]);
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        $password = $request->validated('delete_password');

        if (filled($password)) {
            AppSetting::setDeletePassword($password);
        }

        return redirect()
            ->route('settings.edit')
            ->with('success', __('dobs.flash_settings_updated'));
    }
}
