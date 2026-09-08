<?php

use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\Wistia\Tests\TestCase;

uses(TestCase::class)
    ->beforeEach(fn () => Http::preventStrayRequests())
    ->in('Feature');
