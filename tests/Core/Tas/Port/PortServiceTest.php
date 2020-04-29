<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       22/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

namespace App\Tests\Core\Tas\Port;

use App\Core\Exception\InvalidInputException;
use App\Core\File\IniReader;
use App\Core\File\TextFileReader;
use App\Core\Tas\Exception\MissingPortException;
use App\Core\Tas\Port\PortService;
use PHPUnit\Framework\TestCase;

class PortServiceTest extends TestCase
{
    public function testBasics(): void
    {
        $service = new PortService(
            new IniReader(new TextFileReader()),
            $_ENV['TAS_LOCATION'] . DIRECTORY_SEPARATOR . 'Good Scenario'
        );

        static::assertEquals('AHAH', $service->getPortFirstWaypoint('Napoli'));
        static::assertEquals('OHOH', $service->getPortFirstWaypoint('Tarento'));

        try {
            $service->getPortFirstWaypoint('Mangalay');
            static::fail('Since the port is not in the file, an exceptionw as expected');
        } catch (MissingPortException $exception) {
            static::assertEquals(
                "Port 'Mangalay' not found",
                $exception->getMessage()
            );
        }

        try {
            $service->getPortData('Napoli', 'EH');
            static::fail('Since the key is not in the file, an exceptionw as expected');
        } catch (InvalidInputException $exception) {
            static::assertEquals(
                "Unknown key 'EH'",
                $exception->getMessage()
            );
        }
    }

    public function testBadVersion(): void
    {
        $service = new PortService(
            new IniReader(new TextFileReader()),
            $_ENV['TAS_LOCATION'] . DIRECTORY_SEPARATOR . 'Bad GoebenReminiscence'
        );

        try {
            $service->getPortData('Vialas', 'FWP');
            static::fail('Since the version of the file is incorrect, an exception was expected');
        } catch (InvalidInputException $exception) {
            static::assertEquals(
                "Invalid ports file version: '2.A'",
                $exception->getMessage()
            );
        }
    }
}
