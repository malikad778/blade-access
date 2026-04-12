<?php

namespace MalikAd778\BladeAlly\Ignores;

use Illuminate\Filesystem\Filesystem;

class IgnoreFileParser
{
    private Filesystem $files;

    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    public function parse(string $filePath): array
    {
        if (!$this->files->exists($filePath)) {
            return [];
        }

        $content = $this->files->get($filePath);
        if ($content === false) {
            return [];
        }

        $lines = explode("\n", $content);
        $ignores = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line !== '' && !str_starts_with($line, '#')) {
                $ignores[] = $line;
            }
        }

        return $ignores;
    }
}
