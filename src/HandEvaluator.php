<?php

namespace RPurinton\poker;

class HandEvaluator
{
    public function __construct(private GameType $GameType)
    {
    }

    public function setGameType(GameType $GameType): void
    {
        $this->GameType = $GameType;
    }

    public function hand_toString(array $holeCards, array $communityCards): string
    {
        switch ($this->GameType) {
            case GameType::TEXAS_HOLDEM:
                return $this->hand_toString_texas($holeCards, $communityCards);
            case GameType::OMAHA;
                return $this->hand_toString_omaha_hi($holeCards, $communityCards);
            case GameType::OMAHA_HILO;
                return $this->hand_toString_omaha_hi($holeCards, $communityCards) . $this->hand_toString_omaha_lo($holeCards, $communityCards);
        }
    }

    public function hand_toString_texas(array $holeCards, array $communityCards): string
    {
        switch (true) {
            case $this->is_royal_flush($holeCards, $communityCards):
                return $this->royal_flush_toString($holeCards, $communityCards);
            case $this->is_straight_flush($holeCards, $communityCards):
                return $this->straight_flush_toString($holeCards, $communityCards);
            case $this->is_four_of_a_kind($holeCards, $communityCards):
                return $this->four_of_a_kind_toString($holeCards, $communityCards);
            case $this->is_full_house($holeCards, $communityCards):
                return $this->full_house_toString($holeCards, $communityCards);
            case $this->is_flush($holeCards, $communityCards):
                return $this->flush_toString($holeCards, $communityCards);
            case $this->is_straight($holeCards, $communityCards):
                return $this->straight_toString($holeCards, $communityCards);
            case $this->is_three_of_a_kind($holeCards, $communityCards):
                return $this->three_of_a_kind_toString($holeCards, $communityCards);
            case $this->is_two_pair($holeCards, $communityCards):
                return $this->two_pair_toString($holeCards, $communityCards);
            case $this->is_pair($holeCards, $communityCards):
                return $this->pair_toString($holeCards, $communityCards);
            case $this->is_high_card($holeCards, $communityCards):
                return $this->high_card_toString($holeCards, $communityCards);
        }
        return "Unknown";
    }

    public function hand_toString_omaha_hi(array $holeCards, array $communityCards): string
    {
        return "Omaha Hi";
    }

    public function hand_toString_omaha_lo(array $holeCards, array $communityCards): string
    {
        return "Omaha Lo";
    }

    private function is_royal_flush(array $holeCards, array $communityCards): bool
    {
        if (count($holeCards) + count($communityCards) < 5) return false;
        $combos = $this->get_combos_texas($holeCards, $communityCards);
        foreach ($combos as $combo) {
            if (count($combo) != 5) continue;
            usort($combo, function ($a, $b) {
                return $a->getRank()->numeric() <=> $b->getRank()->numeric();
            });
            $straight_string = "";
            foreach ($combo as $card) {
                $straight_string .= $card->getRank()->display();
            }
            $flush_string = "";
            foreach ($combo as $card) {
                $flush_string .= $card->getSuit()->display();
            }
            $flush = false;
            if (strpos($flush_string, "ccccc") !== false) $flush = true;
            if (strpos($flush_string, "ddddd") !== false) $flush = true;
            if (strpos($flush_string, "hhhhh") !== false) $flush = true;
            if (strpos($flush_string, "sssss") !== false) $flush = true;
            $straight = false;
            if (strpos($straight_string, "TJQKA") !== false) $straight = true;
            if ($flush && $straight) return true;
        }
        return false;
    }

    private function royal_flush_toString(array $holeCards, array $communityCards): string
    {
        return "Royal Flush";
    }

    private function is_straight_flush(array $holeCards, array $communityCards): bool
    {
        if (count($holeCards) + count($communityCards) < 5) return false;
        $combos = $this->get_combos_texas($holeCards, $communityCards);
        foreach ($combos as $combo) {
            if (count($combo) != 5) continue;
            usort($combo, function ($a, $b) {
                return $a->getRank()->numeric() <=> $b->getRank()->numeric();
            });
            $straight_string = "";
            foreach ($combo as $card) {
                $straight_string .= $card->getRank()->display();
            }
            $flush_string = "";
            foreach ($combo as $card) {
                $flush_string .= $card->getSuit()->display();
            }
            $flush = false;
            if (strpos($flush_string, "ccccc") !== false) $flush = true;
            if (strpos($flush_string, "ddddd") !== false) $flush = true;
            if (strpos($flush_string, "hhhhh") !== false) $flush = true;
            if (strpos($flush_string, "sssss") !== false) $flush = true;
            $straight = false;
            if (strpos($straight_string, "2345A") !== false) $straight = true;
            if (strpos($straight_string, "23456") !== false) $straight = true;
            if (strpos($straight_string, "34567") !== false) $straight = true;
            if (strpos($straight_string, "45678") !== false) $straight = true;
            if (strpos($straight_string, "56789") !== false) $straight = true;
            if (strpos($straight_string, "6789T") !== false) $straight = true;
            if (strpos($straight_string, "789TJ") !== false) $straight = true;
            if (strpos($straight_string, "89TJQ") !== false) $straight = true;
            if (strpos($straight_string, "9TJQK") !== false) $straight = true;
            if (strpos($straight_string, "TJQKA") !== false) $straight = true;
            if ($flush && $straight) return true;
        }
        return false;
    }

    private function straight_flush_toString(array $holeCards, array $communityCards): string
    {
        return "Straight Flush";
    }

    private function is_four_of_a_kind(array $holeCards, array $communityCards): bool
    {
        if (count($holeCards) + count($communityCards) < 5) return false;
        $combos = $this->get_combos_texas($holeCards, $communityCards);
        foreach ($combos as $combo) {
            if (count($combo) != 5) continue;
            usort($combo, function ($a, $b) {
                return $a->getRank()->numeric() <=> $b->getRank()->numeric();
            });
            $fok_string = "";
            foreach ($combo as $card) {
                $fok_string .= $card->getRank()->display();
            }
            if ($fok_string[0] === $fok_string[1] && $fok_string[1] === $fok_string[2] && $fok_string[2] === $fok_string[3]) return true;
            if ($fok_string[1] === $fok_string[2] && $fok_string[2] === $fok_string[3] && $fok_string[3] === $fok_string[4]) return true;
        }
        return false;
    }

    private function four_of_a_kind_toString(array $holeCards, array $communityCards): string
    {
        return "Four of a Kind";
    }

    private function is_full_house(array $holeCards, array $communityCards): bool
    {
        if (count($holeCards) + count($communityCards) < 5) return false;
        if (!$this->is_three_of_a_kind($holeCards, $communityCards)) return false;
        $combos = $this->get_combos_texas($holeCards, $communityCards);
        foreach ($combos as $combo) {
            if (count($combo) != 5) continue;
            usort($combo, function ($a, $b) {
                return $a->getRank()->numeric() <=> $b->getRank()->numeric();
            });
            $full_house_string = "";
            foreach ($combo as $card) {
                $full_house_string .= $card->getRank()->display();
            }
            if ($full_house_string[0] === $full_house_string[1] && $full_house_string[1] === $full_house_string[2] && $full_house_string[3] === $full_house_string[4]) return true;
            if ($full_house_string[0] === $full_house_string[1] && $full_house_string[2] === $full_house_string[3] && $full_house_string[3] === $full_house_string[4]) return true;
        }
        return false;
    }

    private function full_house_toString(array $holeCards, array $communityCards): string
    {
        return "Full House";
    }

    private function is_flush(array $holeCards, array $communityCards): bool
    {
        if (count($holeCards) + count($communityCards) < 5) return false;
        $combos = $this->get_combos_texas($holeCards, $communityCards);
        foreach ($combos as $combo) {
            if (count($combo) != 5) continue;
            $suits = [];
            foreach ($combo as $card) {
                $suits[] = $card->getSuit()->display();
            }
            $suits = array_unique($suits);
            if (count($suits) === 1) return true;
        }
        return false;
    }

    private function flush_toString(array $holeCards, array $communityCards): string
    {
        return "Flush";
    }

    private function is_straight(array $holeCards, array $communityCards): bool
    {
        if (count($holeCards) + count($communityCards) < 5) return false;
        $combos = $this->get_combos_texas($holeCards, $communityCards);
        foreach ($combos as $combo) {
            if (count($combo) != 5) continue;
            usort($combo, function ($a, $b) {
                return $a->getRank()->numeric() <=> $b->getRank()->numeric();
            });
            $straight_string = "";
            foreach ($combo as $card) {
                $straight_string .= $card->getRank()->display();
            }
            if (strpos($straight_string, "2345A") !== false) return true;
            if (strpos($straight_string, "23456") !== false) return true;
            if (strpos($straight_string, "34567") !== false) return true;
            if (strpos($straight_string, "45678") !== false) return true;
            if (strpos($straight_string, "56789") !== false) return true;
            if (strpos($straight_string, "6789T") !== false) return true;
            if (strpos($straight_string, "789TJ") !== false) return true;
            if (strpos($straight_string, "89TJQ") !== false) return true;
            if (strpos($straight_string, "9TJQK") !== false) return true;
            if (strpos($straight_string, "TJQKA") !== false) return true;
        }
        return false;
    }

    private function straight_toString(array $holeCards, array $communityCards): string
    {
        return "Straight";
    }

    private function is_three_of_a_kind(array $holeCards, array $communityCards): bool
    {
        if (count($holeCards) + count($communityCards) < 5) return false;
        $combos = $this->get_combos_texas($holeCards, $communityCards);
        foreach ($combos as $combo) {
            if (count($combo) != 5) continue;
            usort($combo, function ($a, $b) {
                return $a->getRank()->numeric() <=> $b->getRank()->numeric();
            });
            for ($i = 0; $i < count($combo) - 2; $i++) {
                if ($combo[$i]->getRank() === $combo[$i + 1]->getRank() && $combo[$i + 1]->getRank() === $combo[$i + 2]->getRank()) return true;
            }
        }
        return false;
    }

    private function three_of_a_kind_toString(array $holeCards, array $communityCards): string
    {
        return "Three of a Kind";
    }

    private function is_two_pair(array $holeCards, array $communityCards): bool
    {
        if (count($holeCards) + count($communityCards) < 5) return false;
        $combos = $this->get_combos_texas($holeCards, $communityCards);
        foreach ($combos as $combo) {
            if (count($combo) != 5) continue;
            usort($combo, function ($a, $b) {
                return $a->getRank()->numeric() <=> $b->getRank()->numeric();
            });
            $pairs = 0;
            for ($i = 0; $i < count($combo) - 1; $i++) {
                if ($combo[$i]->getRank() === $combo[$i + 1]->getRank()) $pairs++;
            }
            if ($pairs === 2) return true;
        }
        return false;
    }

    private function two_pair_toString(array $holeCards, array $communityCards): string
    {
        return "Two Pair";
    }

    private function is_pair(array $holeCards, array $communityCards): bool
    {
        if ($holeCards[0]->getRank() === $holeCards[1]->getRank()) return true;
        $combos = $this->get_combos_texas($holeCards, $communityCards);
        foreach ($combos as $combo) {
            usort($combo, function ($a, $b) {
                return $a->getRank()->numeric() <=> $b->getRank()->numeric();
            });
            for ($i = 0; $i < count($combo) - 1; $i++) {
                if ($combo[$i]->getRank() === $combo[$i + 1]->getRank()) return true;
            }
        }
        return false;
    }

    private function pair_toString(array $holeCards, array $communityCards): string
    {
        return "Pair";
    }

    private function is_high_card(array $holeCards, array $communityCards): bool
    {
        return true;
    }

    private function high_card_toString(array $holeCards, array $communityCards): string
    {
        return "High Card";
    }


    private function get_combos_texas(array $holeCards, array $communityCards): array
    {
        $combos = [];
        $cards = array_merge($holeCards, $communityCards);
        $numCards = count($cards);
        $numHoleCards = count($holeCards);
        $numCommunityCards = count($communityCards);

        // if there's less than 5 cards total we can return false
        if ($numCards < 5) return $combos;

        // if there's exactly 5 cards total we can return the only possible combo
        if ($numCards === 5) {
            $combos[] = $cards;
            return $combos;
        }

        // if there's exactly 6 cards total we can return the only possible combos
        if ($numCards === 6) {
            $combos[] = array_merge($holeCards, array_slice($communityCards, 0, 3));
            $combos[] = array_merge($holeCards, array_slice($communityCards, 0, 2), array_slice($communityCards, 3, 1));
            $combos[] = array_merge($holeCards, array_slice($communityCards, 0, 1), array_slice($communityCards, 2, 1), array_slice($communityCards, 4, 1));
            $combos[] = array_merge($holeCards, array_slice($communityCards, 1, 1), array_slice($communityCards, 3, 1), array_slice($communityCards, 4, 1));
            $combos[] = array_merge($holeCards, array_slice($communityCards, 2, 1), array_slice($communityCards, 3, 1), array_slice($communityCards, 4, 1));
            return $combos;
        }

        // if there's exactly 7 cards total we can return the only possible combos
        if ($numCards === 7) {
            $combos[] = array_merge($holeCards, array_slice($communityCards, 0, 5));
            $combos[] = array_merge($holeCards, array_slice($communityCards, 0, 4), array_slice($communityCards, 5, 1));
            $combos[] = array_merge($holeCards, array_slice($communityCards, 0, 3), array_slice($communityCards, 4, 1), array_slice($communityCards, 5, 1));
            $combos[] = array_merge($holeCards, array_slice($communityCards, 0, 2), array_slice($communityCards, 3, 1), array_slice($communityCards, 4, 1), array_slice($communityCards, 5, 1));
            $combos[] = array_merge($holeCards, array_slice($communityCards, 0, 1), array_slice($communityCards, 2, 1), array_slice($communityCards, 4, 1), array_slice($communityCards, 5, 1), array_slice($communityCards, 6, 1));
            $combos[] = array_merge($holeCards, array_slice($communityCards, 1, 1), array_slice($communityCards, 3, 1), array_slice($communityCards, 4, 1), array_slice($communityCards, 5, 1), array_slice($communityCards, 6, 1));
            $combos[] = array_merge($holeCards, array_slice($communityCards, 2, 1), array_slice($communityCards, 3, 1), array_slice($communityCards, 4, 1), array_slice($communityCards, 5, 1), array_slice($communityCards, 6, 1));
            return $combos;
        }
    }

    private function get_combos_omaha(array $holeCards, array $communityCards): array
    {
        $combos = [];
        $cards = array_merge($holeCards, $communityCards);
        $numCards = count($cards);
        $numHoleCards = count($holeCards);
        $numCommunityCards = count($communityCards);

        // if there's less than 5 cards total we can return false
        if ($numCards < 5) return $combos;

        // if there's exactly 5 cards total we can return the only possible combo
        if ($numCards === 5) {
            $combos[] = $cards;
            return $combos;
        }

        // if there's exactly 6 cards total we can return the only possible combos
        if ($numCards === 6) {
            $combos[] = array_merge($holeCards, array_slice($communityCards, 0, 3));
            $combos[] = array_merge($holeCards, array_slice($communityCards, 0, 2), array_slice($communityCards, 3, 1));
            $combos[] = array_merge($holeCards, array_slice($communityCards, 0, 1), array_slice($communityCards, 2, 1), array_slice($communityCards, 4, 1));
            $combos[] = array_merge($holeCards, array_slice($communityCards, 1, 1), array_slice($communityCards, 3, 1), array_slice($communityCards, 4, 1));
            $combos[] = array_merge($holeCards, array_slice($communityCards, 2, 1), array_slice($communityCards, 3, 1), array_slice($communityCards, 4, 1));
            return $combos;
        }

        // if there's exactly 7 cards total we can return the only possible combos
        if ($numCards === 7) {
            $combos[] = array_merge($holeCards, array_slice($communityCards, 0, 5));
            $combos[] = array_merge($holeCards, array_slice($communityCards, 0, 4), array_slice($communityCards, 5, 1));
            $combos[] = array_merge($holeCards, array_slice($communityCards, 0, 3), array_slice($communityCards, 4, 1), array_slice($communityCards, 5, 1));
            $combos[] = array_merge($holeCards, array_slice($communityCards, 0, 2), array_slice($communityCards, 3, 1), array_slice($communityCards, 4, 1), array_slice($communityCards, 5, 1));
            $combos[] = array_merge($holeCards, array_slice($communityCards, 0, 1), array_slice($communityCards, 2, 1), array_slice($communityCards, 4, 1), array_slice($communityCards, 5, 1), array_slice($communityCards, 6, 1));
            $combos[] = array_merge($holeCards, array_slice($communityCards, 1, 1), array_slice($communityCards, 3, 1), array_slice($communityCards, 4, 1), array_slice($communityCards, 5, 1), array_slice($communityCards, 6, 1));
            $combos[] = array_merge($holeCards, array_slice($communityCards, 2, 1), array_slice($communityCards, 3, 1), array_slice($communityCards, 4, 1), array_slice($communityCards, 5, 1), array_slice($communityCards, 6, 1));
            return $combos;
        }
    }
}
