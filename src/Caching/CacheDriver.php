<?php

namespace MalikAd778\BladeAlly\Caching;

use Illuminate\Filesystem\Filesystem;

class CacheDriver
{
    private string $cacheDir;
    private int $ttl;
    private Filesystem $files;

    public function __construct(string $cacheDir, int $ttl, Filesystem $files)
    {
        $this->cacheDir = $cacheDir;
        $this->ttl = $ttl;
        $this->files = $files;
    }

    public function get(string $key)
    {
        $path = $this->getPath($key);

        if (!$this->files->exists($path)) {
            return null;
        }

        if (time() - $this->files->lastModified($path) > $this->ttl) {
            $this->files->delete($path);
            return null;
        }

        $content = $this->files->get($path);
        return unserialize($content);
    }

    public function put(string $key, $value): void
    {
        $this->ensureDirectoryExists();
        $path = $this->getPath($key);
        $this->files->put($path, serialize($value));
    }

    public function forget(string $key): void
    {
        $path = $this->getPath($key);
        if ($this->files->exists($path)) {
            $this->files->delete($path);
        }
    }

    public function clear(): void
    {
        if ($this->files->isDirectory($this->cacheDir)) {
            $this->files->deleteDirectory($this->cacheDir);
        }
    }

    private function getPath(string $key): string
    {
        return $this->cacheDir . DIRECTORY_SEPARATOR . md5($key) . '.cache';
    }

    private function ensureDirectoryExists(): void
    {
        if (!$this->files->isDirectory($this->cacheDir)) {
            $this->files->makeDirectory($this->cacheDir, 0755, true);
        }
    }
}
