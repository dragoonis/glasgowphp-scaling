<?php

namespace App\Projection;

use Predis\ClientInterface;

final class OrderProjectionRepository
{
    private const REDIS_KEY_PREFIX = 'order:';
    private const REDIS_ALL_KEY = 'orders:all';
    private const REDIS_CUSTOMER_KEY = 'orders:customer:';

    public function __construct(
        private readonly ClientInterface $redis
    ) {}

    public function find(int $id): ?OrderProjection
    {
        $data = $this->redis->get(self::REDIS_KEY_PREFIX . $id);
        
        if (!$data) {
            return null;
        }

        $decoded = json_decode($data, true);
        return $this->buildFromArray($decoded);
    }

    public function findAll(): array
    {
        // Get all IDs in one query
        $allIds = $this->redis->smembers(self::REDIS_ALL_KEY);
        
        if (empty($allIds)) {
            return [];
        }

        // Build all keys at once
        $keys = array_map(fn($id) => self::REDIS_KEY_PREFIX . $id, $allIds);
        
        // Get all orders in ONE query using MGET
        $allData = $this->redis->mget($keys);

        $projections = [];
        foreach ($allData as $index => $data) {
            if ($data !== null) {
                $decoded = json_decode($data, true);
                if ($decoded) {
                    $projections[] = $this->buildFromArray($decoded);
                }
            }
        }

        return $projections;
    }

    public function save(OrderProjection $projection): void
    {
        $data = json_encode($projection->toArray());
        $this->redis->set(self::REDIS_KEY_PREFIX . $projection->getId(), $data);
        $this->redis->sadd(self::REDIS_ALL_KEY, $projection->getId());
        $this->redis->sadd(self::REDIS_CUSTOMER_KEY . $projection->getCustomerId(), $projection->getId());
    }

    public function delete(int $id): void
    {
        $projection = $this->find($id);
        if ($projection) {
            $this->redis->del(self::REDIS_KEY_PREFIX . $id);
            $this->redis->srem(self::REDIS_ALL_KEY, $id);
            $this->redis->srem(self::REDIS_CUSTOMER_KEY . $projection->getCustomerId(), $id);
        }
    }

    public function clear(): void
    {
        $allIds = $this->redis->smembers(self::REDIS_ALL_KEY);
        
        if (!empty($allIds)) {
            $keys = array_map(fn($id) => self::REDIS_KEY_PREFIX . $id, $allIds);
            $this->redis->del($keys);
        }
        
        $this->redis->del(self::REDIS_ALL_KEY);
        
        // Clear customer-specific sets
        $customerKeys = $this->redis->keys(self::REDIS_CUSTOMER_KEY . '*');
        if (!empty($customerKeys)) {
            $this->redis->del($customerKeys);
        }
    }

    private function buildFromArray(array $data): OrderProjection
    {
        return new OrderProjection(
            $data['id'],
            $data['customer_id'],
            $data['customer_name'],
            $data['order_number'],
            $data['total_amount'],
            $data['status'],
            $data['items'],
            new \DateTimeImmutable($data['created_at']),
            isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null
        );
    }
} 