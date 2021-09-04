<?php

namespace App\Controller;

use Exception;
use App\Services\CacheService;
use OpenApi\Annotations as OA;
use App\Services\PaginatorService;
use App\Repository\PhoneRepository;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use JMS\Serializer\SerializerInterface as JMSInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PhoneController extends AbstractController
{
    protected $phoneRepository;
    protected $serializer;

    public function __construct(
        PhoneRepository $phoneRepository,
        JMSInterface $serializer
    )
    {
        $this->phoneRepository = $phoneRepository;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/phone", name="api_phone_list", methods={"GET"})
     * 
     * @OA\Get(summary="Get list of BileMo phones")
     * @OA\Response(
     *     response=Response::HTTP_OK,
     *     description="Returns the list of phones"
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
     *     description="Number of phones by page",
     *     @OA\Schema(type="int", default = 5)
     * )
     * @OA\Tag(name="Phones")
     */
    public function index(
        PaginatorService $paginator, 
        Request $request,
        CacheService $cache
    ): Response
    {
        $phoneRepository = $this->phoneRepository;

        $data = $cache->getPhones($phoneRepository);

        $result = $paginator->paginate($data, $request);

        $json = $this->serializer->serialize(
            $result,
            'json', 
            SerializationContext::create()->setGroups(array('phone:list'))
        );

        return new Response(
            $json, 
            Response::HTTP_OK, 
            array('Content-Type' => 'application/json')
        );
    }

    /**
     * @Route("/phone/{id}", name="api_phone_details", methods={"GET"})
     * 
     * @OA\Get(summary="Get details of a phone")
     * @OA\Response(
     *     response=Response::HTTP_OK,
     *     description="Returns a phone"
     * )
     * @OA\Response(
     *     response=Response::HTTP_NOT_FOUND,
     *     description="Phone not found"
     * )
     * @OA\Response(
     *     response=Response::HTTP_UNAUTHORIZED,
     *     description="Invalid JWT Token"
     * )
     * @OA\Tag(name="Phones")
     */
    public function show($id): Response
    {
        $phone = $this->phoneRepository->findOneBy(['id' => $id]);

        if($phone === null) {
            throw new Exception('phone not found', Response::HTTP_NOT_FOUND);
        }

        $json = $this->serializer->serialize(
            $phone, 
            'json', 
            SerializationContext::create()->setGroups(array('phone:details'))
        );

        return new Response(
            $json, 
            Response::HTTP_OK, array('Content-Type' => 'application/json')
        );
    }
}
