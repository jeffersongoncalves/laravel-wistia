<?php

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JeffersonGoncalves\Wistia\WistiaClient;

it('fetches the account', function () {
    Http::fake([
        'api.wistia.com/v1/account.json' => Http::response(['id' => 1, 'name' => 'Acme'], 200),
    ]);

    expect(WistiaClient::account())->toBe(['id' => 1, 'name' => 'Acme']);

    Http::assertSent(fn (Request $request) => $request->hasHeader(
        'Authorization',
        'Basic '.base64_encode('fake-token:'),
    ));
});

it('returns null from account on a non-2xx', function () {
    Http::fake([
        'api.wistia.com/v1/account.json' => Http::response('', 401),
    ]);

    expect(WistiaClient::account())->toBeNull();
});

it('lists projects', function () {
    Http::fake([
        'api.wistia.com/v1/projects.json*' => Http::response([['id' => 1], ['id' => 2]], 200),
    ]);

    expect(WistiaClient::projects(['per_page' => 5]))->toBe([['id' => 1], ['id' => 2]]);

    Http::assertSent(fn (Request $request) => str_contains($request->url(), 'per_page=5'));
});

it('fetches a single project', function () {
    Http::fake([
        'api.wistia.com/v1/projects/abc123.json' => Http::response(['hashedId' => 'abc123'], 200),
    ]);

    expect(WistiaClient::project('abc123'))->toBe(['hashedId' => 'abc123']);
});

it('creates a project without optional fields', function () {
    Http::fake([
        'api.wistia.com/v1/projects.json' => Http::response(['id' => 1], 201),
    ]);

    expect(WistiaClient::createProject('Onboarding'))->toBe(['id' => 1]);

    Http::assertSent(fn (Request $request) => $request->data() === ['name' => 'Onboarding']);
});

it('creates a public project with a description', function () {
    Http::fake([
        'api.wistia.com/v1/projects.json' => Http::response(['id' => 1], 201),
    ]);

    WistiaClient::createProject('Onboarding', true, 'Customer onboarding videos');

    Http::assertSent(fn (Request $request) => $request['public'] === true
        && $request['description'] === 'Customer onboarding videos');
});

it('updates a project', function () {
    Http::fake([
        'api.wistia.com/v1/projects/abc123.json' => Http::response(['hashedId' => 'abc123', 'name' => 'Renamed'], 200),
    ]);

    expect(WistiaClient::updateProject('abc123', ['name' => 'Renamed']))
        ->toBe(['hashedId' => 'abc123', 'name' => 'Renamed']);

    Http::assertSent(fn (Request $request) => $request->method() === 'PUT' && $request['name'] === 'Renamed');
});

it('deletes a project', function () {
    Http::fake([
        'api.wistia.com/v1/projects/abc123.json' => Http::response('', 200),
    ]);

    expect(WistiaClient::deleteProject('abc123'))->toBeTrue();
});

it('returns false when a project deletion fails', function () {
    Http::fake([
        'api.wistia.com/v1/projects/abc123.json' => Http::response('', 404),
    ]);

    expect(WistiaClient::deleteProject('abc123'))->toBeFalse();
});

it('lists medias filtered by project', function () {
    Http::fake([
        'api.wistia.com/v1/medias.json*' => Http::response([['hashed_id' => 'vid1']], 200),
    ]);

    expect(WistiaClient::medias(['project_id' => 'abc123']))->toBe([['hashed_id' => 'vid1']]);

    Http::assertSent(fn (Request $request) => str_contains($request->url(), 'project_id=abc123'));
});

it('fetches a single media', function () {
    Http::fake([
        'api.wistia.com/v1/medias/vid1.json' => Http::response(['hashed_id' => 'vid1'], 200),
    ]);

    expect(WistiaClient::media('vid1'))->toBe(['hashed_id' => 'vid1']);
});

it('updates a media', function () {
    Http::fake([
        'api.wistia.com/v1/medias/vid1.json' => Http::response(['hashed_id' => 'vid1'], 200),
    ]);

    WistiaClient::updateMedia('vid1', ['name' => 'Intro', 'description' => 'Welcome']);

    Http::assertSent(fn (Request $request) => $request->method() === 'PUT'
        && $request['name'] === 'Intro'
        && $request['description'] === 'Welcome');
});

it('deletes a media', function () {
    Http::fake([
        'api.wistia.com/v1/medias/vid1.json' => Http::response('', 200),
    ]);

    expect(WistiaClient::deleteMedia('vid1'))->toBeTrue();
});

it('copies a media into a target project', function () {
    Http::fake([
        'api.wistia.com/v1/medias/vid1/copy.json' => Http::response(['hashed_id' => 'vid2'], 201),
    ]);

    expect(WistiaClient::copyMedia('vid1', 'abc123'))->toBe(['hashed_id' => 'vid2']);

    Http::assertSent(fn (Request $request) => $request['project_id'] === 'abc123');
});

it('fetches media stats', function () {
    Http::fake([
        'api.wistia.com/v1/medias/vid1/stats.json' => Http::response(['play_count' => 10], 200),
    ]);

    expect(WistiaClient::mediaStats('vid1'))->toBe(['play_count' => 10]);
});

it('fetches account stats', function () {
    Http::fake([
        'api.wistia.com/v1/stats/account.json' => Http::response(['load_count' => 100], 200),
    ]);

    expect(WistiaClient::accountStats())->toBe(['load_count' => 100]);
});

it('fetches project stats', function () {
    Http::fake([
        'api.wistia.com/v1/stats/projects/abc123.json' => Http::response(['play_count' => 5], 200),
    ]);

    expect(WistiaClient::projectStats('abc123'))->toBe(['play_count' => 5]);
});

it('fetches media stats within a date window', function () {
    Http::fake([
        'api.wistia.com/v1/stats/medias/vid1/by_date.json*' => Http::response(['by_date' => []], 200),
    ]);

    expect(WistiaClient::mediaStatsByDate('vid1', '2026-09-01', '2026-09-07'))->toBe(['by_date' => []]);

    Http::assertSent(fn (Request $request) => str_contains($request->url(), 'start_date=2026-09-01')
        && str_contains($request->url(), 'end_date=2026-09-07'));
});

it('fetches media engagement', function () {
    Http::fake([
        'api.wistia.com/v1/stats/medias/vid1/engagement.json' => Http::response(['engagement' => []], 200),
    ]);

    expect(WistiaClient::mediaEngagement('vid1'))->toBe(['engagement' => []]);
});

it('lists visitors', function () {
    Http::fake([
        'api.wistia.com/v1/stats/visitors.json*' => Http::response([['visitor_key' => 'v1']], 200),
    ]);

    expect(WistiaClient::visitors(['search' => 'jane']))->toBe([['visitor_key' => 'v1']]);

    Http::assertSent(fn (Request $request) => str_contains($request->url(), 'search=jane'));
});

it('fetches a single visitor', function () {
    Http::fake([
        'api.wistia.com/v1/stats/visitors/v1.json' => Http::response(['visitor_key' => 'v1'], 200),
    ]);

    expect(WistiaClient::visitor('v1'))->toBe(['visitor_key' => 'v1']);
});

it('lists events', function () {
    Http::fake([
        'api.wistia.com/v1/stats/events.json*' => Http::response([['event_key' => 'e1']], 200),
    ]);

    expect(WistiaClient::events(['media_id' => 'vid1']))->toBe([['event_key' => 'e1']]);
});

it('lists captions for a media', function () {
    Http::fake([
        'api.wistia.com/v1/medias/vid1/captions.json' => Http::response([['language' => 'eng']], 200),
    ]);

    expect(WistiaClient::captions('vid1'))->toBe([['language' => 'eng']]);
});

it('creates a caption', function () {
    Http::fake([
        'api.wistia.com/v1/medias/vid1/captions.json' => Http::response(['language' => 'eng'], 201),
    ]);

    expect(WistiaClient::createCaption('vid1', 'eng', "1\n00:00:01,000 --> 00:00:02,000\nHi\n"))
        ->toBe(['language' => 'eng']);

    Http::assertSent(fn (Request $request) => $request['language'] === 'eng'
        && str_contains((string) $request['caption_file'], 'Hi'));
});

it('omits the caption file when none is given', function () {
    Http::fake([
        'api.wistia.com/v1/medias/vid1/captions.json' => Http::response(['language' => 'eng'], 201),
    ]);

    WistiaClient::createCaption('vid1', 'eng');

    Http::assertSent(fn (Request $request) => $request->data() === ['language' => 'eng']);
});

it('falls back to the services.wistia.token config value', function () {
    config()->set('wistia.token', null);
    config()->set('services.wistia.token', 'services-token');

    Http::fake([
        'api.wistia.com/v1/account.json' => Http::response(['id' => 1], 200),
    ]);

    WistiaClient::account();

    Http::assertSent(fn (Request $request) => $request->hasHeader(
        'Authorization',
        'Basic '.base64_encode('services-token:'),
    ));
});

it('returns null and logs when the request throws', function () {
    Log::spy();

    Http::fake(fn () => throw new ConnectionException('Connection timed out'));

    expect(WistiaClient::account())->toBeNull();

    Log::shouldHaveReceived('warning')
        ->withArgs(fn (string $message, array $context) => $message === 'WistiaClient outbound fetch failed'
            && $context['context'] === 'get')
        ->once();
});
