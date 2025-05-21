<?php

namespace App\DTO;

class CurrentWeather
{
    public function __construct(
        public string $city,
        public int    $timezone,
        public int    $dt,
        public float  $temp,
        public float  $feelsLike,
        public int    $humidity,
        public string $description,
        public string $icon,
    ) {}
}