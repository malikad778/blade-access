<?php

namespace MalikAd778\BladeAlly\Violations;

class Violation
{
    public function __construct(
        public readonly string $ruleId,
        public readonly string $filePath,
        public readonly int $line,
        public readonly int $column,
        public readonly string $severity,
        public readonly string $message,
        public readonly string $fixHint = '',
        public readonly string $wcagCriteria = '',
        public readonly string $wcagUrl = ''
    ) {}

    public function fingerprint(): string
    {
        return md5($this->ruleId . ':' . $this->filePath . ':' . $this->line . ':' . $this->message);
    }

    public function toArray(): array
    {
        return [
            'rule'      => $this->ruleId,
            'file'      => $this->filePath,
            'line'      => $this->line,
            'column'    => $this->column,
            'severity'  => $this->severity,
            'message'   => $this->message,
            'fix_hint'  => $this->fixHint,
            'wcag'      => $this->wcagCriteria,
            'wcag_url'  => $this->wcagUrl,
        ];
    }
}
