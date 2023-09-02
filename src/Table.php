<?php

namespace RPurinton\poker;

require_once(__DIR__ . '/TableStatus.php');
require_once(__DIR__ . '/GameType.php');
require_once(__DIR__ . '/Seat.php');

class Table
{
    private int $id = 0;
    private TableStatus $status = TableStatus::WAITING_FOR_PLAYERS;
    private ?GameType $gameType = null;
    public array $seats = [];
    private array $config = [
        "seats" => 9,
        "smallBlind" => 1,
        "bigBlind" => 2,
        "minBuyIn" => 100,
        "maxBuyIn" => 1000,
        "straddles" => 0,
        "buttonstraddle" => false,
        "straddleAmount" => 4,
    ];

    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
        $this->createSeats();
    }

    public function getSeats(): array
    {
        return $this->seats;
    }

    public function getGameType(): ?GameType
    {
        return $this->gameType;
    }

    public function setGameType(GameType $gameType): void
    {
        $this->gameType = $gameType;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }

    private function createSeats(): void
    {
        for ($i = 0; $i < $this->config['seats']; $i++) {
            $this->seats[] = new Seat();
        }
    }

    public function setStakes($minBuyIn, $maxBuyIn, $smallBlind, $bigBlind): void
    {
        $this->config['minBuyIn'] = $minBuyIn;
        $this->config['maxBuyIn'] = $maxBuyIn;
        $this->config['smallBlind'] = $smallBlind;
        $this->config['bigBlind'] = $bigBlind;
    }

    public function seatPlayer(Player $player, Seat $seat): void
    {
        $seat->setPlayer($player);
        $seat->setStatus(SeatStatus::WAITING);
        $player->setStatus(PlayerStatus::SEATED);
    }

    public function reserveSeat(Player $player, Seat $seat): void
    {
        $seat->setPlayer($player);
        $seat->setStatus(SeatStatus::RESERVED);
    }
}
