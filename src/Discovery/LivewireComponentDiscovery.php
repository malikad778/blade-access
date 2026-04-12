<?php

namespace MalikAd778\BladeAlly\Discovery;

use Illuminate\Filesystem\Filesystem;

class LivewireComponentDiscovery
{
    private Filesystem $files;

    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    public function discover(array $classPaths): array
    {
        $components = [];
        $viewBase   = config('blade-ally.livewire.view_path', resource_path('views/livewire'));

        foreach ($classPaths as $path) {
            if (!$this->files->isDirectory($path)) {
                continue;
            }

            foreach ($this->files->allFiles($path) as $file) {
                if (!str_ends_with($file->getFilename(), '.php')) {
                    continue;
                }

                $classPath = $file->getPathname();
                $viewPath  = $this->resolveViewPath($classPath, $path, $viewBase);

                if ($viewPath && $this->files->isFile($viewPath)) {
                    $components[] = [
                        'class' => $classPath,
                        'view'  => $viewPath,
                    ];
                }
            }
        }

        return $components;
    }

    private function resolveViewPath(string $classPath, string $classBase, string $viewBase): ?string
    {
        $relative = ltrim(str_replace($classBase, '', $classPath), DIRECTORY_SEPARATOR);
        $relative = preg_replace('/\.php$/', '', $relative);
        $slug     = strtolower(preg_replace('/([A-Z])/', '-$1', lcfirst(basename($relative))));
        $slug     = ltrim($slug, '-');

        $candidate = rtrim($viewBase, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $slug . '.blade.php';
        return file_exists($candidate) ? $candidate : null;
    }
}
