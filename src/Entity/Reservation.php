<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['reservation.add'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Groups(['reservation.add','reservation.delete'])]
    private ?\DateTimeImmutable $date = null;

    #[ORM\ManyToOne(inversedBy: 'reservations', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['reservation.add','reservation.delete'])]
    private ?Foodtruck $foodtruck = null;

    #[ORM\ManyToOne(inversedBy: 'reservations', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['reservation.add','reservation.delete'])]
    private ?Placement $placement = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getFoodtruck(): ?Foodtruck
    {
        return $this->foodtruck;
    }

    public function setFoodtruck(?Foodtruck $foodtruck): static
    {
        $this->foodtruck = $foodtruck;

        return $this;
    }

    public function getPlacement(): ?Placement
    {
        return $this->placement;
    }

    public function setPlacement(?Placement $placement): static
    {
        $this->placement = $placement;

        return $this;
    }

}
