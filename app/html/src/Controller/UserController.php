<?php

namespace App\Controller;

use DateTime;
use Exception;
use App\Entity\User;
use App\Services\CacheService;
use OpenApi\Annotations as OA;
use App\Repository\UserRepository;
use App\Services\PaginatorService;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use JMS\Serializer\SerializerInterface as JMSInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    protected $userRepository;
    protected $cache;
    protected $serializer;
    protected $validator;
    protected $hasher;

    public function __construct(
        UserRepository $userRepository,
        CacheService $cache,
        JMSInterface $serializer,
        ValidatorInterface $validator,
        UserPasswordHasherInterface $hasher
    ) 
    {
        $this->userRepository = $userRepository;
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->hasher = $hasher;
    }

    /**
     * @Route("/user", name="api_user_list", methods={"GET"})
     * 
     * @OA\Get(summary="Get list of BileMo users")
     * @OA\Response(
     *     response=Response::HTTP_OK,
     *     description="Returns the list of users"
     * )
     * @OA\Response(
     *     response=Response::HTTP_UNAUTHORIZED,
     *     description="Invalid JWT Token"
     * )
     * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="The page number",
     *     @OA\Schema(type="int", default = "1")
     * )
     * @OA\Parameter(
     *     name="limit",
     *     in="query",
     *     description="Number of users by page",
     *     @OA\Schema(type="int", default = 5)
     * )
     * @OA\Tag(name="Users")
     */
    public function index(
        CacheService $cache,
        PaginatorService $paginator, 
        Request $request
    ): Response 
    {
        $customer_id = $this->getUser()->getCustomer()->getId();

        $key = 'users_' . $customer_id;

        $data = $cache->getUsers($key, $customer_id, $this->userRepository);

        $result = $paginator->paginate($data, $request);

        $json = $this->serializer->serialize(
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
     * 
     * @OA\Get(summary="Get details of a user")
     * @OA\Response(
     *     response=Response::HTTP_OK,
     *     description="Returns a user"
     * )
     * @OA\Response(
     *     response=Response::HTTP_NOT_FOUND,
     *     description="User not found"
     * )
     * @OA\Response(
     *     response=Response::HTTP_UNAUTHORIZED,
     *     description="Invalid JWT Token"
     * )
     * @OA\Tag(name="Users")
     */
    public function show($id): Response
    {
        $customerId = $this->getUser()->getCustomer()->getId();

        $userBdd = $this->userRepository->findOneBy(['id' => $id]);

        if ($userBdd === null) {
            throw new Exception('user not found', Response::HTTP_NOT_FOUND);
        }

        if ($userBdd->getCustomer()->getId() !== $customerId) {
            throw new Exception('unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        $json = $this->serializer->serialize(
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
     * 
     * @OA\Post(summary="Create a new user")
     * @OA\RequestBody(
     *     description="Create a new user",
     *     required=true,
     *     @OA\MediaType(
     *         mediaType="application/Json",
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="fullName",
     *                 description="Fisrtname and lastname for usereyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE2MzA2NjAzNjYsImV4cCI6MTYzMDY2MDQ4Niwicm9sZXMiOlsiUk9MRV9VU0VSIl0sInVzZXJuYW1lIjoidXNlcjNAZ21haWwuY29tIn0.p5OyWLypypdjDFYfdRtKYFQpTqdCWA7gP5I128oN1VVlsbvK5jRufNdp6CYwV26GtMjScmtiQsN8om-rvQ7OWA7Ktrpt_AoKyyu5x3c2D2ZqZZlKb8nPG-liRUInnFm9TG1naLkqEfveGdrebJDR--NO3QOC2_90AZsdLOeyePy5nbA2qQkZrrIYuDlD_Kw3FeFov4bOVA14iwU9KyZj4U9PZO45NjTY55c6a3dlw3Ow9UWSoK8mNlmXLKBNSogY1g_40ZHZdUD1Znpy2gcrVkg7SeUxGrJHKLIYTWcBi0e9byvahe4K-TBbSv2LQHxlsUs2CVACsslDumoJvesZ4Q identification",
     *                 type="string"
     *             ),     
     *             @OA\Property(
     *                 property="email",
     *                 description="User's email",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 description="Password chosen by the user with a minimum of height characters, one uppercase letter, one special character and one number",
     *                 type="string"
     *             )
     *         )
     *     )
     * )
     * @OA\Response(
     *     response=Response::HTTP_CREATED,
     *     description="Returns the new user"
     * )
     * @OA\Response(
     *     response=Response::HTTP_BAD_REQUEST,
     *     description="Bad Json syntax or incorrect data"
     * )
     * @OA\Response(
     *     response=Response::HTTP_UNAUTHORIZED,
     *     description="Invalid JWT Token"
     * )
     * @OA\Tag(name="Users")
     */
    public function create(
        Request $request,
        EntityManagerInterface $em,
        CustomerRepository $customerRepository
    ): Response
    {
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

        $json = $this->serializer->serialize(
            $user,
            'json',
            SerializationContext::create()->setGroups(array('user:list'))
        );

        return new Response(
            $json,
            Response::HTTP_CREATED,
            array('Content-Type' => 'application/json')
        );
    }

    /**
     * @Route("/user/{id}", name="api_user_update", methods={"PUT"})
     * 
     * @OA\Put(summary="Update a user")
     * @OA\RequestBody(
     *     description="User data to modify : put only the fields to modify",
     *     required=true,
     *     @OA\MediaType(
     *         mediaType="application/Json",
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="fullName",
     *                 description="Fisrtname and lastname for user identification",
     *                 type="string"
     *             ),     
     *             @OA\Property(
     *                 property="email",
     *                 description="User's email",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 description="Password chosen by the user",
     *                 type="string"
     *             )
     *         )
     *     )
     * )
     * @OA\Response(
     *     response=Response::HTTP_OK,
     *     description="Returns a modified user"
     * )
     * @OA\Response(
     *     response=Response::HTTP_BAD_REQUEST,
     *     description="Bad Json syntax or incorrect data"
     * )
     * @OA\Response(
     *     response=Response::HTTP_UNAUTHORIZED,
     *     description="Invalid JWT Token"
     * )
     * @OA\Tag(name="Users")
     */
    public function update(Request $request, EntityManagerInterface $em, $id): Response
    {
        $user = $this->userRepository->findOneBy(['id' => $id]);

        $userJson = $this->serializer->deserialize($request->getContent(), User::class, 'json');

        if ($userJson->getEmail()) {
            $user->setEmail($userJson->getEmail());
        }

        if ($userJson->getPassword()) {
            $user->setPassword($userJson->getPassword());
        }

        if ($userJson->getfullName()) {
            $user->setFullName($userJson->getFullName());
        }

        $user->setUpdatedAt(new DateTime());

        $errors = $this->validator->validate($user);

        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new Exception($errorsString, Response::HTTP_BAD_REQUEST);
        }

        $user->setPassword($this->hasher->hashPassword($user, $userJson->getPassword()));

        $em->flush();

        $customerId = $this->getUser()->getCustomer()->getId();

        $this->cache->delete('users_' . $customerId);

        return new Response(
            json_encode([]),
            Response::HTTP_OK,
            array('Content-Type' => 'application/json')
        );
    }

    /**
     * @Route("/user/{id}", name="api_user_delete", methods={"DELETE"})
     * 
     * @OA\Delete(summary="Delete a user")
     * @OA\Response(
     *     response=Response::HTTP_NO_CONTENT,
     *     description="Delete a user"
     * )
     * @OA\Response(
     *     response=Response::HTTP_UNAUTHORIZED,
     *     description="Invalid JWT Token"
     * )
     * @OA\Tag(name="Users")
     */
    public function delete(User $user, EntityManagerInterface $em): Response
    {
        $em->remove($user);
        $em->flush();

        $customerId = $this->getUser()->getCustomer()->getId();

        $this->cache->delete('users_' . $customerId);

        return new Response(
            json_encode([]),
            Response::HTTP_NO_CONTENT,
            array('Content-Type' => 'application/json')
        );
    }
}
