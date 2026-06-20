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
            'mailHost' => AppSetting::get(AppSetting::KEY_MAIL_HOST),
            'mailPort' => AppSetting::get(AppSetting::KEY_MAIL_PORT),
            'mailUsername' => AppSetting::get(AppSetting::KEY_MAIL_USERNAME),
            'mailPassword' => AppSetting::get(AppSetting::KEY_MAIL_PASSWORD),
            'mailEncryption' => AppSetting::get(AppSetting::KEY_MAIL_ENCRYPTION),
            'mailFromAddress' => AppSetting::get(AppSetting::KEY_MAIL_FROM_ADDRESS),
            'mailFromName' => AppSetting::get(AppSetting::KEY_MAIL_FROM_NAME),
        ]);
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        $password = $request->validated('delete_password');

        if (filled($password)) {
            AppSetting::setDeletePassword($password);
        }

        $mailFields = [
            AppSetting::KEY_MAIL_HOST => 'mail_host',
            AppSetting::KEY_MAIL_PORT => 'mail_port',
            AppSetting::KEY_MAIL_USERNAME => 'mail_username',
            AppSetting::KEY_MAIL_PASSWORD => 'mail_password',
            AppSetting::KEY_MAIL_ENCRYPTION => 'mail_encryption',
            AppSetting::KEY_MAIL_FROM_ADDRESS => 'mail_from_address',
            AppSetting::KEY_MAIL_FROM_NAME => 'mail_from_name',
        ];

        foreach ($mailFields as $key => $field) {
            if ($request->has($field)) {
                AppSetting::set($key, $request->input($field));
            }
        }

        return redirect()
            ->route('settings.edit')
            ->with('success', __('dobs.flash_settings_updated'));
    }
}
