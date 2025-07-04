<?php

namespace App\Projection;

use App\Entity\Customer;

final class CustomerProjectionBuilder
{
    public function build(Customer $customer): CustomerProjection
    {
        return new CustomerProjection(
            $customer->getId(),
            $customer->getName(),
            $customer->getEmail(),
            $customer->getAddress(),
            $customer->getCity(),
            $customer->getPostalCode(),
            $customer->getCountry(),
            $customer->getCreatedAt()
        );
    }
} 