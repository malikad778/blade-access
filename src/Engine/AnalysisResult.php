<?php

namespace MalikAd778\BladeAlly\Engine;

use MalikAd778\BladeAlly\Violations\ViolationCollection;

class AnalysisResult
{
    public function __construct(
        public readonly ViolationCollection $violations,
        public readonly int $filesAnalyzed,
        public readonly float $elapsedMs,
        public readonly array $config = []
    ) {}

    public function hasErrors(): bool
    {
        return $this->violations->countBySeverity('error') > 0;
    }

    public function hasWarnings(): bool
    {
        return $this->violations->countBySeverity('warning') > 0;
    }

    public function hasFailed(): bool
    {
        $failOn = $this->config['fail_on'] ?? 'error';
        return match ($failOn) {
            'info'    => $this->violations->count() > 0,
            'warning' => $this->hasErrors() || $this->hasWarnings(),
            default   => $this->hasErrors(),
        };
    }

    public function summary(): AnalysisSummary
    {
        return new AnalysisSummary(
            totalViolations: $this->violations->count(),
            errors: $this->violations->countBySeverity('error'),
            warnings: $this->violations->countBySeverity('warning'),
            infos: $this->violations->countBySeverity('info'),
            filesAnalyzed: $this->filesAnalyzed,
            elapsedMs: $this->elapsedMs
        );
    }

    public function hasViolationsAtOrAbove(string $severity): bool
    {
        $levels = ['info' => 1, 'warning' => 2, 'error' => 3, 'critical' => 4];
        $min = $levels[$severity] ?? 0;
        foreach ($this->violations as $v) {
            $l = $levels[$v->severity ?? 'info'] ?? 0;
            if ($l >= $min) return true;
        }
        return false;
    }
}

