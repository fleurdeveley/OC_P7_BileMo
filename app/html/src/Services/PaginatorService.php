<?php

namespace App\Services;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class PaginatorService
{
    protected $paginator;

    public function __construct(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;
    }

    public function paginate($data, Request $request)
    {
        $pagination = $this->paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 5)
        );

        $result = [
            'items' => $pagination->getItems(), 
            'meta' => $pagination->getPaginationData()
        ];

        return $result;
    }
}
