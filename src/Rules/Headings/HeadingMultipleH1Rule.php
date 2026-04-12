<?php

namespace MalikAd778\BladeAlly\Rules\Headings;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class HeadingMultipleH1Rule extends AbstractRule
{
    public function getId(): string { return 'heading-multiple-h1'; }
    public function getDescription(): string { return 'A page should have only one <h1> element.'; }
    public function getCategory(): string { return 'Headings'; }
    public function getDefaultSeverity(): string { return 'warning'; }
    public function getWcagCriteria(): string { return '1.3.1 Info and Relationships'; }
    public function getFixHint(): string { return 'Remove duplicate <h1> elements; only one <h1> per page is recommended.'; }

    public function check(array $ast, RuleContext $context): array
    {
        $h1Nodes = $this->collectH1s($ast);
        if (count($h1Nodes) > 1) {
            return [$this->makeViolation($context->filePath,
                $h1Nodes[1]['line'] ?? 1, $h1Nodes[1]['column'] ?? 1,
                'Multiple <h1> elements found. A page should have only one <h1>.')];
        }
        return [];
    }

    private function collectH1s(array $ast): array
    {
        $h1s = [];
        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'h1') { $h1s[] = $node; }
            if (!empty($node['children'])) { $h1s = array_merge($h1s, $this->collectH1s($node['children'])); }
        }
        return $h1s;
    }
}
