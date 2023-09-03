<?php

namespace RPurinton\poker;

class Pot
{
    public function __construct(private float $amount = 0, public array $eligible = [])
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
        $this->eligible[] = $seat;
    }

    public function __toString(): string
    {
        return '$' . number_format($this->amount, 2, '.', ',');
    }
}
