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
}
