<?php

namespace Laravel\Services;

/**
 * A value read through the cache, plus where it came from ('redis' | 'mysql').
 */
final readonly class CacheResult
{
    public function __construct(
        public mixed $data,
        public string $source,
        public ?int $version = null,
    ) {}
}
