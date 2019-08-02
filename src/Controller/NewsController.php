<?php

namespace App\Controller;

use App\Entity\Img;
use App\Entity\News;
use App\Service\ApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\TranslatorInterface;

class NewsController extends AbstractController
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
     * @param Request $request
     * @param ApiService $apiService
     * @param null $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @Route("/api/news/create", name="api_create_news")
     */
    public function getNews(TranslatorInterface $translator, Request $request, ApiService $apiService, $id = null){
        if (!$apiService->CheckApiKey($request)){
            return $this->json($translator->trans($this->_error), 403);
        }
        $em = $this->getDoctrine()->getRepository(News::class);
        if ($id){
            $news = $em->find($id);
            $news['title'] = $translator->trans($news['title']);
            $news['text'] = $translator->trans($news['text']);
            return $this->json($news, 200);
        }
        $transNews = [];
        $news = $em->findAll();
        foreach ($news as $key => $new){
            $transNews[$key]['title'] = $translator->trans($new->getTitle());
            $transNews[$key]['text'] = $translator->trans($new->getText());
            $transNews[$key]['img'] = $new->getImg();
            $transNews[$key]['isActive'] = $new->getIsActive();
            $transNews[$key]['id'] = $new->getId();
        }
        return $this->json($transNews, 200);
    }

    /**
     * @param TranslatorInterface $translator
     * @param ApiService $apiService
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @Route("/api/news/create", name="api_news_create")
     */
    public function createNews(TranslatorInterface $translator,ApiService $apiService, Request $request){
        if (!$apiService->CheckApiKey($request)){
            return $this->json($translator->trans($this->_error), 403);
        }
        $em = $this->getDoctrine()->getManager();
        $news = new News();
        $data = $this->_serializer->decode($request->getContent(), 'json');
        $news->setTitle($data['title']);
        $frParsed = Yaml::parseFile($this->getParameter('fr.trans.file'));
        $deParsed = Yaml::parseFile($this->getParameter('de.trans.file'));

       $deParsed[$data['title']] = $data['deTitle'];
       $deParsed[$data['text']] = $data['deText'];

       $frParsed[$data['title']] = $data['frTitle'];
       $frParsed[$data['text']] = $data['frText'];

       $frDump = Yaml::dump($frParsed);
       $deDump = Yaml::dump($deParsed);

       $news->setText($data['text']);
       if ($data['img']){
           $news->setImg($em->getRepository(Img::class)->find($data['img']));
       }
       if ($data['isActive'] === true){
           $news->setIsActive(true);
       }
       else{
           $news->setIsActive(false);
       }
       file_put_contents($this->getParameter('de.trans.file'), $deDump);
       file_put_contents($this->getParameter('fr.trans.file'), $frDump);
       $em->persist($news);
       $em->flush();
       return $this->json($translator->trans('news added'), 200);
    }

    /**
     * @param ApiService $apiService
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @Route("/api/news/update/{id}", name="api_news_update")
     */
    public function updateNews(ApiService $apiService, Request $request,TranslatorInterface $translator, $id){
        if (!$apiService->CheckApiKey($request)){
            return $this->json($translator->trans($this->_error), 403);
        }
        $em = $this->getDoctrine()->getManager();
        $news = $em->getRepository(News::class)->find($id);
        $data = $this->_serializer->decode($request->getContent(), 'json');
        $news->setTitle($data['title']);
        $frParsed = Yaml::parseFile($this->getParameter('fr.trans.file'));
        $deParsed = Yaml::parseFile($this->getParameter('de.trans.file'));

        $deParsed[$data['title']] = $data['deTitle'];
        $deParsed[$data['text']] = $data['deText'];

        $frParsed[$data['title']] = $data['frTitle'];
        $frParsed[$data['text']] = $data['frText'];

        $frDump = Yaml::dump($frParsed);
        $deDump = Yaml::dump($deParsed);

        $news->setText($data['text']);
        if ($data['img']){
            $news->setImg($em->getRepository(Img::class)->find($data['img']));
        }
        if ($data['isActive'] === true){
            $news->setIsActive(true);
        }
        else{
            $news->setIsActive(false);
        }
        file_put_contents($this->getParameter('de.trans.file'), $deDump);
        file_put_contents($this->getParameter('fr.trans.file'), $frDump);
        $em->flush();
        return $this->json($translator->trans('news updated'), 200);
    }

    /**
     * @param ApiService $apiService
     * @param TranslatorInterface $translator
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @Route("/api/news/delete/{id}", name="api_news_delete")
     */
    public function deleteNews(ApiService $apiService, TranslatorInterface $translator,Request $request, $id){
        if (!$apiService->CheckApiKey($request)){
            return $this->json($translator->trans($this->_error), 403);
        }
        $em = $this->getDoctrine()->getManager();
        $new = $em->getRepository(News::class)->find($id);
        if ($new){
            $em->remove($new);
            $em->flush();
            return $this->json($translator->trans('news deleted'), 200);
        }
        return $this->json($translator->trans('news not found'), 404);
    }
}
