<?php

namespace RPurinton\poker;

class FourofaKind
{
    public static function is(array $combos): bool
    {
        if ((count($combos) === 1) && count($combos[0]) === 2) return false;
        return count(self::possibles($combos)) > 0;
    }

    public static function possibles(array $combos): array
    {
        $possibles = [];
        foreach ($combos as $index => $combo) {
            $fok_string = "";
            foreach ($combo as $card) {
                $fok_string .= $card->getRank()->display();
            }
            if ($fok_string[0] === $fok_string[1] && $fok_string[1] === $fok_string[2] && $fok_string[2] === $fok_string[3]) {
                $possibles[$index] = [
                    "hand" => $combo,
                    "main_rank" => $combo[0]->getRank()->numeric(),
                    "kicker_rank" => $combo[4]->getRank()->numeric(),
                    "display" => "FOUR-OF-A-KIND " . $combo[0]->getRank()->display_long() . "s (" . $combo[4]->getRank()->display_long() . " kicker) [" . implode("] [", $combo) . "]"
                ];
            }
            if ($fok_string[1] === $fok_string[2] && $fok_string[2] === $fok_string[3] && $fok_string[3] === $fok_string[4]) {
                $possibles[$index] = [
                    "hand" => $combo,
                    "main_rank" => $combo[1]->getRank()->numeric(),
                    "kicker_rank" => $combo[0]->getRank()->numeric(),
                    "display" => "FOUR-OF-A-KIND " . $combo[1]->getRank()->display_long() . "s (" . $combo[0]->getRank()->display_long() . " kicker) [" . implode("] [", $combo) . "]"
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
        $best_kicker = 0;
        foreach ($possibles as $possible) {
            if ($possible["main_rank"] > $best_main) {
                $best_main = $possible["main_rank"];
                $best_kicker = $possible["kicker_rank"];
            }
            if ($possible["main_rank"] === $best_main && $possible["kicker_rank"] > $best_kicker) {
                $best_kicker = $possible["kicker_rank"];
            }
        }
        $best = [];
        foreach ($possibles as $index => $possible) {
            if ($possible["main_rank"] === $best_main && $possible["kicker_rank"] === $best_kicker) {
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
