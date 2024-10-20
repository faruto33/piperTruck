<?php

namespace App\Repository;

use App\Entity\Foodtruck;
use App\Entity\Placement;
use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DateTime;
use Symfony\Component\Yaml\Yaml;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    private mixed $config;

    public function __construct(ManagerRegistry $registry)
    {
        // load configuration from yaml file
        $this->config = Yaml::parseFile(__DIR__.'/../../config/reservations.yaml');
        parent::__construct($registry, Reservation::class);
    }

    // redefined find all order by date ASC
    public function findAllByDate(DateTime $dateStart,DateTime $dateEnd):array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.date >= :dateStart')->setParameter('dateStart', $dateStart->format("Y-m-d"))
            ->andWhere('r.date <= :dateEnd')->setParameter('dateEnd', $dateEnd->format("Y-m-d"))
            ->orderBy('r.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

   // format a new array where reservations group by date
   public function groupByDay(array $reservations): array
   {
       $groupedReservations = [];
       foreach ($reservations as $reservation) {
           $groupedReservations[$reservation->getDate()->format('Y-m-d')][] = [
               'foodtruck' => $reservation->getFoodtruck()->getId(),
               'placement' => [
                   'id'=> $reservation->getPlacement()->getId(),
                   'description' => $reservation->getPlacement()->getDescription()]
           ];
       }
       return $groupedReservations;
   }

    // check if a registration already been passed and max reservations rules
    public function checkReservation(array $values): array
    {
        // check if reservation already exists
        $res = $this->createQueryBuilder('r')
            ->andWhere('r.date = :date')->setParameter('date', $values['date']->format("Y-m-d"))
            ->andWhere('r.foodtruck = :foodtruck')->setParameter('foodtruck', $values['foodtruck']->getId())
            ->andWhere('r.placement = :placement')->setParameter('placement', $values['placement']->getId())
            ->getQuery()
            ->getResult();
        return $res;
    }

    // quota exceeded
    public function quota(array $values): array
    {
        // get all reservations this specific day
        $res = $this->createQueryBuilder('r')
            ->select('count(r.id)')
            ->andWhere('r.date = :date')->setParameter('date', $values['date']->format("Y-m-d"))
            ->getQuery()
            ->getSingleScalarResult();
        // get the max allowed reservation per this specific day and test if we exceeded
        return $res<$this->config['reservation.max_per_day'][$values['date']->format("l")]?[]:[$res];
    }

}
