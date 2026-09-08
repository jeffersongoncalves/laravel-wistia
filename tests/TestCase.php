<?php

namespace Jeffersongoncalves\Wistia\Tests;

use Jeffersongoncalves\Wistia\WistiaServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            WistiaServiceProvider::class,
        ];
    }
}
