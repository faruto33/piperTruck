<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\Quota;
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
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ReservationController extends AbstractController
{
    #[Route('/',name: 'reservation.get_all', methods: ['GET'])]
    public function getAllReservations(Request $request,
                                       ReservationRepository $reservationRepository,
                                       ValidatorInterface $validator,
                                       Quota $quota): JsonResponse
    {
        // monday of this week by default
        $dateStart = $request->get('dateStart', default: date("Y-m-d", strtotime("monday this week")));
        $dateEnd = $request->get('dateEnd', default: date("Y-m-d", strtotime("+1 week")));

        // init messages
        $messages=[];
        //check if date range is ok
        $errors = $validator->validate([$dateEnd,$dateStart], [new Assert\All([new Assert\Date()])]);
        if (count($errors) > 0) foreach($errors as $error)$messages[]=$error->getInvalidValue().' is not a valid date';

        // If errors => error 400
        if($messages)return $this->json($messages,status:400);

        // get all reservations by date
        $reservations = $reservationRepository->findAllByDate(
            DateTime::createFromFormat('Y-m-d', $dateStart),
            DateTime::createFromFormat('Y-m-d', $dateEnd));

        // format reservations per day and return result
        return $this->json($reservationRepository->groupByDay($reservations,$quota->countplace()));
    }

    #[Route('/add', name: 'reservation.add', methods: ['POST'])]
    public function addReservation(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        Quota $quota): JsonResponse
    {
        // extract json content
        $toadd = json_decode($request->getContent(), true);

        // find the related foodtruck
        $foodtruck = $entityManager->getRepository(Foodtruck::class)->find($toadd['foodtruck']['id']);
        // find the related placement
        $placement = $entityManager->getRepository(Placement::class)->find($toadd['placement']['id']);

        // init error message
        $messages=[];
        // validate the date
        $errors = $validator->validate($toadd['date'], [new Assert\Date()]);
        if (count($errors) > 0)$messages[]='The date is not valid';

        // set a new reservation
        $reservation = (new Reservation())
            ->setDate(DateTimeImmutable::createFromFormat('Y-m-d', $toadd['date']))
            ->setFoodtruck($foodtruck)
            ->setPlacement($placement);

        // validate the reservation
        $errors = $validator->validate($reservation,groups: ['reservation.add']);
        if (count($errors) > 0) foreach($errors as $error)$messages[]=$error->getMessage();

        // validate the quota
        if (!$quota->validate($reservation))$messages[]='Reservation quota excedeed';

        // if errors messages display them and return
        if(count($messages))return $this->json($messages,400);

        // persist the reservation
        $entityManager->persist($reservation);
        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();
        // return last added reservation
        return $this->json($reservation,200,[],['groups'=>['reservation.add']]);
    }

    #[Route('/delete/{id}', name: 'delete', requirements: ['placement'=>'\d+'],methods:['DELETE'])]
    public function delete(ReservationRepository $reservationRepository,int $id): JsonResponse
    {
        // if the reservation exists we delete it
        if($reservation = $reservationRepository->find($id))
        {
            // delete a product
            $reservationRepository->remove($reservation);
            $reservationRepository->flush();
            return $this->json($reservation,200,[],['groups'=>['reservation.delete'],]);
        }
        // otherwise return unfound error
        return $this->json(['Reservation not found'],status:400);
    }

}
