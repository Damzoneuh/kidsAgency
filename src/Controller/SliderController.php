<?php

namespace App\Controller;

use App\Entity\Img;
use App\Entity\Slider;
use App\Service\ApiService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\TranslatorInterface;

class SliderController extends AbstractController
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
     * @param TranslatorInterface $translator
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @Route("/api/slider/img/{id}", name="api_get_slider_img")
     */
    public function getSliderImage(TranslatorInterface $translator){
        $em = $this->getDoctrine()->getRepository(Slider::class);
        $sliders = $em->findBy(['is_active' => true]);
        $transSlider = [];
        if (count($sliders) > 0) {
            foreach ($sliders as $key => $value) {
                $transSlider[$key]['title'] = $translator->trans($value->getTitle());
                $transSlider[$key]['img'] = $value->getImg()->getPath();
                $transSlider[$key]['id'] = $value->getId();
            }
            return $this->json($transSlider, 200);
        }
        return $this->json($translator->trans('image not found'), 404);
    }

    /**
     * @param Request $request
     * @param ApiService $apiService
     * @param TranslatorInterface $translator
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @Route("/api/slider/img/create", name="api_create_slider_img")
     */
    public function createSliderImage(Request $request, ApiService $apiService, TranslatorInterface $translator){
        if (!$apiService->CheckApiKey($request)){
            return $this->json($translator->trans($this->_error), 403);
        }
        $data = $this->_serializer->decode($request->getContent(), 'json');
        $em = $this->getDoctrine()->getManager();
        $img = $em->getRepository(Img::class)->find($data['imgId']);
        if ($img){
            $slider = new Slider();
            $slider->setIsActive($data['isActive']);
            $slider->setTitle($data['title']);
            $slider->setImg($img);
            $fr = Yaml::parseFile($this->getParameter('fr.trans.file'));
            $de = Yaml::parseFile($this->getParameter('de.trans.file'));
            $deTrans = $de;
            $frTrans = $fr;
            $deTrans[$data['title']] = $data['deTitle'];
            $frTrans[$data['title']] = $data['frTitle'];
            $frDump = Yaml::dump($frTrans);
            $deDump = Yaml::dump($deTrans);
            file_put_contents($this->getParameter('fr.trans.file'), $frDump);
            file_put_contents($this->getParameter('de.trans.file'), $deDump);

            return $this->json($translator->trans('slider image added'), 200);
        }
        return $this->json($translator->trans('image not found'), 404);
    }

    /**
     * @param Request $request
     * @param ApiService $apiService
     * @param TranslatorInterface $translator
     * @param $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @Route("/api/slider/img", name="api_update_slider_img")
     */
    public function updateSliderImage(Request $request, ApiService $apiService, TranslatorInterface $translator, $id){
        if(!$apiService->CheckApiKey($request)){
            return $this->json($translator->trans($this->_error), 403);
        }
        $em = $this->getDoctrine()->getManager();
        $slider = $em->getRepository(Slider::class)->find($id);
        if ($slider && $slider->getIsActive()){
            $slider->setIsActive(false);
            $em->flush();
            return $this->json($translator->trans('the slider image is now disabled'), 200);
        }
        if (!$slider){
            return $this->json($translator->trans('image not found'));
        }
        $slider->setIsActive(true);
        $em->flush();
        return $this->json($translator->trans('the slider image is now enabled'), 200);
    }

    /**
     * @param ApiService $apiService
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @Route("/api/slider/img/delete/{id}", name="api_delete_slider_image")
     */
    public function deleteSliderImage(ApiService $apiService, Request $request, TranslatorInterface $translator, $id){
        if (!$apiService->CheckApiKey($request)){
            return $this->json($translator->trans($this->_error), 403);
        }
        $em = $this->getDoctrine()->getManager();
        $slider = $em->getRepository(Slider::class)->find($id);
        $em->remove($slider);
        $em->flush();

        return $this->json($translator->trans('the slider image as been deleted'), 200);
    }
}
