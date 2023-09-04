<?php

namespace RPurinton\poker;

require_once(__DIR__ . '/Pot.php');
require_once(__DIR__ . '/Enums/PlayerType.php');
require_once(__DIR__ . '/Enums/PlayerStatus.php');

class Player
{
    private PlayerStatus $status = PlayerStatus::STANDING;
    private ?Pot $bankroll;
    public function __construct(
        private string $name,
        public PlayerType $type = PlayerType::HUMAN,
        public bool $auto_top_up = true
    ) {
        $this->bankroll = new Pot(0);
    }

    public function get_name(): string
    {
        return $this->name;
    }

    public function get_bankroll(): Pot
    {
        return $this->bankroll;
    }

    public function setBankroll(Pot $bankroll): void
    {
        $this->bankroll = $bankroll;
    }

    public function set_status(PlayerStatus $status): void
    {
        $this->status = $status;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
