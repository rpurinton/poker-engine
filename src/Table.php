<?php

namespace RPurinton\poker;

require_once(__DIR__ . '/Seat.php');

class Table
{
    private array $seats = [];
    private array $config = [
        "seats" => 9,
        "smallBlind" => 1,
        "bigBlind" => 2,
        "minBuyIn" => 100,
        "maxBuyIn" => 1000,
        "minPlayers" => 2,
        "maxPlayers" => 9,
    ];
}
