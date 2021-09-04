<?php

namespace App\Services;

use App\Repository\PhoneRepository;
use App\Repository\UserRepository;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

class CacheService
{
    protected $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function getUsers($key, $customer_id, UserRepository $userRepository)
    {
        $data = $this->cache->get(
            $key,
            function (ItemInterface $item)
            use ($userRepository, $customer_id) {
                $item->expiresAfter(3600);
                return $userRepository->findBy(['customer' => $customer_id]);
            }
        );

        return $data;
    }

    public function getPhones(PhoneRepository $phoneRepository)
    {
        $data = $this->cache->get('phones', function(ItemInterface $item) use($phoneRepository){
            $item->expiresAfter(3600);
            return $phoneRepository->findAll();
        });

        return $data;
    }

    public function delete($key)
    {
        $this->cache->delete($key);
    }
}
