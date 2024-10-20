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
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
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
        $dateStart = $request->get('dateStart', default: date("Y-m-d", strtotime("now")));
        $dateEnd = $request->get('dateEnd', default: date("Y-m-d", strtotime("+1 week")));

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
    }

    #[Route('/add', name: 'reservation.add', methods: ['POST'])]
    public function addReservation(Request $request,
                    EntityManagerInterface $entityManager,
                    #[MapRequestPayload(
                        validationGroups:['reservation.add'] ,
                        validationFailedStatusCode: 400)] Reservation $reservation): JsonResponse
    {
        // get content to add from json file
        $toadd = json_decode($request->getContent(), true);
        //create a validator
        $validator = Validation::createValidator();
        //check if date range is ok
        $violations = $validator->validate($toadd['date'], [
            new Assert\Date()
        ]);
        // init errors
        $errors=[];
        // If error we display and stop execution
        if (0 !== count($violations))$errors[]='Wrong date '. $violations[0]->getInvalidValue();
        // find the related foodtruck
        if(!$foodtruck_obj = $entityManager->getRepository(Foodtruck::class)->find($toadd['foodtruck']['id']))
            $errors[]='Foodtruck not found';
        // find the related placement
        if(!$placement_obj = $entityManager->getRepository(Placement::class)->find($toadd['placement']['id']))
            $errors[]='Placement not found';
        // If errors => error 400
        if($errors)return $this->json($errors,status:400);

        // set a new reservation (to avoid reference id issue)
        $reservation = (new Reservation())
            ->setDate(DateTimeImmutable::createFromFormat('Y-m-d', $toadd['date']))
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

    #[Route('/delete/{id}', name: 'delete', requirements: ['placement'=>'\d+'],methods:['DELETE'])]
    public function delete(EntityManagerInterface $entityManager,int $id): JsonResponse
    {
        // if the reservation exists we delete it
        if($reservation = $entityManager->getRepository(Reservation::class)->find($id))
        {
            // delete a product
            $entityManager->remove($reservation);
            $entityManager->flush();
            return $this->json($reservation,200,[],[
                'groups'=>['reservation.delete'],
            ]);
        }
        // otherwise return unfound error
        return $this->json(['Reservation not found'],status:400);
    }

}
