<?php

namespace App\Service;

use App\Entity\Reservation;
use App\Repository\ReservationRepository;

// define service to validate reservation quota
Class Quota{

    ## quotas and entity manager auto wiring
    public function __construct(private readonly ReservationRepository $repository,private readonly array $quotas){}

    // Validate the reservation quota
    public function validate(Reservation $reservation): bool
    {
        // count reservations this specific day
        $res = $this->repository->createQueryBuilder('r')
            ->select('count(r.id)')
            ->andWhere('r.date = :date')->setParameter('date', $reservation->getDate()->format("Y-m-d"))
            ->getQuery()
            ->getSingleScalarResult();
        // test if reservation respect the daily quota
        return $res<=$this->quotas[$reservation->getDate()->format("l")];
    }

    // return number of available placement
    public function countplace(): int
    {
        return $this->quotas['countplace'];
    }

}