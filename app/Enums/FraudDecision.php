<?php

namespace App\Enums;

enum FraudDecision: string
{
    case Allow = 'allow';
    case Review = 'review';
    case Block = 'block';
}
