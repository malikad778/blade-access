<?php

namespace MalikAd778\BladeAlly\Rules\Headings;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class HeadingLogicalOrderRule extends AbstractRule
{
    public function getId(): string { return 'heading-logical-order'; }
    public function getDescription(): string { return 'Headings should follow a logical sequence without skipping levels.'; }
    public function getCategory(): string { return 'Headings'; }
    public function getWcagCriteria(): string { return '1.3.1 Info and Relationships'; }
    public function getFixHint(): string { return 'Do not skip heading levels (e.g. going from H2 directly to H4).'; }

    public function check(array $ast, RuleContext $context): array
    {
        $headings      = $this->collectHeadings($ast);
        $violations    = [];
        $previousLevel = 0;

        foreach ($headings as $heading) {
            $currentLevel = (int) substr($heading['tagName'], 1, 1);
            if ($previousLevel > 0 && $currentLevel > $previousLevel + 1) {
                $violations[] = $this->makeViolation(
                    $context->filePath,
                    $heading['line'] ?? 1,
                    $heading['column'] ?? 1,
                    "Skipped heading level. Found H{$currentLevel} after H{$previousLevel}.",
                );
            }
            $previousLevel = $currentLevel;
        }
        return $violations;
    }

    private function collectHeadings(array $ast): array
    {
        $headings = [];
        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element') {
                if (in_array(strtolower($node['tagName'] ?? ''), ['h1','h2','h3','h4','h5','h6'], true)) {
                    $headings[] = $node;
                }
            }
            if (!empty($node['children'])) {
                $headings = array_merge($headings, $this->collectHeadings($node['children']));
            }
        }
        return $headings;
    }
}
