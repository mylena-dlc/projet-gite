<?php

namespace App\Entity;

use App\Repository\ReservationExtraRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationExtraRepository::class)]
class ReservationExtra
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\ManyToOne(inversedBy: 'reservationExtras')]
    private ?Extra $extra = null;

    #[ORM\ManyToOne(inversedBy: 'reservationExtras')]
    private ?Reservation $Reservation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getExtra(): ?Extra
    {
        return $this->extra;
    }

    public function setExtra(?Extra $extra): static
    {
        $this->extra = $extra;

        return $this;
    }

    public function getReservation(): ?Reservation
    {
        return $this->Reservation;
    }

    public function setReservation(?Reservation $Reservation): static
    {
        $this->Reservation = $Reservation;

        return $this;
    }
}
