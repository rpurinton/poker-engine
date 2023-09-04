<?php

namespace RPurinton\poker;

class Straight
{
    public static function is(array $combos): bool
    {
        if ((count($combos) === 1) && count($combos[0]) === 2) return false;
        return count(self::possibles($combos)) > 0;
    }

    public static function possibles(array $combos): array
    {
        $possibles = [];
        foreach ($combos as $combo) {
            $straight_string = "";
            foreach ($combo as $card) {
                $straight_string .= $card->getRank()->display();
            }
            $flush = false;
            $straight = false;
            if ($straight_string == "2345A") $straight = true;
            if ($straight_string == "23456") $straight = true;
            if ($straight_string == "34567") $straight = true;
            if ($straight_string == "45678") $straight = true;
            if ($straight_string == "56789") $straight = true;
            if ($straight_string == "6789T") $straight = true;
            if ($straight_string == "789TJ") $straight = true;
            if ($straight_string == "89TJQ") $straight = true;
            if ($straight_string == "9TJQK") $straight = true;
            if ($straight_string == "TJQKA") $straight = true;
            if ($flush && $straight) $possibles[] = [
                "hand" => $combo,
                "kicker1" => $combo[4]->getRank()->numeric(),
                "display" => $combo[4]->getRank()->display_long() . "high STRAIGHT [" . implode("] [", $combo) . "]"
            ];
        }
        return $possibles;
    }

    public static function best(array $possibles): array
    {
        if (count($possibles) === 0) return [];
        if (count($possibles) === 1) foreach ($possibles as $index => $possible) return [$index => $possible];
        $best_kicker1 = 0;
        foreach ($possibles as $possible) {
            if ($possible["kicker1"] > $best_kicker1) {
                $best_kicker1 = $possible["kicker1"];
            }
        }
        $best = [];
        foreach ($possibles as $index => $possible) {
            if ($possible["kicker1"] === $best_kicker1) {
                $best[$index] = $possible;
            }
        }
        return $best;
    }

    public static function toString(array $combos): string
    {
        $best = self::best(self::possibles($combos));
        foreach ($best as $combo) return $combo["display"];
    }
}
