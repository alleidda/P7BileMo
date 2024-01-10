<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Pagination;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/api/users', name: 'users', methods: ['GET'])]
    public function getAllUser(UserRepository $userRepository, Pagination $paginator, SerializerInterface $serializer): JsonResponse
    {
        /* $data = $paginator->paginate(
            'SELECT user
            FROM App\Entity\User user
            WHERE user.customer = :id
            ORDER BY user.id DESC',
            ['id' => $this->getUser()]
        ); */

        $data = $userRepository->findAll();

        $context = SerializationContext::create()->setGroups(["getUsers"]);
        $jsonUsersList = $serializer->serialize($data, 'json', $context);

       return new JsonResponse($jsonUsersList, Response::HTTP_OK, [], true);
    }


    #[Route('/api/users/{id}', name: 'user', methods: ['GET'])]
    public function getUserbyId(User $user, SerializerInterface $serializer): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(["getUsers"]);
        $jsonUser = $serializer->serialize($user, 'json', $context);
        
        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }
}
