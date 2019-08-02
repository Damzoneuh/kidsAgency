<?php

namespace App\Controller;

use App\Entity\Img;
use App\Entity\Partner;
use App\Service\ApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\Translation\TranslatorInterface;

class PartnerController extends AbstractController
{
    private $_error;
    private $_serializer;

    public function __construct()
    {
        $this->_error = 'Bad api key';
        $_encoders = [new JsonEncoder()];
        $_normalizers = [new ObjectNormalizer()];
        $this->_serializer = new Serializer($_normalizers, $_encoders);
    }

    /**
     * @param null $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @Route("/api/partner/{id}")
     */
    public function getPartner($id = null){
        $em = $this->getDoctrine()->getRepository(Partner::class);
        if ($id){
            $partner = $em->find($id);
            return $this->json($partner, 200);
        }
        $partners = $em->findAll();
        return $this->json($partners, 200);
    }

    /**
     * @param ApiService $apiService
     * @param Request $request
     * @param TranslatorInterface $translator
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @Route("/api/partner/create", name="api_create_partner")
     */
    public function createPartner(ApiService $apiService, Request $request, TranslatorInterface $translator){
        if (!$apiService->CheckApiKey($request)){
            return $this->json($translator->trans($this->_error), 403);
        }
        $data = $this->_serializer->decode($request->getContent(), 'json');
        $em = $this->getDoctrine()->getManager();
        $partner = new Partner();
        $partner->setName($data['name']);
        $partner->setUrl($data['url']);
        if ($data['img']){
            $img = $em->getRepository(Img::class)->find($data['img']);
            $partner->setImg($img);
        }
        $em->persist($partner);
        $em->flush();
        return $this->json($translator->trans('partner added'), 200);
    }

    /**
     * @param ApiService $apiService
     * @param TranslatorInterface $translator
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @Route("/api/partner/update", name="api_update_partner")
     */
    public function updatePartner(ApiService $apiService, TranslatorInterface $translator, Request $request, $id){
        if (!$apiService->CheckApiKey($request)){
            return $this->json($translator->trans($this->_error), 403);
        }
        $data = $this->_serializer->decode($request->getContent(), 'json');
        $em = $this->getDoctrine()->getManager();
        $partner = $em->getRepository(Partner::class)->find($id);
        if ($partner){
            $partner->setName($data['name']);
            $partner->setUrl($data['url']);
            if ($data['img']){
                $img = $em->getRepository(Img::class)->find($data['img']);
                $partner->setImg($img);
            }
            $em->flush();
            return $this->json($translator->trans('partner updated'), 200);
        }

        return $this->json($translator->trans('partner not found'), 404);
    }

    /**
     * @param ApiService $apiService
     * @param TranslatorInterface $translator
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @Route("/api/partner/delete/{id}", name="api_delete_partner")
     */
    public function deletePartner(ApiService $apiService, TranslatorInterface $translator, Request $request, $id){
        if (!$apiService->CheckApiKey($request)){
            return $this->json($translator->trans($this->_error), 403);
        }
        $em = $this->getDoctrine()->getManager();
        $partner = $em->getRepository(Partner::class)->find($id);
        if ($partner){
            $em->remove($partner);
            $em->flush();
            return $this->json($translator->trans('partner deleted'), 200);
        }
        return $this->json($translator->trans('partner not found'));
    }
}
