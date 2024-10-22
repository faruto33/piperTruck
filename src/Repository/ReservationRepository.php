<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DateTime;
use phpDocumentor\Reflection\Location;

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
            ->andWhere('r.date >= :dateStart')->setParameter('dateStart', $dateStart->format("Y-m-d"))
            ->andWhere('r.date <= :dateEnd')->setParameter('dateEnd', $dateEnd->format("Y-m-d"))
            ->orderBy('r.date,r.placement', 'ASC')
            ->getQuery()
            ->getResult();
    }

   // format a new array where reservations by date
   public function groupByDay(array $reservations,int $countplace): array
   {
       $groupedReservations = [];
       foreach ($reservations as $reservation)
       {
           if(!isset($groupedReservations[$reservation->getDate()->format('Y-m-d')]))
               $groupedReservations[$reservation->getDate()->format('Y-m-d')]=array_fill(1,$countplace,'');
           $groupedReservations[$reservation->getDate()->format('Y-m-d')][$reservation->getPlacement()->getId()] = $reservation->getFoodtruck()->getId();
       }
       return $groupedReservations;
   }

}
