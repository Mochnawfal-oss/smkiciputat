<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if ($this->app['config']->get('database.connections.teachers.database') === ':memory:') {
            Artisan::call('migrate', [
                '--database' => 'teachers',
                '--path' => 'database/migrations/2026_11_06_192016_create_teachers_table.php',
                '--realpath' => true,
                '--force' => true,
            ]);
        }
    }
}
