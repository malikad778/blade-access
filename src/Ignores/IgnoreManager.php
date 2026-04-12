<?php

namespace MalikAd778\BladeAlly\Ignores;

use Illuminate\Support\Str;
use MalikAd778\BladeAlly\Violations\Violation;

class IgnoreManager
{
    private array $inlineIgnores = [];
    private array $fileIgnores   = [];

    public function loadInlineIgnores(string $file, string $content): void
    {
        $parser = new InlineIgnoreParser();
        $this->inlineIgnores[$file] = $parser->parse($content);
    }

    public function loadFileIgnores(string $filePath, IgnoreFileParser $parser): void
    {
        $this->fileIgnores = $parser->parse($filePath);
    }

    public function isIgnored(Violation $violation): bool
    {
        foreach ($this->fileIgnores as $pattern) {
            if (Str::is($pattern, $violation->filePath)) {
                return true;
            }
        }

        $fileIgnores = $this->inlineIgnores[$violation->filePath] ?? [];
        if (isset($fileIgnores[$violation->line])) {
            $rules = $fileIgnores[$violation->line];
            if (in_array('*', $rules, true) || in_array($violation->ruleId, $rules, true)) {
                return true;
            }
        }

        return false;
    }
}
