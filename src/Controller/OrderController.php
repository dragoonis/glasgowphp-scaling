<?php

namespace App\Controller;

use App\Command\AddOrderCommand;
use App\Command\DeleteOrderCommand;
use App\Command\GetOrderCommand;
use App\Command\ListOrdersCommand;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/orders')]
final class OrderController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus
    ) {}

    #[Route('', methods: ['GET'], name: 'list_orders')]
    public function listOrders(): JsonResponse
    {
        $envelope = $this->commandBus->dispatch(new ListOrdersCommand());
        $projections = $envelope->last(HandledStamp::class)->getResult();
        return $this->json([
            'orders' => array_map(fn($p) => $p->toArray(), $projections),
            'total' => count($projections)
        ]);
    }

    #[Route('', methods: ['POST'], name: 'add_order')]
    public function addOrder(Request $request): JsonResponse
    {
        $data = $request->toArray();
        $items = $data['items'] ?? [];
        $command = new AddOrderCommand(
            $data['customer_id'] ?? null,
            $data['order_number'] ?? '',
            $data['total_amount'] ?? '',
            $data['status'] ?? '',
            $items,
            isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : new \DateTimeImmutable('now')
        );
        $this->commandBus->dispatch($command);
        return $this->json(['message' => 'Order created successfully'], 201);
    }

    #[Route('/{id}', methods: ['GET'], name: 'get_order')]
    public function getOrder(int $id): JsonResponse
    {
        $envelope = $this->commandBus->dispatch(new GetOrderCommand($id));
        $order = $envelope->last(HandledStamp::class)->getResult();

        if (!$order) {
            return $this->json(['error' => 'Order not found'], 404);
        }

        return $this->json(['order' => $order]);
    }

    #[Route('/{id}', methods: ['DELETE'], name: 'delete_order')]
    public function deleteOrder(int $id): JsonResponse
    {
        $command = new DeleteOrderCommand($id);
        $this->commandBus->dispatch($command);
        return $this->json(['message' => 'Order deleted successfully']);
    }
} 