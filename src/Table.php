<?php

namespace RPurinton\poker;

require_once(__DIR__ . '/Enums/TableStatus.php');
require_once(__DIR__ . '/Enums/GameType.php');
require_once(__DIR__ . '/Enums/Limit.php');
require_once(__DIR__ . '/Seat.php');
require_once(__DIR__ . '/Deck.php');
require_once(__DIR__ . '/HandEvaluator.php');

class Table
{
    public array $seats = [];
    private array $config = [
        "id" => null,
        "status" => TableStatus::WAITING_FOR_PLAYERS,
        "GameType" => GameType::TEXAS_HOLDEM,
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
    public HandEvaluator $HandEvaluator;
    private int $button_position = 0;
    private int $action_position = 0;
    public array $chat_history = [];

    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
        $this->createSeats();
        $this->deck = new Deck();
        $this->HandEvaluator = new HandEvaluator($this->config["GameType"]);
    }

    public function getSeats(): array
    {
        return $this->seats;
    }

    public function getGameType(): ?GameType
    {
        return $this->config['GameType'];
    }

    public function setGameType(GameType $GameType): void
    {
        $this->config['GameType'] = $GameType;
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
        echo ("=============================================================\n");
        $this->chat("Starting a new hand of " . $this->config["limit"]->display() . " " . $this->config['GameType']->display() . " [$" . $this->config['smallBlind'] . "/$" . $this->config['bigBlind'] . "]");
        $this->config['status'] = TableStatus::STARTING;
        $players_ready = $this->resetSeats();
        if ($players_ready < 2) {
            $this->chat("Not enough players to start a new hand.");
            return;
        }
        $this->muck = [];
        $this->communityCards = [];
        $this->pots = [];
        $this->pots[0] = new Pot(0, false);
        $this->advanceButton();
        $this->deck = new Deck();
        $this->action_position = $this->postBlinds();
        $this->deck->shuffle();
        $this->deck->cut();
        $this->dealHoleCards();
        $this->config['status'] = TableStatus::PREFLOP;
        $this->bettingRound();
        if ($this->config['status'] == TableStatus::HAND_OVER) return;
        $this->config['status'] = TableStatus::FLOP;
        $this->deck->dealCard($this->muck);
        $this->deck->dealCard($this->communityCards);
        $this->deck->dealCard($this->communityCards);
        $this->deck->dealCard($this->communityCards);
        echo ("========================================\n");
        $this->chat("Flop:\t[" . implode("] [", $this->communityCards) . "]");
        $this->bettingRound();
        if ($this->config['status'] == TableStatus::HAND_OVER) return;
        $this->config['status'] = TableStatus::TURN;
        $this->deck->dealCard($this->muck);
        $this->deck->dealCard($this->communityCards);
        echo ("========================================\n");
        $this->chat("Turn:\t[" . implode("] [", $this->communityCards) . "]");
        $this->bettingRound();
        if ($this->config['status'] == TableStatus::HAND_OVER) return;
        $this->config['status'] = TableStatus::RIVER;
        $this->deck->dealCard($this->muck);
        $this->deck->dealCard($this->communityCards);
        echo ("========================================\n");
        $this->chat("River:\t[" . implode("] [", $this->communityCards) . "]");
        $this->action_position = $this->button_position;
        $this->bettingRound();
        if ($this->config['status'] == TableStatus::HAND_OVER) return;
        $this->config['status'] = TableStatus::SHOWDOWN;
        //$this->showdown();
    }

    private function bettingRound(): void
    {
        $this->action_position = $this->button_position;
        $this->pots[0]->good = false;
        while (!$this->potsGood()) {
            $action_order = $this->getActionOrder();
            foreach ($action_order as $seat_number) {
                $seat = $this->seats[$seat_number];
                $seat->prompt($this);
            }
            $this->pots[0]->good = true;
        }
    }

    private function potsGood(): bool
    {
        foreach ($this->pots as $pot) {
            if (!$pot->good) return false;
        }
        return true;
    }

    private function getActionOrder(): array
    {
        $action_order = [];
        $seat_number = $this->action_position;
        while (true) {
            $seat_number++;
            if ($seat_number >= count($this->seats)) $seat_number = 0;
            $seat = $this->seats[$seat_number];
            if (in_array($seat->getStatus(), [SeatStatus::PLAYING, SeatStatus::POSTED])) $action_order[] = $seat_number;
            if ($seat_number == $this->action_position) break;
        }
        return $action_order;
    }

    private function dealHoleCards(): void
    {
        $deal_order = $this->getDealOrder();
        for ($i = 0; $i < $this->config["GameType"]->num_hole_cards(); $i++) {
            foreach ($deal_order as $seat_number) {
                $this->deck->dealCard($this->seats[$seat_number]->cards);
            }
        }
    }

    private function getDealOrder(): array
    {
        $deal_order = [];
        $seat_number = $this->button_position;
        while (true) {
            $seat_number++;
            if ($seat_number >= count($this->seats)) $seat_number = 0;
            $seat = $this->seats[$seat_number];
            if (in_array($seat->getStatus(), [SeatStatus::PLAYING, SeatStatus::POSTED])) $deal_order[] = $seat_number;
            if ($seat_number == $this->button_position) break;
        }
        return $deal_order;
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
        while ($small_blind_seat->getStack()->getAmount() < $small_blind_amount) {
            $small_blind_seat->setStatus(SeatStatus::SITOUT);
            $small_blind_seat_number = $this->getNextActiveSeat($this->button_position);
            $small_blind_seat = $this->seats[$small_blind_seat_number];
        }
        $this->pots[0]->contribute($small_blind_amount, $small_blind_seat);
        $small_blind_seat->setStatus(SeatStatus::POSTED);
        $this->chat($small_blind_seat->getPlayer()->getName() . " posts the small blind of $" . $small_blind_amount);
        return $small_blind_seat_number;
    }

    private function postBigBlind($small_blind_seat_number): int
    {
        $big_blind_amount = $this->config['bigBlind'];
        $big_blind_seat_number = $this->getNextActiveSeat($small_blind_seat_number);
        $big_blind_seat = $this->seats[$big_blind_seat_number];
        while ($big_blind_seat->getStack()->getAmount() < $big_blind_amount) {
            $big_blind_seat->setStatus(SeatStatus::SITOUT);
            $big_blind_seat_number = $this->getNextActiveSeat($this->button_position + 2);
            $big_blind_seat = $this->seats[$big_blind_seat_number];
        }
        $this->pots[0]->contribute($big_blind_amount, $big_blind_seat);
        $big_blind_seat->setStatus(SeatStatus::POSTED);
        $this->chat($big_blind_seat->getPlayer()->getName() . " posts the big blind of $" . $big_blind_amount);
        return $big_blind_seat_number;
    }

    private function advanceButton(): void
    {
        // move the button to the next active seat
        $this->button_position = $this->getNextActiveSeat($this->button_position);
        $this->chat("The button is at seat " . $this->button_position . ". ({$this->seats[$this->button_position]->getPlayer()->getName()})");
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
        echo ("Seat\tBankroll\tChips In Play  \tPlayer Name\n");
        foreach ($this->seats as $seat_number => $seat) {
            $seat->clearCards();
            switch ($seat->getStatus()) {
                case SeatStatus::WAITING:
                case SeatStatus::POSTED:
                case SeatStatus::FOLDED:
                case SeatStatus::PLAYING:
                    if ($seat->getStack()->getAmount() < $this->config['bigBlind']) $seat->setStatus(SeatStatus::SITOUT);
                    else {
                        $seat->setStatus(SeatStatus::PLAYING);
                        $seat->topUp($this->config['maxBuyIn']);
                        $this->chat("$seat_number\t{$seat->getPlayer()->getBankRoll()}\t{$seat->getStack()}\t{$seat->getPlayer()->getName()}");
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

    public function chat($message)
    {
        $this->chat_history[] = $message;
        echo ($message . "\n");
    }
}
