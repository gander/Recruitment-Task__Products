<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ProductApiTest extends WebTestCase
{
    private KernelBrowser $client;

    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get('doctrine')->getManager();
        (new SchemaTool($this->em))->createSchema($this->em->getMetadataFactory()->getAllMetadata());
    }

    public function testValidProductIsStoredAndReturns201(): void
    {
        $this->post('{"name": "Foo Bar","price": "123.45"}');

        self::assertResponseStatusCodeSame(201);
        self::assertSame(['status' => true, 'product' => 1], $this->json());

        $product = $this->em->getRepository(Product::class)->find(1);
        self::assertSame('Foo Bar', $product->getName());
        self::assertSame('123.45', $product->getPrice());
    }

    public function testEmptyObjectReturns400WithFieldErrors(): void
    {
        $this->post('{}');

        self::assertResponseStatusCodeSame(400);
        $errors = $this->json()['errors'];
        self::assertSame(['name', 'price'], array_column($errors, 'property'));
        self::assertCount(0, $this->em->getRepository(Product::class)->findAll());
    }

    public function testMalformedJsonReturns400(): void
    {
        $this->post('{');

        self::assertResponseStatusCodeSame(400);
        self::assertSame(['status' => false, 'errors' => ['Empty or malformed JSON content']], $this->json());
    }

    public function testEmptyBodyReturns400(): void
    {
        $this->post('');

        self::assertResponseStatusCodeSame(400);
    }

    public function testInvalidPriceReturns400(): void
    {
        $this->post('{"name": "Foo","price": "-5"}');

        self::assertResponseStatusCodeSame(400);
        self::assertContains('price', array_column($this->json()['errors'], 'property'));
    }

    public function testOnlyPostIsAllowed(): void
    {
        $this->client->request('GET', '/api/products');

        self::assertResponseStatusCodeSame(405);
    }

    private function post(string $body): void
    {
        $this->client->request('POST', '/api/products', server: ['CONTENT_TYPE' => 'application/json'], content: $body);
    }

    /** @return array<string, mixed> */
    private function json(): array
    {
        return json_decode($this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    }
}
