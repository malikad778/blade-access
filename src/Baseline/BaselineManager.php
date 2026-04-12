<?php

namespace MalikAd778\BladeAlly\Baseline;

use MalikAd778\BladeAlly\Violations\ViolationCollection;

class BaselineManager
{
    private string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function load(): array
    {
        if (!file_exists($this->path)) {
            return [];
        }

        $content = file_get_contents($this->path);
        if ($content === false) {
            return [];
        }

        $data = json_decode($content, true);
        if (!is_array($data) || !isset($data['violations'])) {
            return [];
        }

        $entries = [];
        foreach ($data['violations'] as $violation) {
            if (isset($violation['file'], $violation['rule'], $violation['fingerprint'])) {
                $entries[] = BaselineEntry::fromArray($violation);
            }
        }

        return $entries;
    }

    public function save(ViolationCollection $violations): void
    {
        $generator = new BaselineFingerprintGenerator();
        $entries = [];

        foreach ($violations->all() as $violation) {
            $entries[] = [
                'file' => $violation->filePath,
                'rule' => $violation->ruleId,
                'fingerprint' => $generator->generate($violation)
            ];
        }

        $data = [
            'generated_at' => date('c'),
            'version' => '1.0.0',
            'violations' => $entries,
        ];

        file_put_contents($this->path, json_encode($data, JSON_PRETTY_PRINT));
    }
}
