<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $configCache = __DIR__ . '/../bootstrap/cache/config.php';
        if (file_exists($configCache)) {
            @unlink($configCache);
        }

        $envTesting = __DIR__ . '/../.env-testing';
        $envDotTesting = __DIR__ . '/../.env.testing';

        if (file_exists($envTesting)) {
            copy($envTesting, $envDotTesting);
        }

        $app = require __DIR__ . '/../bootstrap/app.php';

        if (file_exists($envTesting)) {
            $app->loadEnvironmentFrom('.env-testing');
        } elseif (file_exists($envDotTesting)) {
            $app->loadEnvironmentFrom('.env.testing');
        }

        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }
}
