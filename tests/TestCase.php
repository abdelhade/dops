<?php

namespace Tests;

use App\Models\AppSetting;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    protected string $deletePassword = 'test-delete-pass';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedDeletePasswordForTests();
    }

    protected function seedDeletePasswordForTests(): void
    {
        try {
            if (Schema::hasTable('app_settings')) {
                AppSetting::setDeletePassword($this->deletePassword);
            }
        } catch (\Throwable) {
            //
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function withDeletePassword(array $data = []): array
    {
        return array_merge($data, ['delete_password' => $this->deletePassword]);
    }
}
