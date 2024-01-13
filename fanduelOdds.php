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

    }

    private function getLiveMarketIds() {
        /**
         * Helper function that returns ids of all live NBA markets. Calls the $liveMarketsEndpoint
         *
         * @return array Array of live market ids
         */
    }

    private function getMarketPrices() {
        /**
         * Helper function that returns market prices for Spread Betting, Money Line and Total Points for next Raptors game
         *
         * @return array Array of market prices for next Raptors game
         */
    }
}