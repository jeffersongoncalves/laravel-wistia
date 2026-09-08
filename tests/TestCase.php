<?php

namespace JeffersonGoncalves\Wistia\Tests;

use JeffersonGoncalves\Wistia\WistiaServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            WistiaServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('wistia.token', 'fake-token');
        $app['config']->set('wistia.timeout', 5);
    }
}
