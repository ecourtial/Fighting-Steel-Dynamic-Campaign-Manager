<?php

declare(strict_types=1);

namespace App\Tests\ScenarioGenerator\Engine;

use App\Core\Tas\Scenario\Scenario;
use App\ScenarioGenerator\Engine\DictionaryExtractor;
use App\ScenarioGenerator\Engine\ShipQuantity;
use App\ScenarioGenerator\Engine\ShipsSelector;
use PHPUnit\Framework\TestCase;

class ShipsSelectorTest extends TestCase
{
//    public function testMixedNavies(): void
//    {
//
//    }
//
//    public function testNotMixedNavies(): void
//    {
//
//    }
//
//    public function testWithShipQtyLimitation(): void
//    {
//
//    }
//
//    public function testVariousAreaAndNavies(): void
//    {
//
//    }

    public function testSecurityForLimitedNavies(): void
    {
        $dictionaryPath = 'somewhere';
        $dictionary = $this->createMock(DictionaryExtractor::class);
        $dictionary->method('getShipDictionary')
            ->with($dictionaryPath . '/src/Data/FSP10.3_Ship_List.csv')->willReturn(
                ['IJN' => [],
                'USN' => [],
                'RN' => [],
                'KM' => [],
                    ]
            );

        $shipSelector = new ShipsSelector($dictionary, $dictionaryPath);
        $shipQty = new ShipQuantity(2, 2, 2, 2);
        static::expectExceptionMessage('AH');
        $shipSelector->getShips('Atlantic', 2, $shipQty, false);
    }

//
//    // Test the structure, no redundancy in navies and ships...
//    public function testStructureWithMixedNavies(): void
//    {
//        $dictionaryPath = 'somewhere';
//        $dictionary = $this->getDictionary($dictionaryPath);
//        $shipSelector = new ShipsSelector($dictionary, $dictionaryPath);
//        $shipQty = new ShipQuantity(2,2,2,2);
//
//        $result = $shipSelector->getShips('Atlantic', 2, $shipQty,true);
//
//        $previousSide = '';
//        $expectedAlliedNavies = ['RN', 'USN'];
//        $expectedAxisNavies = ['IJN', 'KM'];
//        $navies = [];
//        $shipsStored = [];
//
//        //dd($result);
//        foreach ($result as $side => $navy) {
//            static::assertTrue(in_array($side, Scenario::SIDES));
//
//            // Test the side
//            if ($previousSide === '') {
//                $previousSide = $side;
//            } elseif($previousSide === $side) {
//                static::fail('Duplicate side: ' . $side);
//            }
//
//            foreach ($navy as $navyName => $ships) {
//                if ($side === Scenario::ALLIED_SIDE) {
//                    static::assertTrue(in_array($navyName, $expectedAlliedNavies));
//                } else {
//                    static::assertTrue(in_array($navyName, $expectedAxisNavies));
//                }
//
//                if (in_array($navyName, $navies)) {
//                    static::fail('Duplicate navy: ' . $navyName);
//                }
//
//                $navies[] = $navyName;
//
//                foreach ($ships as $ship) {
//                    static::assertFalse(in_array($ship['name'], $shipsStored));
//                    $shipsStored[] = $ship['name'];
//                    $originalShip = $this->getDictionaryContent()[$navyName][$ship['type']][$ship['name']];
//                    static::assertEquals($originalShip['name'], $ship['name']);
//                    static::assertEquals($originalShip['class'], $ship['class']);
//                    static::assertEquals($originalShip['type'], $ship['type']);
//                }
//            }
//        }
//    }
//
//    public function testBigShipsOnlyAndNoMixedNavies(): void
//    {
//        $dictionaryPath = 'somewhere';
//        $dictionary = $this->getDictionary($dictionaryPath);
//        $shipSelector = new ShipsSelector($dictionary, $dictionaryPath);
//        $shipQty = new ShipQuantity(2,2,2,2);
//
//        $result = $shipSelector->getShips('Atlantic', 2, $shipQty,false);
//
//        foreach ($result as $side => $navy) {
//            foreach ($navy as $ships) {
//                foreach ($ships as $ship) {
//                    static::assertTrue(in_array($ship['type'], ShipsSelector::BIG_SHIPS_TYPES));
//                }
//            }
//        }
//    }
//
//    private function getDictionary(string $dictionaryPath): DictionaryExtractor
//    {
//        $dictionary = $this->createMock(DictionaryExtractor::class);
//        $dictionary->method('getShipDictionary')
//            ->with($dictionaryPath . '/src/Data/FSP10.3_Ship_List.csv')->willReturn($this->getDictionaryContent());
//
//        return $dictionary;
//    }

    private function getDictionaryContent(): array
    {
        return [
            'IJN' => [
                'BB' => [
                    'Ise' => [
                        'name' => 'Ise',
                        'class' => 'Ise',
                        'type' => 'BB',
                    ],
                    'Yamato' => [
                        'name' => 'Yamato',
                        'class' => 'Yamato',
                        'type' => 'BB',
                    ],
                ],
                'CL' => [
                    'Haguro' => [
                        'name' => 'Haguro',
                        'class' => 'Haguro',
                        'type' => 'CL',
                    ],
                    'Takatoukite' => [
                        'name' => 'Takatoukite',
                        'class' => 'Takatoukite',
                        'type' => 'CL',
                    ],
                ],
                'DD' => [
                    'Mikado1' => [
                        'name' => 'Mikado1',
                        'class' => 'Mikado1',
                        'type' => 'DD',
                    ],
                    'Mikado2' => [
                        'name' => 'Mikado2',
                        'class' => 'Mikado2',
                        'type' => 'DD',
                    ],
                ],
            ],
            'KM' => [
                'BB' => [
                    'Bismarck' => [
                        'name' => 'Bismarck',
                        'class' => 'Bismarck',
                        'type' => 'BB',
                    ],
                    'Gneisenau' => [
                        'name' => 'Scharnhorst',
                        'class' => 'Scharnhorst',
                        'type' => 'BB',
                    ],
                ],
                'CL' => [
                    'Koln' => [
                        'name' => 'Koln',
                        'class' => 'Koln',
                        'type' => 'CL',
                    ],
                    'Leipzig' => [
                        'name' => 'Leipzig',
                        'class' => 'Leipzig',
                        'type' => 'CL',
                    ],
                ],
                'DD' => [
                    'KM1' => [
                        'name' => 'KM1',
                        'class' => 'KM1',
                        'type' => 'DD',
                    ],
                    'KM2' => [
                        'name' => 'KM2',
                        'class' => 'KM2',
                        'type' => 'DD',
                    ],
                ],
            ],
            'USN' => [
                'BB' => [
                    'Arizona' => [
                        'name' => 'Arizona',
                        'class' => 'Arizona',
                        'type' => 'BB',
                    ],
                    'Missouri' => [
                        'name' => 'Missouri',
                        'class' => 'Missouri',
                        'type' => 'BB',
                    ],
                ],
                'CL' => [
                    'USCL1' => [
                        'name' => 'USCL1',
                        'class' => 'USCL1',
                        'type' => 'CL',
                    ],
                    'USCL2' => [
                        'name' => 'USCL2',
                        'class' => 'USCL2',
                        'type' => 'CL',
                    ],
                ],
                'DD' => [
                    'USN1' => [
                        'name' => 'USN1',
                        'class' => 'USN1',
                        'type' => 'DD',
                    ],
                    'USN2' => [
                        'name' => 'USN2',
                        'class' => 'USN2',
                        'type' => 'DD',
                    ],
                ],
            ],
            'RN' => [
                'BB' => [
                    'Hood' => [
                        'name' => 'Hood',
                        'class' => 'Hood',
                        'type' => 'BC',
                    ],
                    'Nelson' => [
                        'name' => 'Nelson',
                        'class' => 'Nelson',
                        'type' => 'BB',
                    ],
                ],
                'CL' => [
                    'RNCL1' => [
                        'name' => 'RNCL1',
                        'class' => 'RNCL1',
                        'type' => 'CL',
                    ],
                    'RNCL2' => [
                        'name' => 'RNCL2',
                        'class' => 'RNCL2',
                        'type' => 'CL',
                    ],
                ],
                'DD' => [
                    'RNDD1' => [
                        'name' => 'RNDD1',
                        'class' => 'RNDD1',
                        'type' => 'DD',
                    ],
                    'RNDD2' => [
                        'name' => 'RNDD2',
                        'class' => 'RNDD2',
                        'type' => 'DD',
                    ],
                ],
            ],
        ];
    }
}
