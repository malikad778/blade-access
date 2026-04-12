<?php

namespace MalikAd778\BladeAlly\Parsers;

class LivewireClassInspector
{
    public function inspect(string $classPath): array
    {
        if (!file_exists($classPath)) {
            return [];
        }

        $content = file_get_contents($classPath);

        preg_match('/class\s+([a-zA-Z0-9_]+)/', $content, $matches);
        $className = $matches[1] ?? 'Unknown';

        return [
            'name' => $className,
            'properties' => [],
            'methods' => [],
        ];
    }
}
