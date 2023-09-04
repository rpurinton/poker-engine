<?php

namespace RPurinton\poker;

class ThreeofaKind
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
            $tok_string = "";
            foreach ($combo as $card) {
                $tok_string .= $card->getRank()->display();
            }
            if ($tok_string[0] === $tok_string[1] && $tok_string[1] === $tok_string[2]) {
                $possibles[] = [
                    "hand" => $combo,
                    "main_rank" => $combo[0]->getRank()->numeric(),
                    "kicker1" => $combo[4]->getRank()->numeric(),
                    "kicker2" => $combo[3]->getRank()->numeric(),
                    "display" => "THREE-OF-A-KIND " . $combo[0]->getRank()->display_long() . "s (" . $combo[4]->getRank()->display_long() . "/" . $combo[3]->getRank()->display_long() . " kickers) [" . implode("] [", $combo) . "]"
                ];
            }
            if ($tok_string[1] === $tok_string[2] && $tok_string[2] === $tok_string[3]) {
                $possibles[] = [
                    "hand" => $combo,
                    "main_rank" => $combo[1]->getRank()->numeric(),
                    "kicker1" => $combo[4]->getRank()->numeric(),
                    "kicker2" => $combo[0]->getRank()->numeric(),
                    "display" => "THREE-OF-A-KIND " . $combo[1]->getRank()->display_long() . "s (" . $combo[4]->getRank()->display_long() . "/" . $combo[0]->getRank()->display_long() . " kickers) [" . implode("] [", $combo) . "]"
                ];
            }
            if ($tok_string[2] === $tok_string[3] && $tok_string[3] === $tok_string[4]) {
                $possibles[] = [
                    "hand" => $combo,
                    "main_rank" => $combo[2]->getRank()->numeric(),
                    "kicker1" => $combo[1]->getRank()->numeric(),
                    "kicker2" => $combo[0]->getRank()->numeric(),
                    "display" => "THREE-OF-A-KIND " . $combo[2]->getRank()->display_long() . "s (" . $combo[1]->getRank()->display_long() . "/" . $combo[0]->getRank()->display_long() . " kickers) [" . implode("] [", $combo) . "]"
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
        foreach ($possibles as $possible) {
            if ($possible["main_rank"] > $best_main) {
                $best_main = $possible["main_rank"];
                $best_kicker1 = $possible["kicker1"];
                $best_kicker2 = $possible["kicker2"];
            }
            if ($possible["main_rank"] === $best_main && $possible["kicker1"] > $best_kicker1) {
                $best_kicker1 = $possible["kicker1"];
                $best_kicker2 = $possible["kicker2"];
            }
            if ($possible["main_rank"] === $best_main && $possible["kicker1"] === $best_kicker1 && $possible["kicker2"] > $best_kicker2) {
                $best_kicker2 = $possible["kicker2"];
            }
        }
        $best = [];
        foreach ($possibles as $index => $possible) {
            if ($possible["main_rank"] === $best_main && $possible["kicker1"] === $best_kicker1 && $possible["kicker2"] === $best_kicker2) {
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
