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
        $conf_dir = __DIR__ . "/../conf.d";
        if (!file_exists($conf_dir)) $conf_dir = __DIR__ . "/conf.d";
        $openai_file = $conf_dir . "/openai.json";
        if (!file_exists($openai_file)) {
            $conf_file["token"] = "<paste your openai api key here>";
            file_put_contents($openai_file, json_encode($conf_file, JSON_PRETTY_PRINT));
            echo ("OpenAI API Key not found, please paste your API key into the file: $openai_file\n");
            exit();
        }
        $conf_data = json_decode(file_get_contents($openai_file));
        if (!isset($conf_data->token) || $conf_data->token == "" || $conf_data->token == "<paste your openai api key here>") {
            echo ("OpenAI API Key not found, please paste your API key into the file: $openai_file\n");
            exit();
        }
        if ($player->type == PlayerType::AI) $this->openai = OpenAI::client($conf_data->token);
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
        //echo ($this->player->get_name() . " [" . implode("] [", $this->cards) . "] " . $this->table->HandEvaluator->hand_toString($this->cards, $this->table->communityCards) . "...\n");
        echo ($this->player->get_name() . "...");
        $answered = false;
        while (!$answered) {
            $messages = [];
            $model = "gpt-3.5-turbo-0613";
            $system_message2 = "Welcome to the poker game! Get ready for an exciting round of Texas Hold'em. As you navigate through each hand, remember to consider the following factors that can influence your decision-making:

                Hand Strength: Evaluate the ranking and potential of your starting hand versus your opponents ranges.
                Chip Stack Size: Assess your chip stack compared to blinds and antes.
                Position at the Table: Utilize your position to make informed decisions.
                Tournament Stage: Consider the stage of the tournament and number of players at the table.
                Betting History: Analyze the betting patterns and tendencies of your opponents.
                Table Dynamics: Observe player interactions, chat messages, and betting behavior.
                Blind Levels and Tournament Stage: Adapt your strategy based on the stage of the tournament and number of players at the table.
                Stack-to-Pot Ratio (SPR): Consider the relationship between your chip stack and the pot.
                Player Image: Be aware of your own table image and use it strategically.
                
                Incorporating these factors into your decision-making will lead to a more strategic and profitable gameplay experience. Good luck, and may the best hand win!";
            $messages[] = ["role" => "system", "content" => $this->minify_prompt($system_message2)];
            $system_message1 = "Your name is " . $this->player->get_name() . " and you are in seat " . $this->seat_num . "\n";
            $system_message1 .= "Everyone starts with 1500 chips and the blinds start at $10/$20 and go up every 9 hands.\n";
            $system_message1 .= "You are currently in hand #" . $this->table->hand_count . " of the tournament and the blinds are currently at $" . number_format($this->table->config["smallBlind"], 2, ".", ",") . "/$" . number_format($this->table->config["bigBlind"], 2, ".", ",") . "\n";
            $system_message1 .= "We have included as much of the recent history of the table chat and the current state of the table below.\n";
            $messages[] = ["role" => "system", "content" => $this->minify_prompt($system_message1)];
            $user_message1 = implode("\n", $this->table->get_chat_history(3072));
            $messages[] = ["role" => "user", "content" => $this->minify_prompt($user_message1)];
            $user_message1 = "Current Action is on:\n";
            $user_message1 .= "Seat\tStack\tIn For\tName\tPocket\tHand\n";
            $user_message1 .= $this->seat_num . "\t" . $this->get_stack() . "\t$" . number_format($this->total_bet, 2, ".", ",") . "\t" . $this->player->get_name() . "\t [" . implode("] [", $this->cards) . "]\t" . $this->table->HandEvaluator->hand_toString($this->cards, $this->table->communityCards) . "\n";
            $user_message1 .= "Community Cards: [" . implode("] [", $this->table->communityCards) . "]\n";
            $messages[] = ["role" => "user", "content" => $this->minify_prompt($user_message1)];
            $user_message2 = "Hey " . $this->player->get_name() . " its your move... play smart!  if you have the nuts you must raise on the river! use GTO strategy to determine the best move in this specific scenario and then take_action!\n";
            $messages[] = ["role" => "user", "content" => $this->minify_prompt($user_message2)];
            $options_json = json_encode($options);
            //print_r($options);
$prompt = [
    "model" => $model,
    "messages" => $messages,
    "temperature" => 0.986,
    "top_p" => 0.986,
    "frequency_penalty" => 1,
    "presence_penalty" => 1,
    'functions' => [
        [
            'name' => 'take_action',
            'description' => 'Make your move!',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'action' => [
                        'type' => 'string',
                        'description' => "a single lower letter representing the action you want to take of the following available options only:\n$options_json",
                    ],
                    'amount' => [
                        'type' => 'string',
                        'description' => 'If betting or raising, the amount you want to raise increase the total bet by or to. Numerical formatted (float value)',
                    ],
                    'chat_message' => [
                        'type' => 'string',
                        'description' => 'continue the chat conversation... the chat message to send to the table (playful fun good natured table banter) (fun part of the game!) (dont reapeat the same message over and over, be creative!)',
                    ],
                ],
                'required' => ['action', 'amount', 'chat_message']
            ],
        ],
    ],
];
            try {
                $response = $this->openai->chat()->create($prompt);
            } catch (\Exception $e) {
                //echo ("Error: " . $e->getMessage() . "\n");
                echo (".");
                continue;
            } catch (\Throwable $e) {
                //echo ("Error: " . $e->getMessage() . "\n");
                echo (".");
                continue;
            } catch (\Error $e) {
                //echo ("Error: " . $e->getMessage() . "\n");
                echo (".");
                continue;
            }
            foreach ($response->choices as $result) {
                if ($result->finishReason == "function_call") {
                    if ($result->message->functionCall->name == "take_action") {
                        $json_string = $result->message->functionCall->arguments;
                        $data = json_decode($json_string, true);
                        //print_r($data);
                        if (isset($data["chat_message"]) && $data["chat_message"] != "") $this->table->chat($this->player->get_name() . " said: " . $data["chat_message"]);
                        $char = strtolower(substr($data["action"], 0, 1));
                        if ($char == "r") $char = "b";
                        if (array_key_exists($char, $options)) {
                            $answered = true;
                            switch ($char) {
                                case "c":
                                    if (substr($options["c"], 0, 4) == "Call") $this->table->call($this);
                                    else $this->table->check($this);
                                    break;
                                case "f":
                                    $this->table->fold($this);
                                    break;
                                case "b":
                                    $amount = str_replace(",", "", $data["amount"]);
                                    $amount = str_replace("$", "", $amount);
                                    $amount = str_replace("<", "", $amount);
                                    $amount = str_replace(">", "", $amount);
                                    $amount = str_replace(" ", "", $amount);
                                    $amount = str_replace("[", "", $amount);
                                    $amount = str_replace("]", "", $amount);
                                    $amount = (float)$amount;
                                    if (!is_numeric($amount) || $amount <= 0) {
                                        echo ("AI returned invalid amount (" . $data["amount"] . "), Retrying...\n");
                                        $answered = false;
                                    } else $this->table->raise_by($this, $amount);
                                    break;
                                case "a":
                                    $this->table->all_in($this);
                                    break;
                                default:
                                    echo ("AI returned invalid action (" . $data["action"] . "), Retrying...\n");
                                    $answered = false;
                                    break;
                            }
                        }
                    }
                }
            }
            if (!$answered) echo (".");
        }
    }

    public function prompt_human($options): void
    {
        echo ("=======================IT'S ON YOU!==========================\n");
        echo ("Stack\t\tIn For\tName\tPocket\n");
        echo ($this->get_stack() . "\t$" . number_format($this->total_bet, 2, ".", ",") . "\t" . $this->player->get_name() . "\t[" . implode("] [", $this->cards) . "]\nYour best hand is: " . $this->table->HandEvaluator->hand_toString($this->cards, $this->table->communityCards) . "\n");
        foreach ($options as $key => $option) {
            echo (" [" . strtoupper($key) . "] " . $option . "\n");
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
            case "b":
                $amount = 0;
                while ($amount <= 0) {
                    echo ($options["b"] . ": ");
                    $handle = fopen("php://stdin", "r");
                    $amount = (float)fgets($handle);
                    fclose($handle);
                    if (!is_numeric($amount) || $amount <= 0) echo ("Invalid amount, Please try again...\n");
                }
                $this->table->raise_by($this, $amount);
                break;
            case "a":
                $this->table->all_in($this);
                break;
            case "q":
                echo ("Thanks for Playing!\n");
                exit();
                break;
        }
    }
    private function minify_prompt(string $text): string
    {
        // remove null chars
        $text = str_replace("\0", "", $text);
        // replace tabs with spaces
        $text = str_replace("\t", " ", $text);
        // remove all double spaces
        while (strpos($text, "  ") !== false) $text = str_replace("  ", " ", $text);
        // remove all blank lines
        while (strpos($text, "\n\n") !== false) $text = str_replace("\n\n", "\n", $text);
        return $text;
    }
}
