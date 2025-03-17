<?php

namespace App\Service;

use Exception;
use App\Repository\PeriodRepository;
class ReservationService
{
    private PeriodRepository $periodRepository;

    public function __construct(
        PeriodRepository $periodRepository,
    ) {
        $this->periodRepository = $periodRepository;
    }


    /**
    * Calcule les détails de la réservation
    */
    public function calculateReservationDetails(array $reservationDetails, int $nightPrice, int $cleaningCharge): array
    {
        $startDate = \DateTime::createFromFormat('d/m/Y', $reservationDetails['startDate']);
        $endDate = \DateTime::createFromFormat('d/m/Y', $reservationDetails['endDate']);
        $numberAdult = $reservationDetails['numberAdult'];
        $numberKid = $reservationDetails['numberKid'];

        $totalNight = $this->calculateTotalNights($startDate, $endDate);
        $supplement = $this->calculateSupplement($startDate, $endDate);
        $price = $this->calculateBasePrice($totalNight, $nightPrice, $cleaningCharge, $supplement);
        $tax = $this->calculateTax($price, $totalNight, $numberAdult, $numberKid);
        $tva = $this->calculateTva($price);
        $totalPrice = $price + $tva + $tax;

        return [
            'totalNight' => $totalNight,
            'nightPrice' => $nightPrice,
            'cleaningCharge' => $cleaningCharge,
            'supplement' => $supplement,
            'tax' => $tax,
            'tva' => $tva,
            'totalPrice' => $totalPrice
        ];
    }

    /**
    * Calcule le nombre total de nuits
    */
    public function calculateTotalNights(\DateTime $startDate, \DateTime $endDate): int
    {
        return $startDate->diff($endDate)->days;
    }
    
    /**
    * Calcule les suppléments pour les périodes spéciales
    */
    public function calculateSupplement(\DateTime $startDate, \DateTime $endDate): float
    {
        $supplement = 0;
        $overlappingPeriods = $this->periodRepository->findOverlappingPeriods($startDate, $endDate);

        foreach ($overlappingPeriods as $period) {
            $overlapStartDate = new \DateTime(max($startDate->format('Y-m-d'), $period->getStartDate()->format('Y-m-d')));
            $overlapEndDate = new \DateTime(min($endDate->format('Y-m-d'), $period->getEndDate()->format('Y-m-d')));
            $overlapNightCount = $overlapStartDate->diff($overlapEndDate)->days;
            $supplement += $overlapNightCount * $period->getSupplement();
        }

        return (float) $supplement;
    }

    /**
    * Calcule le prix HT
    */
    public function calculateBasePrice(int $totalNight, int $nightPrice, int $cleaningCharge, float $supplement): float
    {
        return (float) (($totalNight * $nightPrice) + $cleaningCharge + $supplement);
    }

    /**
    * Calcule la taxe de séjour 
    */
    public function calculateTax(float $price, int $totalNight, int $numberAdult, int $numberKid): float
    {
        $numberOfOccupants = $numberAdult + $numberKid;

        // Vérification du nombre d'occupants
        if ($numberOfOccupants <= 0) {
            throw new Exception("Le nombre total d'occupants doit être supérieur à 0 pour calculer la taxe.");
        }

        $costPerPersonPerNight = $price / ($totalNight * $numberOfOccupants);
        $taxePerPersonPerNight = min($costPerPersonPerNight * 0.05, 3.00);
        $taxePerPersonPerNightWithDept = $taxePerPersonPerNight * 1.10;

        return round($taxePerPersonPerNightWithDept * $numberOfOccupants * $totalNight, 2);
    }

    /**
    * Calcule la TVA (20%)
    */
    public function calculateTva(float $price): float
    {
        return round($price * 0.2, 2);
    }

    /**
    * Calcule le prix total avec taxes et TVA
    */
    public function calculateTotalPrice(float $basePrice, float $tva, float $tax): float
    {
        return round($basePrice + $tva + $tax, 2);
    }
}



    /**
     * Calcule les détails de la réservation 
     */
    // public function calculateReservationDetails(array $reservationDetails, int $nightPrice, int $cleaningCharge): array
    // {
    //     $startDate = \DateTime::createFromFormat('d/m/Y', $reservationDetails['startDate']);
    //     $endDate = \DateTime::createFromFormat('d/m/Y', $reservationDetails['endDate']);
    //     $numberAdult = $reservationDetails['numberAdult'];
    //     $numberKid = $reservationDetails['numberKid'];

    //     // Calcul du nombre de nuits
    //     $totalNight = $startDate->diff($endDate)->days;

    //     // Vérifie si des suppléments s'appliquent
    //     $supplement = 0;
    //     $overlappingPeriods = $this->periodRepository->findOverlappingPeriods($startDate, $endDate);
    //     foreach ($overlappingPeriods as $period) {
    //         $overlapStartDate = max($startDate, $period->getStartDate());
    //         $overlapEndDate = min($endDate, $period->getEndDate());
    //         $overlapNightCount = $overlapStartDate->diff($overlapEndDate)->days;
    //         $supplement += $overlapNightCount * $period->getSupplement();
    //     }

    //     // Calcul du prix HT
    //     $price = ($totalNight * $nightPrice) + $cleaningCharge + $supplement;

    //     // Calcul de la taxe de séjour
    //     $numberOfOccupants = $numberAdult + $numberKid;
    //     $costPerPersonPerNight = $price / ($totalNight * $numberOfOccupants);
    //     $taxePerPersonPerNight = min($costPerPersonPerNight * 0.05, 3.00);
    //     $taxePerPersonPerNightWithDept = $taxePerPersonPerNight * 1.10;
    //     $tax = $taxePerPersonPerNightWithDept * $numberOfOccupants * $totalNight;

    //     // Ajout de la TVA (20%)
    //     $tva = $price * 0.2;
    //     $totalPrice = $price + $tva + $tax;

    //     return [
    //         'totalNight' => $totalNight,
    //         'nightPrice' => $nightPrice,
    //         'cleaningCharge' => $cleaningCharge,
    //         'supplement' => $supplement,
    //         'tax' => $tax,
    //         'tva' => $tva,
    //         'totalPrice' => $totalPrice
    //     ];
    // }


