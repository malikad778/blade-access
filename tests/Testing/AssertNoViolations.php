<?php

namespace MalikAd778\BladeAlly\Tests\Testing;

use PHPUnit\Framework\Assert;

class AssertNoViolations
{
    public static function assertTrue(bool $condition, string $message = ''): void
    {
        Assert::assertTrue($condition, $message);
    }
}
