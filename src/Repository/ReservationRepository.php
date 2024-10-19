<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DateTime;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    // redefined find all order by date ASC
    public function findAllByDate(DateTime $dateStart,DateTime $dateEnd):array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.date >= :dateStart')->setParameter('dateStart', $dateStart)
           // ->andWhere('r.date <= :dateEnd')
            ->orderBy('r.date', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    // format a new array where reservations group by date
   public function groupByDay(array $reservations): array
   {
       $groupedReservations = [];
       foreach ($reservations as $reservation) {
           $groupedReservations[$reservation->getDate()->format('Y-m-d')] = [
               'foodtruck' => $reservation->getFoodtruck()->getId(),
               'placement' => $reservation->getPlacement()->getDescription()];
       }
       return $groupedReservations;
   }
}
