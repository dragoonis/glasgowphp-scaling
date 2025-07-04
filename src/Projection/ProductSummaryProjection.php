<?php

namespace App\Projection;

final readonly class ProductSummaryProjection
{
    public function __construct(
        public int $productId,
        public string $name,
        public string $description,
        public string $price,
        public \DateTimeImmutable $createdAt
    ) {}

    public function toArray(): array
    {
        return [
            'product_id' => $this->productId,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int) $data['product_id'],
            $data['name'],
            $data['description'],
            $data['price'],
            new \DateTimeImmutable($data['created_at'])
        );
    }
} 