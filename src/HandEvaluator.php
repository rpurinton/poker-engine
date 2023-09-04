<?php

namespace RPurinton\poker;

use JetBrains\PhpStorm\Internal\ReturnTypeContract;

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
        if (StraightFlush::is($combos)) return StraightFlush::toString($combos);
        if (FourofaKind::is($combos)) return FourofaKind::toString($combos);
        if (FullHouse::is($combos)) return FullHouse::toString($combos);
        if (Flush::is($combos)) return Flush::toString($combos);
        if (Straight::is($combos)) return Straight::toString($combos);
        if (ThreeofaKind::is($combos)) return ThreeofaKind::toString($combos);
        if (TwoPair::is($combos)) return TwoPair::toString($combos);
        if (Pair::is($combos)) return Pair::toString($combos);
        return HighCard::toString($combos);
    }

    public function hand_toRank(array $combos): int
    {
        if (StraightFlush::is($combos)) return 8;
        if (FourofaKind::is($combos)) return 7;
        if (FullHouse::is($combos)) return 6;
        if (Flush::is($combos)) return 5;
        if (Straight::is($combos)) return 4;
        if (ThreeofaKind::is($combos)) return 3;
        if (TwoPair::is($combos)) return 2;
        if (Pair::is($combos)) return 1;
        return 0;
    }

    public function hand_get_best_combo1(array $combos)
    {
        if (StraightFlush::is($combos)) return StraightFlush::best(StraightFlush::possibles($combos));
        if (FourofaKind::is($combos)) return FourofaKind::best(FourofaKind::possibles($combos));
        if (FullHouse::is($combos)) return FullHouse::best(FullHouse::possibles($combos));
        if (Flush::is($combos)) return Flush::best(Flush::possibles($combos));
        if (Straight::is($combos)) return Straight::best(Straight::possibles($combos));
        if (ThreeofaKind::is($combos)) return ThreeofaKind::best(ThreeofaKind::possibles($combos));
        if (TwoPair::is($combos)) return TwoPair::best(TwoPair::possibles($combos));
        if (Pair::is($combos)) return Pair::best(Pair::possibles($combos));
        return HighCard::best(HighCard::possibles($combos));
    }

    public function hand_get_best_combo2(array $best): array
    {
        foreach ($best as $hand) return $hand["hand"];
    }

    public function get_winner_indexes(array $hands, array $communityCards): array
    {
        switch ($this->GameType) {
            case GameType::TEXAS_HOLDEM:
                return $this->get_winner_indexes_texas($hands, $communityCards);
            default:
                return [];
        }
    }

    public function get_winner_indexes_texas(array $hands, array $communityCards): array
    {
        $high_rank = 0;
        foreach ($hands as $index => $hand) {
            $hands[$index]["combos"] = $this->get_combos_texas($hand, $communityCards);
            $hands[$index]["display"] = $this->hand_toString($hand, $communityCards);
            $hands[$index]["rank"] = $this->hand_toRank($hands[$index]["combos"]);
            $hands[$index]["best_combo"] = $this->hand_get_best_combo2(($this->hand_get_best_combo1($hands[$index]["combos"])));
            if ($hands[$index]["rank"] > $high_rank) $high_rank = $hands[$index]["rank"];
        }
        foreach ($hands as $index => $hand) {
            if ($hand["rank"] < $high_rank) unset($hands[$index]);
        }
        if (count($hands) < 2) foreach ($hands as $index => $hand) {
            return [$index => $hand];
        }
        $contenders = [];
        foreach ($hands as $index => $hand) $contenders[$index] = $hand["best_combo"];
        switch ($high_rank) {
            case 8:
                $best = StraightFlush::best(StraightFlush::possibles($contenders));
                break;
            case 7:
                $best = FourofaKind::best(FourofaKind::possibles($contenders));
                break;
            case 6:
                $best = FullHouse::best(FullHouse::possibles($contenders));
                break;
            case 5:
                $best = Flush::best(Flush::possibles($contenders));
                break;
            case 4:
                $best = Straight::best(Straight::possibles($contenders));
                break;
            case 3:
                $best = ThreeofaKind::best(ThreeofaKind::possibles($contenders));
                break;
            case 2:
                $best = TwoPair::best(TwoPair::possibles($contenders));
                break;
            case 1:
                $best = Pair::best(Pair::possibles($contenders));
                break;
            default:
                $best = HighCard::best(HighCard::possibles($contenders));
        }
        return $best;
    }

    private function get_combos_texas(array $holeCards, array $communityCards): array
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
