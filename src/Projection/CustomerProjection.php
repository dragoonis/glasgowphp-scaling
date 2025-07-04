<?php

namespace App\Projection;

readonly class CustomerProjection
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public string $address,
        public string $city,
        public string $postalCode,
        public string $country,
        public \DateTimeImmutable $createdAt
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'address' => $this->address,
            'city' => $this->city,
            'postal_code' => $this->postalCode,
            'country' => $this->country,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }
} 