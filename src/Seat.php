<?php

namespace RPurinton\poker;

require_once(__DIR__ . '/Card.php');
require_once(__DIR__ . '/Player.php');
require_once(__DIR__ . '/SeatStatus.php');

class Seat
{
    private SeatStatus $status = SeatStatus::EMPTY;
    private ?Player $player = null;
    private array $cards = [];
    private Pot $pot;

    public function __construct()
    {
        $this->pot = new Pot(0);
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

    public function getCards(): array
    {
        return $this->cards;
    }

    public function setCards(array $cards): void
    {
        $this->cards = $cards;
    }

    public function getPot(): Pot
    {
        return $this->pot;
    }

    public function setPot(Pot $pot): void
    {
        $this->pot = $pot;
    }

    public function __toString(): string
    {
        return $this->player->getName();
    }

    public function addCard(Card $card): void
    {
        $this->cards[] = $card;
    }

    public function removeCard(Card $card): void
    {
        $key = array_search($card, $this->cards);

        if ($key !== false) {
            unset($this->cards[$key]);
        }
    }

    public function clearCards(): void
    {
        $this->cards = [];
    }

    public function buyChips(float $amount): void
    {
        $this->player->getBankroll()->remove($amount);
        $this->pot->add($amount);
    }
}
