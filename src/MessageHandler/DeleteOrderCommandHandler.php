<?php

namespace App\MessageHandler;

use App\Command\DeleteOrderCommand;
use App\Projection\OrderProjectionService;
use App\Repository\OrderRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class DeleteOrderCommandHandler
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly OrderProjectionService $projectionService
    ) {}

    public function __invoke(DeleteOrderCommand $command): void
    {
        $order = $this->orderRepository->find($command->id);
        
        if ($order) {
            $this->orderRepository->remove($order, true);
            $this->projectionService->deleteProjection($command->id);
        }
    }
} 