<?php

declare(strict_types=1);

namespace App\Tests\ScenarioGenerator\Engine\Ships;

use App\ScenarioGenerator\Engine\Ships\DictionaryExtractor;
use App\ScenarioGenerator\Engine\Ships\ShipProvider;
use PHPUnit\Framework\TestCase;

class ShipProviderTest extends TestCase
{
    public function testBehavior(): void
    {
        $extractor = static::createMock(DictionaryExtractor::class);
        $provider = new ShipProvider($extractor, '.');

        $extractor->expects(static::once())->method('getShipDictionary')->willReturn($this->getDictionaryContent());

        // Get one Big Ship
        $ship = $provider->getBigShip('RN');
        $shipName = $ship['name'];
        static::assertTrue(in_array($shipName, ['Nelson', 'Rodney'], true));

        // Get the other one
        $ship = $provider->getBigShip('RN');
        static::assertTrue(in_array($shipName, ['Nelson', 'Rodney'], true));
        static::assertNotEquals($shipName, $ship['name']);

        // Get a small ship
        $ship = $provider->getDestroyer('RN');
        static::assertTrue(in_array($ship['name'], ['Tribal', 'Tribal2', 'Tribal3'], true));

        // No more big ship available
        static::expectExceptionMessage("Impossible to find any big ship for the navy 'RN'");
        $provider->getBigShip('RN');
    }

    private function getDictionaryContent(): array
    {
        return [
            'RN' => [
                'BB' => [
                    'Nelson' => [
                        'name' => 'Nelson',
                        'class' => 'Nelson',
                        'type' => 'BB',
                    ],
                    'Rodney' => [
                        'name' => 'Rodney',
                        'class' => 'Nelson',
                        'type' => 'BB',
                    ],
                ],
                'DD' => [
                    'Tribal' => [
                        'name' => 'Tribal',
                        'class' => 'Whatever',
                        'type' => 'DD',
                    ],
                    'Tribal2' => [
                        'name' => 'Tribal2',
                        'class' => 'Whatever',
                        'type' => 'DD',
                    ],
                    'Tribal3' => [
                        'name' => 'Tribal3',
                        'class' => 'Whatever',
                        'type' => 'DD',
                    ],
                ],
            ],
            'KM' => [
                'Bismarck' => [
                    'name' => 'Bismarck',
                    'class' => 'Bismarck',
                    'type' => 'BB',
                ],
            ],
        ];
    }
}
