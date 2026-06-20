<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class AppSetting extends Model
{
    public const KEY_DELETE_PASSWORD_HASH = 'delete_password_hash';
    public const KEY_MAIL_HOST = 'mail_host';
    public const KEY_MAIL_PORT = 'mail_port';
    public const KEY_MAIL_USERNAME = 'mail_username';
    public const KEY_MAIL_PASSWORD = 'mail_password';
    public const KEY_MAIL_ENCRYPTION = 'mail_encryption';
    public const KEY_MAIL_FROM_ADDRESS = 'mail_from_address';
    public const KEY_MAIL_FROM_NAME = 'mail_from_name';

    private const CACHE_KEY = 'app_settings_all';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'value',
    ];

    public static function get(string $key, ?string $default = null): ?string
    {
        $settings = static::cachedAll();

        return $settings[$key] ?? $default;
    }

    public static function set(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        Cache::forget(static::CACHE_KEY);
    }

    public static function setDeletePassword(string $password): void
    {
        static::set(static::KEY_DELETE_PASSWORD_HASH, Hash::make($password));
    }

    public static function isDeletePasswordConfigured(): bool
    {
        return filled(static::get(static::KEY_DELETE_PASSWORD_HASH));
    }

    public static function verifyDeletePassword(?string $password): bool
    {
        $hash = static::get(static::KEY_DELETE_PASSWORD_HASH);

        if (! $hash || ! is_string($password) || $password === '') {
            return false;
        }

        return Hash::check($password, $hash);
    }

    /**
     * @return array<string, string|null>
     */
    private static function cachedAll(): array
    {
        /** @var array<string, string|null> $settings */
        $settings = Cache::rememberForever(static::CACHE_KEY, function (): array {
            return static::query()
                ->pluck('value', 'key')
                ->all();
        });

        return $settings;
    }
}
