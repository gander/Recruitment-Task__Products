<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Product;
use App\Helper\ViolationsMapper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\Json;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: '/api', name: 'api')]
class ApiController extends AbstractController
{
    public function __construct(private readonly \Doctrine\Persistence\ManagerRegistry $managerRegistry)
    {
    }
    /**
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param ViolationsMapper $mapper
     * @return JsonResponse
     */
    #[Route(path: '/products', methods: ['POST'])]
    public function addProduct(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        ViolationsMapper $mapper
    ): JsonResponse {
        $violations = $validator->validate($request->getContent(), [new NotBlank(), new Json()]);

        if ($violations->count()) {
            return new JsonResponse([
                'status' => false,
                'errors' => ['Empty or malformed JSON content']
            ], 400);
        }

        /** @var Product $product */
        $product = $serializer->deserialize($request->getContent(), Product::class, 'json');
        $violations = $validator->validate($product, null, ['api']);

        if ($violations->count()) {
            return new JsonResponse([
                'status' => false,
                'errors' => $mapper($violations)
            ], 400);
        }

        $em = $this->managerRegistry->getManager();
        $em->persist($product);
        $em->flush();

        return new JsonResponse([
            'status' => true,
            'product' => $product->getId()
        ], 201);
    }
}
