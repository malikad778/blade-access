<?php

namespace MalikAd778\BladeAlly\Rules\Structure;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class DocumentTitleRule extends AbstractRule
{
    public function getId(): string
    {
        return 'document-title';
    }

    public function getDescription(): string
    {
        return 'Documents must contain a <title> element to orient users.';
    }

    public function getCategory(): string
    {
        return 'Structure';
    }

    public function getWcagCriteria(): string
    {
        return '2.4.2 Page Titled';
    }

    public function getFixHint(): string
    {
        return 'Ensure the <head> of the document contains a descriptive <title> tag. Do not leave it empty.';
    }

    public function check(array $ast, RuleContext $context): array
    {
        $hasHeadBlock = false;
        $violations = [];

        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'head') {
                $hasHeadBlock = true;
                $hasTitle = $this->hasValidTitleTag($node['children'] ?? []);

                if (!$hasTitle) {
                    $violations[] = $this->makeViolation(
                        $context->filePath,
                        $node['line'] ?? 1,
                        $node['column'] ?? 1,
                        '<head> element is missing a descriptive <title> tag.',
                        $this->getDefaultSeverity(),
                        $this->getFixHint()
                    );
                }
            }

            if (!empty($node['children']) && !$hasHeadBlock) {
                $violations = array_merge($violations, $this->check($node['children'], $context));
            }
        }

        return $violations;
    }

    private function hasValidTitleTag(array $ast): bool
    {
        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'title') {
                return $this->hasVisibleText($node);
            }

            if (!empty($node['children'])) {
                if ($this->hasValidTitleTag($node['children'])) {
                    return true;
                }
            }
        }
        return false;
    }

    private function hasVisibleText(array $node): bool
    {
        if (($node['nodeType'] ?? '') === 'Text' && trim($node['value'] ?? '') !== '') {
            return true;
        }

        if (!empty($node['children'])) {
            foreach ($node['children'] as $child) {
                if ($this->hasVisibleText($child)) {
                    return true;
                }
            }
        }

        return false;
    }
}
