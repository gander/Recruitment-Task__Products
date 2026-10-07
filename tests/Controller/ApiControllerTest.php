<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\ApiController;
use App\Entity\Product;
use App\Helper\ViolationsMapper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ApiControllerTest extends TestCase
{
    public function testMalformedJsonReturns400WithoutTouchingTheSerializer(): void
    {
        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects(self::never())->method('deserialize');
        $validator = $this->createStub(ValidatorInterface::class);
        $validator->method('validate')->willReturn(new ConstraintViolationList([
            new ConstraintViolation('Invalid JSON.', null, [], null, '', ''),
        ]));

        $response = $this->controller()->addProduct(Request::create('/api/products', 'POST', content: '{'), $serializer, $validator, new ViolationsMapper());

        self::assertSame(400, $response->getStatusCode());
        self::assertSame(['status' => false, 'errors' => ['Empty or malformed JSON content']], $this->decode($response->getContent()));
    }

    public function testInvalidProductReturns400WithMappedErrors(): void
    {
        $serializer = $this->createStub(SerializerInterface::class);
        $serializer->method('deserialize')->willReturn(new Product());
        $validator = $this->createStub(ValidatorInterface::class);
        $validator->method('validate')->willReturnOnConsecutiveCalls(
            new ConstraintViolationList(),
            new ConstraintViolationList([new ConstraintViolation('Blank.', null, [], null, 'name', '')]),
        );

        $response = $this->controller()->addProduct(Request::create('/api/products', 'POST', content: '{}'), $serializer, $validator, new ViolationsMapper());

        self::assertSame(400, $response->getStatusCode());
        self::assertSame(
            ['status' => false, 'errors' => [['property' => 'name', 'message' => 'Blank.']]],
            $this->decode($response->getContent()),
        );
    }

    public function testValidProductIsPersistedAndReturns201(): void
    {
        $product = (new Product())->setName('Foo Bar')->setPrice('123.45');
        $serializer = $this->createStub(SerializerInterface::class);
        $serializer->method('deserialize')->willReturn($product);
        $validator = $this->createStub(ValidatorInterface::class);
        $validator->method('validate')->willReturn(new ConstraintViolationList());

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('persist')->with($product);
        $em->expects(self::once())->method('flush');
        $registry = $this->createStub(ManagerRegistry::class);
        $registry->method('getManager')->willReturn($em);

        $response = (new ApiController($registry))->addProduct(
            Request::create('/api/products', 'POST', content: '{"name":"Foo Bar","price":"123.45"}'),
            $serializer,
            $validator,
            new ViolationsMapper(),
        );

        self::assertSame(201, $response->getStatusCode());
        self::assertSame(['status' => true, 'product' => null], $this->decode($response->getContent()));
    }

    private function controller(): ApiController
    {
        return new ApiController($this->createStub(ManagerRegistry::class));
    }

    private function decode(string|false $json): array
    {
        return json_decode((string) $json, true, flags: JSON_THROW_ON_ERROR);
    }
}
