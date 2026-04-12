<?php

namespace MalikAd778\BladeAlly\Support;

class GenericLabelList
{
    public static function getLabels(): array
    {
        return [
            'click here',
            'here',
            'read more',
            'more',
            'go',
            'submit',
            'send',
            'click',
            'link',
            'details',
            'continue',
            'button',
        ];
    }

    public static function isGeneric(string $label): bool
    {
        $normalized = trim(strtolower($label));
        return in_array($normalized, self::getLabels(), true);
    }
}
