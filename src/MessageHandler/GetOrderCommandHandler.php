<?php

namespace App\MessageHandler;

use App\Command\GetOrderCommand;
use App\Projection\OrderProjectionRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetOrderCommandHandler
{
    public function __construct(
        private readonly OrderProjectionRepository $projectionRepository
    ) {}

    public function __invoke(GetOrderCommand $command): ?array
    {
        $projection = $this->projectionRepository->find($command->id);
        
        if (!$projection) {
            return null;
        }

        return $projection->toArray();
    }
} 