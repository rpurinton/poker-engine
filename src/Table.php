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
    private int $button_position = 0;
    private int $action_position = 0;
    public array $chat_history = [];

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
        $this->chat("Starting a new hand of " . $this->config["limit"]->display() . " " . $this->config['gametype']->display() . " [$" . $this->config['smallBlind'] . "/$" . $this->config['bigBlind'] . "]");
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
    }

    private function bettingRound(): void
    {
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
        for ($i = 0; $i < $this->config["gametype"]->num_hole_cards(); $i++) {
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
                        $this->chat("Seat $seat_number\t{$seat->getStack()}\t{$seat->getPlayer()->getName()}");
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
