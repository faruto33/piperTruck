<?php

namespace App\Controller;

use App\Entity\Foodtruck;
use App\Entity\Placement;
use App\Entity\Reservation;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Yaml\Yaml;

class ScheduleController extends AbstractController
{

    #[Route('/schedule', name: 'schedule')]
    public function index(HttpClientInterface $httpClient,EntityManagerInterface $entityManager): Response
    {
        // get all placements list
        $placements = $entityManager->getRepository(Placement::class)->findAll();
        // get all foodtrucks list
        $foodtrucks = $entityManager->getRepository(Foodtruck::class)->findAll();

        // get url from yaml config file
        $url = Yaml::parseFile(__DIR__.'/../../config/reservations.yaml')['url']['prod'];
        $response = $httpClient->request('GET', $url);

        // $contentType = 'application/json'
        $contents = json_decode($response->getContent(),true);
dd($contents);
        return $this->render('schedule/index.html.twig', [
            'contents' => $contents,
            'placements' => $placements,
            'foodtrucks' => $foodtrucks,
        ]);
    }
}
