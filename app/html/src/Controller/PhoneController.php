<?php

namespace App\Controller;

use App\Repository\PhoneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class PhoneController extends AbstractController
{
    /**
     * @Route("/phone", name="api_phone_list", methods={"GET"})
     */
    public function index(PhoneRepository $phoneRepository)
    {
        return $this->json(
            $phoneRepository->findAll(), 
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
