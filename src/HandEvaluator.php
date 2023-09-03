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
        return "Omaha Hi\n";
    }

    public function hand_toString_omaha_lo(array $holeCards, array $communityCards): string
    {
        return "Omaha Lo\n";
    }

    private function is_royal_flush(array $holeCards, array $communityCards): bool
    {
        // holeCards must be 2 or 4 cards
        // communityCards might be 0, 3, 4, or 5 cards
        // in texas you can use any combination of hole and community cards to make your hand
        // in omaha you must use exactly 2 hole cards and 3 community cards
        // if there's less than 5 cards total we can return false
        if (count($holeCards) + count($communityCards) < 5) return false;

        switch ($this->GameType) {
            case GameType::TEXAS_HOLDEM:
                break;
            case GameType::OMAHA;
                break;
        }
        return false;
    }

    private function royal_flush_toString(array $holeCards, array $communityCards): string
    {
        return "Royal Flush\n";
    }

    private function is_straight_flush(array $holeCards, array $communityCards): bool
    {
        return false;
    }

    private function straight_flush_toString(array $holeCards, array $communityCards): string
    {
        return "Straight Flush\n";
    }

    private function is_four_of_a_kind(array $holeCards, array $communityCards): bool
    {
        return false;
    }

    private function four_of_a_kind_toString(array $holeCards, array $communityCards): string
    {
        return "Four of a Kind\n";
    }

    private function is_full_house(array $holeCards, array $communityCards): bool
    {
        return false;
    }

    private function full_house_toString(array $holeCards, array $communityCards): string
    {
        return "Full House\n";
    }

    private function is_flush(array $holeCards, array $communityCards): bool
    {
        return false;
    }

    private function flush_toString(array $holeCards, array $communityCards): string
    {
        return "Flush\n";
    }

    private function is_straight(array $holeCards, array $communityCards): bool
    {
        return false;
    }

    private function straight_toString(array $holeCards, array $communityCards): string
    {
        return "Straight\n";
    }

    private function is_three_of_a_kind(array $holeCards, array $communityCards): bool
    {
        return false;
    }

    private function three_of_a_kind_toString(array $holeCards, array $communityCards): string
    {
        return "Three of a Kind\n";
    }

    private function is_two_pair(array $holeCards, array $communityCards): bool
    {
        return false;
    }

    private function two_pair_toString(array $holeCards, array $communityCards): string
    {
        return "Two Pair\n";
    }

    private function is_pair(array $holeCards, array $communityCards): bool
    {
        return false;
    }

    private function pair_toString(array $holeCards, array $communityCards): string
    {
        return "Pair\n";
    }

    private function is_high_card(array $holeCards, array $communityCards): bool
    {
        return false;
    }

    private function high_card_toString(array $holeCards, array $communityCards): string
    {
        return "High Card\n";
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
}
