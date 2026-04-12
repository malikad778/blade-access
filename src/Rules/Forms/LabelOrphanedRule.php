<?php

namespace MalikAd778\BladeAlly\Rules\Forms;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class LabelOrphanedRule extends AbstractRule
{
    public function getId(): string
    {
        return 'label-orphaned';
    }

    public function getDescription(): string
    {
        return 'Labels with a `for` attribute must reference a valid `id` within the template.';
    }

    public function getCategory(): string
    {
        return 'Forms';
    }

    public function getWcagCriteria(): string
    {
        return '1.3.1 Info and Relationships';
    }

    public function getFixHint(): string
    {
        return 'Ensure the label\'s "for" attribute exactly matches the "id" of an existing input in the template.';
    }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];
        $ids = $this->collectIds($context->ast);

        $violations = array_merge($violations, $this->findOrphanedLabels($ast, $ids, $context));

        return $violations;
    }

    private function collectIds(array $ast): array
    {
        $ids = [];

        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                if (isset($attrs['id']) && trim($attrs['id']) !== '') {
                    $ids[] = trim($attrs['id']);
                }
            }

            if (!empty($node['children'])) {
                $ids = array_merge($ids, $this->collectIds($node['children']));
            }
        }

        return array_unique($ids);
    }

    private function findOrphanedLabels(array $ast, array $ids, RuleContext $context): array
    {
        $violations = [];

        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'label') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                if (isset($attrs['for']) && trim($attrs['for']) !== '') {
                    $targetId = trim($attrs['for']);
                    
                    if (!in_array($targetId, $ids, true) && !str_contains($targetId, '{{') && !str_contains($targetId, '$')) {
                        $violations[] = $this->makeViolation(
                            $context->filePath,
                            $node['line'] ?? 1,
                            $node['column'] ?? 1,
                            "<label for=\"{$targetId}\"> references an id that does not exist in the template.",
                            $this->getDefaultSeverity(),
                            $this->getFixHint()
                        );
                    }
                }
            }

            if (!empty($node['children'])) {
                $violations = array_merge($violations, $this->findOrphanedLabels($node['children'], $ids, $context));
            }
        }

        return $violations;
    }
}
