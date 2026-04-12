<?php

namespace MalikAd778\BladeAlly\Caching;

class AnalysisCache
{
    private CacheDriver $driver;
    private bool $enabled;

    public function __construct(CacheDriver $driver, bool $enabled = true)
    {
        $this->driver  = $driver;
        $this->enabled = $enabled;
    }

    public function get(string $filePath, string $fileHash): ?array
    {
        if (!$this->enabled) {
            return null;
        }
        return $this->driver->get($this->generateKey($filePath, $fileHash));
    }

    public function put(string $filePath, string $fileHash, array $violations): void
    {
        if (!$this->enabled) {
            return;
        }
        $this->driver->put($this->generateKey($filePath, $fileHash), $violations);
    }

    public function flush(): void
    {
        $this->driver->clear();
    }

    private function generateKey(string $filePath, string $fileHash): string
    {
        return md5($filePath . '|' . $fileHash);
    }
}
