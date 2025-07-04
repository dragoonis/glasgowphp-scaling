<?php

namespace App\Command;

readonly class DeleteOrderCommand
{
    public function __construct(
        public int $id
    ) {}

    public function getId(): int
    {
        return $this->id;
    }
} 