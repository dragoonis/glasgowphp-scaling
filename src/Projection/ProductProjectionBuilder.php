<?php

namespace App\Projection;

use App\Entity\Product;

final class ProductProjectionBuilder
{
    public function build(Product $product): ProductProjection
    {
        return new ProductProjection(
            $product->getId(),
            $product->getName(),
            $product->getDescription(),
            $product->getPrice(),
            $product->getCreatedAt()
        );
    }
} 