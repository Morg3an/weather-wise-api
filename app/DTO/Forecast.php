<?php

namespace App\DTO;

class Forecast
{
    /** @param array<CurrentWeather> $slices */
    public function __construct(
        public string $city,
        public array  $slices,   // 3‑hour bins
    ) {}

    public static function fromApi(array $j): self
    {
        $city   = $j['city']['name'] ?? '‑';
        $items  = [];

        foreach ($j['list'] ?? [] as $slice) {
            $items[] = new CurrentWeather(
                city:        $city,
                timezone:    $j['city']['timezone'] ?? 0,
                dt:          $slice['dt'],
                temp:        $slice['main']['temp'],
                feelsLike:   $slice['main']['feels_like'],
                humidity:    $slice['main']['humidity'],
                description: $slice['weather'][0]['description'] ?? '',
                icon:        $slice['weather'][0]['icon'] ?? '',
            );
        }

        return new self($city, $items);
    }
}