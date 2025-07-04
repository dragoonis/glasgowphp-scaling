<?php

namespace App\MessageHandler;

use App\Command\GetProductCommand;
use App\Projection\ProductProjectionRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetProductCommandHandler
{
    public function __construct(
        private readonly ProductProjectionRepository $projectionRepository
    ) {}

    public function __invoke(GetProductCommand $command): ?array
    {
        $projection = $this->projectionRepository->find($command->productId);
        
        if (!$projection) {
            return null;
        }

        return $projection->toArray();
    }
} 