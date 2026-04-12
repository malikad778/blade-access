<?php

namespace MalikAd778\BladeAlly\Rules;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\Contracts\RuleInterface;
use MalikAd778\BladeAlly\Violations\Violation;

abstract class AbstractRule implements RuleInterface
{
    public function getDefaultSeverity(): string
    {
        return 'warning';
    }

    public function getWcagUrl(): string
    {
        $criteria = $this->getWcagCriteria();
        if (!$criteria) {
            return '';
        }
        
        $match = [];
        preg_match('/^([0-9.]+)\s*/', $criteria, $match);
        $sc = $match[1] ?? '';
        
        $overrides = [
            '1.1.1' => 'non-text-content',
            '1.3.1' => 'info-and-relationships',
            '1.3.5' => 'identify-input-purpose',
            '1.4.3' => 'contrast-minimum',
            '2.1.1' => 'keyboard',
            '2.4.3' => 'focus-order',
            '2.4.4' => 'link-purpose-in-context',
            '2.4.6' => 'headings-and-labels',
            '2.4.7' => 'focus-visible',
            '3.1.1' => 'language-of-page',
            '3.1.2' => 'language-of-parts',
            '3.3.1' => 'error-identification',
            '3.3.2' => 'labels-or-instructions',
            '4.1.2' => 'name-role-value',
            '4.1.3' => 'status-messages',
        ];
        
        $slug = $overrides[$sc] ?? strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', preg_replace('/^[0-9.]+\s*/', '', $criteria)), '-'));
        return "https://www.w3.org/WAI/WCAG22/Understanding/{$slug}.html";      
    }

    protected function makeViolation(
        string $filePath,
        int    $line,
        int    $column,
        string $message,
        string $severity = '',
        string $fixHint  = ''
    ): Violation {
        return new Violation(
            ruleId:       $this->getId(),
            filePath:     $filePath,
            line:         $line,
            column:       $column,
            severity:     $severity ?: $this->getDefaultSeverity(),
            message:      $message,
            fixHint:      $fixHint ?: $this->getFixHint(),
            wcagCriteria: $this->getWcagCriteria(),
            wcagUrl:      $this->getWcagUrl()
        );
    }

    protected function findPattern(string $content, string $pattern): array
    {
        $matches = [];
        preg_match_all($pattern, $content, $found, PREG_OFFSET_CAPTURE);
        foreach ($found[0] as $match) {
            [$text, $offset] = $match;
            [$line, $col]    = $this->getLineCol($content, $offset);
            $matches[] = ['match' => $text, 'line' => $line, 'col' => $col, 'offset' => $offset];
        }
        return $matches;
    }

    protected function getLineCol(string $content, int $offset): array
    {
        $line        = substr_count(substr($content, 0, $offset), "\n") + 1;
        $lastNewline = strrpos(substr($content, 0, $offset), "\n");
        $col         = $lastNewline === false ? $offset + 1 : $offset - $lastNewline;
        return [$line, $col];
    }
}
