<?php

namespace App\Tests\Service;

use App\Entity\Period;
use App\Repository\PeriodRepository;
use App\Service\ReservationService;
use PHPUnit\Framework\TestCase;

class ReservationServiceTest extends TestCase
{
    private \PHPUnit\Framework\MockObject\MockObject|PeriodRepository $mockRepo;

    protected function setUp(): void
    {
        // Création du mock une seule fois pour tous les tests
        $this->mockRepo = $this->createMock(PeriodRepository::class);
        $this->mockRepo->method('findOverlappingPeriods')->willReturn([]);
    }

    public function testCalculateSupplement(): void
    {
        // Nouveau mock spécifique à ce test
        $mockRepo = $this->createMock(PeriodRepository::class);
    
        $period = new Period();
        $period->setStartDate(new \DateTime('2024-08-01'));
        $period->setEndDate(new \DateTime('2024-08-10'));
        $period->setSupplement(20.00);
    
        $mockRepo->method('findOverlappingPeriods')->willReturn([$period]);
    
        $service = new ReservationService($mockRepo);
    
        $startDate = new \DateTime('2024-08-05');
        $endDate = new \DateTime('2024-08-08');
    
        $supplement = $service->calculateSupplement($startDate, $endDate);
    
        $this->assertEquals(60.00, $supplement); // 3 nuits * 20€
    }
    

    public function testCalculateTva(): void
    {
        $service = new ReservationService($this->mockRepo);
        $this->assertEquals(20.0, $service->calculateTva(100));
    }

    public function testCalculateTax(): void
    {
        $service = new ReservationService($this->mockRepo);
        $tax = $service->calculateTax(200, 2, 2, 0); // 4 personnes, 2 nuits
        $this->assertGreaterThan(0, $tax);
    }

    public function testCalculateTaxWithZeroOccupants(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Le nombre total d'occupants doit être supérieur à 0 pour calculer la taxe.");

        $service = new ReservationService($this->mockRepo);
        $service->calculateTax(100, 2, 0, 0); // 0 occupant
    }

    public function testCalculateBasePrice(): void
    {
        $service = new ReservationService($this->mockRepo);
        $price = $service->calculateBasePrice(3, 100, 50, 20);
        $this->assertEquals(370.0, $price); // (3*100) + 50 + 20 = 370
    }
}
