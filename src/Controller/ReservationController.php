<?php

namespace App\Controller;

use App\Repository\ReservationRepository;
use DateTimeImmutable;
use DateTime;
use App\Entity\Reservation;
use App\Entity\Foodtruck;
use App\Entity\Placement;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

class ReservationController extends AbstractController
{
    #[Route('/',name: 'reservation.get_all', methods: ['GET'])]
    public function getAllReservations(Request $request,ReservationRepository $reservationRepository): JsonResponse
    {
        // now day for date end by default
        $dateStart = $request->get('dateStart', default: date("Y-m-d", strtotime("-1 month")));
        $dateEnd = $request->get('dateEnd', default: date('Y-m-d'));
        //create a validator
        $validator = Validation::createValidator();
        //check if date range is ok
        $violations = $validator->validate([$dateEnd,$dateStart], [
            new Assert\All([new Assert\Date()])
        ]);
        // init errors
        $errors=[];
        // If invalid date we keep errors
        if (0 !== count($violations)) {
            foreach($violations as $violation) {
                $errors[] = 'Wrong date ' . $violation->getInvalidValue();
            }
        }
        // If errors => error 400
        if($errors)return $this->json($errors,status:400);

        // convert into dateTime
        $dateStart = DateTime::createFromFormat('Y-m-d', $dateStart);
        $dateEnd = DateTime::createFromFormat('Y-m-d', $dateEnd);
        // get all reservations by date
        $reservations = $reservationRepository->findAllByDate($dateStart, $dateEnd);
        // format reservations per day and return result
        return $this->json($reservationRepository->groupByDay($reservations));
        // format reservations per day and return result
    }

    #[Route('/add/{date}/{placement}/{foodtruck}',
        name: 'reservation.add',
        requirements: [
            'date'=>'\d{4}-\d{2}-\d{2}',
            'placement'=>'\d{1}'
        ],
        methods: ['GET']
    )]
    public function addReservation(
                    EntityManagerInterface $entityManager,
                    string $date,
                    int $placement,
                    string $foodtruck): JsonResponse
    {
        //create a validator
        $validator = Validation::createValidator();
        //check if date range is ok
        $violations = $validator->validate([$date], [
            new Assert\All([new Assert\Date()])
        ]);
        // init errors
        $errors=[];
        // If error we display and stop execution
        if (0 !== count($violations))$errors[]='Wrong date '. $violations[0]->getInvalidValue();
        // find the related foodtruck
        $foodtruck_obj = $entityManager->getRepository(Foodtruck::class)->find($foodtruck);
        if(!$foodtruck_obj)$errors[]='Food truck not found';
        // find the related placement
        $placement_obj = $entityManager->getRepository(Placement::class)->find($placement);
        if(!$placement_obj)$errors[]='placement not found';
        // If errors => error 400
        if($errors)return $this->json($errors,status:400);

        // set a new reservation
        $reservation = (new Reservation())
            ->setDate(DateTimeImmutable::createFromFormat('Y-m-d', $date))
            ->setFoodtruck($foodtruck_obj)
            ->setPlacement($placement_obj);
        // persiste the reservation
        $entityManager->persist($reservation);
        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return $this->json($reservation,200,[],[
            'groups'=>['reservation.add'],
        ]);
    }

    #[Route('/delete/{id}',name: 'delete_product',methods:['DELETE'])]
    public function delete(Reservation $reservation,EntityManagerInterface $entityManager): JsonResponse
    {
        $res = $reservation;
        // delete a product
        $entityManager->remove($reservation);
        $entityManager->flush();
        return $this->json($res,200,[],[
            'groups'=>['reservation.delete'],
        ]);
    }

}
