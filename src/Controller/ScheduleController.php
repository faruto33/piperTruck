<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Yaml\Yaml;

class ScheduleController extends AbstractController
{

    #[Route('/schedule', name: 'schedule')]
    public function index(HttpClientInterface $httpClient): Response
    {
        // get url from yaml config file
        $url = Yaml::parseFile(__DIR__.'/../../config/reservations.yaml')['url']['prod'];
        $response = $httpClient->request('GET', $url);

        // $contentType = 'application/json'
        $content = json_decode($response->getContent(),true);
dd($content);
        return $this->render('schedule/index.html.twig', [
            'content' => $content,
        ]);
    }
}
