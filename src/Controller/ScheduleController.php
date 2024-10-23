<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Foodtruck;
use App\Entity\Placement;
use App\Service\Quota;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ScheduleController extends AbstractController
{
    #[Route('/schedule', name: 'schedule')]
    public function index(EntityManagerInterface $entityManager, Quota $quota): Response
    {
        // get all placements list
        $placements = $entityManager->getRepository(Placement::class)->findAll();
        // get all foodtrucks list
        $foodtrucks = $entityManager->getRepository(Foodtruck::class)->findAll();

        // get API json results from reservation controller
        $response = $this->forward('App\Controller\ReservationController::getAllReservations');
        // $contentType = 'application/json'
        $contents = json_decode($response->getContent(),true);

        return $this->render('schedule/index.html.twig', [
            'contents' => $contents,
            'placements' => $placements,
            'foodtrucks' => $foodtrucks,
            'count' => $quota->countplace(),
        ]);
    }
}
