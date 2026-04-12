<?php

namespace MalikAd778\BladeAlly\Rules\Images;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class VideoMissingCaptionsRule extends AbstractRule
{
    public function getId(): string { return 'video-missing-captions'; }
    public function getDescription(): string { return '<video> elements must have a <track kind="captions"> for prerecorded content.'; }
    public function getCategory(): string { return 'Images'; }
    public function getDefaultSeverity(): string { return 'error'; }
    public function getWcagCriteria(): string { return '1.2.2 Captions (Prerecorded)'; }
    public function getFixHint(): string { return 'Add <track kind="captions" src="captions.vtt"> inside the <video> element.'; }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];
        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'video') {
                $hasTrack = false;
                foreach ($node['children'] ?? [] as $child) {
                    if (($child['nodeType'] ?? '') === 'Element' && strtolower($child['tagName'] ?? '') === 'track') {
                        $childAttrs = array_change_key_case($child['attributes'] ?? [], CASE_LOWER);
                        if (in_array(strtolower($childAttrs['kind'] ?? ''), ['captions', 'subtitles'], true)) {
                            $hasTrack = true;
                            break;
                        }
                    }
                }
                if (!$hasTrack) {
                    $violations[] = $this->makeViolation($context->filePath, $node['line'] ?? 1, $node['column'] ?? 1,
                        '<video> is missing a <track kind="captions">.');
                }
            }
            if (!empty($node['children'])) {
                $violations = array_merge($violations, $this->check($node['children'], $context));
            }
        }
        return $violations;
    }

}
