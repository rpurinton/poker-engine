<?php

namespace RPurinton\poker;

require_once(__DIR__ . '/TableStatus.php');
require_once(__DIR__ . '/GameType.php');
require_once(__DIR__ . '/Seat.php');
require_once(__DIR__ . '/Deck.php');
require_once(__DIR__ . '/HandEvaluator.php');
require_once(__DIR__ . '/Limit.php');

class Table
{
    public array $seats = [];
    private array $config = [
        "id" => null,
        "status" => TableStatus::WAITING_FOR_PLAYERS,
        "gametype" => GameType::TEXAS_HOLDEM,
        "seats" => 9,
        "smallBlind" => 1,
        "bigBlind" => 2,
        "limit" => Limit::NL,
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
    private HandEvaluator $handEvaluator;
    private $button_position = 0;
    private $action_position = 0;

    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
        $this->createSeats();
        $this->deck = new Deck();
        $this->handEvaluator = new HandEvaluator();
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

    public function new_hand(): void
    {
        echo ("Starting a new hand of " . $this->config['gametype']->display() . " [$" . $this->config['smallBlind'] . "/$" . $this->config['bigBlind'] . "]\n");
        $players_ready = $this->resetSeats();
        if ($players_ready < 2) {
            echo ("Not enough players to start a new hand.\n");
            return;
        }
        $this->muck = [];
        $this->communityCards = [];
        $this->pots = [];
        $this->pots[0] = new Pot(0);
        $this->advanceButton();
        $this->deck = new Deck();
        $this->action_position = $this->getNextActiveSeat($this->postBlinds());
        $this->deck->shuffle();
        $this->deck->cut();
    }

    private function postBlinds(): int
    {
        return $this->postBigBlind($this->postSmallBlind());
    }

    private function postSmallBlind(): int
    {
        $small_blind_amount = $this->config['smallBlind'];
        $small_blind_seat_number = $this->getNextActiveSeat($this->button_position);
        $small_blind_seat = $this->seats[$small_blind_seat_number];
        while ($small_blind_seat->getPot()->getAmount() < $small_blind_amount) {
            $small_blind_seat->setStatus(SeatStatus::SITOUT);
            $small_blind_seat_number = $this->getNextActiveSeat($this->button_position);
            $small_blind_seat = $this->seats[$small_blind_seat_number];
        }
        $this->pots[0]->contribute($small_blind_amount, $small_blind_seat);
        $small_blind_seat->setStatus(SeatStatus::POSTED);
        echo ($small_blind_seat->getPlayer()->getName() . " posts the small blind of $" . $small_blind_amount . "\n");
        return $small_blind_seat_number;
    }

    private function postBigBlind($small_blind_seat_number): int
    {
        $big_blind_amount = $this->config['bigBlind'];
        $big_blind_seat_number = $this->getNextActiveSeat($small_blind_seat_number);
        $big_blind_seat = $this->seats[$big_blind_seat_number];
        while ($big_blind_seat->getPot()->getAmount() < $big_blind_amount) {
            $big_blind_seat->setStatus(SeatStatus::SITOUT);
            $big_blind_seat_number = $this->getNextActiveSeat($this->button_position + 2);
            $big_blind_seat = $this->seats[$big_blind_seat_number];
        }
        $this->pots[0]->contribute($big_blind_amount, $big_blind_seat);
        $big_blind_seat->setStatus(SeatStatus::POSTED);
        echo ($big_blind_seat->getPlayer()->getName() . " posts the big blind of $" . $big_blind_amount . "\n");
        return $big_blind_seat_number;
    }

    private function advanceButton(): void
    {
        // move the button to the next active seat
        $this->button_position = $this->getNextActiveSeat($this->button_position);
    }

    private function getNextActiveSeat($seat): int
    {
        $seat++;
        if ($seat >= count($this->seats)) $seat = 0;
        if (!in_array($this->seats[$seat]->getStatus(), [SeatStatus::PLAYING, SeatStatus::POSTED])) $seat = $this->getNextActiveSeat($seat);
        return $seat;
    }

    public function resetSeats(): int
    {
        $players_ready = 0;
        foreach ($this->seats as $seat_number => $seat) {
            $seat->setCards([]);
            switch ($seat->getStatus()) {
                case SeatStatus::WAITING:
                case SeatStatus::POSTED:
                case SeatStatus::FOLDED:
                case SeatStatus::PLAYING:
                    if ($seat->getPot()->getAmount() < $this->config['bigBlind']) $seat->setStatus(SeatStatus::SITOUT);
                    else {
                        $seat->setStatus(SeatStatus::PLAYING);
                        echo ("Seat $seat_number\t{$seat->getPot()}\t{$seat->getPlayer()->getName()}\n");
                        $players_ready++;
                    }
                    break;
                case SeatStatus::TIMEOUT:
                    $seat->setStatus(SeatStatus::SITOUT);
                    break;
                default:
                    break;
            }
        }
        return $players_ready;
    }
}
