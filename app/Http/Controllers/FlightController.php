<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class FlightController extends Controller
{

    protected $url_api_flights;
    protected $groups;

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
     *     @OA\Response(
     *      response=401,
     *       description="Unauthenticated"
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
        $groups = $this->groupByPrice($flights);

        $this->setReturnGroups($groups, $flights);

        return response()->json($this->groups, Response::HTTP_OK);
    }

      /**
     * Agrupa os voos por preço
     *
     * @param array $flights
     * @return $flights
     */
    private function groupByPrice(array $flights)
    {
        $groups = array();
        $countGroup = 0;
        foreach ($flights['groups'] as $key => $f) {
            $collection = collect($f['inbound']);

            $inboundGroup = $collection->groupBy('price');

            $collection = collect($f['outbound']);
            $outboundGroup = $collection->groupBy('price');

            foreach ($inboundGroup as $in) {
                foreach ($outboundGroup as $out) {
                    $groups[] = [
                        'uniqueId' => $countGroup + 1,
                        'totalPrice' => $in->first()['price'] + $out->first()['price'],
                        'inbound' => $in->all(),
                        'outbound' => $out->all(),
                    ];
                    $countGroup++;
                }
            }
        }
        return $groups;
    }

    /**
     * Define o formato do retorno da api
     *
     * @param array $groups
     * @param array $flights
     * @return $flights
     */
    private function setReturnGroups(array $groups, array $flights)
    {
        $groups = collect($groups)->sortBy('totalPrice');
        $groups = $groups->values()->all();

        $this->groups['flights'] = $flights['flights'];
        $this->groups['groups'] = $groups;

        $this->groups['groups']['totalGroups'] = count($groups);
        $this->groups['groups']['totalFlights'] = count($flights['flights']);
        $this->groups['groups']['cheapestPrice'] = $groups[0]['totalPrice'];
        $this->groups['groups']['cheapestGroup'] = $groups[0]['uniqueId'];
    }
}
