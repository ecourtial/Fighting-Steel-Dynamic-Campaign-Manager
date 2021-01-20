<?php

declare(strict_types=1);

namespace App\ScenarioGenerator\Engine;

use App\Core\Tas\Scenario\Scenario;

class CoordinatesCalculator
{
    public const DIVISION_SPACING = 500;
    public const DIVISION_HEADING = [0, 90, 180, 270];
    public const ALLIED_X = 40000;
    public const ALLIED_Z = 40000;
    public const ALLIED_YARDS_X = 4000;
    public const ALLIED_YARDS_Z = 4000;

    public function getShipLocation(int $divisionsHeading, string $side, string $shipName): array
    {
        /**
         * Reminder:
         * $x = XPOSITION in the scenario file
         * $z = ZPOSITION in the scenario file
         * $x_y = YARDSXPOSITION in the scenario file
         * $z_y = YARDSZPOSITION in the scenario file
         */
        static $x = 0;
        static $z = 0;
        static $x_y = 0;
        static $z_y = 0;
        static $previousSide = '';
        static $previousHeading = 0;
        static $coords = [];

        if (true === array_key_exists($shipName, $coords)) {
            return $coords[$shipName];
        }


        if ($previousSide === '' && $side === Scenario::ALLIED_SIDE) {
            // First iteration (for the allied ships)
            $x = static::ALLIED_X;
            $z = static::ALLIED_Z;
            $x_y = static::ALLIED_YARDS_X;
            $z_y = static::ALLIED_YARDS_Z;
            $previousSide = Scenario::ALLIED_SIDE;
            $previousHeading = $divisionsHeading; // Use to keep a local trace of the allied heading
        } elseif ($side !== $previousSide) {
            // First iteration for the axis ships
            $previousSide = Scenario::AXIS_SIDE;
            [$x, $z, $x_y, $z_y] = $this->generateAxisStartLocation($previousHeading);
        } else {
            [$x, $z, $x_y, $z_y] = $this->getUpdatedCoordinates($divisionsHeading, $x, $z, $x_y, $z_y);
        }

        // We store it for the next call (z)
        $coords[$shipName] = ['x' => $x, 'z' => $z, 'x_y' => $x_y, 'z_y' => $z_y];

        return $coords[$shipName];
    }

    private function getUpdatedCoordinates(int $divisionsHeading, int $x, int $z, int $x_y, int $z_y): array
    {
        switch ($divisionsHeading) {
            case 0:
                $z -= static::DIVISION_SPACING;
                $z_y -= static::DIVISION_SPACING;
                break;
            case 90:
                $x -= static::DIVISION_SPACING;
                $x_y -= static::DIVISION_SPACING;
                break;
            case 180:
                $z += static::DIVISION_SPACING;
                $z_y += static::DIVISION_SPACING;
                break;
            case 270:
                $x += static::DIVISION_SPACING;
                $x_y += static::DIVISION_SPACING;
                break;
            default:
                throw new \InvalidArgumentException("Unknown division heading : '$divisionsHeading'");
        }

        return [$x, $z, $x_y, $z_y];
    }

    private function generateAxisStartLocation(int $alliedHeading): array
    {
        $distance = random_int(13000, 27000);
        $alliedX = static::ALLIED_X;
        $alliedZ = static::ALLIED_Z;
        $allied_x_y = static::ALLIED_YARDS_X;
        $allied_z_y = static::ALLIED_YARDS_Z;

        switch ($alliedHeading) {
            case 0:
                $alliedZ += $distance;
                $allied_z_y += $distance;
                break;
            case 90:
                $alliedX += $distance;
                $allied_x_y += $distance;
                break;
            case 180:
                $alliedZ -= $distance;
                $allied_z_y -= $distance;
                break;
            case 270:
                $alliedX -= $distance;
                $allied_x_y -= $distance;
                break;
            default:
                throw new \InvalidArgumentException("Unknown division heading : '$alliedHeading'");
        }

        return [$alliedX, $alliedZ, $allied_x_y, $allied_z_y];
    }
}
