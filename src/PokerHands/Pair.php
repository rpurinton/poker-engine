<?php

namespace RPurinton\poker;

class Pair
{
    public static function is(array $combos): bool
    {
        if (count($combos) === 1) foreach ($combos as $combo) if (count($combo) === 2) {
            return $combo[0]->getRank()->numeric() === $combo[1]->getRank()->numeric();
        }
        return count(self::possibles($combos)) > 0;
    }

    public static function possibles(array $combos): array
    {
        $possibles = [];
        foreach ($combos as $combo) {
            if (count($combo) === 2) {
                if ($combo[0]->getRank()->numeric() === $combo[1]->getRank()->numeric()) {
                    return [
                        [
                            "hand" => $combo,
                            "main" => $combo[0]->getRank()->numeric(),
                            "display" => "PAIR of " . $combo[0]->getRank()->display_long() . "s [" . implode("] [", $combo) . "]"
                        ]
                    ];
                }
            }
            $pair_string = "";
            foreach ($combo as $card) {
                $pair_string .= $card->getRank()->display();
            }
            if ($pair_string[0] === $pair_string[1]) {
                $possibles[] = [
                    "hand" => $combo,
                    "main" => $combo[0]->getRank()->numeric(),
                    "kicker1" => $combo[4]->getRank()->numeric(),
                    "kicker2" => $combo[3]->getRank()->numeric(),
                    "kicker3" => $combo[2]->getRank()->numeric(),
                    "display" => "PAIR of " . $combo[0]->getRank()->display_long() . "s (" . $combo[4]->getRank()->display_long() . " kicker) [" . implode("] [", $combo) . "]"
                ];
            }
            if ($pair_string[1] === $pair_string[2]) {
                $possibles[] = [
                    "hand" => $combo,
                    "main" => $combo[1]->getRank()->numeric(),
                    "kicker1" => $combo[4]->getRank()->numeric(),
                    "kicker2" => $combo[3]->getRank()->numeric(),
                    "kicker3" => $combo[0]->getRank()->numeric(),
                    "display" => "PAIR of " . $combo[1]->getRank()->display_long() . "s (" . $combo[4]->getRank()->display_long() . " kicker) [" . implode("] [", $combo) . "]"
                ];
            }
            if ($pair_string[2] === $pair_string[3]) {
                $possibles[] = [
                    "hand" => $combo,
                    "main" => $combo[2]->getRank()->numeric(),
                    "kicker1" => $combo[4]->getRank()->numeric(),
                    "kicker2" => $combo[1]->getRank()->numeric(),
                    "kicker3" => $combo[0]->getRank()->numeric(),
                    "display" => "PAIR of " . $combo[2]->getRank()->display_long() . "s (" . $combo[4]->getRank()->display_long() . " kicker) [" . implode("] [", $combo) . "]"
                ];
            }
            if ($pair_string[3] === $pair_string[4]) {
                $possibles[] = [
                    "hand" => $combo,
                    "main" => $combo[3]->getRank()->numeric(),
                    "kicker1" => $combo[2]->getRank()->numeric(),
                    "kicker2" => $combo[1]->getRank()->numeric(),
                    "kicker3" => $combo[0]->getRank()->numeric(),
                    "display" => "PAIR of " . $combo[3]->getRank()->display_long() . "s (" . $combo[2]->getRank()->display_long() . " kicker) [" . implode("] [", $combo) . "]"
                ];
            }
        }
        return $possibles;
    }

    public static function best(array $possibles): array
    {
        if (count($possibles) === 0) return [];
        if (count($possibles) === 1) foreach ($possibles as $index => $possible) return [$index => $possible];
        $best_main = 0;
        $best_kicker1 = 0;
        $best_kicker2 = 0;
        $best_kicker3 = 0;
        foreach ($possibles as $possible) {
            if ($possible["main"] > $best_main) {
                $best_main = $possible["main"];
                $best_kicker1 = $possible["kicker1"];
                $best_kicker2 = $possible["kicker2"];
                $best_kicker3 = $possible["kicker3"];
            }
            if ($possible["main"] === $best_main && $possible["kicker1"] > $best_kicker1) {
                $best_kicker1 = $possible["kicker1"];
                $best_kicker2 = $possible["kicker2"];
                $best_kicker3 = $possible["kicker3"];
            }
            if ($possible["main"] === $best_main && $possible["kicker1"] === $best_kicker1 && $possible["kicker2"] > $best_kicker2) {
                $best_kicker2 = $possible["kicker2"];
                $best_kicker3 = $possible["kicker3"];
            }
            if ($possible["main"] === $best_main && $possible["kicker1"] === $best_kicker1 && $possible["kicker2"] === $best_kicker2 && $possible["kicker3"] > $best_kicker3) {
                $best_kicker3 = $possible["kicker3"];
            }
        }
        $best = [];
        foreach ($possibles as $index => $possible) {
            if ($possible["main"] === $best_main && $possible["kicker1"] === $best_kicker1 && $possible["kicker2"] === $best_kicker2 && $possible["kicker3"] === $best_kicker3) {
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
