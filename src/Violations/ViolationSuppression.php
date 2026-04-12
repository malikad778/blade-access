<?php

namespace MalikAd778\BladeAlly\Violations;

class ViolationSuppression
{
    public function isSuppressed(Violation $violation, string $content): bool
    {
        $lines      = explode("\n", $content);
        $targetLine = $violation->line - 1;

        if ($targetLine < 1) {
            return false;
        }

        $prevLine = trim($lines[$targetLine - 1] ?? '');

        if (str_contains($prevLine, 'blade-ally-ignore-all')) {
            return true;
        }

        if (str_contains($prevLine, 'blade-ally-ignore') && str_contains($prevLine, $violation->ruleId)) {
            return true;
        }

        return false;
    }
}
