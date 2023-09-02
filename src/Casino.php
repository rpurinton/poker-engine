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
