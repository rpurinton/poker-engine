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

    public function addTable(Table $table): void
    {
        $this->tables[] = $table;
    }

    public function addPlayer(Player $player): void
    {
        $this->players[] = $player;
    }

    public function depositToVault(float $amount): void
    {
        $this->vault->add($amount);
    }

    public function withdrawFromVault(float $amount): void
    {
        $this->vault->remove($amount);
    }
}
