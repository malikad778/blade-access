<?php

namespace MalikAd778\BladeAlly\Baseline;

class BaselineEntry
{
    public string $file;
    public string $rule;
    public string $fingerprint;

    public function __construct(string $file, string $rule, string $fingerprint)
    {
        $this->file = $file;
        $this->rule = $rule;
        $this->fingerprint = $fingerprint;
    }

    public function toArray(): array
    {
        return [
            'file' => $this->file,
            'rule' => $this->rule,
            'fingerprint' => $this->fingerprint,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self($data['file'], $data['rule'], $data['fingerprint']);
    }
}
