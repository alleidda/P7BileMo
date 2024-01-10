<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ProductController extends AbstractController
{
    #[Route('/api/products', name: 'products', methods: ['GET'])]
    public function getAllProduct(ProductRepository $productRepository, SerializerInterface $serializer, Request $request): JsonResponse
    {
        $productList = $productRepository->findAll();
        $jsonProductList = $serializer->serialize($productList, 'json');
        return new JsonResponse($jsonProductList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/products/{id}', name: 'products', methods: ['GET'])]
    public function getProductById(Product $product, SerializerInterface $serializer, Request $request): JsonResponse
    {
        $jsonProduct = $serializer->serialize($product, 'json');
        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    }
}
