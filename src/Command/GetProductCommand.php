<?php

namespace App\Command;

readonly class GetProductCommand
{
    public function __construct(
        public int $productId
    ) {}
}