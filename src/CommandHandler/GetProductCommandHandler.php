<?php

namespace App\CommandHandler;

use App\Command\GetProductCommand;
use App\Projection\ProductSummaryProjectionRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetProductCommandHandler
{
    public function __construct(
        private ProductSummaryProjectionRepository $projectionRepository
    ) {}

    public function __invoke(GetProductCommand $command): ?array
    {
        $projection = $this->projectionRepository->findById($command->productId);
        return $projection?->toArray();
    }
} 