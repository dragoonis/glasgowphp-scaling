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
            $order->getItems(),
            $order->getCreatedAt(),
            $order->getUpdatedAt()
        );
    }
} 