<?php

namespace RPurinton\poker;

class Flush
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
            $flush_string = "";
            foreach ($combo as $card) {
                $flush_string .= $card->getSuit()->display();
            }
            $flush = false;
            if ($flush_string == "ccccc") $flush = true;
            if ($flush_string == "ddddd") $flush = true;
            if ($flush_string == "hhhhh") $flush = true;
            if ($flush_string == "sssss") $flush = true;
            if ($flush) $possibles[] = [
                "hand" => $combo,
                "kicker1" => $combo[4]->getRank()->numeric(),
                "kicker2" => $combo[3]->getRank()->numeric(),
                "kicker3" => $combo[2]->getRank()->numeric(),
                "kicker4" => $combo[1]->getRank()->numeric(),
                "kicker5" => $combo[0]->getRank()->numeric(),
                "display" => $combo[4]->getRank()->display_long() . "high FLUSH [" . implode("] [", $combo) . "]"
            ];
        }
        return $possibles;
    }

    public static function best(array $possibles): array
    {
        if (count($possibles) === 0) return [];
        if (count($possibles) === 1) foreach ($possibles as $index => $possible) return [$index => $possible];
        $best_kicker1 = 0;
        $best_kicker2 = 0;
        $best_kicker3 = 0;
        $best_kicker4 = 0;
        $best_kicker5 = 0;
        foreach ($possibles as $possible) {
            if ($possible["kicker1"] > $best_kicker1) {
                $best_kicker1 = $possible["kicker1"];
                $best_kicker2 = $possible["kicker2"];
                $best_kicker3 = $possible["kicker3"];
                $best_kicker4 = $possible["kicker4"];
                $best_kicker5 = $possible["kicker5"];
            }
            if ($possible["kicker1"] === $best_kicker1 && $possible["kicker2"] > $best_kicker2) {
                $best_kicker2 = $possible["kicker2"];
                $best_kicker3 = $possible["kicker3"];
                $best_kicker4 = $possible["kicker4"];
                $best_kicker5 = $possible["kicker5"];
            }
            if ($possible["kicker1"] === $best_kicker1 && $possible["kicker2"] === $best_kicker2 && $possible["kicker3"] > $best_kicker3) {
                $best_kicker3 = $possible["kicker3"];
                $best_kicker4 = $possible["kicker4"];
                $best_kicker5 = $possible["kicker5"];
            }
            if ($possible["kicker1"] === $best_kicker1 && $possible["kicker2"] === $best_kicker2 && $possible["kicker3"] === $best_kicker3 && $possible["kicker4"] > $best_kicker4) {
                $best_kicker4 = $possible["kicker4"];
                $best_kicker5 = $possible["kicker5"];
            }
            if ($possible["kicker1"] === $best_kicker1 && $possible["kicker2"] === $best_kicker2 && $possible["kicker3"] === $best_kicker3 && $possible["kicker4"] === $best_kicker4 && $possible["kicker5"] > $best_kicker5) {
                $best_kicker5 = $possible["kicker5"];
            }
        }
        $best = [];
        foreach ($possibles as $index => $possible) {
            if ($possible["kicker1"] === $best_kicker1 && $possible["kicker2"] === $best_kicker2 && $possible["kicker3"] === $best_kicker3 && $possible["kicker4"] === $best_kicker4 && $possible["kicker5"] === $best_kicker5) {
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
