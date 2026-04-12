<?php

namespace MalikAd778\BladeAlly\Engine;

class AnalysisSummary
{
    public function __construct(
        public readonly int $totalViolations,
        public readonly int $errors,
        public readonly int $warnings,
        public readonly int $infos,
        public readonly int $filesAnalyzed,
        public readonly float $elapsedMs
    ) {}

    public function isPassing(): bool
    {
        return $this->errors === 0;
    }

    public function toArray(): array
    {
        return [
            'total'         => $this->totalViolations,
            'errors'        => $this->errors,
            'warnings'      => $this->warnings,
            'infos'         => $this->infos,
            'filesAnalyzed' => $this->filesAnalyzed,
            'elapsedMs'     => $this->elapsedMs,
        ];
    }
}
