<?php

namespace MalikAd778\BladeAlly\Engine;

class RuleContext
{
    public function __construct(
        public readonly string $filePath,
        public readonly array $ast,
        public readonly string $rawContent,
        public readonly array $config = [],
        public readonly ?array $livewireComponent = null
    ) {}

    public function isLayoutTemplate(): bool
    {
        return str_contains($this->rawContent, '@yield') || str_contains($this->rawContent, '@section');
    }

    public function isLivewireComponent(): bool
    {
        return $this->livewireComponent !== null;
    }

    public function getRuleConfig(string $ruleId): mixed
    {
        return $this->config['rules'][$ruleId] ?? true;
    }
}
