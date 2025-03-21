<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait TestTrait 
{
    private function createClientAndFollowRedirects(): KernelBrowser
    {
        $client = static::createClient();

        $client->followRedirects();

        return $client;
    }

    private function truncateTableBeforeTest(string $table): void
    {
        $kernel = self::bootKernel();

        $entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $connection = $entityManager->getConnection();
        $connection->executeQuery("TRUNCATE TABLE `{$table}`");

        $connection->close();
    }
} 