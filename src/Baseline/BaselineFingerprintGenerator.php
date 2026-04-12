<?php

namespace MalikAd778\BladeAlly\Baseline;

use MalikAd778\BladeAlly\Violations\Violation;

class BaselineFingerprintGenerator
{
    public function generate(Violation $violation): string
    {
        return $violation->fingerprint();
    }
}

