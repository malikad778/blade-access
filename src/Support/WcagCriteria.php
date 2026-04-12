<?php

namespace MalikAd778\BladeAlly\Support;

class WcagCriteria
{
    public static function getCriteria(string $ruleId): ?array
    {
        $map = [
            'img-missing-alt' => ['sc' => '1.1.1', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/non-text-content.html'],
            'input-missing-label' => ['sc' => '1.3.1', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/info-and-relationships.html'],
            'button-empty' => ['sc' => '4.1.2', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/name-role-value.html'],
        ];

        return $map[$ruleId] ?? null;
    }
}
