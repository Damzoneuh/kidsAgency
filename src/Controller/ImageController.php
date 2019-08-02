<?php

namespace App\Controller;

use App\Entity\Img;
use App\Service\ApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\TranslatorInterface;

class ImageController extends AbstractController
{
    private $_serializer;
    private $_error;

   public function __construct()
   {
       $this->_error = 'Bad api key';
       $_encoders = [new JsonEncoder()];
       $_normalizers = [new ObjectNormalizer()];
       $this->_serializer = new Serializer($_normalizers, $_encoders);
   }

    /**
     * @param TranslatorInterface $translator
     * @param null $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @Route("/api/img/{id}", name="api_get_img")
     */
   public function getImage(TranslatorInterface $translator, $id = null){
       $em = $this->getDoctrine()->getRepository(Img::class);
       if(!$id){
           $imgs = $em->findAll();
           $transImg = [];
           if (count($imgs > 0)) {
               foreach ($imgs as $key => $img) {
                   $transImg[$key]['title'] = $translator->trans($img->getTitle());
                   $transImg[$key]['path'] = $img->getPath();
                   $transImg[$key]['id'] = $img->getId();
               }
               return $this->json($transImg, 200);
           }
           return $this->json($translator->trans('image not found'), 404);
       }
       $img = $em->find($id);
       if ($img) {
           $transImg['title'] = $translator->trans($img->getTitle());
           $transImg['path'] = $img->getPath();
           $transImg['id'] = $img->getId();
           return $this->json($transImg, 200);
       }
       return $this->json($translator->trans('image not found'), 404);
   }


    /**
     * @param ApiService $apiService
     * @param TranslatorInterface $translator
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Exception
     * @Route("/api/img/create", name="api_create_img")
     */
   public function createImage(ApiService $apiService, TranslatorInterface $translator, Request $request){
       if (!$apiService->CheckApiKey($request)){
           return $this->json($translator->trans($this->_error), 403);
       }
       $data['title'] = $request->get('title');
       $data['deTitle'] = $request->get('deTitle');
       $data['frTitle'] = $request->get('frTitle');

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

       $file = $request->files->get('file');
       /** @var UploadedFile $file */
       if(!self::checkAuthorizedExtensions($file)){
           return $this->json($translator->trans('the image format is incorrect'), 400);
       }

       $newFileName = self::getUniqName() . '.' . $file->getClientOriginalExtension();
       $em = $this->getDoctrine()->getManager();
       $img = new Img();
       $img->setPath($this->getParameter('img.path') . $newFileName);
       $img->setTitle($data['title']);

       $file->move($this->getParameter('img.path'), $newFileName);

       $em->persist($img);
       $em->flush();

       return $this->json($translator->trans('image added'), 200);
   }

    /**
     * @param ApiService $apiService
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @Route("/api/img/delete/{id}", name="api_delete_img")
     */
   public function deleteImage(ApiService $apiService, Request $request, TranslatorInterface $translator, $id){
       if (!$apiService->CheckApiKey($request)){
           return $this->json($translator->trans($this->_error));
       }
       $em = $this->getDoctrine()->getManager();
       $img = $em->getRepository(Img::class)->find($id);
       $em->remove($img);
       $em->flush();

       return $this->json($translator->trans('image removed'), 200);
   }

    /**
     * @param UploadedFile $file
     * @return bool
     */
   protected function checkAuthorizedExtensions(UploadedFile $file) : bool {
       switch ($file->getClientMimeType()){
           case 'image/jpeg':
               return true;
               break;

           case 'image/png':
               return true;
               break;

           case 'image/gif':
               return true;
               break;

           default:
               return false;
               break;
       }
   }

    /**
     * @return string
     * @throws \Exception
     */
   protected function getUniqName() : string {
       return bin2hex(random_bytes(32));
   }
}
