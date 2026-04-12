<?php

namespace MalikAd778\BladeAlly\Parsers;

class AlpineJsParser
{
    public function extractDirectives(array $attributes): array
    {
        $directives = [];

        foreach ($attributes as $key => $value) {
            if (str_starts_with($key, 'x-') || str_starts_with($key, '@')) {
                $directives[$key] = $value;
            }
        }

        return $directives;
    }
}
