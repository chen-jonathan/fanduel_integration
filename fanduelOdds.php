<?php

class FanduelOdds {
    public $liveMarketsEndpoint;
    public $liveMarketPricesEndpoint;
    private $secret_key;

    const BASKETBALL_ID = "7522";
    const NBA_ID = "10547864";
    const RAPTORS_ID = "237476";
  
    const MARKET_TYPES = ["MATCH_HANDICAP_(2-WAY)","MONEY_LINE","TOTAL_POINTS_(OVER/UNDER)"];
    const OVER_ID = "7017823";

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

        
        $liveMarketIds = $this->getLiveMarketIds();

        if (count($liveMarketIds) == 0) {
            // there was some error fetching liveMarketIds
            return '';
        }
        else {
            //TODO: call getMarketPrices() and create table with relevant prices here 
            $marketPrices = $this->getMarketPrices($liveMarketIds);
            $processedMarketPrices = $this->processMarketPrices($marketPrices);

            print_r($processedMarketPrices);
            //create table out of processedMarketPrices
            $html = '
                <div>
                    <h1>Test</h1>
                </div>
            ';
            return $html;
        }       
    }

    private function getLiveMarketIds() {
        /**
         * Helper function that returns ids of all live NBA markets. Calls the $liveMarketsEndpoint
         *
         * @return array Array of live market ids
         */

        // setup request to ListMarketCatalogue API
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

        // process the response
        if (curl_errno($liveMarketsCh)) {
            echo 'Error (getLiveMarketIds): ' . curl_error($liveMarketsCh);
            curl_close($liveMarketsCh);
            return [];
        } 
        else {
            $data = json_decode($liveMarketsResponse);
            curl_close($liveMarketsCh);

            $liveMarketIds = [];
            // grab all liveMarketIds
            foreach ($data as $item) {
                array_push($liveMarketIds, $item->marketId);
            }
            return $liveMarketIds;
        }
    }
    private function getMarketPrices($liveMarketIds) {
        /**
         * Helper function that returns market prices for Spread Betting, Money Line and Total Points for next Raptors game
         *
         * @return array Array of market prices for next Raptors game
         */
        
        $marketPricesRequestPayload = json_encode([
            "listMarketPricesRequestParams" => [
                "marketIds" => $liveMarketIds,
            ]
        ]);

        $requestHeaders = [
            "Content-type: application/json",
            "X-Application: " . $this->secret_key
        ];
    
        $marketPricesCh = curl_init();
    
        curl_setopt_array($marketPricesCh, [
            CURLOPT_URL => "https://affiliates.sportsbook.fanduel.com/betting/rest/v1/listMarketPrices/",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $marketPricesRequestPayload,
            CURLOPT_HTTPHEADER => $requestHeaders
        ]);
    
        $marketPricesResponse = curl_exec($marketPricesCh);
    
        if (curl_errno($marketPricesCh)) {
            echo 'Error: ' . curr_error($marketPricesCh);
            curl_close($marketPricesCh);
            return [];
        } 
        else {
            $data = json_decode($marketPricesResponse);
            // print_r($data);
            curl_close($marketPricesCh);
            
            $groupedMarketsByGame = new stdClass();
            $raptorsEventId = "";
            //group market prices by game ID
            foreach($data->marketDetails as $market_detail) {
                $eventId = $market_detail->eventId;
    
                // Initialize the array for this eventId if it hasn't been created yet
                if (!isset($groupedMarketsByGame->$eventId)) {
                    $groupedMarketsByGame->$eventId = [];
                }
                array_push($groupedMarketsByGame->$eventId, $market_detail);
    
                foreach($market_detail->runnerDetails as $runnerDetail) {
                    if ($runnerDetail->selectionId == self::RAPTORS_ID) {
                        $raptorsEventId = $eventId;
                    }
                }
            }
    
            //find Raptors team id, find corresponding game, if Raptors game does not exists return empty html? 
            return $groupedMarketsByGame->$raptorsEventId;
        }
    }

    private function processMarketPrices($marketPrices) {
        /**
         * Helper function that groups market prices by Raptors, opponent, and Over/Under
         *
         * @return array Array of market prices with groupings listed above. [Raptors, Opponent, Over/Under]
         */

        $raptorsPrices = new stdClass();
        $opponentPrices = new stdClass();
        $overUnderPrices = new stdClass();

        foreach ($marketPrices as $market) {            

            if ($market->marketName == "Moneyline") {
                // print_r($market->runnerDetails);
                foreach($market->runnerDetails as $runnerDetail) {
                    if ($runnerDetail->selectionId == self::RAPTORS_ID) {
                        $raptorsPrices->moneyLine = $runnerDetail;
                    }
                    else {
                        $opponentPrices->moneyLine = $runnerDetail;
                    }
                }
            }
            elseif ($market->marketName == "Spread Betting") {
                foreach($market->runnerDetails as $runnerDetail) {
                    if ($runnerDetail->selectionId == self::RAPTORS_ID) {
                        $raptorsPrices->spread = $runnerDetail;
                    }
                    else {
                        $opponentPrices->spread = $runnerDetail;
                    }
                }
            }

            elseif ($market->marketName == "Total Points") {
                foreach($market->runnerDetails as $runnerDetail) {
                    if ($runnerDetail->selectionId == self::OVER_ID) {
                        $overUnderPrices->over = $runnerDetail;
                    }
                    else {
                        $overUnderPrices->under = $runnerDetail;
                    }
                }
            }            
        }
        return [$raptorsPrices, $opponentPrices, $overUnderPrices];

    }
}