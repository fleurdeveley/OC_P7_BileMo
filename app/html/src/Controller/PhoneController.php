<?php

namespace App\Controller;

use App\Repository\PhoneRepository;
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
    /**
     * @Route("/phone", name="api_phone_list", methods={"GET"})
     */
    public function index(
        PhoneRepository $phoneRepository, 
        PaginatorInterface $paginator, 
        Request $request,
        CacheInterface $cache
        ): Response
    {
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

        return $this->json(
            $result,
            JsonResponse::HTTP_OK, 
            [], 
            ['groups' => 'phone:list']);
    }

    /**
     * @Route("/phone/{id}", name="api_phone_details", methods={"GET"})
     */
    public function show($id, PhoneRepository $phoneRepository)
    {
        return $this->json(
            $phoneRepository->findOneBy(['id' => $id]), 
            JsonResponse::HTTP_OK, 
            [], 
            ['groups' => 'phone:details']);
    }
}
