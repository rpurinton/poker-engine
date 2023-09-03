File: src/TableStatus.php:
<?php

namespace RPurinton\poker;

enum TableStatus: int
{
    case WAITING_FOR_PLAYERS = 0;
    case STARTING = 1;
    case PREFLOP = 2;
    case FLOP = 3;
    case TURN = 4;
    case RIVER = 5;
    case SHOWDOWN = 6;
    case HAND_OVER = 7;
}

File: src/Card.php:
<?php

namespace RPurinton\poker;

require_once(__DIR__ . '/Suit.php');
require_once(__DIR__ . '/Rank.php');

class Card
{
    public function __construct(
        private Suit $suit,
        private Rank $rank
    ) {
    }

    public function getSuit(): Suit
    {
        return $this->suit;
    }

    public function getRank(): Rank
    {
        return $this->rank;
    }

    public function __toString(): string
    {
        return $this->rank->display() . $this->suit->display();
    }
}

File: src/Casino.php:
<?php

namespace RPurinton\poker;

require_once(__DIR__ . '/Table.php');
require_once(__DIR__ . '/Player.php');

class Casino
{
    public array $tables = [];
    public array $players = [];
    public Pot $vault;

    public function __construct(
        private string $name
    ) {
        $this->vault = new Pot(0);
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        if (strlen($name) < 3) {
            throw new \Exception('Name must be at least 3 characters.');
        }

        $this->name = $name;
    }

    public function addTable(Table $table): Table
    {
        $this->tables[] = $table;
        return $table;
    }

    public function addPlayer(Player $player): Player
    {
        $this->players[] = $player;
        return $player;
    }

    public function depositToVault(float $amount): void
    {
        $this->vault->add($amount);
    }

    public function withdrawFromVault(float $amount): void
    {
        $this->vault->remove($amount);
    }

    public function buyChips(Player $player, float $amount): void
    {
        $this->depositToVault($amount);
        $player->getBankroll()->add($amount);
    }

    public function cashOut(Player $player): void
    {
        $this->withdrawFromVault($player->getBankroll()->getAmount());
        $player->getBankroll()->setAmount(0);
    }

    public function cashOutPartial(Player $player, float $amount): void
    {
        $this->withdrawFromVault($amount);
        $player->getBankroll()->remove($amount);
    }
}

File: src/Seat.php:
<?php

namespace RPurinton\poker;

require_once(__DIR__ . '/Card.php');
require_once(__DIR__ . '/Player.php');
require_once(__DIR__ . '/SeatStatus.php');

class Seat
{
    private SeatStatus $status = SeatStatus::EMPTY;
    private ?Player $player = null;
    private array $cards = [];
    private Pot $pot;

    public function __construct()
    {
        $this->pot = new Pot(0);
    }

    public function getStatus(): SeatStatus
    {
        return $this->status;
    }

    public function setStatus(SeatStatus $status): void
    {
        $this->status = $status;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): void
    {
        $this->player = $player;
    }

    public function getCards(): array
    {
        return $this->cards;
    }

    public function setCards(array $cards): void
    {
        $this->cards = $cards;
    }

    public function getPot(): Pot
    {
        return $this->pot;
    }

    public function setPot(Pot $pot): void
    {
        $this->pot = $pot;
    }

    public function __toString(): string
    {
        return $this->player->getName();
    }

    public function addCard(Card $card): void
    {
        $this->cards[] = $card;
    }

    public function removeCard(Card $card): void
    {
        $key = array_search($card, $this->cards);

        if ($key !== false) {
            unset($this->cards[$key]);
        }
    }

    public function clearCards(): void
    {
        $this->cards = [];
    }

    public function buyChips(float $amount): void
    {
        $this->player->getBankroll()->remove($amount);
        $this->pot->add($amount);
    }
}

File: src/PlayerStatus.php:
<?php

namespace RPurinton\poker;

enum PlayerStatus
{
    case STANDING;
    case WAITING_FOR_TABLE;
    case SEAT_RESERVED;
    case SEATED;
}

File: src/Suit.php:
<?php

namespace RPurinton\poker;

enum Suit
{
    case CLUBS;
    case DIAMONDS;
    case HEARTS;
    case SPADES;

    public function display(): string
    {
        return match ($this) {
            Suit::CLUBS => 'c',
            Suit::DIAMONDS => 'd',
            Suit::HEARTS => 'h',
            Suit::SPADES => 's',
        };
    }

    public static function toArray(): array
    {
        return [
            Suit::CLUBS,
            Suit::DIAMONDS,
            Suit::HEARTS,
            Suit::SPADES,
        ];
    }
}

File: src/SeatStatus.php:
<?php

namespace RPurinton\poker;

enum SeatStatus
{
    case EMPTY;
    case RESERVED;
    case WAITING;
    case POSTED;
    case PLAYING;
    case SITOUT;
    case TIMEOUT;
    case FOLDED;
}

File: src/HandEvaluator.php:
<?php

namespace RPurinton\poker;

class HandEvaluator
{
}

File: src/Limit.php:
<?php

namespace RPurinton\poker;

enum Limit
{
    case NL;
    case PL;
    case FL;

    public function display(): string
    {
        return match ($this) {
            Limit::NL => 'NL',
            Limit::PL => 'PL',
            Limit::FL => 'FL',
        };
    }

    public static function toArray(): array
    {
        return [
            Limit::NL,
            Limit::PL,
            Limit::FL,
        ];
    }
}

File: src/Deck.php:
<?php

namespace RPurinton\poker;

require_once 'Card.php';

class Deck
{
    private $cards = [];

    public function __construct()
    {
        $this->cards = $this->createDeck();
    }

    public function getCards()
    {
        return $this->cards;
    }

    public function shuffle()
    {
        shuffle($this->cards);
    }

    public function cut()
    {
        $cut = rand(1, 51);
        $this->cards = array_merge(
            array_slice($this->cards, $cut),
            array_slice($this->cards, 0, $cut)
        );
    }

    public function dealCard(array &$destination): void
    {
        $destination[] = array_pop($this->cards);
    }

    private function createDeck()
    {
        $cards = [];

        foreach (Suit::toArray() as $suit) {
            foreach (Rank::toArray() as $rank) {
                $cards[] = new Card($suit, $rank);
            }
        }

        return $cards;
    }

    public function toString()
    {
        $deck = '';

        foreach ($this->cards as $card) {
            $deck .= $card . ' ';
        }

        return $deck;
    }
}

File: src/Pot.php:
<?php

namespace RPurinton\poker;

class Pot
{
    public function __construct(private float $amount = 0, public array $eligible = [])
    {
        $this->amount = round($this->amount, 2);
    }

    public function add(float $amount): void
    {
        $this->amount += round($amount, 2);
    }

    public function remove(float $amount): void
    {
        $this->amount -= round($amount, 2);
    }

    public function getAmount(): float
    {
        return round($this->amount, 2);
    }

    public function setAmount(float $amount): void
    {
        $this->amount = round($amount, 2);
    }

    public function contribute(float $amount, Seat $seat)
    {
        $this->add($amount);
        $seat->getPot()->remove($amount);
        $this->eligible[] = $seat;
    }

    public function __toString(): string
    {
        return '$' . number_format($this->amount, 2, '.', ',');
    }
}

File: src/Rank.php:
<?php

namespace RPurinton\poker;

enum Rank: int
{
    case TWO = 2;
    case THREE = 3;
    case FOUR = 4;
    case FIVE = 5;
    case SIX = 6;
    case SEVEN = 7;
    case EIGHT = 8;
    case NINE = 9;
    case TEN = 10;
    case JACK = 11;
    case QUEEN = 12;
    case KING = 13;
    case ACE = 14;

    public function display(): string
    {
        return match ($this) {
            self::TWO => '2',
            self::THREE => '3',
            self::FOUR => '4',
            self::FIVE => '5',
            self::SIX => '6',
            self::SEVEN => '7',
            self::EIGHT => '8',
            self::NINE => '9',
            self::TEN => 'T',
            self::JACK => 'J',
            self::QUEEN => 'Q',
            self::KING => 'K',
            self::ACE => 'A',
        };
    }

    public static function toArray(): iterable
    {
        return [
            self::TWO,
            self::THREE,
            self::FOUR,
            self::FIVE,
            self::SIX,
            self::SEVEN,
            self::EIGHT,
            self::NINE,
            self::TEN,
            self::JACK,
            self::QUEEN,
            self::KING,
            self::ACE,
        ];
    }
}

File: src/GameType.php:
<?php

namespace RPurinton\poker;

enum GameType: int
{
    case TEXAS_HOLDEM = 1;
    case OMAHA = 2;
    case OMAHA_HILO = 3;

    public function display()
    {
        return match ($this) {
            GameType::TEXAS_HOLDEM => 'Texas Hold \'Em',
            GameType::OMAHA => 'Omaha',
            GameType::OMAHA_HILO => 'Omaha Hi/Lo',
        };
    }

    public function num_hole_cards()
    {
        return match ($this) {
            GameType::TEXAS_HOLDEM => 2,
            GameType::OMAHA, GameType::OMAHA_HILO => 4,
        };
    }
}

File: src/Table.php:
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
        $players = $this->getDealOrder();
        print_r($players);
    }

    private function getDealOrder(): array
    {
        $players = [];
        $seat_number = $this->button_position;
        while (true) {
            $seat_number++;
            if ($seat_number >= count($this->seats)) $seat_number = 0;
            $seat = $this->seats[$seat_number];
            if ($seat->getStatus() == SeatStatus::PLAYING) $players[] = $seat->getPlayer();
            if ($seat_number == $this->button_position) break;
        }
        return $players;
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

File: src/Player.php:
<?php

namespace RPurinton\poker;

require_once(__DIR__ . '/Pot.php');
require_once(__DIR__ . '/PlayerStatus.php');

class Player
{
    private PlayerStatus $status = PlayerStatus::STANDING;
    public function __construct(
        private string $name,
        private ?Pot $bankroll = new Pot(0)
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBankroll(): Pot
    {
        return $this->bankroll;
    }

    public function setBankroll(Pot $bankroll): void
    {
        $this->bankroll = $bankroll;
    }

    public function setStatus(PlayerStatus $status): void
    {
        $this->status = $status;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}

File: test.php:
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

