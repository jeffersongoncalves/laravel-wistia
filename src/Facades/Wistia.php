<?php

namespace Jeffersongoncalves\Wistia\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Jeffersongoncalves\Wistia\Wistia
 */
class Wistia extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-wistia';
    }
}
