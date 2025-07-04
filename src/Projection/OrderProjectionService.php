<?php

namespace App\Projection;

use App\Entity\Order;
use App\Repository\OrderRepository;

final class OrderProjectionService
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly OrderProjectionRepository $projectionRepository,
        private readonly OrderProjectionBuilder $projectionBuilder
    ) {}

    public function rebuildAll(): void
    {
        $this->projectionRepository->clear();
        
        $orders = $this->orderRepository->findAll();
        
        foreach ($orders as $order) {
            $projection = $this->projectionBuilder->build($order);
            $this->projectionRepository->save($projection);
        }
    }

    public function updateProjection(Order $order): void
    {
        $projection = $this->projectionBuilder->build($order);
        $this->projectionRepository->save($projection);
    }

    public function deleteProjection(int $id): void
    {
        $this->projectionRepository->delete($id);
    }
} 