<?php

class FanduelOdds {
    public $liveMarketsEndpoint;
    public $liveMarketPricesEndpoint;
    private $secret_key;

    const BASKETBALL_ID = "7522";
    const NBA_ID = "10547864";
    const RAPTORS_ID = ""; //TODO: fill out with value from Confluence doc 
  
    const MARKET_TYPES = ["MATCH_HANDICAP_(2-WAY)","MONEY_LINE","TOTAL_POINTS_(OVER/UNDER)"];

    // Constructor 
    public function __construct($secret_key, $liveMarketsEndpoint="https://affiliates.sportsbook.fanduel.com/betting/rest/v1/listMarketCatalogue/", 
    $liveMarketPricesEndpoint="https://affiliates.sportsbook.fanduel.com/betting/rest/v1/listMarketPrices/") {
        $this->liveMarketsEndpoint = $liveMarketsEndpoint;
        $this->liveMarketPricesEndpoint = $liveMarketPricesEndpoint;
        $this->secret_key = $secret_key;
    }

    public function getHtml() {
        /**
         * Returns a table (in HTML) of prices for the next Raptors game
         *
         * @param
         * @return HTML the corresponding prices table 
         */
        
        $html = '
            <div>
                <h1>Welcome to My Website</h1>
                <p>This is an example of returning HTML from a PHP function.</p>
            </div>
        ';

        return $html;
    }

    public function getLiveMarketIds() {
        /**
         * Helper function that returns ids of all live NBA markets. Calls the $liveMarketsEndpoint
         *
         * @return array Array of live market ids
         */

        $liveMarketsRequestPayload = json_encode([
            "listMarketCatalogueRequestParams" => [
                "marketFilter" => [
                    "eventTypeIds" => [self::BASKETBALL_ID],
                    "competitionIds" => [self::NBA_ID],
                    "marketTypes" => self::MARKET_TYPES
                ],
                "maxResults" => 1000
            ]
        ]);
        
        $requestHeaders = [
            "Content-type: application/json",
            "X-Application: " . $this->secret_key
        ];

        $liveMarketsCh = curl_init();

        curl_setopt_array($liveMarketsCh, [
            CURLOPT_URL => $this->liveMarketsEndpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $liveMarketsRequestPayload,
            CURLOPT_HTTPHEADER => $requestHeaders
        ]);

        $liveMarketsResponse = curl_exec($liveMarketsCh);

        if (curl_errno($liveMarketsCh)) {
            echo 'Error: ' . curl_error($liveMarketsCh);
            curl_close($liveMarketsCh);
            return [];
        } 
        else {
            $data = json_decode($liveMarketsResponse);
            $liveMarketIds = [];
            foreach ($data as $item) {
                array_push($liveMarketIds, $item->marketId);

                // echo "Market ID: " . $item->marketId . PHP_EOL;
                // echo "Market Name: " . $item->marketName . "\n";
                // echo "Market Start Time: " . $item->marketStartTime . "\n";
                // echo "--------------------------\n";
            }
            curl_close($liveMarketsCh);
            return $liveMarketIds;
        }
    }
    private function getMarketPrices() {
        /**
         * Helper function that returns market prices for Spread Betting, Money Line and Total Points for next Raptors game
         *
         * @return array Array of market prices for next Raptors game
         */
        return [];
    }
}