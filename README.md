<div class="filament-hidden">

![Laravel Wistia](https://raw.githubusercontent.com/jeffersongoncalves/laravel-wistia/main/art/jeffersongoncalves-laravel-wistia.png)

</div>

# Laravel Wistia

[![Tests](https://github.com/jeffersongoncalves/laravel-wistia/actions/workflows/tests.yml/badge.svg)](https://github.com/jeffersongoncalves/laravel-wistia/actions/workflows/tests.yml)
[![PHPStan](https://github.com/jeffersongoncalves/laravel-wistia/actions/workflows/phpstan.yml/badge.svg)](https://github.com/jeffersongoncalves/laravel-wistia/actions/workflows/phpstan.yml)
[![Code Style](https://github.com/jeffersongoncalves/laravel-wistia/actions/workflows/pint.yml/badge.svg)](https://github.com/jeffersongoncalves/laravel-wistia/actions/workflows/pint.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/jeffersongoncalves/laravel-wistia.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-wistia)
[![Total Downloads](https://img.shields.io/packagist/dt/jeffersongoncalves/laravel-wistia.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-wistia)
[![License](https://img.shields.io/packagist/l/jeffersongoncalves/laravel-wistia.svg?style=flat-square)](LICENSE.md)

A lightweight Wistia Data API client for Laravel. It wraps the `api.wistia.com` v1 endpoints behind a small static client, threads your API password as HTTP basic auth, and defensively returns `null` (rather than throwing) on network failures or non-2xx responses.

## Features

- **Account** — `account()`, `accountStats()`
- **Projects** — `projects()`, `project()`, `createProject()`, `updateProject()`, `deleteProject()`, `projectStats()`
- **Medias** — `medias()`, `media()`, `updateMedia()`, `deleteMedia()`, `copyMedia()`, `mediaStats()`
- **Stats** — `mediaStatsByDate()`, `mediaEngagement()`, `visitors()`, `visitor()`, `events()`
- **Captions** — `captions()`, `createCaption()`

## Installation

```bash
composer require jeffersongoncalves/laravel-wistia
```

Optionally publish the config file:

```bash
php artisan vendor:publish --tag="laravel-wistia-config"
```

## Configuration

Add to your `.env`:

```env
WISTIA_API_KEY=...
```

Create an API password from your [Wistia account API settings](https://my.wistia.com/account/api).

### Config Options

```php
// config/wistia.php
return [
    'token' => env('WISTIA_API_KEY'),
    'timeout' => (int) env('WISTIA_TIMEOUT', 8),
];
```

When `wistia.token` is null the client falls back to `config('services.wistia.token')`.

## Usage

```php
use JeffersonGoncalves\Wistia\WistiaClient;

// Account
$account = WistiaClient::account();
$accountStats = WistiaClient::accountStats();

// Projects
$projects = WistiaClient::projects(['per_page' => 50]);
$project = WistiaClient::project('abc123');
$created = WistiaClient::createProject('Onboarding', public: true, description: 'Customer onboarding videos');
WistiaClient::updateProject('abc123', ['name' => 'Onboarding 2026']);
WistiaClient::deleteProject('abc123');
$projectStats = WistiaClient::projectStats('abc123');

// Medias
$medias = WistiaClient::medias(['project_id' => 'abc123', 'type' => 'Video']);
$media = WistiaClient::media('vid1');
WistiaClient::updateMedia('vid1', ['name' => 'Intro', 'description' => 'Welcome']);
WistiaClient::copyMedia('vid1', targetProjectId: 'def456');
WistiaClient::deleteMedia('vid1');
$mediaStats = WistiaClient::mediaStats('vid1');

// Stats
$byDate = WistiaClient::mediaStatsByDate('vid1', startDate: '2026-09-01', endDate: '2026-09-07');
$engagement = WistiaClient::mediaEngagement('vid1');
$visitors = WistiaClient::visitors(['search' => 'jane', 'per_page' => 50]);
$visitor = WistiaClient::visitor('visitor_key');
$events = WistiaClient::events(['media_id' => 'vid1']);

// Captions
$captions = WistiaClient::captions('vid1');
WistiaClient::createCaption('vid1', 'eng', file_get_contents('subtitles.srt'));
```

Single-resource methods return `array<string, mixed>|null`, collection methods return `list<array<string, mixed>>|null`, and `deleteProject()`/`deleteMedia()` return `bool` — `null`/`false` on any network failure or non-2xx response instead of throwing. Null arguments are stripped from the payload before it is sent, so optional fields are simply omitted.

## Testing

```bash
composer test
```

## Static Analysis

```bash
composer analyse
```

## Code Formatting

```bash
composer format
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
