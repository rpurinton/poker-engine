#!/usr/local/bin/php -f
<?php

namespace RPurinton\poker;

require_once('src/Deck.php');
$deck = new Deck();
$deck->shuffle();

$card = $deck->dealCard();
print_r($card);
