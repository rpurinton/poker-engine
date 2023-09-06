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
    public array $config = [
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
    public $hand_count = 0;
    private float $bet = 0;
    private float $last_raise_amount = 0;

    public $encoder = null;

    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
        $this->create_seats();
        $this->deck = new Deck();
        $this->HandEvaluator = new HandEvaluator($this->config["GameType"]);
        $this->encoder = new \TikToken\Encoder;
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
        $seat->set_player($player);
        $seat->set_status(SeatStatus::WAITING);
        $player->set_status(PlayerStatus::SEATED);
        return $seat;
    }

    public function reserve_seat(Player $player, Seat $seat): Seat
    {
        $seat->set_player($player);
        $seat->set_status(SeatStatus::RESERVED);
        $player->set_status(PlayerStatus::SEAT_RESERVED);
        return $seat;
    }

    public function new_hand(): bool
    {
        $this->pots = [];
        $this->pots[0] = new Pot(0, false);
        $players_ready = $this->reset_seats();
        if ($players_ready < 2) {
            $this->chat("Not enough players to start a new hand.");
            return false;
        }
        $this->hand_count++;
        $hand_count_display = number_format($this->hand_count, 0, '.', ',');
        echo ("\n\n===============STARTING HAND #$hand_count_display====================\n");
        $this->chat($this->config["limit"]->display() . " " . $this->config['GameType']->display() . " [$" . $this->config['smallBlind'] . "/$" . $this->config['bigBlind'] . "]");
        $this->config['status'] = TableStatus::STARTING;
        $this->muck = [];
        $this->communityCards = [];
        $this->deck = new Deck();
        if ($this->hand_count === 1) $this->randomize_button();
        else  $this->advance_button();
        $this->action_position = $this->post_blinds();
        $this->deck->shuffle();
        $this->deck->cut();
        $this->deal_cards();
        $this->config['status'] = TableStatus::PREFLOP;
        $this->betting_round();
        if ($this->config['status'] == TableStatus::HAND_OVER) return true;
        if ($this->config['status'] == TableStatus::ALLIN) {
            $this->deck->deal_card($this->muck);
            $this->deck->deal_card($this->communityCards);
            $this->deck->deal_card($this->communityCards);
            $this->deck->deal_card($this->communityCards);
            echo ("================FLOP===================\n");
            $this->chat("Flop:\t[" . implode("] [", $this->communityCards) . "]");
            $this->deck->deal_card($this->muck);
            $this->deck->deal_card($this->communityCards);
            echo ("================TURN===================\n");
            $this->chat("Turn:\t[" . implode("] [", $this->communityCards) . "]");
            $this->deck->deal_card($this->muck);
            $this->deck->deal_card($this->communityCards);
            echo ("================RIVER==================\n");
            $this->chat("River:\t[" . implode("] [", $this->communityCards) . "]");
            $this->showdown();
            return true;
        }
        $this->config['status'] = TableStatus::FLOP;
        $this->deck->deal_card($this->muck);
        $this->deck->deal_card($this->communityCards);
        $this->deck->deal_card($this->communityCards);
        $this->deck->deal_card($this->communityCards);
        echo ("================FLOP===================\n");
        $this->chat("Flop:\t[" . implode("] [", $this->communityCards) . "]");
        $this->betting_round();
        if ($this->config['status'] == TableStatus::HAND_OVER) return true;
        if ($this->config['status'] == TableStatus::ALLIN) {
            $this->deck->deal_card($this->muck);
            $this->deck->deal_card($this->communityCards);
            echo ("================TURN===================\n");
            $this->chat("Turn:\t[" . implode("] [", $this->communityCards) . "]");
            $this->deck->deal_card($this->muck);
            $this->deck->deal_card($this->communityCards);
            echo ("================RIVER==================\n");
            $this->chat("River:\t[" . implode("] [", $this->communityCards) . "]");
            $this->showdown();
            return true;
        }
        $this->config['status'] = TableStatus::TURN;
        $this->deck->deal_card($this->muck);
        $this->deck->deal_card($this->communityCards);
        echo ("================TURN===================\n");
        $this->chat("Turn:\t[" . implode("] [", $this->communityCards) . "]");
        $this->betting_round();
        if ($this->config['status'] == TableStatus::HAND_OVER) return true;
        if ($this->config['status'] == TableStatus::ALLIN) {
            $this->deck->deal_card($this->muck);
            $this->deck->deal_card($this->communityCards);
            echo ("================RIVER==================\n");
            $this->chat("River:\t[" . implode("] [", $this->communityCards) . "]");
            $this->showdown();
            return true;
        }
        $this->config['status'] = TableStatus::RIVER;
        $this->deck->deal_card($this->muck);
        $this->deck->deal_card($this->communityCards);
        echo ("================RIVER==================\n");
        $this->chat("River:\t[" . implode("] [", $this->communityCards) . "]");
        $this->action_position = $this->button_position;
        $this->betting_round();
        if ($this->config['status'] == TableStatus::HAND_OVER) return true;
        $this->config['status'] = TableStatus::SHOWDOWN;
        $this->showdown();
        return true;
    }

    private function showdown(): void
    {
        $status_message =  ("\n=============SHOWDOWN!=============\n");
        foreach ($this->pots as $key => $pot) {
            if ($key == 0) $pot_display_name = "Main Pot";
            else $pot_display_name = "Side Pot " . $key;
            $eligible_seats = array_keys($pot->eligible);
            $eliglbe_player_names = [];
            foreach ($eligible_seats as $seat_num) $eliglbe_player_names[] = $this->seats[$seat_num]->get_player()->get_name() . " ($" . number_format($pot->eligible[$seat_num]["contributed"], 2, '.', ',') . ")";
            $eliglbe_player_names_string = implode(", ", $eliglbe_player_names);
            $status_message .= $pot_display_name . ": $pot [$eliglbe_player_names_string]\n";
        }
        $this->chat($status_message);
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
        echo ("Shuffling...");
        for ($i = 0; $i < 3; $i++) {
            echo (".");
            sleep(1);
        }
        echo ("\n");
    }

    private function betting_round(): void
    {
        $status_message =  ("===============POTS================\n");
        foreach ($this->pots as $key => $pot) {
            if ($key == 0) $pot_display_name = "Main Pot";
            else $pot_display_name = "Side Pot " . $key;
            $eligible_seats = array_keys($pot->eligible);
            $eliglbe_player_names = [];
            foreach ($eligible_seats as $seat_num) $eliglbe_player_names[] = $this->seats[$seat_num]->get_player()->get_name() . " ($" . number_format($pot->eligible[$seat_num]["contributed"], 2, '.', ',') . ")";
            $eliglbe_player_names_string = implode(", ", $eliglbe_player_names);
            $status_message .= $pot_display_name . ": $pot [$eliglbe_player_names_string]\n";
        }
        $this->chat($status_message);
        $action_order = $this->get_action_order();
        if (count($action_order) < 2) {
            $this->config['status'] = TableStatus::ALLIN;
            $this->end_betting_round();
            return;
        }
        foreach ($action_order as $seat_number) {
            $this->seats[$seat_number]->status = SeatStatus::UPCOMING_ACTION;
        }
        if ($this->config["status"] != TableStatus::PREFLOP) $this->action_position = $this->button_position;
        $this->pots[count($this->pots) - 1]->good = false;
        while (!$this->all_pots_are_good()) {
            if ($this->config["status"] == TableStatus::HAND_OVER) break;
            if ($this->config["status"] == TableStatus::ALLIN) break;
            $action_order = $this->get_action_order();
            if (count($action_order) < 1) {
                $this->config['status'] = TableStatus::ALLIN;
                break;
            }
            $seat_number = $action_order[0];
            $this->action_position = $seat_number;
            $seat = $this->seats[$seat_number];
            $available_actions = $this->get_available_actions($seat);
            if (count($available_actions)) $seat->prompt($available_actions);
            else die("Error: No available actions for seat $seat_number");
            if ($this->config['status'] == TableStatus::HAND_OVER) return;
            if ($this->count_upcoming() === 0) break;
        }
        $action_order = $this->get_action_order();
        if (count($action_order) < 2) {
            $this->config['status'] = TableStatus::ALLIN;
        }
        $this->end_betting_round();
    }

    public function count_upcoming(): int
    {
        $count = 0;
        foreach ($this->seats as $seat) {
            if ($seat->get_status() == SeatStatus::UPCOMING_ACTION) $count++;
        }
        return $count;
    }

    public function end_betting_round(): void
    {
        $this->action_position = $this->button_position;
        $this->split_pots();
        $this->check_hand_over();
        $this->bet = 0;
        $this->last_raise_amount = 0;
        foreach ($this->seats as $seat) $seat->bet = 0;
    }

    public function check_hand_over(): void
    {
        $active_players = 0;
        foreach ($this->seats as $seat) {
            if ($seat->get_status()->active() || $seat->get_status() == SeatStatus::ALLIN) $active_players++;
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

    public function split_pots(): void
    {
        $current_pot = count($this->pots) - 1;
        $eligible = $this->pots[$current_pot]->eligible;
        if (count($eligible) > 1) {
            $contributions = [];
            foreach ($eligible as $seat) {
                $contributions[] = $seat["contributed"];
            }
            $max_contribution = max($contributions);
            $min_contribution = min($contributions);
            while ($max_contribution > $min_contribution) {
                $this->pots[$current_pot + 1] = new Pot(0);
                $eligible = $this->pots[$current_pot]->eligible;
                $contributions = [];
                foreach ($eligible as $seat) {
                    $contributions[] = $seat["contributed"];
                }
                $max_contribution = max($contributions);
                $min_contribution = min($contributions);
                foreach ($eligible as $seat_num => $seat) {
                    if ($seat["contributed"] > $min_contribution) {
                        $diff_amount = $seat["contributed"] - $min_contribution;
                        $this->pots[$current_pot]->uncontribute($diff_amount, $seat["seat"]);
                        $this->pots[$current_pot + 1]->contribute($diff_amount, $seat["seat"]);
                    }
                }
                $current_pot++;
                $eligible = $this->pots[$current_pot]->eligible;
                $contributions = [];
                foreach ($eligible as $seat) {
                    $contributions[] = $seat["contributed"];
                }
                $max_contribution = max($contributions);
                $min_contribution = min($contributions);
            }
        }
    }

    private function get_available_actions(Seat $seat): array
    {
        $available_actions = [];

        if ($seat->bet < $this->bet) {
            $available_actions["f"] = "Fold";
            $bet_diff = $this->bet - $seat->bet;

            if ($seat->get_stack()->get_amount() > $bet_diff) {
                extract($this->get_min_max($seat));
                $available_actions["c"] = "Call $" . number_format($bet_diff, 2, '.', ',');
                if ($min_raise_amount_by < $max_opponent_stack) {
                    $available_actions["b"] = "Bet/Raise BY [$" . number_format($min_raise_amount_by, 2, '.', ',') . " <=> $" . number_format($max_raise_amount_by, 2, '.', ',') . "]";
                    $available_actions["a"] = "All-In for $" . number_format($max_raise_amount_by, 2, '.', ',') . ' more';
                }
            } else if ($seat->get_stack()->get_amount() < $bet_diff) {
                $available_actions["c"] = "Call All In for Less (" . $seat->get_stack() . ")";
            } else if ($seat->get_stack()->get_amount() == $bet_diff) {
                unset($available_actions["c"]);
                $available_actions["c"] = "Call All In for " . $seat->get_stack() . " more";
            }
        } else if ($seat->bet == $this->bet) {
            extract($this->get_min_max($seat));
            $available_actions["c"] = "Check";
            if ($min_raise_amount_by < $max_opponent_stack) {
                $available_actions["b"] = "Bet/Raise BY [$" . number_format($min_raise_amount_by, 2, '.', ',') . " <=> $" . number_format($max_raise_amount_by, 2, '.', ',') . "]";
                $available_actions["a"] = "All-In for $" . number_format($max_raise_amount_by, 2, '.', ',');
            }
        }

        return $available_actions;
    }

    private function get_min_max(Seat $seat): array
    {
        $max_opponent_stack = 0;
        $max_opponent_bet = 0;
        foreach ($this->seats as $other_seat) {
            if ($other_seat->seat_num == $seat->seat_num) continue;
            if ($other_seat->get_stack()->get_amount() > $max_opponent_stack) {
                $max_opponent_stack = $other_seat->get_stack()->get_amount();
                $max_opponent_bet = $other_seat->bet;
            }
        }
        $bet_diff = $this->bet - $seat->bet;
        $min_raise_amount_by = max($this->last_raise_amount, $this->config["bigBlind"]);
        $min_raise_amount_to = $this->bet + $min_raise_amount_by;
        $max_raise_amount_by = $seat->get_stack()->get_amount();
        $max_raise_amount_to = $seat->get_stack()->get_amount() + $seat->bet;
        $min_raise_amount_by = min($min_raise_amount_by, $max_opponent_stack + $max_opponent_bet);
        $min_raise_amount_to = min($min_raise_amount_to, $max_opponent_stack);
        $max_raise_amount_by = min($max_raise_amount_by, $max_opponent_stack + $max_opponent_bet);
        $max_raise_amount_to = min($max_raise_amount_to, $max_opponent_stack);
        return [
            "min_raise_amount_by" => $min_raise_amount_by,
            "min_raise_amount_to" => $min_raise_amount_to,
            "max_raise_amount_by" => $max_raise_amount_by,
            "max_raise_amount_to" => $max_raise_amount_to,
            "bet_diff" => $bet_diff,
            "max_opponent_stack" => $max_opponent_stack,
            "max_opponent_bet" => $max_opponent_bet,
        ];
    }


    public function fold(Seat $seat): void
    {
        echo ("\r                                                                                          \r");
        $seat->set_status(SeatStatus::FOLDED);
        $this->chat($seat->get_player()->get_name() . " folds.");
        foreach ($this->pots as $pot) unset($pot->eligible[$seat->seat_num]);
        // if all other players have folded, the hand is over
        $active_players = 0;
        foreach ($this->seats as $seat) {
            if ($seat->get_status()->active() || $seat->get_status() == SeatStatus::ALLIN) $active_players++;
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

    public function pass(Seat $seat): void
    {
        $seat->set_status(SeatStatus::CHECKED);
    }

    public function check(Seat $seat): void
    {
        echo ("\r                                                                                          \r");
        $seat->set_status(SeatStatus::CHECKED);
        $this->chat($seat->get_player()->get_name() . " checks.");
    }

    public function call(Seat $seat): void
    {
        echo ("\r                                                                                          \r");
        extract($this->get_min_max($seat));
        $amount = $bet_diff;
        $amount = min($amount, $seat->get_stack()->get_amount());
        $seat->bet += $amount;
        $seat->total_bet += $amount;
        $amount = min($amount, $seat->get_stack()->get_amount());
        $this->pots[count($this->pots) - 1]->contribute($amount, $seat);
        $this->chat($seat->get_player()->get_name() . " calls $" . number_format($amount, 2, '.', ','));
        if ($seat->get_stack()->get_amount() <= 0) {
            $seat->set_status(SeatStatus::ALLIN);
            $this->chat($seat->get_player()->get_name() . " is ALL-IN!");
        } else $seat->set_status(SeatStatus::CALLED);
    }

    public function raise_by(Seat $seat, $amount): void
    {
        echo ("\r                                                                                          \r");
        extract($this->get_min_max($seat));
        $amount += $bet_diff;
        $amount = min($amount, $max_raise_amount_by + $bet_diff);
        $amount = max($amount, $min_raise_amount_by);
        $amount = max($amount, $this->last_raise_amount);
        $amount = min($amount, $seat->get_stack()->get_amount());
        $seat->bet += $amount;
        $seat->total_bet += $amount;
        $this->pots[count($this->pots) - 1]->contribute($amount, $seat);
        $this->last_raise_amount = (float)$seat->bet - (float)$this->bet;
        $this->bet = $seat->bet;
        $this->chat($seat->get_player()->get_name() . " raises it to $" . number_format($seat->bet, 2, '.', ',') . " total.");
        foreach ($this->seats as $other_seat) {
            if ($other_seat->get_status()->active()) $other_seat->set_status(SeatStatus::UPCOMING_ACTION);
        }
        if ($seat->get_stack()->get_amount() <= 0) {
            $seat->set_status(SeatStatus::ALLIN);
            $this->chat($seat->get_player()->get_name() . " is ALL-IN!");
        } else $seat->set_status(SeatStatus::RAISED);
    }

    public function all_in(Seat $seat): void
    {
        echo ("\r                                                                                          \r");
        extract($this->get_min_max($seat));
        $amount = $max_raise_amount_by + $bet_diff;
        $amount = min($amount, $seat->get_stack()->get_amount());
        $seat->bet += $amount;
        $seat->total_bet += $amount;
        $this->pots[count($this->pots) - 1]->contribute($amount, $seat);
        $this->bet = $seat->bet;
        $this->chat($seat->get_player()->get_name() . " is all in for " . number_format($amount, 2, ".", ",") . " more, $" . number_format($seat->bet, 2, '.', ',') . " total.");
        foreach ($this->seats as $other_seat) {
            if ($other_seat->get_status()->active()) $other_seat->set_status(SeatStatus::UPCOMING_ACTION);
        }
        if ($seat->get_stack()->get_amount() <= 0) {
            $seat->set_status(SeatStatus::ALLIN);
            $this->chat($seat->get_player()->get_name() . " is ALL-IN!");
        } else $seat->set_status(SeatStatus::RAISED);
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
            if ($seat_number > count($this->seats)) $seat_number = 1;
            $seat = $this->seats[$seat_number];
            if ($seat->get_status()->active()) {
                $action_order[] = $seat_number;
            }
            if ($seat_number == $this->action_position) break;
        }
        return $action_order;
    }

    private function get_deal_order(): array
    {
        $action_order = [];
        $seat_number = $this->action_position;
        while (true) {
            $seat_number++;
            if ($seat_number > count($this->seats)) $seat_number = 1;
            $seat = $this->seats[$seat_number];
            if ($seat->get_status()->active() || $seat->get_status() == SeatStatus::ALLIN) {
                $action_order[] = $seat_number;
            }
            if ($seat_number == $this->action_position) break;
        }
        return $action_order;
    }

    private function deal_cards(): void
    {
        $action_order = $this->get_deal_order();
        for ($i = 0; $i < $this->config["GameType"]->num_hole_cards(); $i++) {
            foreach ($action_order as $seat_number) {
                $this->deck->deal_card($this->seats[$seat_number]->cards);
            }
        }
    }

    private function post_blinds(): int
    {
        return $this->post_big_blind($this->post_small_blind());
    }

    private function post_small_blind(): int
    {
        //todo modify for cash vs tourney
        $small_blind_amount = $this->config['smallBlind'];
        $small_blind_seat_number = $this->get_next_active_seat($this->button_position);
        $small_blind_seat = $this->seats[$small_blind_seat_number];
        $small_blind_amount = min($small_blind_amount, $small_blind_seat->get_stack()->get_amount());
        while ($small_blind_seat->get_stack()->get_amount() < $small_blind_amount) {
            $small_blind_seat->set_status(SeatStatus::SITOUT);
            $small_blind_seat_number = $this->get_next_active_seat($this->button_position);
            $small_blind_seat = $this->seats[$small_blind_seat_number];
        }
        $this->pots[count($this->pots) - 1]->contribute($small_blind_amount, $small_blind_seat);
        $small_blind_seat->set_status(SeatStatus::UPCOMING_ACTION);
        if ($small_blind_seat->get_stack()->get_amount() == 0) {
            $small_blind_seat->set_status(SeatStatus::ALLIN);
            $this->chat($small_blind_seat->get_player()->get_name() . " is ALL-IN!");
        }
        $small_blind_seat->bet = $this->config['smallBlind'];
        $small_blind_seat->total_bet = $this->config['smallBlind'];
        $this->bet = $this->config['smallBlind'];
        $this->last_raise_amount = $this->config['smallBlind'];
        $this->chat($small_blind_seat->get_player()->get_name() . " posts the small blind of $" . $small_blind_amount);
        return $small_blind_seat_number;
    }

    private function post_big_blind($small_blind_seat_number): int
    {
        //todo modify for cash vs tourney
        $big_blind_amount = $this->config['bigBlind'];
        $big_blind_seat_number = $this->get_next_active_seat($small_blind_seat_number);
        $big_blind_seat = $this->seats[$big_blind_seat_number];
        $big_blind_amount = min($big_blind_amount, $big_blind_seat->get_stack()->get_amount());
        while ($big_blind_seat->get_stack()->get_amount() < $big_blind_amount) {
            $big_blind_seat->set_status(SeatStatus::SITOUT);
            $big_blind_seat_number = $this->get_next_active_seat($this->button_position + 2);
            $big_blind_seat = $this->seats[$big_blind_seat_number];
        }
        $this->pots[count($this->pots) - 1]->contribute($big_blind_amount, $big_blind_seat);
        $big_blind_seat->set_status(SeatStatus::UPCOMING_ACTION);
        if ($big_blind_seat->get_stack()->get_amount() == 0) {
            $big_blind_seat->set_status(SeatStatus::ALLIN);
            $this->chat($big_blind_seat->get_player()->get_name() . " is ALL-IN!");
        }
        $big_blind_seat->bet = $this->config['bigBlind'];
        $big_blind_seat->total_bet = $this->config['bigBlind'];
        $this->bet = $this->config['bigBlind'];
        $this->last_raise_amount = $this->config['bigBlind'] - $this->config['smallBlind'];
        $this->chat($big_blind_seat->get_player()->get_name() . " posts the big blind of $" . $big_blind_amount);
        return $big_blind_seat_number;
    }

    private function randomize_button(): void
    {
        $this->button_position = array_rand($this->seats);
        $this->chat("Randomly put the button at seat " . $this->button_position . ". ({$this->seats[$this->button_position]->get_player()->get_name()})");
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
        if ($seat_num > count($this->seats)) $seat_num = 1;
        if (!$this->seats[$seat_num]->status->active()) $seat_num = $this->get_next_active_seat($seat_num);
        return $seat_num;
    }

    public function reset_seats(): int
    {
        $players_ready = 0;
        echo ("Seat\tStack\tName\tRace\tStatus\n");
        foreach ($this->seats as $seat_number => $seat) {
            if ($seat->get_stack()->get_amount() <= 0) $seat->set_status(SeatStatus::BUSTED);
            $seat->clear_cards();
            $seat->bet = 0;
            $seat->total_bet = 0;
            //$seat->top_up($this->config['maxBuyIn']);
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
                    // todo: something different for cash game vs tourney
                    $seat->set_status(SeatStatus::PLAYING);
                    $this->chat("$seat_number\t" .
                        "{$seat->get_stack()}\t" .
                        "{$seat->get_player()->get_name()}\t" .
                        "{$seat->get_player()->type->display()}\t" .
                        "{$seat->get_status()->display()}");
                    $players_ready++;
                    $this->pots[0]->eligible[$seat_number] = [
                        "seat" => $seat,
                        "contributed" => 0
                    ];

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
        $this->chat_history[] = ["message" => $message, "tokens" => count($this->encoder->encode($message))];
        echo ("\r                                                                                          \r");
        echo ($message . "\n");
    }

    public function get_chat_history(int $token_limit = 3596): array
    {
        $chat_history = [];
        $token_count = 0;
        foreach (array_reverse($this->chat_history) as $chat) {
            $token_count += $chat["tokens"];
            if ($token_count > $token_limit) break;
            $chat_history[] = $chat["message"];
        }
        return array_reverse($chat_history);
    }
}
