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

    public function __toString(): string
    {
        return $this->name;
    }
}
