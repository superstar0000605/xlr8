<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Star\HotelFinder\Services\HotelService;
use Star\HotelFinder\Clients\HotelClient;

class HotelController extends Controller
{
    protected $hotelService;

    public function __construct()
    {
        $sources = config('sources.hotels');
        $client = new HotelClient($sources);
        $hotelService = new HotelService($client);
        $this->hotelService = $hotelService;
    }

    /**
     * Handle the request to get nearby hotels.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNearbyHotels(Request $request)
    {
        try {
            $validated = $request->validate([
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'orderby' => 'sometimes|string|in:proximity,pricepernight',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 400);
        }

        $latitude = $validated['latitude'];
        $longitude = $validated['longitude'];
        $orderby = $validated['orderby'] ?? 'proximity';

        $hotels = $this->hotelService->getNearbyHotels($latitude, $longitude, $orderby);

        return response()->json($hotels);
    }
}
