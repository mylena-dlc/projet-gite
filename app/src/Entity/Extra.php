<?php

namespace App\Entity;

use App\Repository\ExtraRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExtraRepository::class)]
class Extra
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?float $price = null;

    /**
     * @var Collection<int, ReservationExtra>
     */
    #[ORM\OneToMany(targetEntity: ReservationExtra::class, mappedBy: 'extra')]
    private Collection $reservationExtras;

    public function __construct()
    {
        $this->reservationExtras = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return Collection<int, ReservationExtra>
     */
    public function getReservationExtras(): Collection
    {
        return $this->reservationExtras;
    }

    public function addReservationExtra(ReservationExtra $reservationExtra): static
    {
        if (!$this->reservationExtras->contains($reservationExtra)) {
            $this->reservationExtras->add($reservationExtra);
            $reservationExtra->setExtra($this);
        }

        return $this;
    }

    public function removeReservationExtra(ReservationExtra $reservationExtra): static
    {
        if ($this->reservationExtras->removeElement($reservationExtra)) {
            // set the owning side to null (unless already changed)
            if ($reservationExtra->getExtra() === $this) {
                $reservationExtra->setExtra(null);
            }
        }

        return $this;
    }
}
