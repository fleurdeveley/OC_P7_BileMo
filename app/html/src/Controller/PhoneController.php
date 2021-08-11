<?php

namespace App\Controller;

use App\Repository\PhoneRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerInterface as JMSInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class PhoneController extends AbstractController
{
    protected $phoneRepository;

    public function __construct(
        PhoneRepository $phoneRepository
    )
    {
        $this->phoneRepository = $phoneRepository;
    }

    /**
     * @Route("/phone", name="api_phone_list", methods={"GET"})
     */
    public function index(
        PaginatorInterface $paginator, 
        Request $request,
        CacheInterface $cache,
        JMSInterface $serializer
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

        $json = $serializer->serialize(
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
     */
    public function show($id, JMSInterface $serializer)
    {
        $phone = $this->phoneRepository->findOneBy(['id' => $id]);

        $json = $serializer->serialize(
            $phone, 
            'json', 
            SerializationContext::create()->setGroups(array('phone:details'))
        );

        return new Response(
            $json, 
            JsonResponse::HTTP_OK, array('Content-Type' => 'application/json')
        );
    }
}
