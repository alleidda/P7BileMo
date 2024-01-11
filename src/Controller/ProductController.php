<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;



class ProductController extends AbstractController
{

  /**
     * @OA\Get(
     *   tags={"Products"},
     *   summary="Get all products",
     *   @OA\Parameter(
     *     name="page",
     *     description="Current page number",
     *     in="query",
     *     required=false,
     *     @OA\Schema(type="integer"),
     *   ),
     *   @OA\Parameter(
     *     name="limit",
     *     description="Limit items per page",
     *     in="query",
     *     required=false,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(response=200, description="All products"),
     *   @OA\Response(response=401, description="JWT unauthorized error"),
     *   @OA\Response(response=404, description="No product found")
     * )
     */

    #[Route('/api/products', name: 'products', methods: ['GET'])]
    public function getAllProduct(ProductRepository $productRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $idCache = "getAllProducts-" . $page . "-" . $limit;

        $jsonProductList = $cache->get($idCache, function(ItemInterface $item) use ($productRepository, $page, $limit, $serializer) {
            $item->tag("productsCache");
            $productList = $productRepository->findAllWithPagination($page, $limit);
            $context = SerializationContext::create()->setGroups(['getProducts']);
            return $serializer->serialize($productList, 'json', $context);
        });

        return new JsonResponse($jsonProductList, Response::HTTP_OK, [], true);
    }

    /**
     * @OA\Get(
     *  tags={"Products"},
     *  summary="Get product by Id",
     * @OA\Response(response=200, description="Products details"),
     *   @OA\Response(response=401, description="JWT unauthorized error"),
     *   @OA\Response(response=404, description="No product found with this Id")
     * )
     *
     * @param Product $product
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */

    #[Route('/api/products/{id}', name: 'product', methods: ['GET'])]
    public function getProductById(Product $product, SerializerInterface $serializer, Request $request): JsonResponse
    {
        if ($product) {
        $jsonProduct = $serializer->serialize($product, 'json');
        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}
