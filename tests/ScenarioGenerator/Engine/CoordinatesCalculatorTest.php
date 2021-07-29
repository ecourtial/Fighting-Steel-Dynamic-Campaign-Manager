<?php

declare(strict_types=1);

namespace App\Tests\ScenarioGenerator\Engine;

use App\Core\Tas\Scenario\Scenario;
use App\ScenarioGenerator\Engine\CoordinatesCalculator;
use App\ScenarioGenerator\Engine\Tools;
use PHPUnit\Framework\TestCase;

class CoordinatesCalculatorTest extends TestCase
{
    private static CoordinatesCalculator $calculator;

    public function setUp(): void
    {
        $tools = new Tools();
        static::$calculator = new CoordinatesCalculator($tools);
    }

    public function testErrorUnknownAlliedDivisionHeading(): void
    {
        static::expectExceptionMessage("Unknown division heading : '-1'");

        static::$calculator->getShipLocation(
            -1,
            '',
            'Prince Of Wales',
        );
    }

    public function testLazyLoading(): void
    {
        $alliedShip1Coords = static::$calculator->getShipLocation(
            0,
            Scenario::ALLIED_SIDE,
            'Hood',
        );

        static::$calculator->getShipLocation(
            0,
            Scenario::ALLIED_SIDE,
            'Prince Of Wales',
        );

        $alliedShip3Coords = static::$calculator->getShipLocation(
            0,
            Scenario::ALLIED_SIDE,
            'Hood',
        );

        static::assertEquals($alliedShip1Coords, $alliedShip3Coords);
    }

    /** @dataProvider coordinatesDataProvider */
    public function testCoordinatesCalculation(int $heading, array $ships): void
    {
        // Check Allied ships
        $alliedShip1Coords = static::$calculator->getShipLocation(
            $heading,
            Scenario::ALLIED_SIDE,
            'Hood',
        );

        $alliedShip2Coords = static::$calculator->getShipLocation(
            $heading,
            Scenario::ALLIED_SIDE,
            'Prince Of Wales',
        );

        static::assertEquals($ships[Scenario::ALLIED_SIDE][0]['x'], $alliedShip1Coords['x']);
        static::assertEquals($ships[Scenario::ALLIED_SIDE][0]['z'], $alliedShip1Coords['z']);
        static::assertEquals($ships[Scenario::ALLIED_SIDE][0]['x_y'], $alliedShip1Coords['x_y']);
        static::assertEquals($ships[Scenario::ALLIED_SIDE][0]['z_y'], $alliedShip1Coords['z_y']);

        static::assertEquals($ships[Scenario::ALLIED_SIDE][1]['x'], $alliedShip2Coords['x']);
        static::assertEquals($ships[Scenario::ALLIED_SIDE][1]['z'], $alliedShip2Coords['z']);
        static::assertEquals($ships[Scenario::ALLIED_SIDE][1]['x_y'], $alliedShip2Coords['x_y']);
        static::assertEquals($ships[Scenario::ALLIED_SIDE][1]['z_y'], $alliedShip2Coords['z_y']);

        // Check axis ships
        $axisShip1Coords = static::$calculator->getShipLocation(
            $heading,
            Scenario::AXIS_SIDE,
            'Bismarck',
        );

        $x1 = $axisShip1Coords['x'];
        $z1 = $axisShip1Coords['z'];
        $x_y1 = $axisShip1Coords['x_y'];
        $z_y1 = $axisShip1Coords['z_y'];

        $axisShip2Coords = static::$calculator->getShipLocation(
            $heading,
            Scenario::AXIS_SIDE,
            'Prinz Eugen',
        );

        $x2 = $axisShip2Coords['x'];
        $z2 = $axisShip2Coords['z'];
        $x_y2 = $axisShip2Coords['x_y'];
        $z_y2 = $axisShip2Coords['z_y'];

        switch ($heading) {
            case 0:
                // Check ship 1
                $difference_z1 = $z1 - CoordinatesCalculator::ALLIED_Z;
                $difference_z_y1 = $z_y1 - CoordinatesCalculator::ALLIED_YARDS_Z;
                static::assertTrue($difference_z1 >= CoordinatesCalculator::ENNEMY_RANGE_MIN && $difference_z1 <= CoordinatesCalculator::ENNEMY_RANGE_MAX);
                static::assertTrue($difference_z_y1 >= CoordinatesCalculator::ENNEMY_RANGE_MIN && $difference_z_y1 <= CoordinatesCalculator::ENNEMY_RANGE_MAX);
                static::assertTrue(CoordinatesCalculator::ALLIED_X === $x1);
                static::assertTrue(CoordinatesCalculator::ALLIED_YARDS_X === $x_y1);

                // Check ship 2
                $difference_z2 = $z2 - CoordinatesCalculator::ALLIED_Z;
                $difference_z_y2 = $z_y2 - CoordinatesCalculator::ALLIED_YARDS_Z;
                static::assertTrue($difference_z2 === ($difference_z1 - CoordinatesCalculator::DIVISION_SPACING));
                static::assertTrue($difference_z_y2 === ($difference_z_y1 - CoordinatesCalculator::DIVISION_SPACING));
                static::assertTrue(CoordinatesCalculator::ALLIED_X === $x2);
                static::assertTrue(CoordinatesCalculator::ALLIED_YARDS_X === $x_y2);

                break;
            case 90:
                // Check ship 1
                $difference_x1 = $x1 - CoordinatesCalculator::ALLIED_Z;
                $difference_x_y1 = $x_y1 - CoordinatesCalculator::ALLIED_YARDS_Z;
                static::assertTrue($difference_x1 >= CoordinatesCalculator::ENNEMY_RANGE_MIN && $difference_x1 <= CoordinatesCalculator::ENNEMY_RANGE_MAX);
                static::assertTrue($difference_x_y1 >= CoordinatesCalculator::ENNEMY_RANGE_MIN && $difference_x_y1 <= CoordinatesCalculator::ENNEMY_RANGE_MAX);
                static::assertTrue(CoordinatesCalculator::ALLIED_X === $z1);
                static::assertTrue(CoordinatesCalculator::ALLIED_YARDS_X === $z_y1);

                // Check ship 2
                $difference_x2 = $x2 - CoordinatesCalculator::ALLIED_X;
                $difference_x_y2 = $x_y2 - CoordinatesCalculator::ALLIED_YARDS_X;
                static::assertTrue($difference_x2 === ($difference_x1 - CoordinatesCalculator::DIVISION_SPACING));
                static::assertTrue($difference_x_y2 === ($difference_x_y1 - CoordinatesCalculator::DIVISION_SPACING));
                static::assertTrue(CoordinatesCalculator::ALLIED_Z === $z2);
                static::assertTrue(CoordinatesCalculator::ALLIED_YARDS_Z === $z_y2);

                break;
            case 180:
                // Check ship 1
                $difference_z1 = CoordinatesCalculator::ALLIED_Z - $z1;
                $difference_z_y1 = CoordinatesCalculator::ALLIED_YARDS_Z - $z_y1;
                static::assertTrue($difference_z1 >= CoordinatesCalculator::ENNEMY_RANGE_MIN && $difference_z1 <= CoordinatesCalculator::ENNEMY_RANGE_MAX);
                static::assertTrue($difference_z_y1 >= CoordinatesCalculator::ENNEMY_RANGE_MIN && $difference_z_y1 <= CoordinatesCalculator::ENNEMY_RANGE_MAX);
                static::assertTrue(CoordinatesCalculator::ALLIED_X === $x1);
                static::assertTrue(CoordinatesCalculator::ALLIED_YARDS_X === $x_y1);

                // Check ship 2
                $difference_z2 = CoordinatesCalculator::ALLIED_Z - $z2;
                $difference_z_y2 = CoordinatesCalculator::ALLIED_YARDS_Z - $z_y2;
                static::assertTrue($difference_z2 === ($difference_z1 - CoordinatesCalculator::DIVISION_SPACING));
                static::assertTrue($difference_z_y2 === ($difference_z_y1 - CoordinatesCalculator::DIVISION_SPACING));
                static::assertTrue(CoordinatesCalculator::ALLIED_X === $x2);
                static::assertTrue(CoordinatesCalculator::ALLIED_YARDS_X === $x_y2);

                break;

            case 270:
                // Check ship 1
                $difference_x1 = CoordinatesCalculator::ALLIED_Z - $x1;
                $difference_x_y1 = CoordinatesCalculator::ALLIED_YARDS_Z - $x_y1;
                static::assertTrue($difference_x1 >= CoordinatesCalculator::ENNEMY_RANGE_MIN && $difference_x1 <= CoordinatesCalculator::ENNEMY_RANGE_MAX);
                static::assertTrue($difference_x_y1 >= CoordinatesCalculator::ENNEMY_RANGE_MIN && $difference_x_y1 <= CoordinatesCalculator::ENNEMY_RANGE_MAX);
                static::assertTrue(CoordinatesCalculator::ALLIED_X === $z1);
                static::assertTrue(CoordinatesCalculator::ALLIED_YARDS_X === $z_y1);

                // Check ship 2
                $difference_x2 = CoordinatesCalculator::ALLIED_X - $x2;
                $difference_x_y2 = CoordinatesCalculator::ALLIED_YARDS_X - $x_y2;
                static::assertTrue($difference_x2 === ($difference_x1 - CoordinatesCalculator::DIVISION_SPACING));
                static::assertTrue($difference_x_y2 === ($difference_x_y1 - CoordinatesCalculator::DIVISION_SPACING));
                static::assertTrue(CoordinatesCalculator::ALLIED_Z === $z2);
                static::assertTrue(CoordinatesCalculator::ALLIED_YARDS_Z === $z_y2);

            break;

            default: static::fail("Unknown heading: $heading");
        }
    }

    public function coordinatesDataProvider(): array
    {
        return [
                [
                    'heading' => 0,
                    [Scenario::ALLIED_SIDE => [
                        ['x' => 40000, 'z' => 40000, 'x_y' => 4000, 'z_y' => 4000],
                        ['x' => 40000, 'z' => 39500, 'x_y' => 4000, 'z_y' => 3500],
                    ]],
                ],
                [
                    'heading' => 90,
                    [Scenario::ALLIED_SIDE => [
                        ['x' => 40000, 'z' => 40000, 'x_y' => 4000, 'z_y' => 4000],
                        ['x' => 39500, 'z' => 40000, 'x_y' => 3500, 'z_y' => 4000],
                    ]],
                ],
                [
                    'heading' => 180,
                    [Scenario::ALLIED_SIDE => [
                        ['x' => 40000, 'z' => 40000, 'x_y' => 4000, 'z_y' => 4000],
                        ['x' => 40000, 'z' => 40500, 'x_y' => 4000, 'z_y' => 4500],
                    ]],
                ],
                [
                    'heading' => 270,
                    [Scenario::ALLIED_SIDE => [
                        ['x' => 40000, 'z' => 40000, 'x_y' => 4000, 'z_y' => 4000],
                        ['x' => 40500, 'z' => 40000, 'x_y' => 4500, 'z_y' => 4000],
                    ]],
                ],
        ];
    }
}
