<?php

require_once 'fanduelOdds.php';

$fanduel = new FanduelOdds(/* Secret Key */);

print_r($fanduel->getHtml());


