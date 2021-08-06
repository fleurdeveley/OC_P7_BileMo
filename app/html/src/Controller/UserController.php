<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="api_user_list", methods={"GET"})
     */
    public function index(
        UserRepository $userRepository,
        PaginatorInterface $paginator,
        Request $request,
        CacheInterface $cache
    ): Response
    {
        $data = $cache->get('users', function(ItemInterface $item) use($userRepository){
            $item->expiresAfter(3600);
            return $userRepository->findAll();
        });

        $pagination = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 5)
        );

        $result = [
            'users' => $pagination->getItems(),
            'meta' => $pagination->getPaginationData()
        ];

        return $this->json(
            $result,
            JsonResponse::HTTP_OK,
            [],
            ['groups' => 'user:list'],
        );
    }

    /**
     * @Route("/user/{id}", name="api_user_details", methods={"GET"})
     */
    public function show($id, UserRepository $userRepository)
    {
        return $this->json(
            $userRepository->findOneBy(['id' => $id]), 
            JsonResponse::HTTP_OK, 
            [], 
            ['groups' => 'user:details']
        );
    }

    /**
     * @Route("/user", name="api_user_create", methods={"POST"})
     */
    public function create(
        Request $request, 
        SerializerInterface $serializer, 
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        CustomerRepository $customerRepository,
        UserPasswordHasherInterface $hasher
    )
    {
        $jsonRecu = $request->getContent();

        try {
            $user = $serializer->deserialize($jsonRecu, User::class, 'json');
           
            $user->setPassword($hasher->hashPassword($user, 'password'))
                ->setRoles(['ROLE_USER'])
                ->setCustomer($customerRepository->findOneBy(['id' => 81]));

            $errors = $validator->validate($user);

            if(count($errors) > 0) {
                return $this->json($errors, Response::HTTP_BAD_REQUEST);
            }

            $em->persist($user);
            $em->flush();

            return $this->json(
                $user, 
                Response::HTTP_CREATED, 
                [],
                ['groups' => 'user:details']
            );  
        } catch(NotEncodableValueException $e) {
            return $this->json([
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

        /**
     * @Route("/user/{id}", name="api_user_update", methods={"PUT"})
     */
    public function update(
        Request $request, 
        SerializerInterface $serializer, 
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        CustomerRepository $customerRepository,
        UserPasswordHasherInterface $hasher
    )
    {
        $jsonRecu = $request->getContent();

        try {
            $user = $serializer->deserialize($jsonRecu, User::class, 'json');

            $errors = $validator->validate($user);

            if(count($errors) > 0) {
                return $this->json($errors, Response::HTTP_BAD_REQUEST);
            }

            $em->flush();

            return $this->json(
                null, 
                Response::HTTP_NO_CONTENT, 
            );  
        } catch(NotEncodableValueException $e) {
            return $this->json([
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
