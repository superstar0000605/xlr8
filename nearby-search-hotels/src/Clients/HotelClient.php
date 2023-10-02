<?php

namespace Star\HotelFinder\Clients;

use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;

class HotelClient
{
    protected array $sources;

    public function __construct(array $sources)
    {
        $this->sources = $sources;
    }

    /**
     * Consolidate and return hotels data from multiple sources.
     *
     * @return Collection
     */
    public function getHotels(): Collection
    {
        $hotels = collect();
        $responses = Http::pool(fn (Pool $pool) => collect($this->sources)->map(fn ($source) => $pool->get($source)));

        foreach ($responses as $response) {
            if ($response->successful()) {
                $data = $response->json();
                if ($data['success']) {
                    foreach ($data['message'] as $hotelData) {
                        $hotels->push([
                            'name' => $hotelData[0],
                            'latitude' => $hotelData[1],
                            'longitude' => $hotelData[2],
                            'price_per_night' => $hotelData[3]
                        ]);
                    }
                }
            }
        }

        return $hotels;
    }
}
