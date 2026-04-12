<?php

namespace MalikAd778\BladeAlly\Discovery;

use Illuminate\Filesystem\Filesystem;

class BladeFileDiscovery
{
    private Filesystem $files;

    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    public function discover(array $paths, array $excludes = []): array
    {
        $found = [];

        foreach ($paths as $path) {
            if ($this->files->isDirectory($path)) {
                $files = $this->files->allFiles($path);
                foreach ($files as $file) {
                    if (str_ends_with($file->getFilename(), '.blade.php')) {
                        if (!$this->isExcluded($file->getPathname(), $excludes)) {
                            $found[] = $file->getPathname();
                        }
                    }
                }
            } elseif ($this->files->isFile($path) && str_ends_with($path, '.blade.php')) {
                if (!$this->isExcluded($path, $excludes)) {
                    $found[] = $path;
                }
            }
        }

        return array_unique($found);
    }

    private function isExcluded(string $path, array $excludes): bool
    {
        foreach ($excludes as $exclude) {
            if (str_starts_with($path, $exclude)) {
                return true;
            }
        }

        return false;
    }
}
