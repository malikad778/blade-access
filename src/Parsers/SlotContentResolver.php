<?php

namespace MalikAd778\BladeAlly\Parsers;

class SlotContentResolver
{
    public function resolveNamedSlots(array $ast): array
    {
        $slots = [];

        foreach ($ast as $node) {
            if ($node['tagName'] === 'x-slot' && isset($node['attributes']['name'])) {
                $slots[$node['attributes']['name']] = $node['children'] ?? [];
            }
        }

        return $slots;
    }
}
