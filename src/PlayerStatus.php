<?php

namespace RPurinton\poker;

enum PlayerStatus
{
    case STANDING;
    case WAITING_FOR_TABLE;
    case SEATED;
}
