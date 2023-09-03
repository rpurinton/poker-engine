<?php

namespace RPurinton\poker;

require_once(__DIR__ . '/Card.php');
require_once(__DIR__ . '/Player.php');
require_once(__DIR__ . '/SeatStatus.php');
require_once(__DIR__ . '/Table.php');

class Seat
{
    private SeatStatus $status = SeatStatus::EMPTY;
    private ?Player $player = null;
    public array $cards = [];
    private Pot $stack;

    public function __construct()
    {
        $this->stack = new Pot(0);
    }

    public function getStatus(): SeatStatus
    {
        return $this->status;
    }

    public function setStatus(SeatStatus $status): void
    {
        $this->status = $status;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): void
    {
        $this->player = $player;
    }

    public function getStack(): Pot
    {
        return $this->stack;
    }

    public function __toString(): string
    {
        return $this->player->getName();
    }

    public function clearCards(): void
    {
        $this->cards = [];
    }

    public function buyChips(float $amount): void
    {
        $amount = min($amount, $this->player->getBankroll()->getAmount());
        $this->player->getBankroll()->remove($amount);
        $this->stack->add($amount);
    }

    public function topUp(float $amount): void
    {
        $current_stack = $this->stack->getAmount();
        if ($current_stack < $amount) {
            $this->buyChips($amount - $current_stack);
        }
    }

    public function prompt(Table $table): void
    {
        switch ($this->player->type) {
            case PlayerType::HUMAN:
                echo ($this->player->getName() . "'s cards: [" . implode('] [', $this->cards) . "]\n");
                break;
            case PlayerType::BOT:
                echo ($this->player->getName() . "'s cards: [" . implode('] [', $this->cards) . "]\n");
                break;
        }
    }
}
