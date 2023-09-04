<?php

namespace RPurinton\poker;

class Pot
{
    public array $eligible = [];

    public function __construct(private float $amount = 0, public bool $good = true)
    {
        $this->amount = round($this->amount, 2);
    }

    public function add(float $amount): void
    {
        $this->amount += round($amount, 2);
    }

    public function remove(float $amount): void
    {
        $this->amount -= round($amount, 2);
    }

    public function getAmount(): float
    {
        return round($this->amount, 2);
    }

    public function setAmount(float $amount): void
    {
        $this->amount = round($amount, 2);
    }

    public function contribute(float $amount, Seat $seat)
    {
        $this->add($amount);
        $seat->getStack()->remove($amount);
        $this->eligible[$seat->seat_num] = $seat;
    }

    public function payout(array $winner_indexes, string $display_name): array
    {
        $results = [];
        $amount = round($this->amount / count($winner_indexes), 2);
        foreach ($winner_indexes as $index) {
            $results[] = $this->eligible[$index]->getPlayer()->getName() . " wins $" . number_format($amount, 2, '.', ',') . " from " . $display_name;
            $this->eligible[$index]->getStack()->add($amount);
            $this->remove($amount);
        }
        if ($this->amount) {
            $results[] = "The extra $" . number_format($this->amount, 2, '.', ',') . " goes to " . $this->eligible[$index]->getPlayer()->getName();
            $this->eligible[$index]->getStack()->add($this->amount);
            $this->remove($this->amount);
        }
        return $results;
    }

    public function __toString(): string
    {
        return '$' . number_format($this->amount, 2, '.', ',');
    }
}
