<?php

namespace RPurinton\poker;

enum SeatStatus
{
    case EMPTY;
    case RESERVED;
    case WAITING;
    case POSTED;
    case PLAYING;
    case SITOUT;
    case TIMEOUT;
    case FOLDED;
    case CHECKED;
    case CALLED;
    case BET;
    case RAISED;
    case ALLIN;
    case UPCOMING_ACTION;

    public function active(): bool
    {
        return match ($this) {
            SeatStatus::UPCOMING_ACTION,
            SeatStatus::POSTED,
            SeatStatus::PLAYING,
            SeatStatus::CHECKED,
            SeatStatus::CALLED,
            SeatStatus::BET,
            SeatStatus::RAISED => true,
            default => false,
        };
    }

    public function display(): string
    {
        return match ($this) {
            SeatStatus::EMPTY => "Empty",
            SeatStatus::RESERVED => "Reserved",
            SeatStatus::WAITING => "Waiting",
            SeatStatus::POSTED => "Posted",
            SeatStatus::PLAYING => "Playing",
            SeatStatus::SITOUT => "Sitout",
            SeatStatus::TIMEOUT => "Timeout",
            SeatStatus::FOLDED => "Folded",
            SeatStatus::CHECKED => "Checked",
            SeatStatus::CALLED => "Called",
            SeatStatus::BET => "Bet",
            SeatStatus::RAISED => "Raised",
            SeatStatus::ALLIN => "All-in",
            SeatStatus::UPCOMING_ACTION => "Upcoming Action",
        };
    }
}
