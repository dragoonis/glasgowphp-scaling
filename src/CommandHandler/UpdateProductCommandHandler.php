<?php

namespace App\CommandHandler;

use App\Command\UpdateProductCommand;
use App\Repository\ProductRepository;
use App\Projection\ProductProjectionService;
use App\Service\ProductSummaryProjectionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Psr\Log\LoggerInterface;

#[AsMessageHandler]
final readonly class UpdateProductCommandHandler
{
    public function __construct(
        private ProductRepository $productRepository,
        private ProductProjectionService $productProjectionService,
        private ProductSummaryProjectionService $summaryProjectionService,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {}

    public function __invoke(UpdateProductCommand $command): void
    {
        try {
            $this->entityManager->beginTransaction();

            $product = $this->productRepository->find($command->id);
            if (!$product) {
                throw new \RuntimeException('Product not found');
            }

            if ($command->name !== null) {
                $product->setName($command->name);
            }
            if ($command->description !== null) {
                $product->setDescription($command->description);
            }
            if ($command->price !== null) {
                $product->setPrice($command->price);
            }
            if ($command->createdAt !== null) {
                $product->setCreatedAt($command->createdAt);
            }

            $this->entityManager->flush();
            $this->entityManager->refresh($product);

            $this->productProjectionService->updateProjection($product);
//            $this->summaryProjectionService->updateProductSummary($product->getId());

            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            $this->logger->error('UpdateProduct failed', [
                'command' => $command::class,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
} 