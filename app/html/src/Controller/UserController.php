<?php

namespace App\Controller;

use DateTime;
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
    protected $cache;
    protected $userRepository;
    protected $serializer;
    protected $validator;
    protected $hasher;

    public function __construct(
        CacheInterface $cache, 
        UserRepository $userRepository,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        UserPasswordHasherInterface $hasher
    ) {
        $this->cache = $cache;
        $this->userRepository = $userRepository;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->hasher = $hasher;
    }

    /**
     * @Route("/user", name="api_user_list", methods={"GET"})
     */
    public function index(PaginatorInterface $paginator, Request $request): Response
    {
        $userRepository = $this->userRepository;

        $customer_id = $this->getUser()->getCustomer()->getId();

        $key = 'users_' . $customer_id;

        $data = $this->cache->get($key, function(ItemInterface $item) use($userRepository, $customer_id){
            $item->expiresAfter(3600);
            return $userRepository->findBy(['customer' => $customer_id]);
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
    public function show($id)
    {
        $customerId = $this->getUser()->getCustomer()->getId();

        $userBdd = $this->userRepository->findOneBy(['id' => $id]);

        if($userBdd->getCustomer()->getId() === $customerId) {
            return $this->json(
                $userBdd, 
                JsonResponse::HTTP_OK, 
                [], 
                ['groups' => 'user:details']
            );
        } else {
            return $this->json([
                'status' => Response::HTTP_UNAUTHORIZED,
            ], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * @Route("/user", name="api_user_create", methods={"POST"})
     */
    public function create(
        Request $request, 
        EntityManagerInterface $em,
        CustomerRepository $customerRepository
    )
    {
        try {
            $user = $this->serializer->deserialize($request->getContent(), User::class, 'json');

            $customerId = $this->getUser()->getCustomer()->getId();

            $errors = $this->validator->validate($user);

            $user->setPassword($this->hasher->hashPassword($user, $user->getPassword()))
                ->setRoles(['ROLE_USER'])
                ->setCustomer($customerRepository->findOneBy(['id' => $customerId]))
                ->setCreatedAt(new DateTime())
                ->setUpdatedAt(new DateTime());

            if(count($errors) > 0) {
                return $this->json($errors, Response::HTTP_BAD_REQUEST);
            }

            $em->persist($user);
            $em->flush();

            $this->cache->delete('users_' . $customerId);

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
    public function update(Request $request, EntityManagerInterface $em, $id)
    {
        try {
            $user = $this->userRepository->findOneBy(['id' => $id]);

            $userJson = $this->serializer->deserialize($request->getContent(), User::class, 'json');

            $errors = $this->validator->validate($userJson);

            if(count($errors) > 0) {
                return $this->json($errors, Response::HTTP_BAD_REQUEST);
            }

            if($userJson->getEmail()){
                $user->setEmail($userJson->getEmail());
            }

            if($userJson->getPassword()){
                $user->setPassword($this->hasher->hashPassword($user, $userJson->getPassword()));
            }

            if($userJson->getfullName()){
                $user->setFullName($userJson->getFullName());
            }

            $user->setUpdatedAt(new DateTime());

            $em->flush();

            $customerId = $this->getUser()->getCustomer()->getId();

            $this->cache->delete('users_' . $customerId);

            return $this->json(
                [],
                Response::HTTP_OK 
            );  
        } catch(NotEncodableValueException $e) {
            return $this->json([
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/user/{id}", name="api_user_delete", methods={"DELETE"})
     */
    public function delete(User $user, EntityManagerInterface $em)
    {
        $em->remove($user);
        $em->flush();

        $customerId = $this->getUser()->getCustomer()->getId();

        $this->cache->delete('users_' . $customerId);

        return $this->json(
            [], 
            Response::HTTP_NO_CONTENT
        );  
    }
}
