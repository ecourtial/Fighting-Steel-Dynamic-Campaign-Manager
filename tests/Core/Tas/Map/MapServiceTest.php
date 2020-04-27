<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       22/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

namespace App\Tests\Core\Tas\Map;

use App\Core\Exception\InvalidInputException;
use App\Core\Tas\Map\MapService;
use PHPUnit\Framework\TestCase;

class MapServiceTest extends TestCase
{
    private MapService $service;

    public function setUp(): void
    {
        $this->service = new MapService();
    }

    /** @dataProvider calculateDistanceBetweenTwoCoordProvider */
    public function testCalculateDistanceBetweenTwoCoord(string $from, string $to, float $expected): void
    {
        static::assertEquals(
            $expected,
            $this->service->calculateDistanceBetweenTwoCoord($from, $to)
        );
    }

    public function calculateDistanceBetweenTwoCoordProvider(): array
    {
        return [
            ['4361N 00386E', '4384N 00437E', 48.3], // Montpellier to Nîmes
            ['4365N 07939W', '4549N 07358W', 503.5], // Toronto to Montreal
        ];
    }

    /** @dataProvider decToDmsProvider*/
    public function testConvertionDecToDms(string $latitudeOrLongitude, float $expected): void
    {
        static::assertEquals($expected, $this->service->convertDECtoDMS($latitudeOrLongitude));
    }

    public function decToDmsProvider(): array
    {
        /*
         * Note that the element which are on the same block are part of the same location
         * First value is the TAS input, the second one is the expected one
         */
        return [
            // Means 35°50N 001°54W
            ['3550N', 35.5],
            ['00154W', -1.54],
            //Means 36°51N 000°14E
            ['3651N', 36.51],
            ['00014E', 0.14],
            // Means 36°33N 001°18W
            ['3633N', 36.33],
            ['00118W', -1.18],
            // Means 37°09N 026°43E
            ['3709N', 37.09],
            ['02643E', 26.43],
            // Means 0538S 00345E
            ['0538S', -5.38],
            ['00345E', 3.45],
        ];
    }

    public function testConvertionDecToDmsWithError(): void
    {
        try {
            $this->service->convertDECtoDMS('AH');
            static::fail('The given location is wrong. So an exception was expected');
        } catch (InvalidInputException $exception) {
            static::assertEquals(
                "Unknown TAS location part: 'AH'",
                $exception->getMessage()
            );
        }
    }

    public function testGetFirstWaypointData(): void
    {
    }
}
