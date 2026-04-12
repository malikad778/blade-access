<?php

namespace MalikAd778\BladeAlly\Engine;

class AnalysisOptions
{
    public function __construct(
        public readonly array $paths = [],
        public readonly array $rules = [],
        public readonly ?string $minSeverity = null,
        public readonly bool $ciMode = false,
        public readonly string $format = 'terminal',
        public readonly ?string $output = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            paths: $data['paths'] ?? [],
            rules: $data['rules'] ?? [],
            minSeverity: $data['min_severity'] ?? null,
            ciMode: $data['ci'] ?? false,
            format: $data['format'] ?? 'terminal',
            output: $data['output'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'paths'        => $this->paths,
            'rules'        => $this->rules,
            'min_severity' => $this->minSeverity,
            'ci'           => $this->ciMode,
            'format'       => $this->format,
            'output'       => $this->output,
        ];
    }
}
