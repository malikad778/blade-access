<?php

namespace MalikAd778\BladeAlly\Ignores;

class InlineIgnoreParser
{
    public function parse(string $content): array
    {
        $ignores = [];
        $lines   = explode("\n", $content);

        foreach ($lines as $index => $line) {
            $nextLine = $index + 2;

            if (str_contains($line, '{{-- blade-ally-ignore-all --}}')) {
                $ignores[$nextLine] = ['*'];
            } elseif (preg_match('/\{\{--\s*blade-ally-ignore\s+([a-zA-Z0-9\-_,\s]+)\s*--\}\}/', $line, $matches)) {
                $rules              = array_map('trim', explode(',', $matches[1]));
                $ignores[$nextLine] = $rules;
            }
        }

        return $ignores;
    }
}
