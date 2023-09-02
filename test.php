#!/usr/local/bin/php -f
<?php

namespace RPurinton\poker;

require_once('src/Casino.php');
$casino = new Casino("My Casino");
$table = $casino->addTable(new Table());
$player = $casino->addPlayer(new Player("Bob"));
$casino->buyChips($player, 10000);
$player->setStatus(PlayerStatus::WAITING_FOR_TABLE);
$table->reserveSeat($player, $table->seats[0]);
print_r($casino);
