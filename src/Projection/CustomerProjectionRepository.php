<?php

namespace App\Projection;

use Predis\ClientInterface;

final class CustomerProjectionRepository
{
    private const REDIS_KEY_PREFIX = 'customer:';
    private const REDIS_ALL_KEY = 'customers:all';

    public function __construct(
        private readonly ClientInterface $redis
    ) {}

    public function find(int $id): ?CustomerProjection
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
        $allIds = $this->redis->smembers(self::REDIS_ALL_KEY);
        $projections = [];

        foreach ($allIds as $id) {
            $projection = $this->find((int) $id);
            if ($projection) {
                $projections[] = $projection;
            }
        }

        return $projections;
    }

    public function save(CustomerProjection $projection): void
    {
        $data = json_encode($projection->toArray());
        $this->redis->set(self::REDIS_KEY_PREFIX . $projection->id, $data);
        $this->redis->sadd(self::REDIS_ALL_KEY, $projection->id);
    }

    public function delete(int $id): void
    {
        $this->redis->del(self::REDIS_KEY_PREFIX . $id);
        $this->redis->srem(self::REDIS_ALL_KEY, $id);
    }

    public function clear(): void
    {
        $allIds = $this->redis->smembers(self::REDIS_ALL_KEY);
        
        if (!empty($allIds)) {
            $keys = array_map(fn($id) => self::REDIS_KEY_PREFIX . $id, $allIds);
            $this->redis->del($keys);
        }
        
        $this->redis->del(self::REDIS_ALL_KEY);
    }

    private function buildFromArray(array $data): CustomerProjection
    {
        return new CustomerProjection(
            $data['id'],
            $data['name'],
            $data['email'],
            $data['address'],
            $data['city'],
            $data['postal_code'],
            $data['country'],
            new \DateTimeImmutable($data['created_at'])
        );
    }
} 