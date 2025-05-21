<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ForecastResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        // Pull “city” no‑matter whether $this is an object or array
        $city = $this->city
            ?? Arr::get($this->resource, 'city')
            ?? Arr::get($this->resource, 0 . '.city')  // last‑ditch if nested
            ?? null;

        /**
         * “Slices” can arrive in a few ways:
         *  1. As `$this->slices` on a DTO.
         *  2. As ['slices' => [...] ] inside a raw array.
         *  3. As the resource payload itself (already a flat list).
         */
        $slicesRaw = $this->slices
            ?? Arr::get($this->resource, 'slices')
            ?? $this->resource;

        // Make sure we hand an iterable collection to CurrentWeatherResource
        $slices = CurrentWeatherResource::collection(
            $slicesRaw instanceof Collection ? $slicesRaw : collect($slicesRaw)
        )->map(fn($r) => $r->toArray($request)); // Unwrap

        return [
            'city'   => $city,
            'slices' => $slices,
        ];
    }
}
