<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

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
            ['groups' => 'user:details']);
    }
}
