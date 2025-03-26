<?php

namespace App\Tests\Entity;

use DateTimeImmutable;
use App\Entity\Reservation;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ReservationTest extends KernelTestCase
{
    public function testEntityIsValid(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $reservation = new Reservation();
        $reservation->setArrivalDate(new DateTimeImmutable())
            ->setDepartureDate(new DateTimeImmutable())
            ->setLastName('Nom');
        

        $errors = $container->get('validator')->validate($reservation);

        $this->assertCount(0, $errors);
    }

        public function testIsConfirmDefaultValue(): void
    {
        $reservation = new Reservation();

        $this->assertEquals(['status' => 'en attente'], $reservation->getIsConfirm());
    }
}
