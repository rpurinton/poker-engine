<?php

namespace RPurinton\poker;

enum SeatStatus
{
    case EMPTY;
    case RESERVED;
    case WAITING;
    case OCCUPIED;
}
