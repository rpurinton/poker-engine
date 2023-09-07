<?php

namespace RPurinton\poker;

require_once(__DIR__ . '/Pot.php');

class PotManager
{
    public function __construct(public Table $table)
    {
        echo "PotManager class loaded\n";
        return ($this);
    }

    public function split_pots(): void
    {
        $current_pot = count($this->table->pots) - 1;
        $eligible = $this->table->pots[$current_pot]->eligible;
        if (count($eligible) > 1) {
            $contributions = [];
            foreach ($eligible as $seat) {
                $contributions[] = $seat["contributed"];
            }
            $max_contribution = max($contributions);
            $min_contribution = min($contributions);
            while ($max_contribution > $min_contribution) {
                $this->table->pots[$current_pot + 1] = new Pot(0);
                $eligible = $this->table->pots[$current_pot]->eligible;
                $contributions = [];
                foreach ($eligible as $seat) {
                    $contributions[] = $seat["contributed"];
                }
                $max_contribution = max($contributions);
                $min_contribution = min($contributions);
                foreach ($eligible as $seat_num => $seat) {
                    if ($seat["contributed"] > $min_contribution) {
                        $diff_amount = $seat["contributed"] - $min_contribution;
                        $this->table->pots[$current_pot]->uncontribute($diff_amount, $seat["seat"]);
                        $this->table->pots[$current_pot + 1]->contribute($diff_amount, $seat["seat"]);
                    }
                }
                $current_pot++;
                $eligible = $this->table->pots[$current_pot]->eligible;
                $contributions = [];
                foreach ($eligible as $seat) {
                    $contributions[] = $seat["contributed"];
                }
                $max_contribution = max($contributions);
                $min_contribution = min($contributions);
            }
        }
    }

    public function all_pots_are_good(): bool
    {
        foreach ($this->table->seats as $seat) {
            if ($seat->status == SeatStatus::UPCOMING_ACTION) return false;
        }

        foreach ($this->table->pots as $pot) {
            // check if all contributions are equal
            $contributions = [];
            foreach ($pot->eligible as $seat) {
                $contributions[] = $seat["contributed"];
            }
            if (count(array_unique($contributions)) !== 1) {
                return false;
            }
        }
        return true;
    }
}
