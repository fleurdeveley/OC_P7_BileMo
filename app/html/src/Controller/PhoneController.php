<?php

namespace App\Controller;

use App\Repository\PhoneRepository;
use Exception;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface as JMSInterface;
use Knp\Component\Pager\PaginatorInterface;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

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
        PaginatorInterface $paginator, 
        Request $request,
        CacheInterface $cache
    ): Response
    {
        $phoneRepository = $this->phoneRepository;

        $data = $cache->get('phones', function(ItemInterface $item) use($phoneRepository){
            $item->expiresAfter(3600);
            return $phoneRepository->findAll();
        });

        $pagination = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 5)
        );

        $result = [
            'phones' => $pagination->getItems(), 
            'meta' => $pagination->getPaginationData()
        ];

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
