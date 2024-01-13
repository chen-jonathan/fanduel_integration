<?php

$available_markets_request_payload = json_encode([
    "listMarketCatalogueRequestParams" => [
        "marketFilter" => [
            "eventTypeIds" => ["7522"],
            "competitionIds" => ["10547864"],
            "marketTypes" => ["MATCH_HANDICAP_(2-WAY)","MONEY_LINE","TOTAL_POINTS_(OVER/UNDER)"]
        ],
        "maxResults" => 1000
    ]
]);

$headers = [
    "Content-type: application/json",
    "X-Application: "
];

// Get the current available markets 
$available_markets_ch = curl_init();

curl_setopt_array($available_markets_ch, [
    CURLOPT_URL => "https://affiliates.sportsbook.fanduel.com/betting/rest/v1/listMarketCatalogue/",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => $available_markets_request_payload,
    CURLOPT_HTTPHEADER => $headers
]);

$available_markets_response = curl_exec($available_markets_ch);

if (curl_errno($available_markets_ch)) {
    echo 'Error: ' . curr_error($available_markets_ch);
} 
else {
    $data = json_decode($available_markets_response);
    // print_r($data);
    $available_markets_ids = [];
    foreach ($data as $item) {
        array_push($available_markets_ids, $item->marketId);

        // echo "Market ID: " . $item->marketId . PHP_EOL;
        // echo "Market Name: " . $item->marketName . "\n";
        // echo "Market Start Time: " . $item->marketStartTime . "\n";
        // echo "--------------------------\n";
    }
    // print_r($available_markets_ids);

    //retrieve market prices API call 
    $market_prices_request_payload = json_encode([
        "listMarketPricesRequestParams" => [
            "marketIds" => $available_markets_ids,
        ]
    ]);

    $market_prices_ch = curl_init();

    curl_setopt_array($market_prices_ch, [
        CURLOPT_URL => "https://affiliates.sportsbook.fanduel.com/betting/rest/v1/listMarketPrices/",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $market_prices_request_payload,
        CURLOPT_HTTPHEADER => $headers
    ]);

    $market_prices_response = curl_exec($market_prices_ch);

    if (curl_errno($market_prices_ch)) {
        echo 'Error: ' . curr_error($market_prices_ch);
    } 
    else {
        $data = json_decode($market_prices_response);
        // print_r($data);
        
        $groupedMarkets = new stdClass();
        $raptorsEventId = "";
        //group market prices by game ID
        foreach($data->marketDetails as $market_detail) {
            $eventId = $market_detail->eventId;

            // Initialize the array for this eventId if it hasn't been created yet
            if (!isset($groupedMarkets->$eventId)) {
                $groupedMarkets->$eventId = [];
            }
            $groupedMarkets->$eventId[] = $market_detail;

            foreach($market_detail->runnerDetails as $runnerDetail) {
                if (strpos($runnerDetail->selectionName, "Rockets")) {
                    $raptorsEventId = $eventId;
                }
            }
        }

        //find Raptors team id, find corresponding game, if Raptors game does not exists return empty html? 
        print_r($groupedMarkets->$raptorsEventId);
    }

    

}

curl_close($available_markets_ch);

// var_dump($data);