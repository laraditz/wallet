<?php

namespace Laraditz\Wallet\Enums;

enum TxStatus: int
{
    case Failed = 0;
    case Completed = 1;
    case Processing = 2;
}
