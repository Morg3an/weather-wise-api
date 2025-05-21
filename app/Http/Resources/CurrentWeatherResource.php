<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CurrentWeatherResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => [
                'city'  => $this->city,
                'slice' => [
                    'timestamp'   => $this->dt,
                    'temp'        => $this->temp,
                    'feelsLike'   => $this->feelsLike,
                    'humidity'    => $this->humidity,
                    'pressure'    => $this->pressure ?? null,
                    'windSpeed'   => $this->windSpeed ?? null,
                    'windDeg'     => $this->windDeg ?? null,
                    'summary'     => ucfirst($this->description),
                    'icon'        => $this->icon,
                ]
            ]
        ];
    }
}