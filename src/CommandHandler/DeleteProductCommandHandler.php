<?php

namespace App\CommandHandler;

use App\Command\DeleteProductCommand;
use App\Projection\ProductProjectionRepository;
use App\Repository\ProductRepository;
use App\Projection\ProductSummaryProjectionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Psr\Log\LoggerInterface;

#[AsMessageHandler]
final readonly class DeleteProductCommandHandler
{
    public function __construct(
        private ProductRepository $productRepository,
        private ProductProjectionRepository $projectionRepository,
        private ProductSummaryProjectionRepository $summaryProjectionRepository,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {}

    public function __invoke(DeleteProductCommand $command): void
    {
        try {
            $this->entityManager->beginTransaction();

            $product = $this->productRepository->find($command->productId);

            if ($product) {
                $this->entityManager->remove($product);
                $this->entityManager->flush();
            }

            $this->projectionRepository->delete($command->productId);
            $this->summaryProjectionRepository->remove($command->productId);

            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            $this->logger->error('DeleteProduct failed', [
                'command' => $command::class,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
} 