<?php

namespace App\Controller;

use App\Command\AddProductCommand;
use App\Command\DeleteProductCommand;
use App\Command\GetProductCommand;
use App\Command\ListProductsCommand;
use App\Command\UpdateProductCommand;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/products')]
final class ProductController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus
    ) {}

    #[Route('', name: 'list_products', methods: ['GET'])]
    public function listProducts(): JsonResponse
    {
        $envelope = $this->commandBus->dispatch(new ListProductsCommand());
        $projections = $envelope->last(HandledStamp::class)->getResult();
        return $this->json([
            'products' => array_map(fn($p) => $p->toArray(), $projections),
            'total' => count($projections)
        ]);
    }

    #[Route('', name: 'add_product', methods: ['POST'])]
    public function addProduct(Request $request): JsonResponse
    {
        $command = new AddProductCommand(
            $request->get('name'),
            $request->get('description'),
            $request->get('price'),
            new \DateTimeImmutable($request->get('created_at') ?? 'now')
        );
        $this->commandBus->dispatch($command);
        return $this->json(['message' => 'Product created successfully'], 201);
    }

    #[Route('/{id}', name: 'get_product', methods: ['GET'])]
    public function getProduct(int $id): JsonResponse
    {
        $envelope = $this->commandBus->dispatch(new GetProductCommand($id));
        $product = $envelope->last(HandledStamp::class)->getResult();

        if (!$product) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        return $this->json(['product' => $product]);
    }

    #[Route('/{id}', name: 'delete_product', methods: ['DELETE'])]
    public function deleteProduct(int $id): JsonResponse
    {
        $command = new DeleteProductCommand($id);
        $this->commandBus->dispatch($command);
        return $this->json(['message' => 'Product deleted successfully']);
    }

    #[Route('/{id}', name: 'update_product', methods: ['PUT'])]
    public function updateProduct(int $id, Request $request): JsonResponse
    {
        $data = $request->toArray();
        $command = new UpdateProductCommand(
            $id,
            $data['name'] ?? null,
            $data['description'] ?? null,
            $data['price'] ?? null,
            isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null
        );
        $this->commandBus->dispatch($command);
        return $this->json(['message' => 'Product updated successfully']);
    }
}