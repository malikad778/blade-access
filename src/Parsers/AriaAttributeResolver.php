<?php

namespace MalikAd778\BladeAlly\Parsers;

class AriaAttributeResolver
{
    public function resolveIdRefs(array $ast, string $id): ?array
    {
        foreach ($ast as $node) {
            if (isset($node['attributes']['id']) && $node['attributes']['id'] === $id) {
                return $node;
            }

            if (!empty($node['children'])) {
                $found = $this->resolveIdRefs($node['children'], $id);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }
}
