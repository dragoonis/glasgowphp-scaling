<?php

namespace App\Command;

readonly class AddProductCommand
{
    public function __construct(
        public string $name,
        public string $description,
        public float $price,
        public \DateTimeImmutable $createdAt
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
} 