<?php

namespace App\CommandHandler;

use App\Command\ListProductsCommand;
use App\Projection\ProductProjectionRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ListProductsCommandHandler
{
    public function __construct(
        private ProductProjectionRepository $projectionRepository
    ) {}

    public function __invoke(ListProductsCommand $command): array
    {
        return $this->projectionRepository->findAll();
    }
} 