<?php

namespace RPurinton\poker;

require_once(__DIR__ . '/Seat.php');

class SeatManager
{
    public function __construct(public Table $table)
    {
        echo "SeatManager class loaded\n";
        for ($i = 1; $i <= $table->config['seats']; $i++) {
            $table->seats[$i] = new Seat($i, $table);
        }
        return ($this);
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

    public function reset_seats(): int
    {
        $players_ready = 0;
        echo ("Seat\tStack\tName\tRace\tStatus\n");
        foreach ($this->table->seats as $seat_number => $seat) {
            if ($seat->get_stack()->get_amount() <= 0) $seat->set_status(SeatStatus::BUSTED);
            $seat->clear_cards();
            $seat->bet = 0;
            $seat->total_bet = 0;
            //$seat->top_up($this->table->config['maxBuyIn']);
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
                    $this->table->chat("$seat_number\t" .
                        "{$seat->get_stack()}\t" .
                        "{$seat->get_player()->get_name()}\t" .
                        "{$seat->get_player()->type->display()}\t" .
                        "{$seat->get_status()->display()}");
                    $players_ready++;
                    $this->table->pots[0]->eligible[$seat_number] = [
                        "seat" => $seat,
                        "contributed" => 0
                    ];

                    break;
                case SeatStatus::TIMEOUT:
                    $seat->set_status(SeatStatus::SITOUT);
                    $this->table->chat("$seat_number\t" .
                        "{$seat->get_player()->get_bankroll()}\t" .
                        "{$seat->get_stack()}\t" .
                        "{$seat->get_player()->get_name()}\t" .
                        "{$seat->get_player()->type}\t" .
                        "{$seat->get_status()->display()}");
                    break;
                default:
                    if (isset($seat->player)) {
                        $this->table->chat("$seat_number\t" .
                            "{$seat->get_player()->get_bankroll()}\t" .
                            "{$seat->get_stack()}\t" .
                            "{$seat->get_player()->get_name()}\t" .
                            "{$seat->get_player()->type}\t" .
                            "{$seat->get_status()->display()}");
                    } else {
                        $this->table->chat("$seat_number\t{$seat->get_status()->display()}");
                    }
                    break;
            }
        }
        return $players_ready;
    }
}
