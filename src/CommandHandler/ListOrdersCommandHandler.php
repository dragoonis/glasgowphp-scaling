<?php

namespace App\CommandHandler;

use App\Command\ListOrdersCommand;
use App\Projection\OrderProjectionRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ListOrdersCommandHandler
{
    public function __construct(
        private readonly OrderProjectionRepository $projectionRepository
    ) {}

    public function __invoke(ListOrdersCommand $command): array
    {
        return $this->projectionRepository->findAll();
    }
} 