<?php

namespace App\Enums;

enum LedgerEntryType: string
{
    case Charge = 'charge';
    case Refund = 'refund';
    case Payout = 'payout';
    case Fee = 'fee';
    case Adjustment = 'adjustment';
}
