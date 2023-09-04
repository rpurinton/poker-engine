<?php

namespace RPurinton\poker;

require_once(__DIR__ . "/PokerHands/StraightFlush.php");
require_once(__DIR__ . "/PokerHands/FourofaKind.php");
require_once(__DIR__ . "/PokerHands/FullHouse.php");
require_once(__DIR__ . "/PokerHands/Flush.php");
require_once(__DIR__ . "/PokerHands/Straight.php");
require_once(__DIR__ . "/PokerHands/ThreeofaKind.php");
require_once(__DIR__ . "/PokerHands/TwoPair.php");
require_once(__DIR__ . "/PokerHands/Pair.php");
require_once(__DIR__ . "/PokerHands/HighCard.php");

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
                return $this->hand_toString_texas($this->get_combos_texas($holeCards, $communityCards));
            default:
                return "Unknown Game Type";
        }
    }

    public function hand_toString_texas(array $combos): string
    {
        switch (true) {
            case StraightFlush::is($combos):
                return StraightFlush::toString($combos);
            case FourofaKind::is($combos):
                return FourofaKind::toString($combos);
            case FullHouse::is($combos):
                return FullHouse::toString($combos);
            case Flush::is($combos):
                return Flush::toString($combos);
            case Straight::is($combos):
                return Straight::toString($combos);
            case ThreeofaKind::is($combos):
                return ThreeofaKind::toString($combos);
            case TwoPair::is($combos):
                return TwoPair::toString($combos);
            case Pair::is($combos):
                return Pair::toString($combos);
            default:
                return HighCard::toString($combos);
        }
    }

    public function get_winner_indexes(array $hands, array $communityCards): array
    {
        return $hands;
    }

    private function get_combos_texas(array &$holeCards, array &$communityCards): array
    {
        // return all unique 5 card combinations of hole cards and community cards sorted by rank
        switch (count($communityCards)) {
            case 0:
                return [$this->sort($holeCards)];
            case 3:
                return [$this->sort(array_merge($holeCards, $communityCards))];
            case 4:
                $combos[] = $this->sort([$holeCards[0], $holeCards[1], $communityCards[0], $communityCards[1], $communityCards[2]]);
                $combos[] = $this->sort([$holeCards[0], $holeCards[1], $communityCards[0], $communityCards[1], $communityCards[3]]);
                $combos[] = $this->sort([$holeCards[0], $holeCards[1], $communityCards[0], $communityCards[2], $communityCards[3]]);
                $combos[] = $this->sort([$holeCards[0], $holeCards[1], $communityCards[1], $communityCards[2], $communityCards[3]]);
                $combos[] = $this->sort([$holeCards[0], $communityCards[0], $communityCards[1], $communityCards[2], $communityCards[3]]);
                $combos[] = $this->sort([$holeCards[1], $communityCards[0], $communityCards[1], $communityCards[2], $communityCards[3]]);
                return $combos;
            case 5:
                $combos[] = $this->sort([$holeCards[0], $holeCards[1], $communityCards[0], $communityCards[1], $communityCards[2]]);
                $combos[] = $this->sort([$holeCards[0], $holeCards[1], $communityCards[0], $communityCards[1], $communityCards[3]]);
                $combos[] = $this->sort([$holeCards[0], $holeCards[1], $communityCards[0], $communityCards[1], $communityCards[4]]);
                $combos[] = $this->sort([$holeCards[0], $holeCards[1], $communityCards[0], $communityCards[2], $communityCards[3]]);
                $combos[] = $this->sort([$holeCards[0], $holeCards[1], $communityCards[0], $communityCards[2], $communityCards[4]]);
                $combos[] = $this->sort([$holeCards[0], $holeCards[1], $communityCards[0], $communityCards[3], $communityCards[4]]);
                $combos[] = $this->sort([$holeCards[0], $holeCards[1], $communityCards[1], $communityCards[2], $communityCards[3]]);
                $combos[] = $this->sort([$holeCards[0], $holeCards[1], $communityCards[1], $communityCards[2], $communityCards[4]]);
                $combos[] = $this->sort([$holeCards[0], $holeCards[1], $communityCards[1], $communityCards[3], $communityCards[4]]);
                $combos[] = $this->sort([$holeCards[0], $holeCards[1], $communityCards[2], $communityCards[3], $communityCards[4]]);
                $combos[] = $this->sort([$holeCards[0], $communityCards[0], $communityCards[1], $communityCards[2], $communityCards[3]]);
                $combos[] = $this->sort([$holeCards[0], $communityCards[0], $communityCards[1], $communityCards[2], $communityCards[4]]);
                $combos[] = $this->sort([$holeCards[0], $communityCards[0], $communityCards[1], $communityCards[3], $communityCards[4]]);
                $combos[] = $this->sort([$holeCards[0], $communityCards[0], $communityCards[2], $communityCards[3], $communityCards[4]]);
                $combos[] = $this->sort([$holeCards[0], $communityCards[1], $communityCards[2], $communityCards[3], $communityCards[4]]);
                $combos[] = $this->sort([$holeCards[1], $communityCards[0], $communityCards[1], $communityCards[2], $communityCards[3]]);
                $combos[] = $this->sort([$holeCards[1], $communityCards[0], $communityCards[1], $communityCards[2], $communityCards[4]]);
                $combos[] = $this->sort([$holeCards[1], $communityCards[0], $communityCards[1], $communityCards[3], $communityCards[4]]);
                $combos[] = $this->sort([$holeCards[1], $communityCards[0], $communityCards[2], $communityCards[3], $communityCards[4]]);
                $combos[] = $this->sort([$holeCards[1], $communityCards[1], $communityCards[2], $communityCards[3], $communityCards[4]]);
                return $combos;
        }
    }

    private function sort(array $combo)
    {
        // sort cards by value numerically from lowest to highest
        usort($combo, function ($a, $b) {
            return $a->getRank()->numeric() - $b->getRank()->numeric();
        });
        return $combo;
    }
}
