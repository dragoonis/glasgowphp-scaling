<?php

namespace App\Command;

readonly class DeleteProductCommand
{
    public function __construct(
        public int $productId
    ) {}
} 