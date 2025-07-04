<?php

namespace App\Projection;

use App\Entity\Customer;
use App\Repository\CustomerRepository;

final class CustomerProjectionService
{
    public function __construct(
        private readonly CustomerRepository $customerRepository,
        private readonly CustomerProjectionRepository $projectionRepository,
        private readonly CustomerProjectionBuilder $projectionBuilder
    ) {}

    public function rebuildAll(): void
    {
        $this->projectionRepository->clear();
        
        $customers = $this->customerRepository->findAll();
        
        foreach ($customers as $customer) {
            $projection = $this->projectionBuilder->build($customer);
            $this->projectionRepository->save($projection);
        }
    }

    public function updateProjection(Customer $customer): void
    {
        $projection = $this->projectionBuilder->build($customer);
        $this->projectionRepository->save($projection);
    }

    public function deleteProjection(int $id): void
    {
        $this->projectionRepository->delete($id);
    }
} 