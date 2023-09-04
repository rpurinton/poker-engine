<?php

namespace RPurinton\poker;

class TwoPair
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
            $tp_string = "";
            foreach ($combo as $card) {
                $tp_string .= $card->getRank()->display();
            }
            if ($tp_string[0] === $tp_string[1] && $tp_string[2] === $tp_string[3]) {
                $possibles[] = [
                    "hand" => $combo,
                    "main1" => $combo[2]->getRank()->numeric(),
                    "main2" => $combo[0]->getRank()->numeric(),
                    "kicker" => $combo[4]->getRank()->numeric(),
                    "display" => "TWO-PAIR " . $combo[2]->getRank()->display_long() . "s over " . $combo[0]->getRank()->display_long() . " (" . $combo[4]->getRank()->display_long() . " kicker) [" . implode("] [", $combo) . "]"
                ];
            }
            if ($tp_string[0] === $tp_string[1] && $tp_string[3] === $tp_string[4]) {
                $possibles[] = [
                    "hand" => $combo,
                    "main1" => $combo[3]->getRank()->numeric(),
                    "main2" => $combo[0]->getRank()->numeric(),
                    "kicker" => $combo[2]->getRank()->numeric(),
                    "display" => "TWO-PAIR " . $combo[3]->getRank()->display_long() . "s over " . $combo[0]->getRank()->display_long() . " (" . $combo[2]->getRank()->display_long() . " kicker) [" . implode("] [", $combo) . "]"
                ];
            }
            if ($tp_string[1] === $tp_string[2] && $tp_string[3] === $tp_string[4]) {
                $possibles[] = [
                    "hand" => $combo,
                    "main1" => $combo[3]->getRank()->numeric(),
                    "main2" => $combo[1]->getRank()->numeric(),
                    "kicker" => $combo[0]->getRank()->numeric(),
                    "display" => "TWO-PAIR " . $combo[3]->getRank()->display_long() . "s over " . $combo[1]->getRank()->display_long() . " (" . $combo[0]->getRank()->display_long() . " kicker) [" . implode("] [", $combo) . "]"
                ];
            }
        }
        return $possibles;
    }

    public static function best(array $possibles): array
    {
        if (count($possibles) === 0) return [];
        if (count($possibles) === 1) foreach ($possibles as $index => $possible) return [$index => $possible];
        $best_main1 = 0;
        $best_main2 = 0;
        $best_kicker = 0;
        foreach ($possibles as $possible) {
            if ($possible["main1"] > $best_main1) {
                $best_main1 = $possible["main1"];
                $best_main2 = $possible["main2"];
                $best_kicker = $possible["kicker"];
            }
            if ($possible["main1"] === $best_main1 && $possible["main2"] > $best_main2) {
                $best_main2 = $possible["main2"];
                $best_kicker = $possible["kicker"];
            }
            if ($possible["main1"] === $best_main1 && $possible["main2"] === $best_main2 && $possible["kicker"] > $best_kicker) {
                $best_kicker = $possible["kicker"];
            }
        }
        $best = [];
        foreach ($possibles as $index => $possible) {
            if ($possible["main1"] === $best_main1 && $possible["main2"] === $best_main2 && $possible["kicker"] === $best_kicker) {
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
