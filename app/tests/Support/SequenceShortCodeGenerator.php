<?php

namespace Tests\Support;

use App\Contracts\ShortCodeGenerator;

class SequenceShortCodeGenerator implements ShortCodeGenerator
{
    /** @param list<string> $codes */
    public function __construct(private array $codes)
    {
    }

    public function generate(): string
    {
        return array_shift($this->codes) ?? 'EXHAUSTED';
    }
}
