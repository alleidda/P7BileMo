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

    #[Route('/api/users', name: 'addUser', methods: ['POST'])]
    public function createUser(
    Request $request,
    EntityManagerInterface $em,
    SerializerInterface $serializer,
    UrlGeneratorInterface $urlGenerator
    ): JsonResponse
    {
        $data = $request->getContent();
        $user = $serializer->deserialize($data, User::class, 'json');
        $user->setCustomer($this->getUser());
        $user->setCreatedAt(new \DateTimeImmutable());


        $em->persist($user);
        $em->flush();
        $context = SerializationContext::create()->setGroups(["getUsers"]);
        $jsonUser = $serializer->serialize($user, 'json', $context);
        $location = $urlGenerator->generate('user', ['id' => $user->getId()],  UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ["location" => $location], true);
    }

    
}
