<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       22/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

namespace App\Tests\Core\Tas\Savegame;

use App\Core\Exception\InvalidInputException;
use App\Core\Tas\Savegame\Fleet\TaskForce;
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
            $save->getNavalData()->getShipData('Hood');
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

        $save->getNavalData()->setShipsDataChanged('Allied', false);
        $save->getNavalData()->setShipsDataChanged('Axis', false);
        static::assertFalse($save->getNavalData()->isShipsDataChanged('Allied'));
        static::assertFalse($save->getNavalData()->isShipsDataChanged('Axis'));

        $save->getNavalData()->setShipsDataChanged('Allied', true);
        $save->getNavalData()->setShipsDataChanged('Axis', true);

        static::assertTrue($save->getNavalData()->isShipsDataChanged('Allied'));
        static::assertTrue($save->getNavalData()->isShipsDataChanged('Axis'));
    }

    public function testRemoveShipInPortNotInPort(): void
    {
        $save = new Savegame($this->input);
        try {
            $save->getNavalData()->removeShipInPort('Hood', 'Allied');
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

        $fleet = new TaskForce('Foo');
        $save->getNavalData()->addFleet('Axis', $fleet);
        static::assertEquals('Foo', $save->getNavalData()->getFleets('Axis')['Foo']->getId());
        $save->getNavalData()->removeFleet('Axis', $fleet->getId());
        static::assertEquals(0, count($save->getNavalData()->getFleets('Axis')));
    }

    public function testFleetIncrement(): void
    {
        $save = new class($this->input) extends Savegame {
            public function getFleetCountAxis(): int
            {
                return $this->navalData->getMaxTfNumber('Axis');
            }

            public function getFleetCountAllied(): int
            {
                return $this->navalData->getMaxTfNumber('Allied');
            }
        };

        $save->getNavalData()->incrementMaxTfNumber('Axis');
        $save->getNavalData()->incrementMaxTfNumber('Allied');

        static::assertEquals(1, $save->getFleetCountAxis());
        static::assertEquals(1, $save->getFleetCountAllied());
    }

    public function testAddFleetUnknownSide(): void
    {
        $save = new Savegame($this->input);
        try {
            $save->getNavalData()->addFleet('Hood', new TaskForce('UU'));
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
            $save->getNavalData()->getFleets('Hood');
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
            $save->getNavalData()->incrementMaxTfNumber('Hood');
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
            $save->getNavalData()->getMaxTfNumber('Hood');
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
            $save->getNavalData()->removeFleet('Hood', 'HO');
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
            $save->getNavalData()->setShipsDataChanged('HO', false);
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
            $save->getNavalData()->isShipsDataChanged('HO');
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
            $save->getNavalData()->getShipsInPort('HO');
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
            $save->getNavalData()->removeShipInPort('Hood', 'HO');
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
            $save->getNavalData()->setShipsInPort('HO', []);
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
                $save->getNavalData()->removeShipInPort('Hood', $side);
                static::fail('Since the ship is the incorrect side, an exception was expected');
            } catch (NoShipException $exception) {
                static::assertEquals(
                    "Ship 'Hood' is not in port on the $side side",
                    $exception->getMessage()
                );
            }
        }
    }

    public function testSetShipsAtSea(): void
    {
        $alliedFleets = [
            'TFO' => new TaskForce('TFO'),
            'TF1' => new TaskForce('TF1'),
            'TF3' => new TaskForce('TF3'),
        ];

        $axisFleets = [
            'TFO' => new TaskForce('TFO'),
            'TF1' => new TaskForce('TF1'),
            'TF2' => new TaskForce('TF2'),
        ];

        $save = new Savegame($this->input);
        $save->getNavalData()->setShipsAtSea('Allied', $alliedFleets);
        $save->getNavalData()->setShipsAtSea('Axis', $axisFleets);

        static::assertEquals(3, $save->getNavalData()->getMaxTfNumber('Allied'));
        static::assertEquals(2, $save->getNavalData()->getMaxTfNumber('Axis'));
    }
}
