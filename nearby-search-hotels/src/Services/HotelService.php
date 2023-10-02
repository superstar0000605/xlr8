<?php

namespace Star\HotelFinder\Services;

use Star\HotelFinder\Clients\HotelClient;
use Illuminate\Support\Collection;

class HotelService
{
    protected $hotelClient;

    public function __construct(HotelClient $hotelClient)
    {
        $this->hotelClient = $hotelClient;
    }

    /**
     * Retrieve nearby hotels and return sorted as per user preference.
     *
     * @param $latitude
     * @param $longitude
     * @param $orderby
     * @return Collection
     */
    public function getNearbyHotels($latitude, $longitude, $orderby): Collection
    {
        $hotels = $this->hotelClient->getHotels();
        $preparedHotels = $hotels->map(function ($hotel) use ($latitude, $longitude) {
            $distance = $this->calculateDistance($latitude, $longitude, $hotel['latitude'], $hotel['longitude']);
            return [
                'name' => $hotel['name'],
                'distance' => number_format($distance, 2),
                'price_per_night' => number_format($hotel['price_per_night'], 2)
            ];
        });
        $sortedHotels = ($orderby == 'pricepernight')
            ? $preparedHotels->sortBy('price_per_night')
            : $preparedHotels->sortBy('distance');
        return $sortedHotels->values();
    }

    /**
     * Calculate distance between two geo-locations.
     *
     * @param $latitudeFrom
     * @param $longitudeFrom
     * @param $latitudeTo
     * @param $longitudeTo
     * @return float
     */
    private function calculateDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo): float
    {
        $earthRadius = 6371; // Earth radius in KM

        $latitudeFrom = floatval($latitudeFrom);
        $longitudeFrom = floatval($longitudeFrom);
        $latitudeTo = floatval($latitudeTo);
        $longitudeTo = floatval($longitudeTo);

        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }
}
