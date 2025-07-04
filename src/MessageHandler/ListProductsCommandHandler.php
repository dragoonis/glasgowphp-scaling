<?php

namespace App\MessageHandler;

use App\Command\ListProductsCommand;
use App\Projection\ProductProjectionRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ListProductsCommandHandler
{
    public function __construct(
        private readonly ProductProjectionRepository $projectionRepository
    ) {}

    public function __invoke(ListProductsCommand $command): array
    {
        return $this->projectionRepository->findAll();
    }
} 