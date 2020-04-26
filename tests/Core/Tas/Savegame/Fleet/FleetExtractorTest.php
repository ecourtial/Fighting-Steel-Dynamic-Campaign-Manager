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
            'Valmy' => [
                'TYPE' => 'DD',
                'MAXSPEED' => '40',
                'ENDURANCE' => '160',
                'CURRENTENDURANCE' => '196',
                'RECONRANGE' => '0',
                'LOCATION' => 'Bizerte',
            ],
            'Verdun' => [
                'TYPE' => 'DD',
                'MAXSPEED' => '40',
                'ENDURANCE' => '160',
                'CURRENTENDURANCE' => '196',
                'RECONRANGE' => '0',
                'LOCATION' => 'Bizerte',
            ],
            'Guepard' => [
                'TYPE' => 'DD',
                'MAXSPEED' => '40',
                'ENDURANCE' => '160',
                'CURRENTENDURANCE' => '196',
                'RECONRANGE' => '0',
                'LOCATION' => 'Bizerte',
            ],
            'Vauban' => [
                'TYPE' => 'DD',
                'MAXSPEED' => '40',
                'ENDURANCE' => '160',
                'CURRENTENDURANCE' => '196',
                'RECONRANGE' => '0',
                'LOCATION' => 'Bizerte',
            ],
            'Lion' => [
                'TYPE' => 'DD',
                'MAXSPEED' => '40',
                'ENDURANCE' => '160',
                'CURRENTENDURANCE' => '196',
                'RECONRANGE' => '0',
                'LOCATION' => 'Bizerte',
            ],
            'Epervier' => [
                'TYPE' => 'DD',
                'MAXSPEED' => '40',
                'ENDURANCE' => '160',
                'CURRENTENDURANCE' => '196',
                'RECONRANGE' => '0',
                'LOCATION' => 'Bizerte',
            ],
            'La Marseillaise' => [
                'TYPE' => 'CL',
                'MAXSPEED' => '32',
                'ENDURANCE' => '250',
                'CURRENTENDURANCE' => '196',
                'RECONRANGE' => '0',
                'LOCATION' => 'Bizerte',
            ],
            'Jean De Vienne' => [
                'TYPE' => 'CL',
                'MAXSPEED' => '32',
                'ENDURANCE' => '250',
                'CURRENTENDURANCE' => '196',
                'RECONRANGE' => '0',
                'LOCATION' => 'Bizerte',
            ],
            'Emile Bertin' => [
                'TYPE' => 'CL',
                'MAXSPEED' => '32',
                'ENDURANCE' => '250',
                'CURRENTENDURANCE' => '196',
                'RECONRANGE' => '0',
                'LOCATION' => 'Toulon',
            ],
            'La Poursuivante' => [
                'TYPE' => 'DD',
                'MAXSPEED' => '34',
                'ENDURANCE' => '154',
                'CURRENTENDURANCE' => '195',
                'RECONRANGE' => '0',
                'LOCATION' => 'Hyeres',
            ],
            'Bayonnaise' => [
                'TYPE' => 'DD',
                'MAXSPEED' => '34',
                'ENDURANCE' => '154',
                'CURRENTENDURANCE' => '195',
                'RECONRANGE' => '0',
                'LOCATION' => 'Hyeres',
            ],
            'Baliste' => [
                'TYPE' => 'DD',
                'MAXSPEED' => '34',
                'ENDURANCE' => '154',
                'CURRENTENDURANCE' => '195',
                'RECONRANGE' => '0',
                'LOCATION' => 'Hyeres',
            ],
        ];

        $path = $_ENV['TAS_LOCATION'] . DIRECTORY_SEPARATOR . 'Save1';

        static::assertEquals($expected, static::$fleetExtractor->getShipsInPort($path, 'Allied'));

        $expected = [
            'Provence' => [
                'TYPE' => 'BB',
                'MAXSPEED' => '20',
                'ENDURANCE' => '200',
                'CURRENTENDURANCE' => '197',
                'RECONRANGE' => '0',
                'LOCATION' => 'Tarento',
            ],
            'Ocean' => [
                'TYPE' => 'BB',
                'MAXSPEED' => '20',
                'ENDURANCE' => '200',
                'CURRENTENDURANCE' => '197',
                'RECONRANGE' => '0',
                'LOCATION' => 'Napoli',
            ],
            'Condorcet' => [
                'TYPE' => 'BB',
                'MAXSPEED' => '20',
                'ENDURANCE' => '200',
                'CURRENTENDURANCE' => '197',
                'RECONRANGE' => '0',
                'LOCATION' => 'Napoli',
            ],
            'Mogador' => [
                'TYPE' => 'DD',
                'MAXSPEED' => '20',
                'ENDURANCE' => '200',
                'CURRENTENDURANCE' => '197',
                'RECONRANGE' => '0',
                'LOCATION' => 'Napoli',
            ],
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
        static::assertEquals(21, $fleet->getSpeed());
        static::assertEquals('Breakthrough', $fleet->getMission());
        static::assertEquals([
            0 => '3922N 00431E',
            1 => '4027N 00314E',
            2 => '3538N 01110W',
        ], $fleet->getWaypoints());
        static::assertEquals(
            [
                'TF0DIVISION0' => [
                    'Gneisenau' => [
                        'TYPE' => 'BC',
                        'MAXSPEED' => '21',
                        'ENDURANCE' => '295',
                        'CURRENTENDURANCE' => '286',
                        'RECONRANGE' => '100',
                    ],
                    'Scharnhorst' => [
                        'TYPE' => 'BC',
                        'MAXSPEED' => '32',
                        'ENDURANCE' => '295',
                        'CURRENTENDURANCE' => '391',
                        'RECONRANGE' => '100',
                    ]
                ],
                'TF0DIVISION1' => [
                    'Roma' => [
                        'TYPE' => 'BB',
                        'MAXSPEED' => '21',
                        'ENDURANCE' => '250',
                        'CURRENTENDURANCE' => '220',
                        'RECONRANGE' => '100',
                    ]
                ],
            ],
            $fleet->getDivisions()
        );

        static::assertEquals(1, $fleet->getLastDivisionCount());

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
            'Littorio' => [
                'TYPE' => 'BB',
                'MAXSPEED' => '25',
                'ENDURANCE' => '250',
                'CURRENTENDURANCE' => '210',
                'RECONRANGE' => '100',
            ]
        ]], $fleet->getDivisions());

        static::assertEquals(
            [
                'Littorio' => 'TF1DIVISION0',
            ],
            $fleet->getShips()
        );
        static::assertEquals(0, $fleet->getLastDivisionCount());
    }
}
