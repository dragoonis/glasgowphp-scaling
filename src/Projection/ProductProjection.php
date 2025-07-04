<?php

namespace App\Projection;

readonly class ProductProjection
{
    public function __construct(
        public int $id,
        public string $name,
        public string $description,
        public float $price,
        public \DateTimeImmutable $createdAt
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }
} 