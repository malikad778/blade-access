<?php

namespace MalikAd778\BladeAlly\Support;

class Bcp47LanguageCodes
{
    public static function isValid(string $code): bool
    {
        return preg_match('/^[a-zA-Z]{2,3}(-[a-zA-Z]{2,4})?(-[a-zA-Z0-9]{2,8})?$/ii', $code) === 1;
    }
}
