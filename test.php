#!/usr/local/bin/php -f
<?php
// basic heads up example

namespace RPurinton\poker;

require_once('src/Casino.php');
$casino = new Casino("My Casino");
$table = $casino->addTable(new Table([
    "id" => 1,
    "name" => "My Table",
]));
$player1 = $casino->addPlayer(new Player("Bob"));
$casino->buyChips($player1, 10000);
$table->seatPlayer($player1, $table->seats[0])->buyChips(1000);
$player2 = $casino->addPlayer(new Player("Sally"));
$casino->buyChips($player2, 10000);
$table->seatPlayer($player2, $table->seats[1])->buyChips(1000);
$table->new_hand();
