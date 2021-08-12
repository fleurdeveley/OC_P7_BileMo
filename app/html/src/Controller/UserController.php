<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
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
use JMS\Serializer\SerializerInterface as JMSInterface;

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
    public function index(
        PaginatorInterface $paginator,
        Request $request,
        JMSInterface $serializer
    ): Response {
        $userRepository = $this->userRepository;

        $customer_id = $this->getUser()->getCustomer()->getId();

        $key = 'users_' . $customer_id;

        $data = $this->cache->get(
            $key,
            function (ItemInterface $item)
            use ($userRepository, $customer_id) {
                $item->expiresAfter(3600);
                return $userRepository->findBy(['customer' => $customer_id]);
            }
        );

        $pagination = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 5)
        );

        $result = [
            'users' => $pagination->getItems(),
            'meta' => $pagination->getPaginationData()
        ];

        $json = $serializer->serialize(
            $result,
            'json',
            SerializationContext::create()->setGroups(array('user:list'))
        );

        return new Response(
            $json,
            Response::HTTP_OK,
            array('Content-Type' => 'application/json')
        );
    }

    /**
     * @Route("/user/{id}", name="api_user_details", methods={"GET"})
     */
    public function show($id, JMSInterface $serializer)
    {
        $customerId = $this->getUser()->getCustomer()->getId();

        $userBdd = $this->userRepository->findOneBy(['id' => $id]);

        if ($userBdd === null) {
            throw new Exception('user not found', Response::HTTP_NOT_FOUND);
        }

        if ($userBdd->getCustomer()->getId() !== $customerId) {
            throw new Exception('unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        $json = $serializer->serialize(
            $userBdd,
            'json',
            SerializationContext::create()->setGroups(array('user:details'))
        );

        return new Response(
            $json,
            Response::HTTP_OK,
            array('Content-Type' => 'application/json')
        );
    }

    /**
     * @Route("/user", name="api_user_create", methods={"POST"})
     */
    public function create(
        Request $request,
        EntityManagerInterface $em,
        CustomerRepository $customerRepository
    ) {
        $user = $this->serializer->deserialize($request->getContent(), User::class, 'json');

        $customerId = $this->getUser()->getCustomer()->getId();

        $errors = $this->validator->validate($user);

        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new Exception($errorsString, Response::HTTP_BAD_REQUEST);
        }

        $user->setPassword($this->hasher->hashPassword($user, $user->getPassword()))
            ->setRoles(['ROLE_USER'])
            ->setCustomer($customerRepository->findOneBy(['id' => $customerId]))
            ->setCreatedAt(new DateTime())
            ->setUpdatedAt(new DateTime());

        $em->persist($user);
        $em->flush();

        $this->cache->delete('users_' . $customerId);

        return $this->json(
            $user,
            Response::HTTP_CREATED,
            [],
            ['groups' => 'user:details']
        );
    }

    /**
     * @Route("/user/{id}", name="api_user_update", methods={"PUT"})
     */
    public function update(Request $request, EntityManagerInterface $em, $id)
    {
        $user = $this->userRepository->findOneBy(['id' => $id]);

        $userJson = $this->serializer->deserialize($request->getContent(), User::class, 'json');

        $errors = $this->validator->validate($userJson);

        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new Exception($errorsString, Response::HTTP_BAD_REQUEST);
        }

        if ($userJson->getEmail()) {
            $user->setEmail($userJson->getEmail());
        }

        if ($userJson->getPassword()) {
            $user->setPassword($this->hasher->hashPassword($user, $userJson->getPassword()));
        }

        if ($userJson->getfullName()) {
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
