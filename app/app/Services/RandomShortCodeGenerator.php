<?php

namespace App\Services;

use App\Contracts\ShortCodeGenerator;
use Illuminate\Support\Str;

class RandomShortCodeGenerator implements ShortCodeGenerator
{
    public function generate(): string
    {
        return Str::random(8);
    }
}
