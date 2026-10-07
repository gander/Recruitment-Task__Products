<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Product;
use PHPUnit\Framework\TestCase;

final class ProductTest extends TestCase
{
    public function testNewProductIsEmpty(): void
    {
        $product = new Product();

        self::assertNull($product->getId());
        self::assertNull($product->getName());
        self::assertNull($product->getPrice());
    }

    public function testSettersAreFluentAndStoreValues(): void
    {
        $product = (new Product())->setName('Foo Bar')->setPrice('123.45');

        self::assertSame('Foo Bar', $product->getName());
        self::assertSame('123.45', $product->getPrice());
    }
}
