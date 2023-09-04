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
    private $hand_count = 0;
    private $bet = 0;

    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
        $this->create_seats();
        $this->deck = new Deck();
        $this->HandEvaluator = new HandEvaluator($this->config["GameType"]);
    }

    public function get_seats(): array
    {
        return $this->seats;
    }

    public function get_GameType(): ?GameType
    {
        return $this->config['GameType'];
    }

    public function set_GameType(GameType $GameType): void
    {
        $this->config['GameType'] = $GameType;
    }

    public function get_config(): array
    {
        return $this->config;
    }

    public function set_config(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }

    private function create_seats(): void
    {
        for ($i = 1; $i <= $this->config['seats']; $i++) {
            $this->seats[$i] = new Seat($i, $this);
        }
    }

    public function set_stakes($minBuyIn, $maxBuyIn, $smallBlind, $bigBlind): void
    {
        $this->config['minBuyIn'] = $minBuyIn;
        $this->config['maxBuyIn'] = $maxBuyIn;
        $this->config['smallBlind'] = $smallBlind;
        $this->config['bigBlind'] = $bigBlind;
    }

    public function seat_player(Player $player, Seat $seat): Seat
    {
        $seat->setPlayer($player);
        $seat->set_status(SeatStatus::WAITING);
        $player->set_status(PlayerStatus::SEATED);
        return $seat;
    }

    public function reserve_seat(Player $player, Seat $seat): Seat
    {
        $seat->setPlayer($player);
        $seat->set_status(SeatStatus::RESERVED);
        $player->set_status(PlayerStatus::SEAT_RESERVED);
        return $seat;
    }

    public function new_hand(): void
    {
        $this->hand_count++;
        $hand_count_display = number_format($this->hand_count, 0, '.', ',');
        echo ("=============================================================\n");
        $this->chat("Starting hand #$hand_count_display of " . $this->config["limit"]->display() . " " . $this->config['GameType']->display() . " [$" . $this->config['smallBlind'] . "/$" . $this->config['bigBlind'] . "]");
        $this->config['status'] = TableStatus::STARTING;
        $this->muck = [];
        $this->communityCards = [];
        $this->pots = [];
        $this->pots[0] = new Pot(0, false);
        $this->bet = $this->config["bigBlind"];
        $players_ready = $this->reset_seats();
        if ($players_ready < 2) {
            $this->chat("Not enough players to start a new hand.");
            die();
        }
        $this->advance_button();
        $this->deck = new Deck();
        $this->action_position = $this->post_blinds();
        $this->deck->shuffle();
        $this->deck->cut();
        $this->deal_cards();
        $this->config['status'] = TableStatus::PREFLOP;
        $this->betting_round();
        if ($this->config['status'] == TableStatus::HAND_OVER) return;
        $this->config['status'] = TableStatus::FLOP;
        $this->deck->deal_card($this->muck);
        $this->deck->deal_card($this->communityCards);
        $this->deck->deal_card($this->communityCards);
        $this->deck->deal_card($this->communityCards);
        echo ("========================================\n");
        $this->chat("Flop:\t[" . implode("] [", $this->communityCards) . "]");
        $this->betting_round();
        if ($this->config['status'] == TableStatus::HAND_OVER) return;
        $this->config['status'] = TableStatus::TURN;
        $this->deck->deal_card($this->muck);
        $this->deck->deal_card($this->communityCards);
        echo ("========================================\n");
        $this->chat("Turn:\t[" . implode("] [", $this->communityCards) . "]");
        $this->betting_round();
        if ($this->config['status'] == TableStatus::HAND_OVER) return;
        $this->config['status'] = TableStatus::RIVER;
        $this->deck->deal_card($this->muck);
        $this->deck->deal_card($this->communityCards);
        echo ("========================================\n");
        $this->chat("River:\t[" . implode("] [", $this->communityCards) . "]");
        $this->action_position = $this->button_position;
        $this->betting_round();
        if ($this->config['status'] == TableStatus::HAND_OVER) return;
        $this->config['status'] = TableStatus::SHOWDOWN;
        $this->showdown();
    }

    private function showdown(): void
    {
        foreach ($this->pots as $key => $pot) {
            if ($key == 0) $pot_display = "the Main Pot";
            else $pot_display = "Side Pot $key";
            $hands = [];
            foreach ($pot->eligible as $seat_num => $seat) {
                $hands[$seat_num] = $seat["seat"]->cards;
            }
            $winning_seats = $this->HandEvaluator->get_winner_indexes($hands, $this->communityCards);
            $winners = [];
            foreach ($winning_seats as $index => $best_hand) $winners[$index] = $best_hand["display"];
            $results = $pot->payout($winners, $pot_display);
            foreach ($results as $result) $this->chat($result);
        }
    }

    private function betting_round(): void
    {
        foreach ($this->get_deal_order() as $seat_number) {
            $this->seats[$seat_number]->status = SeatStatus::UPCOMING_ACTION;
        }
        $this->action_position = $this->button_position;
        $this->pots[0]->good = false;
        while (!$this->all_pots_are_good()) {
            $action_order = $this->get_action_order();
            foreach ($action_order as $seat_number) {
                if ($this->config['status'] == TableStatus::HAND_OVER) return;
                $seat = $this->seats[$seat_number];
                $seat->prompt($this->get_available_actions($seat));
            }
        }
    }

    private function get_available_actions(Seat $seat): array
    {
        $available_actions = [];
        if ($seat->bet < $this->bet) {
            $available_actions["f"] = "Fold";
            $bet_diff = number_format($this->bet - $seat->bet, 2, '.', ',');
            $available_actions["c"] = "Call $" . $bet_diff;
            if ($seat->get_stack()->get_amount() >= $this->bet) {
                $available_actions["r"] = "Raise";
                $available_actions["a"] = "All In for " . $seat->get_stack();
            } else if ($seat->get_stack()->get_amount() < $this->bet) {
                $available_actions["a"] = "All In for Less (" . $seat->get_stack() . ")";
            }
        } else {
            $available_actions["c"] = "Check";
            if ($seat->get_stack()->get_amount() >= $this->bet) {
                $available_actions["b"] = "Bet";
                $available_actions["a"] = "All In for " . $seat->get_stack();
            }
        }
        return $available_actions;
    }


    public function fold(Seat $seat): void
    {
        $seat->set_status(SeatStatus::FOLDED);
        $this->chat($seat->get_player()->get_name() . " folds.");
        foreach ($this->pots as $pot) unset($pot->eligible[$seat->seat_num]);
        // if all other players have folded, the hand is over
        $active_players = 0;
        foreach ($this->seats as $seat) {
            if ($seat->get_status()->active()) $active_players++;
        }
        if ($active_players == 1) {
            $this->config['status'] = TableStatus::HAND_OVER;
            // payout the pot to the remaining player
            foreach ($this->pots as $key => $pot) {
                if ($key == 0) $pot_display = "the Main Pot";
                else $pot_display = "Side Pot $key";
                $results = $pot->payout_last_player($pot_display);
                foreach ($results as $result) $this->chat($result);
            }
        }
    }

    public function check(Seat $seat): void
    {
        $seat->set_status(SeatStatus::CHECKED);
        $this->chat($seat->get_player()->get_name() . " checks.");
    }

    public function call(Seat $seat): void
    {
        $diff_amount = $this->bet - $seat->bet;
        if ($diff_amount == $seat->get_stack()->get_amount()) $seat->set_status(SeatStatus::ALLIN);
        else $seat->set_status(SeatStatus::CALLED);
        $seat->bet += $diff_amount;
        $seat->total_bet += $diff_amount;
        // need something around here related to sidepots
        $this->pots[0]->contribute($diff_amount, $seat);
        $this->chat($seat->get_player()->get_name() . " calls $" . $diff_amount);
    }

    public function bet(Seat $seat, $amount): void
    {
        $amount = min($amount, $seat->get_stack()->get_amount());
        if ($amount == $seat->get_stack()->get_amount()) $seat->set_status(SeatStatus::ALLIN);
        else $seat->set_status(SeatStatus::BET);
        $seat->bet += $amount;
        $seat->total_bet += $amount;
        // need somehing around here dealing with side pots
        $this->pots[0]->contribute($amount, $seat);
        $this->bet = $seat->bet;
        $this->chat($seat->get_player()->get_name() . " bet $" . number_format($amount, 2, '.', ','));
    }

    public function raise(Seat $seat, $amount): void
    {
        $amount -= $seat->bet;
        $amount = min($amount, $seat->get_stack()->get_amount());
        if ($amount == $seat->get_stack()->get_amount()) $seat->set_status(SeatStatus::ALLIN);
        else $seat->set_status(SeatStatus::RAISED);
        $seat->bet += $amount;
        $seat->total_bet += $amount;
        // need something around here dealing with side pots
        $this->pots[0]->contribute($amount, $seat);
        $this->bet = $seat->bet;
        $this->chat($seat->get_player()->get_name() . " raises by $" . $amount . " to $" . $seat->bet);
    }

    public function all_in(Seat $seat): void
    {
        $seat->set_status(SeatStatus::ALLIN);
        $amount = $seat->get_stack()->get_amount();
        $seat->bet += $seat->get_stack()->get_amount();
        $seat->total_bet += $seat->get_stack()->get_amount();
        // need something around here dealing with side pots
        $this->pots[0]->contribute($seat->get_stack()->get_amount(), $seat);
        $this->bet = $seat->bet;
        $this->chat($seat->get_player()->get_name() . " is all in for $" . $seat->get_stack()->get_amount());
    }

    public function sit_out(Seat $seat): void
    {
        $this->fold($seat);
        $seat->set_status(SeatStatus::SITOUT);
        $this->chat($seat->get_player()->get_name() . " sits out.");
    }

    public function sit_in(Seat $seat): void
    {
        $seat->set_status(SeatStatus::PLAYING);
        $this->chat($seat->get_player()->get_name() . " sits in.");
    }

    public function timeout(Seat $seat): void
    {
        $this->fold($seat);
        $seat->set_status(SeatStatus::TIMEOUT);
        $this->chat($seat->get_player()->get_name() . " times out.");
    }


    private function all_pots_are_good(): bool
    {
        foreach ($this->seats as $seat) {
            if ($seat->status == SeatStatus::UPCOMING_ACTION) return false;
        }

        foreach ($this->pots as $pot) {
            // check if all contributions are equal
            $contributions = [];
            foreach ($pot->eligible as $seat) {
                $contributions[] = $seat["contributed"];
            }
            if (count(array_unique($contributions)) !== 1) {
                return false;
            }
        }
        return true;
    }

    private function get_action_order(): array
    {
        $action_order = [];
        $seat_number = $this->action_position;
        while (true) {
            $seat_number++;
            if ($seat_number >= count($this->seats)) $seat_number = 1;
            $seat = $this->seats[$seat_number];
            if ($seat->get_status()->active()) {
                $seat->status = SeatStatus::UPCOMING_ACTION;
                $action_order[] = $seat_number;
            }
            if ($seat_number == $this->action_position) break;
        }
        return $action_order;
    }

    private function deal_cards(): void
    {
        $deal_order = $this->get_deal_order();
        for ($i = 0; $i < $this->config["GameType"]->num_hole_cards(); $i++) {
            foreach ($deal_order as $seat_number) {
                $this->deck->deal_card($this->seats[$seat_number]->cards);
            }
        }
    }

    private function get_deal_order(): array
    {
        $deal_order = [];
        $seat_number = $this->button_position;
        while (true) {
            $seat_number++;
            if ($seat_number >= count($this->seats)) $seat_number = 1;
            $seat = $this->seats[$seat_number];
            if ($seat->status->active()) $deal_order[] = $seat_number;
            if ($seat_number == $this->button_position) break;
        }
        return $deal_order;
    }

    private function post_blinds(): int
    {
        return $this->post_big_blind($this->post_small_blind());
    }

    private function post_small_blind(): int
    {
        $small_blind_amount = $this->config['smallBlind'];
        $small_blind_seat_number = $this->get_next_active_seat($this->button_position);
        $small_blind_seat = $this->seats[$small_blind_seat_number];
        while ($small_blind_seat->get_stack()->get_amount() < $small_blind_amount) {
            $small_blind_seat->set_status(SeatStatus::SITOUT);
            $small_blind_seat_number = $this->get_next_active_seat($this->button_position);
            $small_blind_seat = $this->seats[$small_blind_seat_number];
        }
        $this->pots[0]->contribute($small_blind_amount, $small_blind_seat);
        $small_blind_seat->set_status(SeatStatus::UPCOMING_ACTION);
        $small_blind_seat->bet = $small_blind_amount;
        $small_blind_seat->total_bet = $small_blind_amount;
        $this->chat($small_blind_seat->get_player()->get_name() . " posts the small blind of $" . $small_blind_amount);
        return $small_blind_seat_number;
    }

    private function post_big_blind($small_blind_seat_number): int
    {
        $big_blind_amount = $this->config['bigBlind'];
        $big_blind_seat_number = $this->get_next_active_seat($small_blind_seat_number);
        $big_blind_seat = $this->seats[$big_blind_seat_number];
        while ($big_blind_seat->get_stack()->get_amount() < $big_blind_amount) {
            $big_blind_seat->set_status(SeatStatus::SITOUT);
            $big_blind_seat_number = $this->get_next_active_seat($this->button_position + 2);
            $big_blind_seat = $this->seats[$big_blind_seat_number];
        }
        $this->pots[0]->contribute($big_blind_amount, $big_blind_seat);
        $big_blind_seat->set_status(SeatStatus::UPCOMING_ACTION);
        $big_blind_seat->bet = $big_blind_amount;
        $big_blind_seat->total_bet = $big_blind_amount;
        $this->chat($big_blind_seat->get_player()->get_name() . " posts the big blind of $" . $big_blind_amount);
        return $big_blind_seat_number;
    }

    private function advance_button(): void
    {
        // move the button to the next active seat
        $this->button_position = $this->get_next_active_seat($this->button_position);
        $this->chat("The button is at seat " . $this->button_position . ". ({$this->seats[$this->button_position]->get_player()->get_name()})");
    }

    private function get_next_active_seat(int $seat_num): int
    {
        $seat_num++;
        if ($seat_num >= count($this->seats)) $seat_num = 1;
        if (!$this->seats[$seat_num]->status->active()) $seat_num = $this->get_next_active_seat($seat_num);
        return $seat_num;
    }

    public function reset_seats(): int
    {
        $players_ready = 0;
        echo ("Seat\tOff Table\tOn Table  \tName\tRace\tStatus\n");
        foreach ($this->seats as $seat_number => $seat) {
            $seat->clear_cards();
            $seat->bet = 0;
            $seat->total_bet = 0;
            $seat->top_up($this->config['maxBuyIn']);
            switch ($seat->get_status()) {
                case SeatStatus::WAITING:
                case SeatStatus::POSTED:
                case SeatStatus::FOLDED:
                case SeatStatus::PLAYING:
                case SeatStatus::ALLIN:
                case SeatStatus::CALLED:
                case SeatStatus::RAISED:
                case SeatStatus::BET:
                case SeatStatus::CHECKED:
                case SeatStatus::UPCOMING_ACTION:
                    if ($seat->get_stack()->get_amount() < $this->config['bigBlind']) $seat->set_status(SeatStatus::SITOUT);
                    else {
                        $seat->set_status(SeatStatus::PLAYING);
                        $this->chat("$seat_number\t" .
                            "{$seat->get_player()->get_bankroll()}\t" .
                            "{$seat->get_stack()}\t" .
                            "{$seat->get_player()->get_name()}\t" .
                            "{$seat->get_player()->type->display()}\t" .
                            "{$seat->get_status()->display()}");
                        $players_ready++;
                        $this->pots[0]->eligible[$seat_number] = [
                            "seat" => $seat,
                            "contributed" => 0
                        ];
                    }
                    break;
                case SeatStatus::TIMEOUT:
                    $seat->set_status(SeatStatus::SITOUT);
                    $this->chat("$seat_number\t" .
                        "{$seat->get_player()->get_bankroll()}\t" .
                        "{$seat->get_stack()}\t" .
                        "{$seat->get_player()->get_name()}\t" .
                        "{$seat->get_player()->type}\t" .
                        "{$seat->get_status()->display()}");
                    break;
                default:
                    if (isset($seat->player)) {
                        $this->chat("$seat_number\t" .
                            "{$seat->get_player()->get_bankroll()}\t" .
                            "{$seat->get_stack()}\t" .
                            "{$seat->get_player()->get_name()}\t" .
                            "{$seat->get_player()->type}\t" .
                            "{$seat->get_status()->display()}");
                    } else {
                        $this->chat("$seat_number\t{$seat->get_status()->display()}");
                    }
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
