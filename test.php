#!/usr/local/bin/php -f
<?php

namespace RPurinton\poker;

require_once('src/Deck.php');
$deck = new Deck();
$deck->shuffle();
print_r($deck->toString());
