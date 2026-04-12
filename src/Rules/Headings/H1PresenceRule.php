<?php

namespace MalikAd778\BladeAlly\Rules\Headings;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class H1PresenceRule extends AbstractRule
{
    public function getId(): string
    {
        return 'h1-presence';
    }

    public function getDescription(): string
    {
        return 'The document or significant layout view should have an h1 element.';
    }

    public function getCategory(): string
    {
        return 'Headings';
    }

    public function getWcagCriteria(): string
    {
        return '2.4.6 Headings and Labels';
    }

    public function getDefaultSeverity(): string
    {
        return 'info';
    }

    public function getFixHint(): string
    {
        return 'Ensure your main content uses an h1 tag to establish the primary subject of the page.';
    }

    public function check(array $ast, RuleContext $context): array
    {
        $hasBodyOrHtml = $this->hasRootTags($context->ast);
        
        if ($hasBodyOrHtml && !$this->hasH1($context->ast)) {
            return [
                $this->makeViolation(
                    $context->filePath,
                    1,
                    1,
                    'Document is missing an <h1> element.',
                    $this->getDefaultSeverity(),
                    $this->getFixHint()
                )
            ];
        }

        return [];
    }

    private function hasRootTags(array $ast): bool
    {
        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element') {
                $tag = strtolower($node['tagName'] ?? '');
                if (in_array($tag, ['html', 'body', 'main'], true)) {
                    return true;
                }
            }

            if (!empty($node['children'])) {
                if ($this->hasRootTags($node['children'])) {
                    return true;
                }
            }
        }
        return false;
    }

    private function hasH1(array $ast): bool
    {
        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'h1') {
                return true;
            }

            if (!empty($node['children'])) {
                if ($this->hasH1($node['children'])) {
                    return true;
                }
            }
        }
        return false;
    }
}
