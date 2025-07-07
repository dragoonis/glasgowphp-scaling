<?php

namespace App\Projection;

use App\Entity\Order;

final class OrderProjectionBuilder
{
    public function build(Order $order): OrderProjection
    {
        return new OrderProjection(
            $order->getId(),
            $order->getCustomer()->getId(),
            $order->getCustomer()->getName(),
            $order->getOrderNumber(),
            $order->getTotalAmount(),
            $order->getStatus(),
            array_map(function($item) {
                return [
                    'id' => $item->getId(),
                    'name' => $item->getName(),
                    'quantity' => $item->getQuantity(),
                    'price' => $item->getPrice(),
                    'total' => $item->getTotal(),
                ];
            }, $order->getItems()->toArray()),
            $order->getCreatedAt(),
            $order->getUpdatedAt()
        );
    }
} 