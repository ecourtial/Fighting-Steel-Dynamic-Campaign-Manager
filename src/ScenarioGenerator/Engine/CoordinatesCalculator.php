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
    public const ENNEMY_RANGE_MIN = 13000;
    public const ENNEMY_RANGE_MAX = 27000;

    /**
     * Reminder:
     * $x = XPOSITION in the scenario file
     * $z = ZPOSITION in the scenario file
     * $x_y = YARDSXPOSITION in the scenario file
     * $z_y = YARDSZPOSITION in the scenario file
     */
    private int $x = 0;
    private int $z = 0;
    private int $x_y = 0;
    private int $z_y = 0;
    private int $previousHeading = 0;
    private string $previousSide = '';

    /** @var int[][] */
    private array $coords = [];

    private Tools $tools;

    public function __construct(Tools $tools)
    {
        $this->tools = $tools;
    }

    /** @return int[] */
    public function getShipLocation(int $divisionsHeading, string $side, string $shipName): array
    {
        if (true === array_key_exists($shipName, $this->coords)) {
            return $this->coords[$shipName];
        }

        // We must do it for the allied first
        if ('' === $this->previousSide && Scenario::ALLIED_SIDE === $side) {
            // First iteration (for the allied ships)
            $this->x = static::ALLIED_X;
            $this->z = static::ALLIED_Z;
            $this->x_y = static::ALLIED_YARDS_X;
            $this->z_y = static::ALLIED_YARDS_Z;
            $this->previousSide = Scenario::ALLIED_SIDE;
            $this->previousHeading = $divisionsHeading; // Use to keep a local trace of the allied heading
        } elseif ($side !== $this->previousSide) {
            // First iteration for the axis ships
            $this->previousSide = Scenario::AXIS_SIDE;
            [$this->x, $this->z, $this->x_y, $this->z_y] = $this->generateAxisStartLocation($this->previousHeading);
        } else {
            $this->getUpdatedCoordinates($divisionsHeading);
        }

        // We store it for the next call (z)
        $this->coords[$shipName] = ['x' => $this->x, 'z' => $this->z, 'x_y' => $this->x_y, 'z_y' => $this->z_y];

        return $this->coords[$shipName];
    }

    private function getUpdatedCoordinates(int $divisionsHeading): void
    {
        switch ($divisionsHeading) {
            case 0:
                $this->z -= static::DIVISION_SPACING;
                $this->z_y -= static::DIVISION_SPACING;
                break;
            case 90:
                $this->x -= static::DIVISION_SPACING;
                $this->x_y -= static::DIVISION_SPACING;
                break;
            case 180:
                $this->z += static::DIVISION_SPACING;
                $this->z_y += static::DIVISION_SPACING;
                break;
            case 270:
                $this->x += static::DIVISION_SPACING;
                $this->x_y += static::DIVISION_SPACING;
                break;
            default:
                // Do not remove this check, because the axis location depends on the allied one
                throw new \InvalidArgumentException("Unknown division heading : '$divisionsHeading'");
        }
    }

    /** @return int[] */
    private function generateAxisStartLocation(int $alliedHeading): array
    {
        $distance = $this->tools->getRandomEnemyDistance();
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
        }

        return [$alliedX, $alliedZ, $allied_x_y, $allied_z_y];
    }
}
