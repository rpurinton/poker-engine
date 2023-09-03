<?php

namespace RPurinton\poker;

require_once(__DIR__ . '/Pot.php');
require_once(__DIR__ . '/PlayerType.php');
require_once(__DIR__ . '/PlayerStatus.php');

class Player
{
    private PlayerStatus $status = PlayerStatus::STANDING;
    private ?Pot $bankroll;
    public function __construct(
        private string $name,
        public PlayerType $type = PlayerType::HUMAN
    ) {
        $this->bankroll = new Pot(0);
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

    public function setStatus(PlayerStatus $status): void
    {
        $this->status = $status;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
