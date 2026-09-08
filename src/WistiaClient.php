<?php

namespace JeffersonGoncalves\Wistia;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Wistia Data API v1 HTTP layer. Wraps the `api.wistia.com/v1` calls behind
 * a small static client, threading the API password as HTTP basic auth and
 * defensively returning null (rather than throwing) on network failures or
 * non-2xx responses so callers never have to wrap every call in a try/catch.
 */
class WistiaClient
{
    private const BASE_URL = 'https://api.wistia.com/v1';

    /**
     * @return array<string, mixed>|null
     */
    public static function account(): ?array
    {
        return self::json(self::get('/account.json'));
    }

    /**
     * @param  array<string, mixed>  $params  Additional filters: page, per_page, sort_by, sort_direction.
     * @return list<array<string, mixed>>|null
     */
    public static function projects(array $params = []): ?array
    {
        return self::list(self::get('/projects.json', $params));
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function project(string $id): ?array
    {
        return self::json(self::get("/projects/{$id}.json"));
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function createProject(string $name, ?bool $public = null, ?string $description = null): ?array
    {
        return self::json(self::post('/projects.json', [
            'name' => $name,
            'public' => $public,
            'description' => $description,
        ]));
    }

    /**
     * @param  array<string, mixed>  $attributes  Any writable field: name, anonymousCanUpload, public, ...
     * @return array<string, mixed>|null
     */
    public static function updateProject(string $id, array $attributes): ?array
    {
        return self::json(self::put("/projects/{$id}.json", $attributes));
    }

    public static function deleteProject(string $id): bool
    {
        $response = self::delete("/projects/{$id}.json");

        return $response !== null && $response->successful();
    }

    /**
     * @param  array<string, mixed>  $params  Additional filters: page, per_page, project_id, name, type.
     * @return list<array<string, mixed>>|null
     */
    public static function medias(array $params = []): ?array
    {
        return self::list(self::get('/medias.json', $params));
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function media(string $id): ?array
    {
        return self::json(self::get("/medias/{$id}.json"));
    }

    /**
     * @param  array<string, mixed>  $attributes  Any writable field: name, description, ...
     * @return array<string, mixed>|null
     */
    public static function updateMedia(string $id, array $attributes): ?array
    {
        return self::json(self::put("/medias/{$id}.json", $attributes));
    }

    public static function deleteMedia(string $id): bool
    {
        $response = self::delete("/medias/{$id}.json");

        return $response !== null && $response->successful();
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function copyMedia(string $id, ?string $targetProjectId = null): ?array
    {
        return self::json(self::post("/medias/{$id}/copy.json", [
            'project_id' => $targetProjectId,
        ]));
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function mediaStats(string $id): ?array
    {
        return self::json(self::get("/medias/{$id}/stats.json"));
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function accountStats(): ?array
    {
        return self::json(self::get('/stats/account.json'));
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function projectStats(string $id): ?array
    {
        return self::json(self::get("/stats/projects/{$id}.json"));
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function mediaStatsByDate(string $id, ?string $startDate = null, ?string $endDate = null): ?array
    {
        return self::json(self::get("/stats/medias/{$id}/by_date.json", [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]));
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function mediaEngagement(string $id): ?array
    {
        return self::json(self::get("/stats/medias/{$id}/engagement.json"));
    }

    /**
     * @param  array<string, mixed>  $params  Additional filters: page, per_page, search.
     * @return list<array<string, mixed>>|null
     */
    public static function visitors(array $params = []): ?array
    {
        return self::list(self::get('/stats/visitors.json', $params));
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function visitor(string $key): ?array
    {
        return self::json(self::get("/stats/visitors/{$key}.json"));
    }

    /**
     * @param  array<string, mixed>  $params  Additional filters: page, per_page, media_id.
     * @return list<array<string, mixed>>|null
     */
    public static function events(array $params = []): ?array
    {
        return self::list(self::get('/stats/events.json', $params));
    }

    /**
     * @return list<array<string, mixed>>|null
     */
    public static function captions(string $mediaId): ?array
    {
        return self::list(self::get("/medias/{$mediaId}/captions.json"));
    }

    /**
     * @param  string|null  $captionFile  Raw SRT contents, not a path.
     * @return array<string, mixed>|null
     */
    public static function createCaption(string $mediaId, string $language, ?string $captionFile = null): ?array
    {
        return self::json(self::post("/medias/{$mediaId}/captions.json", [
            'language' => $language,
            'caption_file' => $captionFile,
        ]));
    }

    /**
     * @param  array<string, mixed>  $query
     */
    private static function get(string $path, array $query = []): ?Response
    {
        try {
            return self::client()->get(self::BASE_URL.$path, self::filter($query));
        } catch (Throwable $e) {
            self::logFailure('get', $path, $e);

            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private static function post(string $path, array $body = []): ?Response
    {
        try {
            return self::client()->post(self::BASE_URL.$path, self::filter($body));
        } catch (Throwable $e) {
            self::logFailure('post', $path, $e);

            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private static function put(string $path, array $body = []): ?Response
    {
        try {
            return self::client()->put(self::BASE_URL.$path, self::filter($body));
        } catch (Throwable $e) {
            self::logFailure('put', $path, $e);

            return null;
        }
    }

    private static function delete(string $path): ?Response
    {
        try {
            return self::client()->delete(self::BASE_URL.$path);
        } catch (Throwable $e) {
            self::logFailure('delete', $path, $e);

            return null;
        }
    }

    private static function client(): PendingRequest
    {
        $request = Http::timeout(self::timeout());

        if ($token = self::token()) {
            // Wistia accepts the API password as the basic-auth username with an empty password.
            $request = $request->withBasicAuth($token, '');
        }

        return $request;
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function json(?Response $response): ?array
    {
        if ($response === null || ! $response->successful()) {
            return null;
        }

        $data = $response->json();

        return is_array($data) ? $data : null;
    }

    /**
     * Wistia's collection endpoints return a bare JSON array rather than an envelope.
     *
     * @return list<array<string, mixed>>|null
     */
    private static function list(?Response $response): ?array
    {
        if ($response === null || ! $response->successful()) {
            return null;
        }

        $data = $response->json();

        return is_array($data) ? array_values($data) : null;
    }

    /**
     * Drop null values from a query/body payload before it's sent.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    private static function filter(array $params): array
    {
        return array_filter($params, fn (mixed $value): bool => $value !== null);
    }

    private static function logFailure(string $context, string $target, Throwable $e): void
    {
        Log::warning('WistiaClient outbound fetch failed', [
            'context' => $context,
            'target' => $target,
            'exception' => $e::class,
            'message' => $e->getMessage(),
        ]);
    }

    private static function token(): ?string
    {
        $token = config('wistia.token') ?? config('services.wistia.token');

        return is_string($token) && $token !== '' ? $token : null;
    }

    private static function timeout(): int
    {
        return (int) config('wistia.timeout', 8);
    }
}
