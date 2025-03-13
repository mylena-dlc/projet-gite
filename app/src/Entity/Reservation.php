<?php

namespace App\Entity;

use DateTimeZone;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ReservationRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\String\Slugger\SluggerInterface;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $reference = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $reservation_date = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $arrival_date = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $departure_date = null;

    #[ORM\Column(length: 50)]
    private ?string $last_name = null;

    #[ORM\Column(length: 50)]
    private ?string $first_name = null;

    #[ORM\Column(length: 50)]
    private ?string $address = null;

    #[ORM\Column(length: 50)]
    private ?string $cp = null;

    #[ORM\Column(length: 255)]
    private ?string $country = null;

    #[ORM\Column(length: 50)]
    private ?string $phone = null;

    #[ORM\Column]
    private ?bool $is_major = null;

    #[ORM\Column]
    private ?int $number_adult = null;

    #[ORM\Column]
    private ?int $number_kid = null;

    #[ORM\Column]
    private ?int $total_night = null;

    #[ORM\Column]
    private ?float $total_price = null;

    #[ORM\Column]
    private ?float $tourism_tax = null;

    #[ORM\Column]
    private ?float $tva = null;

    #[ORM\Column (nullable: true)]
    private ?array $is_confirm = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    private ?Gite $gite = null;

    #[ORM\ManyToOne(inversedBy: 'supplement')]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $city = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $message = null;

    #[ORM\Column]
    private ?float $supplement = null;

    #[ORM\Column]
    private ?float $cleaning_charge = null;

    #[ORM\Column]
    private ?float $price_night = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    private ?Token $token = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripe_paymentId = null;

    /**
     * @var Collection<int, Review>
     */
    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'Reservation')]
    private Collection $reviews;

    /**
     * @var Collection<int, ReservationExtra>
     */
    #[ORM\OneToMany(targetEntity: ReservationExtra::class, mappedBy: 'Reservation')]
    private Collection $reservationExtras;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $slug = null;

    public function __construct()
    {
        $this->is_confirm = ['status' => 'en attente'];
        $this->reservation_date = new \DateTime('now', new DateTimeZone('Europe/Paris'));
        $this->reviews = new ArrayCollection();
        $this->reservationExtras = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getReservationDate(): ?\DateTimeInterface
    {
        return $this->reservation_date;
    }

    #[ORM\PrePersist]
    public function setReservationDate(): void
    {
        $this->reservation_date = new \DateTime();

    }

    public function getArrivalDate(): ?\DateTimeInterface
    {
        return $this->arrival_date;
    }

    public function setArrivalDate(\DateTimeInterface $arrival_date): static
    {
        // Si l'heure n'est pas déjà définie, on la force à 16h00
    if ($arrival_date->format('H') != '16') {
        $arrival_date->setTime(16, 0);  // Fixe l'heure à 16:00
    }

        $this->arrival_date = $arrival_date;

        return $this;
    }

    public function getDepartureDate(): ?\DateTimeInterface
    {
        return $this->departure_date;
    }

    public function setDepartureDate(\DateTimeInterface $departure_date): static
    {
        // Si l'heure n'est pas déjà définie, on la force à 11h00
        if ($departure_date->format('H') != '11') {
            $departure_date->setTime(11, 0);  // Fixe l'heure à 11:00
        }
        $this->departure_date = $departure_date;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(string $last_name): static
    {
        $this->last_name = $last_name;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(string $first_name): static
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getCp(): ?string
    {
        return $this->cp;
    }

    public function setCp(string $cp): static
    {
        $this->cp = $cp;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getIsMajor(): ?bool
    {
        return $this->is_major;
    }

    public function setIsMajor(bool $is_major): static
    {
        $this->is_major = $is_major;

        return $this;
    }

    public function getNumberAdult(): ?int
    {
        return $this->number_adult;
    }

    public function setNumberAdult(int $number_adult): static
    {
        $this->number_adult = $number_adult;

        return $this;
    }

    public function getNumberKid(): ?int
    {
        return $this->number_kid;
    }

    public function setNumberKid(int $number_kid): static
    {
        $this->number_kid = $number_kid;

        return $this;
    }

    public function getTotalNight(): ?int
    {
        return $this->total_night;
    }

    public function setTotalNight(int $total_night): static
    {
        $this->total_night = $total_night;

        return $this;
    }

    public function getTotalPrice(): ?float
    {
        return $this->total_price;
    }

    public function setTotalPrice(float $total_price): static
    {
        $this->total_price = $total_price;

        return $this;
    }

    public function getTourismTax(): ?float
    {
        return $this->tourism_tax;
    }

    public function setTourismTax(float $tourism_tax): static
    {
        $this->tourism_tax = $tourism_tax;

        return $this;
    }

    public function getTva(): ?float
    {
        return $this->tva;
    }

    public function setTva(float $tva): static
    {
        $this->tva = $tva;

        return $this;
    }

    public function getIsConfirm(): ?array
    {
        return $this->is_confirm;
    }

    public function setIsConfirm(?array $is_confirm): static
    {
        $this->is_confirm = $is_confirm;

        return $this;
    }

    #[ORM\PrePersist]
    public function initializeIsConfirm(): void
    {
        if ($this->is_confirm === null) {
            $this->is_confirm = ['status' => 'en attente'];
        }
    }

    public function getGite(): ?Gite
    {
        return $this->gite;
    }

    public function setGite(?Gite $gite): static
    {
        $this->gite = $gite;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getSupplement(): ?float
    {
        return $this->supplement;
    }

    public function setSupplement(float $supplement): static
    {
        $this->supplement = $supplement;

        return $this;
    }

    public function getCleaningCharge(): ?float
    {
        return $this->cleaning_charge;
    }

    public function setCleaningCharge(float $cleaning_charge): static
    {
        $this->cleaning_charge = $cleaning_charge;

        return $this;
    }

    public function getPriceNight(): ?float
    {
        return $this->price_night;
    }

    public function setPriceNight(float $price_night): static
    {
        $this->price_night = $price_night;

        return $this;
    }

    public function getToken(): ?Token
    {
        return $this->token;
    }

    public function setToken(?Token $token): static
    {
        $this->token = $token;

        return $this;
    }


    public function getStripePaymentId(): ?string
    {
        return $this->stripe_paymentId;
    }

    public function setStripePaymentId(?string $stripe_paymentId): static
    {
        $this->stripe_paymentId = $stripe_paymentId;

        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setReservation($this);
        }

        return $this;
    }

    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getReservation() === $this) {
                $review->setReservation(null);
            }
        }

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
            $reservationExtra->setReservation($this);
        }

        return $this;
    }

    public function removeReservationExtra(ReservationExtra $reservationExtra): static
    {
        if ($this->reservationExtras->removeElement($reservationExtra)) {
            // set the owning side to null (unless already changed)
            if ($reservationExtra->getReservation() === $this) {
                $reservationExtra->setReservation(null);
            }
        }

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

}
