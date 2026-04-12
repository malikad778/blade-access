<?php

namespace MalikAd778\BladeAlly\Rules\Images;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class AudioMissingTranscriptRule extends AbstractRule
{
    public function getId(): string { return 'audio-missing-transcript'; }
    public function getDescription(): string { return '<audio> elements should have an adjacent transcript link or description.'; }
    public function getCategory(): string { return 'Images'; }
    public function getDefaultSeverity(): string { return 'error'; }
    public function getWcagCriteria(): string { return '1.2.1 Audio-only and Video-only (Prerecorded)'; }
    public function getFixHint(): string { return 'Add a link to a transcript near the <audio> element.'; }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];
        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'audio') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                if (!isset($attrs['aria-describedby']) && !isset($attrs['aria-label'])) {
                    $violations[] = $this->makeViolation($context->filePath, $node['line'] ?? 1, $node['column'] ?? 1,
                        '<audio> element has no accessible transcript reference (aria-describedby or aria-label).');
                }
            }
            if (!empty($node['children'])) {
                $violations = array_merge($violations, $this->check($node['children'], $context));
            }
        }
        return $violations;
    }

}
