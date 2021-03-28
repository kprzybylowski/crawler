<?php

namespace App\Http\Controllers;

use Goutte\Client;
use Illuminate\Http\Request;

class CrawlerController extends Controller
{

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $results = [];
        $postcode = '';
        if ($request->postcode) {
            $postcode = $request->postcode;
            $client = new Client();
            $crawler = $client->request('GET', 'https://www.rightmove.co.uk/house-prices.html');
            $form = $crawler->selectButton('List View')->form();
            $crawler = $client->submit($form, array('searchLocation' => $postcode));

            $data = [];
            $extrData = $crawler->filter('script')->each(function ($node) {
                $key = '{"results"';
                $textResult = $node->text();
                $keyPosition = strpos($textResult, $key);
                if (!empty($keyPosition)) {
                    $json = substr($textResult, $keyPosition);
                    $jsonData = json_decode($json);
                    return $jsonData;
                }
            });

            $extrData = array_filter($extrData);
            $extrData = array_shift($extrData);

            $tenYearsAgo = strtotime("-10 years");
            if (!empty($extrData) && isset($extrData->results->properties)) {
                foreach ($extrData->results->properties as $property) {
                    $propertyData = [
                        'address' => $property->address,
                        'type' => $property->propertyType,
                        'price' => 0,
                    ];

                    if (!empty($property->transactions)) {
                        foreach ($property->transactions as $transaction) {
                            $date = strtotime($transaction->dateSold);
                            $price = str_replace(['&pound;', ','], '', $transaction->displayPrice);
                            if ($price > $propertyData['price'] && $date >= $tenYearsAgo) {
                                $propertyData['price'] = $price;
                            }
                        }
                    }

                    $data[] = $propertyData;
                }
            }

            $dataCollection = collect($data);
            $results = $dataCollection->sortByDesc('price')->take(5)->toArray();
        }

        return view('crawler.index', ['properties'=>$results, 'postcode'=>$postcode]);
    }
}
