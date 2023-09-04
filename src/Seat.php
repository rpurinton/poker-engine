<?php

namespace RPurinton\poker;

use OpenAI;

require_once(__DIR__ . '/Card.php');
require_once(__DIR__ . '/Player.php');
require_once(__DIR__ . '/Enums/SeatStatus.php');
require_once(__DIR__ . '/Table.php');

class Seat
{
    public SeatStatus $status = SeatStatus::EMPTY;
    private ?Player $player = null;
    public array $cards = [];
    private Pot $stack;
    public float $bet = 0;
    public float $total_bet = 0;

    public $openai = null;

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

    public function set_player(Player $player): void
    {
        $this->player = $player;
        if ($player->type == PlayerType::AI) $this->openai = OpenAI::client(file_get_contents(__DIR__ . "/openai_token.txt"));
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

    public function buy_chips(float $amount, bool $rebuy = false): void
    {
        $amount = min($amount, $this->player->get_bankroll()->get_amount());
        $this->player->get_bankroll()->remove($amount);
        $this->stack->add($amount);
        if ($rebuy) $this->table->chat($this->player->get_name() . " rebuys $" . number_format($amount, 2, ".", ","));
        else $this->table->chat($this->player->get_name() . " buys in for $" . number_format($amount, 2, ".", ","));
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
        if (!$this->player->auto_top_up) return;
        if ($this->player->get_bankroll()->get_amount() == 0 && $this->stack->get_amount() == 0) {
            $this->status = SeatStatus::SITOUT;
            return;
        }
        if ($this->player->get_bankroll()->get_amount() == 0) return;
        $current_stack = $this->stack->get_amount();
        if ($current_stack < $amount) {
            $diff_amount = $amount - $current_stack;
            $amount = min($diff_amount, $this->player->get_bankroll()->get_amount());
            $this->buy_chips($amount, true);
        }
    }

    public function prompt(array $options): void
    {
        switch ($this->player->type) {
            case PlayerType::HUMAN:
                $this->prompt_human($options);
                return;

            case PlayerType::AI:
                $this->prompt_ai($options);
                return;
        }
    }

    public function prompt_ai($options): void
    {
        // if (substr($options["c"], 0, 4) == "Call") $this->table->call($this);
        // else $this->table->check($this);
        // return;
        echo ($this->player->get_name() . " is thinking...\n");
        $answered = false;
        while (!$answered) {
            $model = "gpt-3.5-turbo-0613";
            $system_message = implode("\n", $this->table->get_chat_history(3596));
            $system_message .= "\n=============================================================\n";
            foreach ($this->table->pots as $key => $pot) {
                if ($key == 0) $pot_display_name = "Main Pot";
                else $pot_display_name = "Side Pot " . $key;
                $system_message .= $pot_display_name . ": $pot\n";
            }
            $system_message .= "Seat\tStack\tIn For\tName\tPocket\tHand\n";
            $system_message .= $this->seat_num . "\t" . $this->get_stack() . "\t$" . number_format($this->total_bet, 2, ".", ",") . "\t" . $this->player->get_name() . "\t" . $this->table->HandEvaluator->hand_toString($this->cards, $this->table->communityCards) . "\n";
            foreach ($options as $key => $option) {
                $system_message .= " [" . strtoupper($key) . "] " . $option . "\t";
            }
            $messages[] = ["role" => "system", "content" => $system_message];
            $messages[] = ["role" => "user", "content" => "Hey " . $this->player->get_name() . ", use the take_action function to make your move!"];
            $prompt = [
                "model" => $model,
                "messages" => $messages,
                "temperature" => 0,
                "top_p" => 0,
                "frequency_penalty" => 0,
                "presence_penalty" => 0,
                'functions' => [
                    [
                        'name' => 'take_action',
                        'description' => 'Make your move!',
                        'parameters' => [
                            'type' => 'object',
                            'properties' => [
                                'action' => [
                                    'type' => 'string',
                                    'description' => 'a single lower letter representing the action you want to take (c, f, r, b, a, q)',
                                ],
                                'amount' => [
                                    'type' => 'string',
                                    'description' => 'If betting or raising, the amount you want to bet or raise, dont use $ or ,',
                                ],
                                'chat_message' => [
                                    'type' => 'string',
                                    'description' => 'optional chat message to send to the table (playful fun good natured table banter) (fun part of the game!)',
                                ],
                            ],
                            'required' => ['action', 'amount', 'chat_message']
                        ],
                    ],
                ],
            ];
            $response = $this->openai->chat()->create($prompt);
            foreach ($response->choices as $result) {
                if ($result->finishReason == "function_call") {
                    if ($result->message->functionCall->name == "take_action") {
                        $json_string = $result->message->functionCall->arguments;
                        $data = json_decode($json_string, true);
                        if (isset($data["chat_message"]) && $data["chat_message"] != "") $this->table->chat($this->player->get_name() . " said: " . $data["chat_message"]);
                        if (array_key_exists($data["action"], $options)) {
                            $answered = true;
                            $char = $data["action"];
                            switch ($char) {
                                case "c":
                                    if (substr($options["c"], 0, 4) == "Call") $this->table->call($this);
                                    else $this->table->check($this);
                                    break;
                                case "f":
                                    $this->table->fold($this);
                                    break;
                                case "r":
                                    $amount = 0;
                                    while ($amount <= 0) {
                                        $amount = (float)$data["amount"];
                                        if (!is_numeric($amount) || $amount <= 0) {
                                            echo ("AI returned invalid amount, Retrying...\n");
                                            $answered = false;
                                        }
                                    }
                                    $this->table->raise($this, $amount);
                                    break;
                                case "b":
                                    $amount = 0;
                                    while ($amount <= 0) {
                                        $amount = (float)$data["amount"];
                                        if (!is_numeric($amount) || $amount <= 0) {
                                            echo ("Invalid amount, Please try again...\n");
                                            $answered = false;
                                        }
                                    }
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
                        }
                    }
                }
            }
        }
    }

    public function prompt_human($options): void
    {
        // if (substr($options["c"], 0, 4) == "Call") $this->table->call($this);
        // else $this->table->check($this);
        // return;
        echo ("=============================================================\n");
        foreach ($this->table->pots as $key => $pot) {
            if ($key == 0) $pot_display_name = "Main Pot";
            else $pot_display_name = "Side Pot " . $key;
            echo ($pot_display_name . ": $pot\n");
        }
        echo ("Seat\tStack\tIn For\tName\tPocket\tHand\n");
        echo ($this->seat_num . "\t" . $this->get_stack() . "\t$" . number_format($this->total_bet, 2, ".", ",") . "\t" . $this->player->get_name() . "\t" . $this->table->HandEvaluator->hand_toString($this->cards, $this->table->communityCards) . "\n");
        foreach ($options as $key => $option) {
            echo (" [" . strtoupper($key) . "] " . $option . "\t");
        }
        echo (" [T] Chat\n");
        echo ($this->player->get_name() . ": ");
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
                    if ($message != "") $this->table->chat($this->player->get_name() . " said: " . $message);
                    readline_callback_handler_install('', function () {
                    });
                    echo ($this->player->get_name() . ": ");
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
                $amount = 0;
                while ($amount <= 0) {
                    echo ("Raise amount: ");
                    $handle = fopen("php://stdin", "r");
                    $amount = (float)fgets($handle);
                    fclose($handle);
                    if (!is_numeric($amount) || $amount <= 0) echo ("Invalid amount, Please try again...\n");
                }
                $this->table->raise($this, $amount);
                break;
            case "b":
                $amount = 0;
                while ($amount <= 0) {
                    echo ("Bet amount: ");
                    $handle = fopen("php://stdin", "r");
                    $amount = (float)fgets($handle);
                    fclose($handle);
                    if (!is_numeric($amount) || $amount <= 0) echo ("Invalid amount, Please try again...\n");
                }
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
    }
}
