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

    public function get_amount(): float
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
        $seat->get_stack()->remove($amount);
        if (!isset($this->eligible[$seat->seat_num])) $this->eligible[$seat->seat_num] = ["seat" => $seat, "contributed" => 0];
        $this->eligible[$seat->seat_num]["contributed"] += $amount;
    }

    public function payout(array $winner_indexes, string $display_name): array
    {
        $results = [];
        $amount = round($this->amount / count($winner_indexes), 2);
        foreach ($winner_indexes as $index => $hand_toString) {
            $results[] = $this->eligible[$index]["seat"]->get_player()->get_name() . " wins $" . number_format($amount, 2, '.', ',') . " from " . $display_name . " with " . $hand_toString;
            $this->eligible[$index]["seat"]->get_stack()->add($amount);
            $this->remove($amount);
        }
        if ($this->amount) {
            $results[] = "The extra $" . number_format($this->amount, 2, '.', ',') . " goes to " . $this->eligible[$index]["seat"]->get_player()->get_name();
            $this->eligible[$index]["seat"]->get_stack()->add($this->amount);
            $this->remove($this->amount);
        }
        return $results;
    }

    public function payout_last_player(string $display_name): array
    {
        $seat = array_pop($this->eligible);
        $seat = $seat["seat"];
        $results = [];
        $results[] = $seat->get_player()->get_name() . " wins $" . number_format($this->amount, 2, '.', ',') . " from " . $display_name;
        $seat->get_stack()->add($this->amount);
        $this->remove($this->amount);
        return $results;
    }

    public function __toString(): string
    {
        return '$' . number_format($this->amount, 2, '.', ',');
    }
}
