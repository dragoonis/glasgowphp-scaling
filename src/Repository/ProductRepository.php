<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Find products by an array of IDs
     *
     * @param array $ids Array of product IDs
     * @return Product[] Returns an array of Product objects
     */
    public function findByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        return $this->createQueryBuilder('p')
            ->andWhere('p.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find products by IDs and return them as a hash map with ID as key
     *
     * @param array $ids Array of product IDs
     * @return Product[] Returns an array of Product objects indexed by their ID
     */
    public function findByIdsAsHashMap(array $ids): array
    {
        $products = $this->findByIds($ids);
        $hashMap = [];

        foreach ($products as $product) {
            $hashMap[$product->getId()] = $product;
        }

        return $hashMap;
    }
} 