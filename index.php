<?php

require_once 'fanduelOdds.php';

$fanduel = new FanduelOdds("Y92oEkhNW2osW6tS");

print_r($fanduel->getLiveMarketIds());


