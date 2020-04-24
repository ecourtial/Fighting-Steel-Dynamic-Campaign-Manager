<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       22/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

namespace Tests\Core\Tas\Savegame\Fleet;

use App\Core\File\IniReader;
use App\Core\File\TextFileReader;
use App\Core\Tas\Savegame\Fleet\FleetExtractor;
use PHPUnit\Framework\TestCase;

class FleetExtractorTest extends TestCase
{
    private static FleetExtractor $fleetExtractor;

    public static function setUpBeforeClass(): void
    {
        static::$fleetExtractor = new FleetExtractor(new IniReader(new TextFileReader()));
    }

    public function testExtractShipsInPortNormal(): void
    {
        $expected = [
            'Valmy' => 'Bizerte',
            'Verdun' => 'Bizerte',
            'Guepard' => 'Bizerte',
            'Vauban' => 'Bizerte',
            'Lion' => 'Bizerte',
            'Epervier' => 'Bizerte',
            'La Marseillaise' => 'Bizerte',
            'Jean De Vienne' => 'Bizerte',
            'Emile Bertin' => 'Toulon',
            'La Poursuivante' => 'Hyeres',
            'Bayonnaise' => 'Hyeres',
            'Baliste' => 'Hyeres',
        ];

        $path = $_ENV['TAS_LOCATION'] . DIRECTORY_SEPARATOR . 'Save1';

        static::assertEquals($expected, static::$fleetExtractor->getShipsInPort($path, 'Allied'));

        $expected = [
            'Provence' => 'Tarento',
            'Ocean' => 'Napoli',
            'Condorcet' => 'Napoli',
            'Mogador' => 'Napoli',
        ];
        static::assertEquals($expected, static::$fleetExtractor->getShipsInPort($path, 'Axis'));
    }

    public function testExtractFleetsNormal(): void
    {
        $path = $_ENV['TAS_LOCATION'] . DIRECTORY_SEPARATOR . 'Save1';

        $fleets = static::$fleetExtractor->extractFleets($path, 'Axis');
        static::assertEquals(2, count($fleets));

        $fleet = $fleets['TF0'];
        static::assertEquals('TF0', $fleet->getId());
        static::assertEquals('Summer Cruise (Lutjens)', $fleet->getName());
        static::assertEquals(100, $fleet->getProb());
        static::assertEquals('385652N 0034523E', $fleet->getLl());
        static::assertEquals([
            0 => '3922N 00431E',
            1 => '4027N 00314E',
            2 => '3538N 01110W',
        ], $fleet->getWaypoints());
        static::assertEquals(
            [
                'TF0DIVISION0' => [
                    0 => 'Gneisenau',
                    1 => 'Scharnhorst',
                ],
                'TF0DIVISION1' => [
                    0 => 'Roma',
                ],
            ],
            $fleet->getDivisions()
        );

        static::assertEquals(
            [
                'Gneisenau' => 'TF0DIVISION0',
                'Scharnhorst' => 'TF0DIVISION0',
                'Roma' => 'TF0DIVISION1',
            ],
            $fleet->getShips()
        );

        $fleet = $fleets['TF1'];
        static::assertEquals('TF1', $fleet->getId());
        static::assertEquals('Macaroni', $fleet->getName());
        static::assertEquals(100, $fleet->getProb());
        static::assertEquals('425652N 0032723E', $fleet->getLl());
        static::assertEquals([
            0 => '3522N 00241E',
            1 => '3538N 01110W',
        ], $fleet->getWaypoints());
        static::assertEquals(['TF1DIVISION0' => [
            0 => 'Littorio',
        ]], $fleet->getDivisions());

        static::assertEquals(
            [
                'Littorio' => 'TF1DIVISION0',
            ],
            $fleet->getShips()
        );
    }
}
