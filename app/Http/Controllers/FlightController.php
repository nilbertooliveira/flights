<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class FlightController extends Controller
{

    protected $url_api_flights;

    public function __construct()
    {
        $this->url_api_flights = env("URL_API_FLIGHTS");
        $this->middleware('auth:api');
    }
    /**
     * @OA\Get(
     *     path="/api/flights",
     *     tags={"Flights"},
     *     summary="Flights",
     *     operationId="flights",
     *     @OA\Response(
     *         response=200,
     *         description="Success"
     *     ),
     *     security={
     *         {"Bearer": {}}
     *     }
     * )
     */

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $flights = array();
        $flights['flights'] = Http::get($this->url_api_flights)->json();

        foreach ($flights['flights'] as $key => $f) {
            if (!isset($flights['groups'][$f['fare']])) {
                $flights['groups'][$f['fare']] = array('uniqueId' => $f['fare'], 'totalPrice' => 0, 'inbound' => array(), 'outbound' => array());
            }
            if ($f['inbound'] == 1) {
                $flights['groups'][$f['fare']]['inbound'][] = $f;
            } else {
                $flights['groups'][$f['fare']]['outbound'][] = $f;
            }
        }
        $flights = $this->orderFlightsByPrice($flights);

        $flights = $this->orderByGroups($flights);

        $flights['totalGroups'] = count($flights['groups']);
        $flights['totalFlights'] = count($flights['flights']);
        $flights['cheapestPrice'] = $flights['groups'][array_key_first($flights['groups'])]['totalPrice'];
        $flights['cheapestGroup'] = array_key_first($flights['groups']);

        return response()->json($flights);
    }

    /**
     * Ordena os voos pelo menor preço
     *
     * @param array $flights
     * @return $flights
     */
    private function orderFlightsByPrice(array $flights)
    {
        foreach ($flights['groups'] as $key => $f) {
            $collection = collect($f['inbound']);
            $sorted = $collection->sortBy(function ($flight, $key) {
                return $flight['price'];
            });

            $flights['groups'][$key]['inbound'] = $sorted->all();

            $collection = collect($f['outbound']);
            $sorted = $collection->sortBy(function ($flight, $key) {
                return $flight['price'];
            });
            $flights['groups'][$key]['outbound'] = $sorted->all();

            //Para efeito desta api peguei o primeiro voo fixo de ida e retorno
            $flights['groups'][$key]['totalPrice'] = $f['inbound'][0]['price'] + $f['outbound'][0]['price'];
        }
        return $flights;
    }

    /**
     * Ordena os grupos pelo menor preço
     *
     * @param array $flights
     * @return $flights
     */
    private function orderByGroups(array $flights)
    {
        $collection = collect($flights['groups']);
        $sorted = $collection->sortBy(function ($flight, $key) {
            return $flight['totalPrice'];
        });
        $flights['groups'] = $sorted->all();

        return $flights;
    }
}
