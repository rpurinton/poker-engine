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
            if ($flush && $straight) {
                $possible[] = ["hand" => $combo, "card" => $straight, "flush" => $flush, "kicker_rank" => $combo[4]->getRank()->numeric(), "kicker" => $combo[4]->getRank()->display_long()];
            }
        }
        if (count($possible) === 1) return $possible[0]["card"] . " Straight Flush" . $possible[0]["flush"] . " [" . implode("] [", $possible[0]["hand"]) . "]";
        $best_hand_index = $this->get_best_straight_flush($possible);
        return $possible[$best_hand_index]["card"] . " Straight Flush" . $possible[$best_hand_index]["flush"] . " [" . implode("] [", $possible[$best_hand_index]["hand"]) . "]";
    }

    private function get_best_straight_flush(array $possible): int
    {
        $best_hand_index = 0;
        $last_kicker_value = 0;
        // compare the kicker
        for ($i = 1; $i < count($possible); $i++) {
            if ($possible[$i]["kicker_rank"] > $last_kicker_value) $best_hand_index = $i;
        }
        return $best_hand_index;
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
        if (count($possible) === 1) return "Four-of-a-Kind " . $possible[0]["card"] . "s [" . implode("] [", $possible[0]["hand"]) . "]";
        $best_hand_index = $this->get_best_four_of_a_kind($possible);
        return "Four-of-a-Kind " . $possible[$best_hand_index]["card"] . "s [" . implode("] [", $possible[$best_hand_index]["hand"]) . "]";
    }

    private function get_best_four_of_a_kind(array $possible): int
    {
        $best_hand_index = 0;
        $last_kicker_value = 0;
        // compare the kicker
        for ($i = 1; $i < count($possible); $i++) {
            if ($possible[$i]["kicker_rank"] > $last_kicker_value) $best_hand_index = $i;
        }
        return $best_hand_index;
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
        if (count($possible) === 1) return $possible[0]["card"] . "s full of " . $possible[0]["kicker"] . "s [" . implode("] [", $possible[0]["hand"]) . "]";
        $best_hand_index = $this->get_best_full_house($possible);
        return $possible[$best_hand_index]["card"] . "s full of " . $possible[$best_hand_index]["kicker"] . "s [" . implode("] [", $possible[$best_hand_index]["hand"]) . "]";
    }

    private function get_best_full_house(array $possible): int
    {
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
        return $best_hand_index;
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
        if (count($possible) === 1) return $possible[0]["kickers"][0] . " High " . $possible[0]["suit"] . " Flush [" . implode("] [", $possible[0]["hand"]) . "]";
        $best_hand_index = $this->get_best_flush($possible);
        return $possible[$best_hand_index]["kickers"][0] . " High " . $possible[$best_hand_index]["suit"] . " Flush [" . implode("] [", $possible[$best_hand_index]["hand"]) . "]";
    }

    private function get_best_flush(array $possible): int
    {
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
        return $best_hand_index;
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
        if (count($possible) === 1) return $possible[0]["card"] . " High Straight [" . implode("] [", $possible[0]["hand"]) . "]";
        $best_hand_index = $this->get_best_straight($possible);
        return $possible[$best_hand_index]["card"] . " High Straight [" . implode("] [", $possible[$best_hand_index]["hand"]) . "]";
    }

    private function get_best_straight(array $possible): int
    {
        $best_hand_index = 0;
        // compare the high card
        for ($i = 1; $i < count($possible); $i++) {
            if ($possible[$i]["kicker_rank"] > $possible[$best_hand_index]["kicker_rank"]) $best_hand_index = $i;
        }
        return $best_hand_index;
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
        if (count($possible) === 1) return "Three-of-a-kind " . $possible[0]["card"] . "s [" . implode("] [", $possible[0]["hand"]) . "]";
        $best_hand_index = $this->get_best_three_of_a_kind($possible);
        return "Three-of-a-kind " . $possible[$best_hand_index]["card"] . "s [" . implode("] [", $possible[$best_hand_index]["hand"]) . "]";
    }

    private function get_best_three_of_a_kind(array $possible): int
    {
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
        return $best_hand_index;
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
        if (count($possible) === 1) return "Two Pair " . $possible[0]["card1"] . "s over " . $possible[0]["card2"] . "s [" . implode("] [", $possible[0]["hand"]) . "]";
        $best_hand_index = $this->get_best_two_pair($possible);
        return "Two Pair " . $possible[$best_hand_index]["card1"] . "s over " . $possible[$best_hand_index]["card2"] . "s [" . implode("] [", $possible[$best_hand_index]["hand"]) . "]";
    }

    private function get_best_two_pair(array $possible): int
    {
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
        return $best_hand_index;
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
        if (count($possible) === 1) return "Pair of " . $possible[0]["card"] . "s [" . implode("] [", $possible[0]["hand"]) . "]";
        $best_hand_index = $this->get_best_pair($possible);
        return "Pair of " . $possible[$best_hand_index]["card"] . "s [" . implode("] [", $possible[$best_hand_index]["hand"]) . "]";
    }

    private function get_best_pair(array $possible): int
    {
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
        return $best_hand_index;
    }

    private function is_high_card(array $holeCards, array $communityCards): bool
    {
        return true;
    }

    private function high_card_toString(array $holeCards, array $communityCards): string
    {
        $combos = $this->get_combos_texas($holeCards, $communityCards);
        $possible = [];
        foreach ($combos as $combo) {
            usort($combo, function ($a, $b) {
                return $a->getRank()->numeric() <=> $b->getRank()->numeric();
            });
            if (count($combo) == 5) $possible[] = ["hand" => $combo, "card1_value" => $combo[4]->getRank()->numeric(), "card1" => $combo[4]->getRank()->display_long(), "card2_value" => $combo[3]->getRank()->numeric(), "card2" => $combo[3]->getRank()->display_long(), "card3_value" => $combo[2]->getRank()->numeric(), "card3" => $combo[2]->getRank()->display_long(), "card4_value" => $combo[1]->getRank()->numeric(), "card4" => $combo[1]->getRank()->display_long(), "card5_value" => $combo[0]->getRank()->numeric(), "card5" => $combo[0]->getRank()->display_long()];
            else $possible[] = ["hand" => $combo, "card1_value" => $combo[1]->getRank()->numeric(), "card1" => $combo[1]->getRank()->display_long(), "card2_value" => $combo[0]->getRank()->numeric(), "card2" => $combo[0]->getRank()->display_long()];
        }
        if (count($possible) === 1) return $possible[0]["card1"] . " High [" . implode("] [", $possible[0]["hand"]) . "]";
        $best_hand_index = $this->get_best_high_card($possible);
        return $possible[$best_hand_index]["card1"] . " High [" . implode("] [", $possible[$best_hand_index]["hand"]) . "]";
    }

    private function get_best_high_card(array $possible): int
    {
        $best_hand_index = 0;
        $last_card1_value = 0;
        $last_card2_value = 0;
        $last_card3_value = 0;
        $last_card4_value = 0;
        $last_card5_value = 0;
        // compare the card1 ranks, then card2, then card3, then card4, and finally card5 if necessary
        for ($i = 1; $i < count($possible); $i++) {
            if ($possible[$i]["card1_value"] > $last_card1_value) {
                $best_hand_index = $i;
                $last_card1_value = $possible[$i]["card1_value"];
                $last_card2_value = $possible[$i]["card2_value"];
                $last_card3_value = $possible[$i]["card3_value"];
                $last_card4_value = $possible[$i]["card4_value"];
                $last_card5_value = $possible[$i]["card5_value"];
            } else if ($possible[$i]["card1_value"] === $last_card1_value && $possible[$i]["card2_value"] > $last_card2_value) {
                $best_hand_index = $i;
                $last_card1_value = $possible[$i]["card1_value"];
                $last_card2_value = $possible[$i]["card2_value"];
                $last_card3_value = $possible[$i]["card3_value"];
                $last_card4_value = $possible[$i]["card4_value"];
                $last_card5_value = $possible[$i]["card5_value"];
            } else if ($possible[$i]["card1_value"] === $last_card1_value && $possible[$i]["card2_value"] === $last_card2_value && $possible[$i]["card3_value"] > $last_card3_value) {
                $best_hand_index = $i;
                $last_card1_value = $possible[$i]["card1_value"];
                $last_card2_value = $possible[$i]["card2_value"];
                $last_card3_value = $possible[$i]["card3_value"];
                $last_card4_value = $possible[$i]["card4_value"];
                $last_card5_value = $possible[$i]["card5_value"];
            } else if ($possible[$i]["card1_value"] === $last_card1_value && $possible[$i]["card2_value"] === $last_card2_value && $possible[$i]["card3_value"] === $last_card3_value && $possible[$i]["card4_value"] > $last_card4_value) {
                $best_hand_index = $i;
                $last_card1_value = $possible[$i]["card1_value"];
                $last_card2_value = $possible[$i]["card2_value"];
                $last_card3_value = $possible[$i]["card3_value"];
                $last_card4_value = $possible[$i]["card4_value"];
                $last_card5_value = $possible[$i]["card5_value"];
            } else if ($possible[$i]["card1_value"] === $last_card1_value && $possible[$i]["card2_value"] === $last_card2_value && $possible[$i]["card3_value"] === $last_card3_value && $possible[$i]["card4_value"] === $last_card4_value && $possible[$i]["card5_value"] > $last_card5_value) {
                $best_hand_index = $i;
                $last_card1_value = $possible[$i]["card1_value"];
                $last_card2_value = $possible[$i]["card2_value"];
                $last_card3_value = $possible[$i]["card3_value"];
                $last_card4_value = $possible[$i]["card4_value"];
                $last_card5_value = $possible[$i]["card5_value"];
            }
        }
        return $best_hand_index;
    }

    public function get_winner_indexes(array $hands, array $communityCards): array
    {
        if (count($hands) == 1) foreach ($hands as $index => $value) return [$index];
        $last_hand_value = 0;
        foreach ($hands as $index => $hand) {
            $hand_value = match (true) {
                $this->is_royal_flush($hand, $communityCards) => 10,
                $this->is_straight_flush($hand, $communityCards) => 9,
                $this->is_four_of_a_kind($hand, $communityCards) => 8,
                $this->is_full_house($hand, $communityCards) => 7,
                $this->is_flush($hand, $communityCards) => 6,
                $this->is_straight($hand, $communityCards) => 5,
                $this->is_three_of_a_kind($hand, $communityCards) => 4,
                $this->is_two_pair($hand, $communityCards) => 3,
                $this->is_pair($hand, $communityCards) => 2,
                $this->is_high_card($hand, $communityCards) => 1,
                default => 0,
            };
            if ($hand_value < $last_hand_value) unset($hands[$index]);
            else if ($hand_value >= $last_hand_value) {
                $last_hand_value = $hand_value;
            }
        }
        foreach ($hands as $index => $hand) {
            $hand_value = match (true) {
                $this->is_royal_flush($hand, $communityCards) => 10,
                $this->is_straight_flush($hand, $communityCards) => 9,
                $this->is_four_of_a_kind($hand, $communityCards) => 8,
                $this->is_full_house($hand, $communityCards) => 7,
                $this->is_flush($hand, $communityCards) => 6,
                $this->is_straight($hand, $communityCards) => 5,
                $this->is_three_of_a_kind($hand, $communityCards) => 4,
                $this->is_two_pair($hand, $communityCards) => 3,
                $this->is_pair($hand, $communityCards) => 2,
                $this->is_high_card($hand, $communityCards) => 1,
                default => 0,
            };
            if ($hand_value < $last_hand_value) unset($hands[$index]);
            else if ($hand_value >= $last_hand_value) {
                $last_hand_value = $hand_value;
            }
        }
        if (count($hands) == 1) foreach ($hands as $index => $value) return [$index];
        // try to break ties
        switch ($last_hand_value) {
            case 10:
                return $this->tie_break_royal_flush($hands, $communityCards);
            case 9:
                return $this->tie_break_straight_flush($hands, $communityCards);
            case 8:
                return $this->tie_break_four_of_a_kind($hands, $communityCards);
            case 7:
                return $this->tie_break_full_house($hands, $communityCards);
            case 6:
                return $this->tie_break_flush($hands, $communityCards);
            case 5:
                return $this->tie_break_straight($hands, $communityCards);
            case 4:
                return $this->tie_break_three_of_a_kind($hands, $communityCards);
            case 3:
                return $this->tie_break_two_pair($hands, $communityCards);
            case 2:
                return $this->tie_break_pair($hands, $communityCards);
            case 1:
                return $this->tie_break_high_card($hands, $communityCards);
            default:
                return [];
        }
    }

    private function tie_break_royal_flush(array $hands, array $communityCards): array
    {
        // can't tie break a royal, so return all indexes
        $indexes = [];
        foreach ($hands as $index => $hand) {
            $indexes[] = $index;
        }
        return $indexes;
    }

    private function tie_break_straight_flush(array $hands, array $communityCards): array
    {
        return [];
    }

    private function tie_break_four_of_a_kind(array $hands, array $communityCards): array
    {
        return [];
    }

    private function tie_break_full_house(array $hands, array $communityCards): array
    {
        return [];
    }

    private function tie_break_flush(array $hands, array $communityCards): array
    {
        return [];
    }

    private function tie_break_straight(array $hands, array $communityCards): array
    {
        return [];
    }

    private function tie_break_three_of_a_kind(array $hands, array $communityCards): array
    {
        return [];
    }

    private function tie_break_two_pair(array $hands, array $communityCards): array
    {
        return [];
    }

    private function tie_break_pair(array $hands, array $communityCards): array
    {
        return [];
    }

    private function tie_break_high_card(array $hands, array $communityCards): array
    {
        return [];
    }

    private function get_combos_texas(array $holeCards, array $communityCards): array
    {
        // return all unique 5 card combinations of hole cards and community cards sorted by rank
        if (count($communityCards) < 3) return [$holeCards];
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
