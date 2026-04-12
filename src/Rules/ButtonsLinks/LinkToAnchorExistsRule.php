<?php

namespace MalikAd778\BladeAlly\Rules\ButtonsLinks;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class LinkToAnchorExistsRule extends AbstractRule
{
    public function getId(): string
    {
        return 'link-to-anchor-exists';
    }

    public function getDescription(): string
    {
        return 'In-page anchor links must point to existing IDs.';
    }

    public function getCategory(): string
    {
        return 'ButtonsLinks';
    }

    public function getWcagCriteria(): string
    {
        return '2.4.1 Bypass Blocks';
    }

    public function getFixHint(): string
    {
        return 'Ensure the hash in href="#some-id" exactly matches an id="some-id" within the same template.';
    }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];
        $ids = $this->collectIds($context->ast);

        $violations = array_merge($violations, $this->findBrokenAnchors($ast, $ids, $context));

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

    private function findBrokenAnchors(array $ast, array $ids, RuleContext $context): array
    {
        $violations = [];

        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'a') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                
                if (isset($attrs['href'])) {
                    $href = trim($attrs['href']);

                    if (str_starts_with($href, '#') && strlen($href) > 1 && !str_contains($href, '{{')) {
                        $targetId = substr($href, 1);
                        if (!in_array($targetId, $ids, true)) {
                            $violations[] = $this->makeViolation(
                                $context->filePath,
                                $node['line'] ?? 1,
                                $node['column'] ?? 1,
                                "Anchor link to '#{$targetId}' does not have a matching id element within the template.",
                                'warning',
                                $this->getFixHint()
                            );
                        }
                    }
                }
            }

            if (!empty($node['children'])) {
                $violations = array_merge($violations, $this->findBrokenAnchors($node['children'], $ids, $context));
            }
        }

        return $violations;
    }
}
