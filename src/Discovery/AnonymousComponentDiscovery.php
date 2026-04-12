<?php

namespace MalikAd778\BladeAlly\Discovery;

use Illuminate\Filesystem\Filesystem;

class AnonymousComponentDiscovery
{
    private Filesystem $files;

    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    public function discover(string $componentsPath): array
    {
        $components = [];

        if ($this->files->isDirectory($componentsPath)) {
            $files = $this->files->allFiles($componentsPath);
            foreach ($files as $file) {
                if (str_ends_with($file->getFilename(), '.blade.php')) {
                    $components[] = $file->getPathname();
                }
            }
        }

        return $components;
    }
}
