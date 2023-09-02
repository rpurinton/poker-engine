<?php

namespace RPurinton\poker;

class Pot
{
    public function __construct(private float $amount = 0)
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

    public function __toString(): string
    {
        return '$' . number_format($this->amount, 2, '.', ',');
    }
}
