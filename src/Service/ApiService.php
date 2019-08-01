<?php


namespace App\Service;

use App\Entity\ApiKey;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

class ApiService
{
    private $_entityManager;
    public function __construct(EntityManager $entityManager)
    {
        $this->_entityManager = $entityManager;
    }

    public function CheckApiKey(Request $request) : bool {
        $key = $this->_entityManager->getRepository(ApiKey::class);
        if ($request->headers->get('Authorization')){
            $apiKey = str_replace('Bearer ', '', $request->headers->get('Authorization'));
            if ($key->findOneBy(['api_key' => $apiKey])){
                return true;
            }
            return false;
        }
        return false;
    }
}