<?php

namespace App\Controller;

use App\Command\AddCustomerCommand;
use App\Command\DeleteCustomerCommand;
use App\Command\GetCustomerCommand;
use App\Command\ListCustomersCommand;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/customers')]
final class CustomerController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus
    ) {}

    #[Route('', methods: ['GET'], name: 'list_customers')]
    public function listCustomers(): JsonResponse
    {
        $envelope = $this->commandBus->dispatch(new ListCustomersCommand());
        $projections = $envelope->last(HandledStamp::class)->getResult();
        return $this->json([
            'customers' => array_map(fn($p) => $p->toArray(), $projections),
            'total' => count($projections)
        ]);
    }

    #[Route('', methods: ['POST'], name: 'add_customer')]
    public function addCustomer(Request $request): JsonResponse
    {
        $command = new AddCustomerCommand(
            $request->get('name'),
            $request->get('email'),
            $request->get('address'),
            $request->get('city'),
            $request->get('postal_code'),
            $request->get('country'),
            new \DateTimeImmutable($request->get('created_at') ?? 'now')
        );
        $this->commandBus->dispatch($command);
        return $this->json(['message' => 'Customer created successfully'], 201);
    }

    #[Route('/{id}', methods: ['GET'], name: 'get_customer')]
    public function getCustomer(int $id): JsonResponse
    {
        $envelope = $this->commandBus->dispatch(new GetCustomerCommand($id));
        $customer = $envelope->last(HandledStamp::class)->getResult();

        if (!$customer) {
            return $this->json(['error' => 'Customer not found'], 404);
        }

        return $this->json(['customer' => $customer]);
    }

    #[Route('/{id}', methods: ['DELETE'], name: 'delete_customer')]
    public function deleteCustomer(int $id): JsonResponse
    {
        $command = new DeleteCustomerCommand($id);
        $this->commandBus->dispatch($command);
        return $this->json(['message' => 'Customer deleted successfully']);
    }
} 