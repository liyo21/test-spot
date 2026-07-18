<?php

namespace App\Contracts;

interface ShortCodeGenerator
{
    public function generate(): string;
}
