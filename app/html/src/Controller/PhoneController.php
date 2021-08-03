<?php

namespace App\Controller;

use App\Repository\PhoneRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PhoneController extends AbstractController
{
    /**
     * @Route("/phone", name="api_phone_list", methods={"GET"})
     */
    public function index(
        PhoneRepository $phoneRepository, 
        PaginatorInterface $paginator, 
        Request $request
        ): Response
    {
        $data = $phoneRepository->findAll();

        $phones = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            5
        );

        return $this->json(
            $phones,
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
