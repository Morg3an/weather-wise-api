<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use App\DTO\CurrentWeather;
use Illuminate\Support\Arr;

class OpenWeatherService
{
    /* --------------------  OpenWeather relative paths  -------------------- */
    private const GEO_PATH      = 'geo/1.0/direct';
    private const ONECALL_PATH  = 'data/3.0/onecall';

    /** Fallback when API omits `timezone_offset` (UTC) */
    private const DEFAULT_TZ_OFFSET = 0;

    private string $base;
    private string $key;
    private int    $ttl;   // cache lifetime (minutes)

    public function __construct()
    {
        $this->base = rtrim(config('services.openweather.base', 'https://api.openweathermap.org'), '/');
        $this->key  = config('services.openweather.key');
        $this->ttl  = (int) config('services.openweather.ttl', 10);
    }

    /* ====================================================================== */
    /*  PUBLIC API                                                            */
    /* ====================================================================== */

    /** Get *current* conditions for a city. */
    public function current(string $city): CurrentWeather
    {
        $coords = $this->geocode($city);

        $query = [
            'lat'     => $coords['lat'],
            'lon'     => $coords['lon'],
            'exclude' => 'minutely,hourly,alerts',
            'units'   => 'metric',
            'appid'   => $this->key,
        ];

        $raw = $this->oneCall($query);

        return $this->mapCurrent($city, $raw);
    }

    /** 8‑day forecast (array of `CurrentWeather` DTOs – one per day). */
    public function forecast(string $city): array
    {
        $coords = $this->geocode($city);

        $query = [
            'lat'     => $coords['lat'],
            'lon'     => $coords['lon'],
            'exclude' => 'minutely,alerts',
            'units'   => 'metric',
            'appid'   => $this->key,
        ];

        $raw = $this->oneCall($query);

        /* For each “daily” slice build a CurrentWeather‑shaped DTO so the
           API surface to controllers stays consistent.                                    */
        return collect($raw['daily'] ?? [])
            ->map(fn ($day) => $this->mapCurrent($city, [
                'timezone_offset' => $raw['timezone_offset'] ?? self::DEFAULT_TZ_OFFSET,
                'current'         => $day,
            ]))
            ->all();
    }

    /* ====================================================================== */
    /*  PRIVATE MAPPERS                                                       */
    /* ====================================================================== */

    private function mapCurrent(string $city, array $raw): CurrentWeather
    {
        // “current” slice when full One‑Call payload; otherwise `oneCall()` may
        // already have scoped to current.
        $c   = $raw['current'] ?? $raw;
        $w   = Arr::first($c['weather'] ?? []) ?? ['description' => '', 'icon' => ''];
        $tz  = (int) ($raw['timezone_offset'] ?? self::DEFAULT_TZ_OFFSET);

        return new CurrentWeather(
            city:         $city,
            timezone:     $tz,
            dt:           $c['dt']          ?? 0,
            temp:        (float) ($c['temp']        ?? 0),
            feelsLike:   (float) ($c['feels_like']  ?? 0),
            humidity:    (int)   ($c['humidity']    ?? 0),
            description:          $w['description'],
            icon:                 $w['icon'],
        );
    }

    /* ====================================================================== */
    /*  INTERNAL HELPERS                                                      */
    /* ====================================================================== */

    /** Resolve “City, Country” → latitude / longitude using OWM geo API. */
    private function geocode(string $city): array
    {
        return Cache::remember("geo:$city", $this->ttl * 60, function () use ($city) {
            $url  = $this->buildUrl(self::GEO_PATH);
            $resp = Http::timeout(10)->get($url, [
                'q'     => $city,
                'limit' => 1,
                'appid' => $this->key,
            ]);

            Log::info('OWM GEO → ' . $resp->effectiveUri(), [
                'status' => $resp->status(),
                'city'   => $city,
            ]);

            if ($resp->failed()) {
                Log::error('OWM GEO ERROR', ['body' => $resp->body()]);
                throw new RuntimeException("Geocoding failed ({$resp->status()})");
            }

            $json = $resp->json();
            Log::debug('OWM GEO body', $json);

            if (empty($json[0]['lat']) || empty($json[0]['lon'])) {
                Cache::put("geo:$city", null, 60); // brief negative‑cache
                throw new RuntimeException("Could not geocode city: $city");
            }

            return ['lat' => $json[0]['lat'], 'lon' => $json[0]['lon']];
        });
    }

    /** Raw One‑Call 3.0 request (with caching & logging). */
    private function oneCall(array $query): array
    {
        $cacheKey = 'onecall:' . md5(json_encode($query));

        return Cache::remember($cacheKey, $this->ttl * 60, function () use ($query) {
            $url  = $this->buildUrl(self::ONECALL_PATH);
            $resp = Http::timeout(10)->get($url, $query);

            Log::info('OWM ONECALL → ' . $resp->effectiveUri(), ['status' => $resp->status()]);

            if ($resp->failed()) {
                Log::error('OWM ONECALL ERROR', ['body' => $resp->body()]);
                throw new RuntimeException("One‑Call request failed ({$resp->status()})");
            }

            $json = $resp->json();
            Log::debug('OWM ONECALL body', $json);

            return $json;   // DTO layer will do further shaping
        });
    }

    /** Build a fully‑qualified OpenWeather URL from a documented relative path. */
    private function buildUrl(string $relativePath): string
    {
        return "{$this->base}/{$relativePath}";
    }
}