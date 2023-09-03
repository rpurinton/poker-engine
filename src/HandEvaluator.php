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
            $flush = null;
            if (strpos($flush_string, "ccccc") !== false) $flush = " of Clubs";
            if (strpos($flush_string, "ddddd") !== false) $flush = " of Diamonds";
            if (strpos($flush_string, "hhhhh") !== false) $flush = " of Hearts";
            if (strpos($flush_string, "sssss") !== false) $flush = " of Spades";
            $straight = false;
            if (strpos($straight_string, "TJQKA") !== false) $straight = true;
            if ($flush && $straight) return "Royal Flush" . $flush . " [" . implode("] [", $combo) . "]";
        }
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
            $flush = null;
            if (strpos($flush_string, "ccccc") !== false) $flush = " of Clubs";
            if (strpos($flush_string, "ddddd") !== false) $flush = " of Diamonds";
            if (strpos($flush_string, "hhhhh") !== false) $flush = " of Hearts";
            if (strpos($flush_string, "sssss") !== false) $flush = " of Spades";
            $straight = null;
            if (strpos($straight_string, "2345A") !== false) $straight = "5 High ";
            if (strpos($straight_string, "23456") !== false) $straight = "6 High ";
            if (strpos($straight_string, "34567") !== false) $straight = "7 High ";
            if (strpos($straight_string, "45678") !== false) $straight = "8 High ";
            if (strpos($straight_string, "56789") !== false) $straight = "9 High ";
            if (strpos($straight_string, "6789T") !== false) $straight = "Ten High ";
            if (strpos($straight_string, "789TJ") !== false) $straight = "Jack High ";
            if (strpos($straight_string, "89TJQ") !== false) $straight = "Queen High ";
            if (strpos($straight_string, "9TJQK") !== false) $straight = "King High ";
            if (strpos($straight_string, "TJQKA") !== false) $straight = "Royal ";
            if ($flush && $straight) return $straight . "Straight Flush" . $flush . " [" . implode("] [", $combo) . "]";
        }
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
        $combos = $this->get_combos_texas($holeCards, $communityCards);
        $possible = [];
        foreach ($combos as $combo) {
            if (count($combo) != 5) continue;
            usort($combo, function ($a, $b) {
                return $a->getRank()->numeric() <=> $b->getRank()->numeric();
            });
            $fok_string = "";
            foreach ($combo as $card) {
                $fok_string .= $card->getRank()->display();
            }
            if ($fok_string[0] === $fok_string[1] && $fok_string[1] === $fok_string[2] && $fok_string[2] === $fok_string[3]) {
                $possible[] = ["hand" => $combo, "card" => $combo[0]->getRank()->display_long(), "kicker_rank" => $combo[4]->getRank()->numeric(), "kicker" => $combo[4]->getRank()->display_long()];
            } else if ($fok_string[1] === $fok_string[2] && $fok_string[2] === $fok_string[3] && $fok_string[3] === $fok_string[4]) {
                $possible[] = ["hand" => $combo, "card" => $combo[1]->getRank()->display_long(), "kicker_rank" => $combo[4]->getRank()->numeric(), "kicker" => $combo[0]->getRank()->display_long()];
            }
        }
        $best_hand_index = 0;
        // compare the kicker
        for ($i = 1; $i < count($possible); $i++) {
            if ($possible[$i]["kicker_rank"] > $possible[$best_hand_index]["kicker_rank"]) $best_hand_index = $i;
        }
        return "Four-of-a-Kind " . $possible[$best_hand_index]["card"] . "s [" . implode("] [", $possible[$best_hand_index]["hand"]) . "]";
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
        $combos = $this->get_combos_texas($holeCards, $communityCards);
        $possible = [];
        foreach ($combos as $combo) {
            if (count($combo) != 5) continue;
            if (!$this->is_three_of_a_kind($combo, [])) continue;
            usort($combo, function ($a, $b) {
                return $a->getRank()->numeric() <=> $b->getRank()->numeric();
            });

            $full_house_string = "";
            foreach ($combo as $card) {
                $full_house_string .= $card->getRank()->display();
            }
            if ($full_house_string[0] === $full_house_string[1] && $full_house_string[1] === $full_house_string[2] && $full_house_string[3] === $full_house_string[4]) {
                $possible[] = ["hand" => $combo, "card1_value" => $combo[0]->getRank()->numeric(), "card" => $combo[0]->getRank()->display_long(), "kicker_rank" => $combo[4]->getRank()->numeric(), "kicker" => $combo[4]->getRank()->display_long()];
            } else if ($full_house_string[0] === $full_house_string[1] && $full_house_string[2] === $full_house_string[3] && $full_house_string[3] === $full_house_string[4]) {
                $possible[] = ["hand" => $combo, "card1_value" => $combo[2]->getRank()->numeric(), "card" => $combo[2]->getRank()->display_long(), "kicker_rank" => $combo[0]->getRank()->numeric(), "kicker" => $combo[0]->getRank()->display_long()];
            }
        }
        $best_hand_index = 0;
        $last_card1_value = 0;
        $last_kicker_value = 0;
        // compare the cards and kicker
        for ($i = 1; $i < count($possible); $i++) {
            if ($possible[$i]["card1_value"] > $last_card1_value) {
                $best_hand_index = $i;
                $last_card1_value = $possible[$i]["card1_value"];
                $last_kicker_value = $possible[$i]["kicker_rank"];
            }
            if ($possible[$i]["card1_value"] == $last_card1_value && $possible[$i]["kicker_rank"] > $last_kicker_value) {
                $best_hand_index = $i;
                $last_card1_value = $possible[$i]["card1_value"];
                $last_kicker_value = $possible[$i]["kicker_rank"];
            }
        }
        return $possible[$best_hand_index]["card"] . "s full of " . $possible[$best_hand_index]["kicker"] . "s [" . implode("] [", $possible[$best_hand_index]["hand"]) . "]";
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
        $combos = $this->get_combos_texas($holeCards, $communityCards);
        $possible = [];
        foreach ($combos as $combo) {
            if (count($combo) != 5) continue;
            $suits = [];
            foreach ($combo as $card) {
                $suits[] = $card->getSuit()->display();
            }
            $suits = array_unique($suits);
            if (count($suits) === 1) {
                usort($combo, function ($a, $b) {
                    return $a->getRank()->numeric() <=> $b->getRank()->numeric();
                });
                $possible[] = ["hand" => $combo, "suit" => $combo[0]->getSuit()->display_long(), "kicker_ranks" => [$combo[4]->getRank()->numeric(), $combo[3]->getRank()->numeric(), $combo[2]->getRank()->numeric(), $combo[1]->getRank()->numeric(), $combo[0]->getRank()->numeric()], "kickers" => [$combo[4]->getRank()->display_long(), $combo[3]->getRank()->display_long(), $combo[2]->getRank()->display_long(), $combo[1]->getRank()->display_long(), $combo[0]->getRank()->display_long()]];
            }
        }
        // compare the kickers
        $best_hand_index = 0;
        $last_kicker1_value = 0;
        $last_kicker2_value = 0;
        $last_kicker3_value = 0;
        $last_kicker4_value = 0;
        $last_kicker5_value = 0;
        for ($i = 1; $i < count($possible); $i++) {
            if ($possible[$i]["kicker_ranks"][0] > $last_kicker1_value) {
                $best_hand_index = $i;
                $last_kicker1_value = $possible[$i]["kicker_ranks"][0];
                $last_kicker2_value = $possible[$i]["kicker_ranks"][1];
                $last_kicker3_value = $possible[$i]["kicker_ranks"][2];
                $last_kicker4_value = $possible[$i]["kicker_ranks"][3];
                $last_kicker5_value = $possible[$i]["kicker_ranks"][4];
            }
            if ($possible[$i]["kicker_ranks"][0] == $last_kicker1_value && $possible[$i]["kicker_ranks"][1] > $last_kicker2_value) {
                $best_hand_index = $i;
                $last_kicker1_value = $possible[$i]["kicker_ranks"][0];
                $last_kicker2_value = $possible[$i]["kicker_ranks"][1];
                $last_kicker3_value = $possible[$i]["kicker_ranks"][2];
                $last_kicker4_value = $possible[$i]["kicker_ranks"][3];
                $last_kicker5_value = $possible[$i]["kicker_ranks"][4];
            }
            if ($possible[$i]["kicker_ranks"][0] == $last_kicker1_value && $possible[$i]["kicker_ranks"][1] == $last_kicker2_value && $possible[$i]["kicker_ranks"][2] > $last_kicker3_value) {
                $best_hand_index = $i;
                $last_kicker1_value = $possible[$i]["kicker_ranks"][0];
                $last_kicker2_value = $possible[$i]["kicker_ranks"][1];
                $last_kicker3_value = $possible[$i]["kicker_ranks"][2];
                $last_kicker4_value = $possible[$i]["kicker_ranks"][3];
                $last_kicker5_value = $possible[$i]["kicker_ranks"][4];
            }
            if ($possible[$i]["kicker_ranks"][0] == $last_kicker1_value && $possible[$i]["kicker_ranks"][1] == $last_kicker2_value && $possible[$i]["kicker_ranks"][2] == $last_kicker3_value && $possible[$i]["kicker_ranks"][3] > $last_kicker4_value) {
                $best_hand_index = $i;
                $last_kicker1_value = $possible[$i]["kicker_ranks"][0];
                $last_kicker2_value = $possible[$i]["kicker_ranks"][1];
                $last_kicker3_value = $possible[$i]["kicker_ranks"][2];
                $last_kicker4_value = $possible[$i]["kicker_ranks"][3];
                $last_kicker5_value = $possible[$i]["kicker_ranks"][4];
            }
            if ($possible[$i]["kicker_ranks"][0] == $last_kicker1_value && $possible[$i]["kicker_ranks"][1] == $last_kicker2_value && $possible[$i]["kicker_ranks"][2] == $last_kicker3_value && $possible[$i]["kicker_ranks"][3] == $last_kicker4_value && $possible[$i]["kicker_ranks"][4] > $last_kicker5_value) {
                $best_hand_index = $i;
                $last_kicker1_value = $possible[$i]["kicker_ranks"][0];
                $last_kicker2_value = $possible[$i]["kicker_ranks"][1];
                $last_kicker3_value = $possible[$i]["kicker_ranks"][2];
                $last_kicker4_value = $possible[$i]["kicker_ranks"][3];
                $last_kicker5_value = $possible[$i]["kicker_ranks"][4];
            }
        }
        return $possible[$best_hand_index]["kickers"][0] . " High " . $possible[$best_hand_index]["suit"] . " Flush [" . implode("] [", $possible[$best_hand_index]["hand"]) . "]";
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
            if ($straight_string == "2345A") return true;
            if ($straight_string == "23456") return true;
            if ($straight_string == "34567") return true;
            if ($straight_string == "45678") return true;
            if ($straight_string == "56789") return true;
            if ($straight_string == "6789T") return true;
            if ($straight_string == "789TJ") return true;
            if ($straight_string == "89TJQ") return true;
            if ($straight_string == "9TJQK") return true;
            if ($straight_string == "TJQKA") return true;
        }
        return false;
    }

    private function straight_toString(array $holeCards, array $communityCards): string
    {
        $combos = $this->get_combos_texas($holeCards, $communityCards);
        $possible = [];
        foreach ($combos as $combo) {
            if (count($combo) != 5) continue;
            usort($combo, function ($a, $b) {
                return $a->getRank()->numeric() <=> $b->getRank()->numeric();
            });

            $straight_string = "";
            foreach ($combo as $card) {
                $straight_string .= $card->getRank()->display();
            }
            if ($straight_string == "2345A") {
                $possible[] = ["hand" => $combo, "card" => "5", "kicker_rank" => $combo[3]->getRank()->numeric(), "kicker" => $combo[3]->getRank()->display_long()];
            }
            if ($straight_string == "23456") {
                $possible[] = ["hand" => $combo, "card" => "6", "kicker_rank" => $combo[4]->getRank()->numeric(), "kicker" => $combo[4]->getRank()->display_long()];
            }
            if ($straight_string == "34567") {
                $possible[] = ["hand" => $combo, "card" => "7", "kicker_rank" => $combo[4]->getRank()->numeric(), "kicker" => $combo[4]->getRank()->display_long()];
            }
            if ($straight_string == "45678") {
                $possible[] = ["hand" => $combo, "card" => "8", "kicker_rank" => $combo[4]->getRank()->numeric(), "kicker" => $combo[4]->getRank()->display_long()];
            }
            if ($straight_string == "56789") {
                $possible[] = ["hand" => $combo, "card" => "9", "kicker_rank" => $combo[4]->getRank()->numeric(), "kicker" => $combo[4]->getRank()->display_long()];
            }
            if ($straight_string == "6789T") {
                $possible[] = ["hand" => $combo, "card" => "Ten", "kicker_rank" => $combo[4]->getRank()->numeric(), "kicker" => $combo[4]->getRank()->display_long()];
            }
            if ($straight_string == "789TJ") {
                $possible[] = ["hand" => $combo, "card" => "Jack", "kicker_rank" => $combo[4]->getRank()->numeric(), "kicker" => $combo[4]->getRank()->display_long()];
            }
            if ($straight_string == "89TJQ") {
                $possible[] = ["hand" => $combo, "card" => "Queen", "kicker_rank" => $combo[4]->getRank()->numeric(), "kicker" => $combo[4]->getRank()->display_long()];
            }
            if ($straight_string == "9TJQK") {
                $possible[] = ["hand" => $combo, "card" => "King", "kicker_rank" => $combo[4]->getRank()->numeric(), "kicker" => $combo[4]->getRank()->display_long()];
            }
            if ($straight_string == "TJQKA") {
                $possible[] = ["hand" => $combo, "card" => "Ace", "kicker_rank" => $combo[4]->getRank()->numeric(), "kicker" => $combo[4]->getRank()->display_long()];
            }
        }

        $best_hand_index = 0;
        // compare the high card
        for ($i = 1; $i < count($possible); $i++) {
            if ($possible[$i]["kicker_rank"] > $possible[$best_hand_index]["kicker_rank"]) $best_hand_index = $i;
        }
        return $possible[$best_hand_index]["card"] . " High Straight [" . implode("] [", $possible[$best_hand_index]["hand"]) . "]";
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
        $combos = $this->get_combos_texas($holeCards, $communityCards);
        $possible = [];
        foreach ($combos as $combo) {
            if (count($combo) != 5) continue;
            if (!$this->is_three_of_a_kind($combo, [])) continue;
            usort($combo, function ($a, $b) {
                return $a->getRank()->numeric() <=> $b->getRank()->numeric();
            });
            if ($combo[0]->getRank() === $combo[1]->getRank() && $combo[1]->getRank() === $combo[2]->getRank()) {
                $possible[] = ["hand" => $combo, "card" => $combo[0]->getRank()->display_long(), "kicker_ranks" => [$combo[4]->getRank()->numeric(), $combo[3]->getRank()->numeric()], "kickers" => [$combo[4]->getRank()->display_long(), $combo[3]->getRank()->display_long()]];
            } else if ($combo[1]->getRank() === $combo[2]->getRank() && $combo[2]->getRank() === $combo[3]->getRank()) {
                $possible[] = ["hand" => $combo, "card" => $combo[1]->getRank()->display_long(), "kicker_ranks" => [$combo[4]->getRank()->numeric(), $combo[0]->getRank()->numeric()], "kickers" => [$combo[4]->getRank()->display_long(), $combo[0]->getRank()->display_long()]];
            } else if ($combo[2]->getRank() === $combo[3]->getRank() && $combo[3]->getRank() === $combo[4]->getRank()) {
                $possible[] = ["hand" => $combo, "card" => $combo[2]->getRank()->display_long(), "kicker_ranks" => [$combo[1]->getRank()->numeric(), $combo[0]->getRank()->numeric()], "kickers" => [$combo[1]->getRank()->display_long(), $combo[0]->getRank()->display_long()]];
            }
        }
        $best_hand_index = 0;
        $last_kicker1_value = 0;
        $last_kicker2_value = 0;
        // compare the kickers

        for ($i = 1; $i < count($possible); $i++) {
            if ($possible[$i]["kicker_ranks"][0] > $last_kicker1_value) {
                $best_hand_index = $i;
                $last_kicker1_value = $possible[$i]["kicker_ranks"][0];
                $last_kicker2_value = $possible[$i]["kicker_ranks"][1];
            } else if ($possible[$i]["kicker_ranks"][0] === $last_kicker1_value && $possible[$i]["kicker_ranks"][1] > $last_kicker2_value) {
                $best_hand_index = $i;
                $last_kicker1_value = $possible[$i]["kicker_ranks"][0];
                $last_kicker2_value = $possible[$i]["kicker_ranks"][1];
            }
        }
        return "Three-of-a-kind " . $possible[$best_hand_index]["card"] . "s [" . implode("] [", $possible[$best_hand_index]["hand"]) . "]";
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
        $combos = $this->get_combos_texas($holeCards, $communityCards);
        $possible = [];
        foreach ($combos as $combo) {
            if (count($combo) != 5) continue;
            if (!$this->is_two_pair($combo, [])) continue;
            usort($combo, function ($a, $b) {
                return $a->getRank()->numeric() <=> $b->getRank()->numeric();
            });

            $pairs = 0;
            for ($i = 0; $i < count($combo) - 1; $i++) {
                if ($combo[$i]->getRank() === $combo[$i + 1]->getRank()) $pairs++;
            }
            if ($pairs === 2) {
                if ($combo[0]->getRank() === $combo[1]->getRank() && $combo[2]->getRank() === $combo[3]->getRank()) {
                    $possible[] = ["hand" => $combo, "card1_value" => $combo[2]->getRank()->numeric(), "card1" => $combo[2]->getRank()->display_long(), "card2_value" => $combo[0]->getRank()->numeric(), "card2" => $combo[0]->getRank()->display_long(), "kicker_rank" => $combo[4]->getRank()->numeric(), "kicker" => $combo[4]->getRank()->display_long()];
                }
                if ($combo[0]->getRank() === $combo[1]->getRank() && $combo[3]->getRank() === $combo[4]->getRank()) {
                    $possible[] = ["hand" => $combo, "card1_value" => $combo[3]->getRank()->numeric(), "card1" => $combo[3]->getRank()->display_long(), "card2_value" => $combo[0]->getRank()->numeric(), "card2" => $combo[0]->getRank()->display_long(), "kicker_rank" => $combo[2]->getRank()->numeric(), "kicker" => $combo[2]->getRank()->display_long()];
                }
                if ($combo[1]->getRank() === $combo[2]->getRank() && $combo[3]->getRank() === $combo[4]->getRank()) {
                    $possible[] = ["hand" => $combo, "card1_value" => $combo[3]->getRank()->numeric(), "card1" => $combo[3]->getRank()->display_long(), "card2_value" => $combo[1]->getRank()->numeric(), "card2" => $combo[1]->getRank()->display_long(), "kicker_rank" => $combo[0]->getRank()->numeric(), "kicker" => $combo[0]->getRank()->display_long()];
                }
            }
        }

        $best_hand_index = 0;
        $last_card1_value = 0;
        $last_card2_value = 0;
        $last_kicker_value = 0;
        // compare the card1 ranks, then card2, and finally the kicker if necessary
        for ($i = 0; $i < count($possible); $i++) {
            if ($possible[$i]["card1_value"] > $last_card1_value) {
                $best_hand_index = $i;
                $last_card1_value = $possible[$i]["card1_value"];
                $last_card2_value = $possible[$i]["card2_value"];
                $last_kicker_value = $possible[$i]["kicker_rank"];
            } else if ($possible[$i]["card1_value"] === $last_card1_value && $possible[$i]["card2_value"] > $last_card2_value) {
                $best_hand_index = $i;
                $last_card1_value = $possible[$i]["card1_value"];
                $last_card2_value = $possible[$i]["card2_value"];
                $last_kicker_value = $possible[$i]["kicker_rank"];
            } else if ($possible[$i]["card1_value"] === $last_card1_value && $possible[$i]["card2_value"] === $last_card2_value && $possible[$i]["kicker_rank"] > $last_kicker_value) {
                $best_hand_index = $i;
                $last_card1_value = $possible[$i]["card1_value"];
                $last_card2_value = $possible[$i]["card2_value"];
                $last_kicker_value = $possible[$i]["kicker_rank"];
            }
        }
        return "Two Pair " . $possible[$best_hand_index]["card1"] . "s over " . $possible[$best_hand_index]["card2"] . "s [" . implode("] [", $possible[$best_hand_index]["hand"]) . "]";
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
        if ($holeCards[0]->getRank() === $holeCards[1]->getRank()) {
            return "Pair of " . $holeCards[0]->getRank()->display_long() . "s";
        }
        $combos = $this->get_combos_texas($holeCards, $communityCards);
        $possible = [];
        foreach ($combos as $combo) {
            if (count($combo) != 5) continue;
            if (!$this->is_pair($combo, [])) continue;
            usort($combo, function ($a, $b) {
                return $a->getRank()->numeric() <=> $b->getRank()->numeric();
            });

            if ($combo[0]->getRank() === $combo[1]->getRank()) {
                $possible[] = ["hand" => $combo, "card_value" => $combo[0]->getRank()->numeric(), "card" => $combo[0]->getRank()->display_long(), "kicker_ranks" => [$combo[4]->getRank()->numeric(), $combo[3]->getRank()->numeric(), $combo[2]->getRank()->numeric()], "kickers" => [$combo[4]->getRank()->display_long(), $combo[3]->getRank()->display_long(), $combo[2]->getRank()->display_long()]];
            } else if ($combo[1]->getRank() === $combo[2]->getRank()) {
                $possible[] = ["hand" => $combo, "card_value" => $combo[1]->getRank()->numeric(), "card" => $combo[1]->getRank()->display_long(), "kicker_ranks" => [$combo[4]->getRank()->numeric(), $combo[3]->getRank()->numeric(), $combo[0]->getRank()->numeric()], "kickers" => [$combo[4]->getRank()->display_long(), $combo[3]->getRank()->display_long(), $combo[0]->getRank()->display_long()]];
            } else if ($combo[2]->getRank() === $combo[3]->getRank()) {
                $possible[] = ["hand" => $combo, "card_value" => $combo[2]->getRank()->numeric(), "card" => $combo[2]->getRank()->display_long(), "kicker_ranks" => [$combo[4]->getRank()->numeric(), $combo[1]->getRank()->numeric(), $combo[0]->getRank()->numeric()], "kickers" => [$combo[4]->getRank()->display_long(), $combo[1]->getRank()->display_long(), $combo[0]->getRank()->display_long()]];
            } else if ($combo[3]->getRank() === $combo[4]->getRank()) {
                $possible[] = ["hand" => $combo, "card_value" => $combo[3]->getRank()->numeric(), "card" => $combo[3]->getRank()->display_long(), "kicker_ranks" => [$combo[2]->getRank()->numeric(), $combo[1]->getRank()->numeric(), $combo[0]->getRank()->numeric()], "kickers" => [$combo[2]->getRank()->display_long(), $combo[1]->getRank()->display_long(), $combo[0]->getRank()->display_long()]];
            }
        }
        $best_hand_index = 0;
        $last_card_value = 0;
        $last_kicker1_value = 0;
        $last_kicker2_value = 0;
        $last_kicker3_value = 0;
        // compare the card rank, then the kickers
        for ($i = 1; $i < count($possible); $i++) {
            if ($possible[$i]["card_value"] > $last_card_value) {
                $best_hand_index = $i;
                $last_card_value = $possible[$i]["card_value"];
                $last_kicker1_value = $possible[$i]["kicker_ranks"][0];
                $last_kicker2_value = $possible[$i]["kicker_ranks"][1];
                $last_kicker3_value = $possible[$i]["kicker_ranks"][2];
            } else if ($possible[$i]["card_value"] === $last_card_value && $possible[$i]["kicker_ranks"][0] > $last_kicker1_value) {
                $best_hand_index = $i;
                $last_card_value = $possible[$i]["card_value"];
                $last_kicker1_value = $possible[$i]["kicker_ranks"][0];
                $last_kicker2_value = $possible[$i]["kicker_ranks"][1];
                $last_kicker3_value = $possible[$i]["kicker_ranks"][2];
            } else if ($possible[$i]["card_value"] === $last_card_value && $possible[$i]["kicker_ranks"][0] === $last_kicker1_value && $possible[$i]["kicker_ranks"][1] > $last_kicker2_value) {
                $best_hand_index = $i;
                $last_card_value = $possible[$i]["card_value"];
                $last_kicker1_value = $possible[$i]["kicker_ranks"][0];
                $last_kicker2_value = $possible[$i]["kicker_ranks"][1];
                $last_kicker3_value = $possible[$i]["kicker_ranks"][2];
            } else if ($possible[$i]["card_value"] === $last_card_value && $possible[$i]["kicker_ranks"][0] === $last_kicker1_value && $possible[$i]["kicker_ranks"][1] === $last_kicker2_value && $possible[$i]["kicker_ranks"][2] > $last_kicker3_value) {
                $best_hand_index = $i;
                $last_card_value = $possible[$i]["card_value"];
                $last_kicker1_value = $possible[$i]["kicker_ranks"][0];
                $last_kicker2_value = $possible[$i]["kicker_ranks"][1];
                $last_kicker3_value = $possible[$i]["kicker_ranks"][2];
            }
        }
        return "Pair of " . $possible[$best_hand_index]["card"] . "s [" . implode("] [", $possible[$best_hand_index]["hand"]) . "]";
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
        // return all unique 5 card combinations of hole cards and community cards sorted by rank
        if (count($holeCards) + count($communityCards) < 5) return [];
        $combos = [];
        $cards = array_merge($holeCards, $communityCards);
        $n = count($cards);
        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                for ($k = $j + 1; $k < $n; $k++) {
                    for ($l = $k + 1; $l < $n; $l++) {
                        for ($m = $l + 1; $m < $n; $m++) {
                            $combo = [$cards[$i], $cards[$j], $cards[$k], $cards[$l], $cards[$m]];
                            usort($combo, function ($a, $b) {
                                return $a->getRank()->numeric() <=> $b->getRank()->numeric();
                            });
                            $combos[] = $combo;
                        }
                    }
                }
            }
        }
        $combos = array_unique($combos, SORT_REGULAR);
        return $combos;
    }
}
