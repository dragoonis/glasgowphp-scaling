<?php

namespace App\Command;

readonly class AddCustomerCommand
{
    public function __construct(
        public string $name,
        public string $email,
        public string $address,
        public string $city,
        public string $postalCode,
        public string $country,
        public \DateTimeImmutable $createdAt
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
} 