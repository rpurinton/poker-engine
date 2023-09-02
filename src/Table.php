<?php

namespace RPurinton\poker;

require_once(__DIR__ . '/TableStatus.php');
require_once(__DIR__ . '/GameType.php');
require_once(__DIR__ . '/Seat.php');
require_once(__DIR__ . '/Deck.php');

class Table
{
    public array $seats = [];
    private array $config = [
        "id" => null,
        "status" => TableStatus::WAITING_FOR_PLAYERS,
        "seats" => 9,
        "smallBlind" => 1,
        "bigBlind" => 2,
        "minBuyIn" => 100,
        "maxBuyIn" => 1000,
        "straddles" => 0,
        "buttonstraddle" => false,
        "straddleAmount" => 4,
    ];
    public ?Deck $deck = null;
    public array $pots = [];
    public array $communityCards = [];
    public array $muck = [];

    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
        $this->createSeats();
        $this->deck = new Deck();
    }

    public function getSeats(): array
    {
        return $this->seats;
    }

    public function getGameType(): ?GameType
    {
        return $this->config['gametype'];
    }

    public function setGameType(GameType $gameType): void
    {
        $this->config['gametype'] = $gameType;
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

    public function seatPlayer(Player $player, Seat $seat): Seat
    {
        $seat->setPlayer($player);
        $seat->setStatus(SeatStatus::WAITING);
        $player->setStatus(PlayerStatus::SEATED);
        return $seat;
    }

    public function reserveSeat(Player $player, Seat $seat): Seat
    {
        $seat->setPlayer($player);
        $seat->setStatus(SeatStatus::RESERVED);
        $player->setStatus(PlayerStatus::SEAT_RESERVED);
        return $seat;
    }
}
