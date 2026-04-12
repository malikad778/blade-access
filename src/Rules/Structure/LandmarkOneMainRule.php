<?php

namespace MalikAd778\BladeAlly\Rules\Structure;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class LandmarkOneMainRule extends AbstractRule
{
    public function getId(): string
    {
        return 'landmark-one-main';
    }

    public function getDescription(): string
    {
        return 'The document should not contain more than one <main> landmark.';
    }

    public function getCategory(): string
    {
        return 'Structure';
    }

    public function getWcagCriteria(): string
    {
        return '1.3.1 Info and Relationships';
    }

    public function getFixHint(): string
    {
        return 'Ensure there is only one visible <main> element, or use proper "hidden" mechanisms for inactive views.';
    }

    public function check(array $ast, RuleContext $context): array
    {
        $mainNodes = $this->collectMainElements($context->ast);
        $violations = [];

        if (count($mainNodes) > 1) {
            $lastViolationNode = $mainNodes[count($mainNodes) - 1];
            $violations[] = $this->makeViolation(
                $context->filePath,
                $lastViolationNode['line'] ?? 1,
                $lastViolationNode['column'] ?? 1,
                'Multiple <main> landmarks found. Only one active <main> element should exist per page.',
                $this->getDefaultSeverity(),
                $this->getFixHint()
            );
        }

        return $violations;
    }

    private function collectMainElements(array $ast): array
    {
        $nodes = [];

        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element') {
                $tagName = strtolower($node['tagName'] ?? '');
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                $role = strtolower($attrs['role'] ?? '');

                if ($tagName === 'main' || $role === 'main') {
                    $nodes[] = $node;
                }
            }

            if (!empty($node['children'])) {
                $nodes = array_merge($nodes, $this->collectMainElements($node['children']));
            }
        }

        return $nodes;
    }
}
