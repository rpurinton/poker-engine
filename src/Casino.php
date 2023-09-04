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

    public function get_name(): string
    {
        return $this->name;
    }

    public function set_name(string $name): void
    {
        if (strlen($name) < 3) {
            throw new \Exception('Name must be at least 3 characters.');
        }

        $this->name = $name;
    }

    public function add_table(Table $table): Table
    {
        $this->tables[] = $table;
        return $table;
    }

    public function add_player(Player $player): Player
    {
        $this->players[] = $player;
        return $player;
    }

    public function deposit(float $amount): void
    {
        $this->vault->add($amount);
    }

    public function withdraw(float $amount): void
    {
        $this->vault->remove($amount);
    }

    public function buy_chips(Player $player, float $amount): void
    {
        $this->deposit($amount);
        $player->get_bankroll()->add($amount);
    }

    public function cash_out(Player $player): void
    {
        $amount = $player->get_bankroll()->get_amount();
        $this->withdraw($player->get_bankroll()->get_amount());
        $player->get_bankroll()->setAmount(0);
        echo ($player->get_name() . " left with $" . number_format($amount, 2, '.', ',') . "\n");
    }

    public function cash_out_partial(Player $player, float $amount): void
    {
        $this->withdraw($amount);
        $player->get_bankroll()->remove($amount);
        echo ($player->get_name() . " cashed out $" . number_format($amount, 2, '.', ',') . ", still has $" . $player->get_bankroll() . "\n");
    }
}
