<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Pagination;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

class UserController extends AbstractController
{   
        /**
     * @OA\Get(
     *   tags={"Users"},
     *   summary="Get all users owned by the current customer",
     *   @OA\Response(response=200, description="All users owned by the current customer"),
     *   @OA\Response(response=401, description="JWT unauthorized error"),
     *   @OA\Response(response=404, description="No user found"),
     *   @OA\Parameter(
     *     name="page",
     *     description="Current page number",
     *     in="query",
     *     required=false,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(
     *     name="limit",
     *     description="Limit items per page",
     *     in="query",
     *     required=false,
     *     @OA\Schema(type="integer")
     *   )
     * )
     */
    #[Route('/api/users', name: 'users', methods: ['GET'])]
    public function getAllUser(Request $request,Pagination $paginator, SerializerInterface $serializer, TagAwareCacheInterface $cache): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $idCache = "getAllUsers-" . $page . "-" .$limit;

        $jsonUsersList = $cache->get($idCache, function(ItemInterface $item) use ($paginator, $page, $limit, $serializer) {
            $item->tag("usersCache");
            $data = $paginator->paginate(
                'SELECT user
                FROM App\Entity\User user
                WHERE user.customer = :id
                ORDER BY user.id DESC',
                ['id' => $this->getUser()],
                $page,
                $limit
            );
            $context = SerializationContext::create()->setGroups(["getUsers"]);
            return $serializer->serialize($data, 'json', $context);
        });

       return new JsonResponse($jsonUsersList, Response::HTTP_OK, [], true);
    }

    public function userNotExist(?User $user)
    {
        if (!$user) {
            throw new NotFoundHttpException("No user found with this ID");
        }
    }

    public function isNotOwner(string $attribute, User $user, string $message)
    {
        // If current customer is not the owner return an exception
        if (!$this->isGranted($attribute, $user)) {
            throw new HttpException(JsonResponse::HTTP_UNAUTHORIZED, $message);
        }
    }


        /**
     * @OA\Get(
     *   tags={"Users"},
     *   summary="Get a user by ID",
     *   @OA\Response(response=200, description="User details"),
     *   @OA\Response(response=401, description="JWT unauthorized error"),
     *   @OA\Response(response=404, description="No user found with this ID"),
     *   @OA\PathParameter(
     *     name="id",
     *     description="ID of the user you want to recover"
     *   )
     * )
     */
    #[Route('/api/users/{id}', name: 'user', methods: ['GET'])]
    public function getUserbyId(User $user, SerializerInterface $serializer): JsonResponse
    {
        $this->userNotExist($user);
        $this->isNotOwner('USER', $user, 'You are not allowed to see this content');
        if ($user) {
            $context = SerializationContext::create()->setGroups(["getUsers"]);
            $jsonUser = $serializer->serialize($user, 'json', $context);
            
            return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    /**
     * @OA\Post(
     *   tags={"Users"},
     *   summary="Create a new user",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         type="object",
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Created user",
     *     @OA\JsonContent(
     *       type="array",
     *       @OA\Items(ref=@Model(type=User::class, groups={"getUsers"}))
     *     )
     *   ),
     *   @OA\Response(response=400, description="JSON field validation failed"),
     *   @OA\Response(
     *     response=401,
     *     description="JWT unauthorized error"
     *   ),
     *   @OA\Response(response=500, description="JSON syntax error or no JSON sent in the request body"),
     * )
     */

    #[Route('/api/users', name: 'addUser', methods: ['POST'])]
    public function createUser(Request $request,EntityManagerInterface $em,SerializerInterface $serializer,UrlGeneratorInterface $urlGenerator,ValidatorInterface $validator): JsonResponse
    {
        $data = $request->getContent();
        $user = $serializer->deserialize($data, User::class, 'json');
        $user->setCustomer($this->getUser());
        $user->setCreatedAt(new \DateTimeImmutable());

        // Errors handling
        $errors = $validator->validate($user);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($user);
        $em->flush();
        $context = SerializationContext::create()->setGroups(["getUsers"]);
        $jsonUser = $serializer->serialize($user, 'json', $context);
        $location = $urlGenerator->generate('user', ['id' => $user->getId()],  UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ["location" => $location], true);
    }

    /**
     * @OA\Delete(
     *   tags={"Users"},
     *   summary="Delete a user by ID",
     *   @OA\Response(response=204, description="User successfully deleted"),
     *   @OA\Response(response=401, description="JWT unauthorized error"),
     *   @OA\Response(response=404, description="No user found with this ID"),
     *   @OA\PathParameter(
     *     name="id",
     *     description="ID of the user you want to delete"
     *   )
     * )
     */
    #[Route('/api/users/{id}', name: 'deleteUser', methods: ['DELETE'])]
    public function deleteUser(?User $user, EntityManagerInterface $em, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $cachePool->invalidateTags(["usersCache"]);
        $this->userNotExist($user);
        $this->isNotOwner('DELETEUSER', $user, 'You are not authorized to delete this content');

        $em->remove($user);
        $em->flush();

        //204 no content
        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
