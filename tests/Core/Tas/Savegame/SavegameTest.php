<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       22/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

namespace App\Tests\Core\Tas\Savegame;

use App\Core\Exception\InvalidInputException;
use App\Core\Tas\Savegame\Fleet\Fleet;
use App\Core\Tas\Savegame\Savegame;
use App\NameSwitcher\Exception\NoShipException;
use PHPUnit\Framework\TestCase;

class SavegameTest extends TestCase
{
    private $input = [
        'Fog' => 'Yes',
        'ScenarioName' => 'Eric',
        'SaveDate' => '19390903',
        'SaveTime' => '1205',
        'CloudCover' => '1',
        'WeatherState' => '0',
    ];

    public function testNormalInput(): void
    {
        $save = new Savegame($this->input);
        static::assertEquals(true, $save->getFog());
        static::assertEquals('Eric', $save->getScenarioName());
        static::assertEquals('19390903', $save->getSaveDate());
        static::assertEquals('1205', $save->getSaveTime());
        static::assertEquals(true, $save->getCloudCover());
        static::assertEquals(false, $save->getWeatherState());
    }

    public function testBadFog(): void
    {
        $input = $this->input;
        $input['Fog'] = 'Foo';
        try {
            $save = new Savegame($input);
            static::fail('Since the fog entry is wrong, an exception was expected');
        } catch (InvalidInputException $exception) {
            static::assertEquals(
                "Invalid fog entry: 'Foo'",
                $exception->getMessage()
            );
        }
    }

    public function testHydrateError(): void
    {
        $input = $this->input;
        unset($input['Fog']);

        try {
            new Savegame($input);
            static::fail('Since the input data is incomplete, an exception was expected');
        } catch (InvalidInputException $exception) {
            static::assertEquals(
                'Invalid attribute quantity in App\Core\Tas\Savegame\Savegame',
                $exception->getMessage()
            );
        }
    }

    public function testGetShipDataMissing(): void
    {
        $save = new Savegame($this->input);
        try {
            $save->getShipData('Hood');
            static::fail('Since the ship does not exist, an exception was expected');
        } catch (NoShipException $exception) {
            static::assertEquals(
                "Ship 'Hood' not found in the savegame",
                $exception->getMessage()
            );
        }
    }

    public function testShipDataChanged(): void
    {
        $save = new Savegame($this->input);

        $save->setShipsDataChanged('Allied', false);
        $save->setShipsDataChanged('Axis', false);
        static::assertFalse($save->isShipsDataChanged('Allied'));
        static::assertFalse($save->isShipsDataChanged('Axis'));

        $save->setShipsDataChanged('Allied', true);
        $save->setShipsDataChanged('Axis', true);

        static::assertTrue($save->isShipsDataChanged('Allied'));
        static::assertTrue($save->isShipsDataChanged('Axis'));
    }

    public function testRemoveShipInPortNotInPort(): void
    {
        $save = new Savegame($this->input);
        try {
            $save->removeShipInPort('Hood', 'Allied');
            static::fail('Since the ship is not in port, an exception was expected');
        } catch (NoShipException $exception) {
            static::assertEquals(
                "Ship 'Hood' is not in port on the Allied side",
                $exception->getMessage()
            );
        }
    }

    public function testRemoveAxisFleet(): void
    {
        $save = new Savegame($this->input);

        $fleet = new Fleet();
        $fleet->setId('Foo');
        $save->addFleet('Axis', $fleet);
        static::assertEquals('Foo', $save->getAxisFleets()['Foo']->getId());
        $save->removeFleet('Axis', $fleet->getId());
        static::assertEquals(0, count($save->getAxisFleets()));
    }

    public function testFleetIncrement(): void
    {
        $save = new class ($this->input) extends Savegame {
            public function getFleetCountAxis(): int
            {
                return $this->axisMaxTfCount;
            }
            public function getFleetCountAllied(): int
            {
                return $this->alliedMaxTfCount;
            }
        };

        $save->incrementMaxTfNumber('Axis');
        $save->incrementMaxTfNumber('Allied');

        static::assertEquals(1, $save->getFleetCountAxis());
        static::assertEquals(1, $save->getFleetCountAllied());
    }

    public function testAddFleetUnknownSide(): void
    {
        $save = new Savegame($this->input);
        try {
            $save->addFleet('Hood', new Fleet());
            static::fail('Since the side is incorrect, an exception was expected');
        } catch (InvalidInputException $exception) {
            static::assertEquals(
                "Side 'Hood' is unknown",
                $exception->getMessage()
            );
        }
    }

    public function testGetFleetBadSide(): void
    {
        $save = new Savegame($this->input);
        try {
            $save->getFleets('Hood');
            static::fail('Since the side is incorrect, an exception was expected');
        } catch (InvalidInputException $exception) {
            static::assertEquals(
                "Side 'Hood' is unknown",
                $exception->getMessage()
            );
        }
    }

    public function testIncrementTfNumberBadSide(): void
    {
        $save = new Savegame($this->input);
        try {
            $save->incrementMaxTfNumber('Hood');
            static::fail('Since the side is incorrect, an exception was expected');
        } catch (InvalidInputException $exception) {
            static::assertEquals(
                "Side 'Hood' is unknown",
                $exception->getMessage()
            );
        }
    }

    public function testGetTfNumberBadSide(): void
    {
        $save = new Savegame($this->input);
        try {
            $save->getMaxTfNumber('Hood');
            static::fail('Since the side is incorrect, an exception was expected');
        } catch (InvalidInputException $exception) {
            static::assertEquals(
                "Side 'Hood' is unknown",
                $exception->getMessage()
            );
        }
    }

    public function testRemoveFleetBadSide(): void
    {
        $save = new Savegame($this->input);
        try {
            $save->removeFleet('Hood', 'HO');
            static::fail('Since the side is incorrect, an exception was expected');
        } catch (InvalidInputException $exception) {
            static::assertEquals(
                "Side 'Hood' is unknown",
                $exception->getMessage()
            );
        }
    }

    public function testShipDataChangedBadSide(): void
    {
        $save = new Savegame($this->input);
        try {
            $save->setShipsDataChanged('HO', false);
            static::fail('Since the side is incorrect, an exception was expected');
        } catch (InvalidInputException $exception) {
            static::assertEquals(
                "Side 'HO' is unknown",
                $exception->getMessage()
            );
        }
    }

    public function testIsShipDataChangedBadSide(): void
    {
        $save = new Savegame($this->input);
        try {
            $save->isShipsDataChanged('HO');
            static::fail('Since the side is incorrect, an exception was expected');
        } catch (InvalidInputException $exception) {
            static::assertEquals(
                "Side 'HO' is unknown",
                $exception->getMessage()
            );
        }
    }

    public function testGetShipInPortChangedBadSide(): void
    {
        $save = new Savegame($this->input);
        try {
            $save->getShipsInPort('HO');
            static::fail('Since the side is incorrect, an exception was expected');
        } catch (InvalidInputException $exception) {
            static::assertEquals(
                "Side 'HO' is unknown",
                $exception->getMessage()
            );
        }
    }

    public function testShipNotInPortBadSide(): void
    {
        $save = new Savegame($this->input);
        try {
            $save->removeShipInPort('Hood', 'HO');
            static::fail('Since the side is incorrect, an exception was expected');
        } catch (InvalidInputException $exception) {
            static::assertEquals(
                "Side 'HO' is unknown",
                $exception->getMessage()
            );
        }
    }

    public function testSetShipsInPortBadSide(): void
    {
        $save = new Savegame($this->input);
        try {
            $save->setShipsInPort('HO', []);
            static::fail('Since the side is incorrect, an exception was expected');
        } catch (InvalidInputException $exception) {
            static::assertEquals(
                "Side 'HO' is unknown",
                $exception->getMessage()
            );
        }
    }

    public function testShipNotInPort(): void
    {
        $save = new Savegame($this->input);

        foreach (['Axis', 'Allied'] as $side) {
            try {
                $save->removeShipInPort('Hood', $side);
                static::fail('Since the ship is the incorrect side, an exception was expected');
            } catch (NoShipException $exception) {
                static::assertEquals(
                    "Ship 'Hood' is not in port on the $side side",
                    $exception->getMessage()
                );
            }
        }
    }
}
