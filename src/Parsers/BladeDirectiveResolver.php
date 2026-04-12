<?php

namespace MalikAd778\BladeAlly\Parsers;

class BladeDirectiveResolver
{
    public function resolve(array $ast, string $currentFile): array
    {
        $resolved = [];

        foreach ($ast as $node) {
            if ($node['nodeType'] === 'BladeDirective') {
                $node['resolvedPath'] = $currentFile;
            }
            $resolved[] = $node;
        }

        return $resolved;
    }
}
