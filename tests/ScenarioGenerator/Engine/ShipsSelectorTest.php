<?php

declare(strict_types=1);

namespace App\Tests\ScenarioGenerator\Engine;

use App\ScenarioGenerator\Engine\Ships\ShipProvider;
use App\ScenarioGenerator\Engine\Ships\ShipQuantity;
use App\ScenarioGenerator\Engine\ShipsSelector;
use App\ScenarioGenerator\Engine\SideGenerator;
use PHPUnit\Framework\TestCase;

class ShipsSelectorTest extends TestCase
{
    private ShipProvider $shipProvider;
    private SideGenerator $sideGenerator;

    protected function setUp(): void
    {
        $this->shipProvider = static::createMock(ShipProvider::class);
        $this->sideGenerator = static::createMock(SideGenerator::class);
    }

    /** @dataProvider mixedNavyProvider */
    public function testMixedNavies(bool $mixedNavies): void
    {
        $selector = new ShipsSelector($this->shipProvider, $this->sideGenerator);
        $shipQty = new ShipQuantity(5, 6, 2, 3);

        $this->sideGenerator->expects(static::exactly(2))->method('getSides')
            ->withConsecutive(
                ['Atlantic', 0, 'Allied', $mixedNavies],
                ['Atlantic', 0, 'Axis', $mixedNavies]
            )
            ->willReturn(['RN'], ['KM']);

        $this->shipProvider
            ->expects(static::exactly(5))
            ->method('getBigShip')
            ->withConsecutive(['RN'], ['RN'], ['KM'], ['KM'], ['KM'])
            ->willReturnOnConsecutiveCalls(['Name' => 'Nelson'], ['Name' => 'RNCL1'], ['Name' => 'Bismarck'], ['Name' => 'Tirpitz'], ['Name' => 'Scheer']);

        $this->shipProvider
            ->expects(static::exactly(6))
            ->method('getDestroyer')
            ->withConsecutive(['RN'], ['RN'], ['RN'], ['KM'], ['KM'], ['KM'])
            ->willReturnOnConsecutiveCalls(['Name' => 'DE1'], ['Name' => 'DE2'], ['Name' => 'DE3'], ['Name' => 'DD1'], ['Name' => 'DD2'], ['Name' => 'DD3']);

        $ships = $selector->getShips('Atlantic', 0, $shipQty, $mixedNavies);

        static::assertEquals(
            [
                'Allied' => [
                    'RN' => [
                        ['Name' => 'Nelson'],
                        ['Name' => 'RNCL1'],
                        ['Name' => 'DE1'],
                        ['Name' => 'DE2'],
                        ['Name' => 'DE3'],
                    ],
                ],
                'Axis' => [
                    'KM' => [
                        ['Name' => 'Bismarck'],
                        ['Name' => 'Tirpitz'],
                        ['Name' => 'Scheer'],
                        ['Name' => 'DD1'],
                        ['Name' => 'DD2'],
                        ['Name' => 'DD3'],
                    ],
                ],
            ],
            $ships
        );
    }

    public function mixedNavyProvider(): array
    {
        return [[true], [false]];
    }
}
