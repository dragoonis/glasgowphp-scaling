<?php

namespace App\MessageHandler;

use App\Command\DeleteCustomerCommand;
use App\Projection\CustomerProjectionService;
use App\Repository\CustomerRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class DeleteCustomerCommandHandler
{
    public function __construct(
        private readonly CustomerRepository $customerRepository,
        private readonly CustomerProjectionService $projectionService
    ) {}

    public function __invoke(DeleteCustomerCommand $command): void
    {
        $customer = $this->customerRepository->find($command->id);
        
        if ($customer) {
            $this->customerRepository->remove($customer, true);
            $this->projectionService->deleteProjection($command->id);
        }
    }
} 