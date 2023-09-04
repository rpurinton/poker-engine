<?php

namespace RPurinton\poker;

class HighCard
{
    public static function is(array $combos): bool
    {
        // all the other hand types were already checked, so must be a HighCard hand
        return true;
    }

    public static function possibles(array $combos): array
    {
        $possibles = [];
        foreach ($combos as $combo) {
            if (count($combo) === 2) {
                return [
                    [
                        "hand" => $combo,
                        "kicker1" => $combo[1]->getRank()->numeric(),
                        "kicker2" => $combo[0]->getRank()->numeric(),
                        "display" => $combo[1]->getRank()->display_long() . " HIGH (" . $combo[0]->getRank()->display_long() . " kicker) [" . implode("] [", $combo) . "]"
                    ]
                ];
            }
            $possibles[] = [
                "hand" => $combo,
                "kicker1" => $combo[4]->getRank()->numeric(),
                "kicker2" => $combo[3]->getRank()->numeric(),
                "kicker3" => $combo[2]->getRank()->numeric(),
                "kicker4" => $combo[1]->getRank()->numeric(),
                "kicker5" => $combo[0]->getRank()->numeric(),
                "display" => $combo[4]->getRank()->display_long() . " HIGH (" . $combo[3]->getRank()->display_long() . " kicker) [" . implode("] [", $combo) . "]"
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
