<?php

namespace MalikAd778\BladeAlly\Caching;

class CacheInvalidator
{
    private CacheDriver $driver;

    public function __construct(CacheDriver $driver)
    {
        $this->driver = $driver;
    }

    public function invalidateAll(): void
    {
        $this->driver->clear();
    }
}
