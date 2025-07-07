<?php

namespace App\CommandHandler;

use App\Command\AddOrderCommand;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Projection\OrderProjectionService;
use App\Repository\CustomerRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class AddOrderCommandHandler
{
    public function __construct(
        private readonly OrderRepository        $orderRepository,
        private readonly CustomerRepository     $customerRepository,
        private readonly OrderProjectionService $projectionService,
        private readonly ProductRepository      $productRepository
    )
    {
    }

    public function __invoke(AddOrderCommand $command): void
    {
        $customer = $this->customerRepository->find($command->customerId);

        if (!$customer) {
            throw new \InvalidArgumentException('Customer not found');
        }

        $totalAmount = 0.0;

        $products = $this->productRepository->findByIdsAsHashMap(
            array_map(
                static fn (array $item) => $item['product_id'],
                $command->items
            ),
        );

        $order = new Order();

        foreach ($command->items as $item) {
            $product = $products[$item['product_id']] ?? null;
            $quantity = $item['quantity'] ?? 1;

            if (! $product) continue;

            $price = (float) $product->getPrice();
            $total = $price * $quantity;

            $orderItem = new OrderItem();
            $orderItem->setOrder($order);
            $orderItem->setName($product->getName());
            $orderItem->setQuantity($quantity);
            $orderItem->setPrice($price);
            $orderItem->setTotal($total);
            $order->addItem($orderItem);

            $totalAmount += $total;
        }

        $order->setCustomer($customer);
        $order->setOrderNumber($command->orderNumber);
        $order->setTotalAmount(number_format($totalAmount, 2, '.', ''));
        $order->setStatus($command->status);
        $order->setCreatedAt($command->createdAt);

        $this->orderRepository->save($order, true);
        $this->projectionService->updateProjection($order);
    }
} 