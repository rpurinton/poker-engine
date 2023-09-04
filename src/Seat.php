<?php

namespace RPurinton\poker;

require_once(__DIR__ . '/Card.php');
require_once(__DIR__ . '/Player.php');
require_once(__DIR__ . '/Enums/SeatStatus.php');
require_once(__DIR__ . '/Table.php');

class Seat
{
    private SeatStatus $status = SeatStatus::EMPTY;
    private ?Player $player = null;
    public array $cards = [];
    private Pot $stack;
    public float $bet = 0;
    public float $total_bet = 0;
    public function __construct(public int $seat_num, public Table $table)
    {
        $this->stack = new Pot(0);
    }

    public function get_status(): SeatStatus
    {
        return $this->status;
    }

    public function set_status(SeatStatus $status): void
    {
        $this->status = $status;
    }

    public function get_player(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): void
    {
        $this->player = $player;
    }

    public function get_stack(): Pot
    {
        return $this->stack;
    }

    public function __toString(): string
    {
        return $this->player->get_name();
    }

    public function clear_cards(): void
    {
        $this->cards = [];
    }

    public function buy_chips(float $amount): void
    {
        $amount = min($amount, $this->player->get_bankroll()->get_amount());
        $this->player->get_bankroll()->remove($amount);
        $this->stack->add($amount);
        $this->table->chat($this->player->get_name() . " buys in for $" . number_format($amount, 2, ".", ","));
    }

    public function cash_out(): void
    {
        if (!isset($this->player)) return;
        $stack_amount = $this->stack->get_amount();
        $this->player->get_bankroll()->add($this->stack->get_amount());
        $this->stack->setAmount(0);
        $this->table->chat($this->player->get_name() . " cashes out $" . number_format($stack_amount, 2, ".", ","));
        $this->status = SeatStatus::EMPTY;
        $this->player = null;
    }

    public function top_up(float $amount): void
    {
        if (!isset($this->player)) return;
        if ($this->player->get_bankroll()->get_amount() == 0 && $this->stack->get_amount() == 0) {
            $this->status = SeatStatus::SITOUT;
            return;
        }
        if ($this->player->get_bankroll()->get_amount() == 0) return;
        $current_stack = $this->stack->get_amount();
        if ($current_stack < $amount) {
            $diff_amount = $amount - $current_stack;
            $amount = min($diff_amount, $this->player->get_bankroll()->get_amount());
            $this->buy_chips($amount);
        }
    }

    public function prompt(array $options): void
    {
        switch ($this->player->type) {
            case PlayerType::HUMAN:
                echo ("=============================================================\n");
                echo ("Seat\tStack\tIn For\tName\tPocket\tHand\n");
                echo ($this->seat_num . "\t" . $this->get_stack() . "\t$" . number_format($this->total_bet, 2, ".", ",") . "\t" . $this->player->get_name() . "\t" . $this->table->HandEvaluator->hand_toString($this->cards, $this->table->communityCards) . "\n");
                foreach ($options as $key => $option) {
                    echo (" [" . strtoupper($key) . "] " . $option . "\n");
                }
                echo (" [T] Type in table chat\n");
                echo (": ");
                $valid = false;
                readline_callback_handler_install('', function () {
                });
                while (!$valid) {
                    $r = [STDIN];
                    $w = NULL;
                    $e = NULL;
                    if (stream_select($r, $w, $e, 0)) {
                        $input = stream_get_contents(STDIN, 1);
                        if ($input == "t") {
                            readline_callback_handler_remove();
                            echo ("\rType a message: ");
                            $handle = fopen("php://stdin", "r");
                            $message = trim(fgets($handle));
                            fclose($handle);
                            $this->table->chat($this->player->get_name() . " said: " . $message);
                            readline_callback_handler_install('', function () {
                            });
                            echo (": ");
                        }
                        if (array_key_exists($input, $options)) {
                            $valid = true;
                            $char = $input;
                        }
                    }
                }
                echo ("\r");
                readline_callback_handler_remove();
                switch ($char) {
                    case "c":
                        if (substr($options["c"], 0, 4) == "Call") $this->table->call($this);
                        else $this->table->check($this);
                        break;
                    case "f":
                        $this->table->fold($this);
                        break;
                    case "r":
                        echo ("Raise amount: ");
                        $handle = fopen("php://stdin", "r");
                        $amount = (float)fgets($handle);
                        fclose($handle);
                        echo ("\n");
                        $this->table->raise($this, $amount);
                        break;
                    case "b":
                        echo ("Bet amount: ");
                        $handle = fopen("php://stdin", "r");
                        $amount = (float)fgets($handle);
                        fclose($handle);
                        echo ("\n");
                        $this->table->bet($this, $amount);
                        break;
                    case "a":
                        $this->table->all_in($this);
                        break;
                    case "q":
                        echo ("Thanks for Playing!\n");
                        exit();
                        break;
                    default:
                        $this->table->check($this);
                        break;
                }
                break;
            case PlayerType::AI:
                // todo add chatGPT API calls here
                echo ($this->player->get_name() . "\t" . $this->table->HandEvaluator->hand_toString($this->cards, $this->table->communityCards) . "\n");
                break;
        }
    }
}
