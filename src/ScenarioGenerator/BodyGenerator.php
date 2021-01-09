<?php

declare(strict_types=1);

namespace App\ScenarioGenerator;

use App\Core\Fs\Scenario\Ship\Ship;
use App\Core\Tas\Scenario\Scenario;
use App\Core\File\TextFileReader;

class BodyGenerator
{
    public const DIVISION_SPACING = 500;
    public const DIVISION_HEADING = [0, 90, 180, 270];
    public const ALLIED_X = 40000;
    public const ALLIED_Z = 40000;

    private ShipsSelector $shipsSelector;
    private TextFileReader $textFileReader;

    public function __construct(ShipsSelector $shipsSelector, TextFileReader $textFileReader)
    {
        $this->shipsSelector = $shipsSelector;
        $this->textFileReader = $textFileReader;
    }

    public function getBody(
        string $code,
        int $period,
        int $year,
        bool $mixedNavies
    ): string {
        $shipQuantity = $this->getShipsQuantities();

        return $this->generateBody(
            $shipQuantity,
            $year,
            $this->shipsSelector->getShips($code, $period, $shipQuantity, $mixedNavies)
        );
    }

    private function getShipsQuantities(): ShipQuantity
    {
        $alliedTotal = \random_int(2, 8);
        $axisTotal = \random_int(2, 8);

        return new ShipQuantity(
            $alliedTotal,
            $axisTotal,
            $this->getBigShipCount($alliedTotal),
            $this->getBigShipCount($axisTotal)
        );
    }

    private function generateBody(ShipQuantity $shipQuantity, int $year, array $ships): string
    {
        $alliedDivisionCount = 1;
        $axisDivisionCount = 1;

        // Reminder: one division for the big units, and one for the escort
        if ($shipQuantity->getAlliedBig() !== $shipQuantity->getAlliedTotal()) {
            $alliedDivisionCount++;
        }

        if ($shipQuantity->getAxisBig() !== $shipQuantity->getAxisTotal()) {
            $axisDivisionCount++;
        }

        $divisionCount = $alliedDivisionCount + $axisDivisionCount;
        $body = "DIVISIONCNT=$divisionCount" . PHP_EOL . PHP_EOL;

        $body .= $this->getDivisions($year, $shipQuantity, Scenario::ALLIED_SIDE, $ships, array_rand(static::DIVISION_HEADING));
        $body .= $this->getDivisions($year, $shipQuantity, Scenario::AXIS_SIDE, $ships, array_rand(static::DIVISION_HEADING));

        return $body;
    }

    private function getDivisions(
        int $year,
        ShipQuantity $shipQuantity,
        string $side,
        array $ships,
        int $divisionsHeading
    ): string {
        static $divisionIndex = 0;
        $sideColor = $side === Scenario::ALLIED_SIDE ? 'Blue' : 'Red';
        $divisions = '';

        // Division with big ships
        $divisions .= $this->getDivisionData(
            $divisionIndex,
            $sideColor,
            $divisionsHeading,
            static::DIVISION_SPACING,
            $side === Scenario::ALLIED_SIDE ? $shipQuantity->getAlliedBig() : $shipQuantity->getAxisBig()
        ) . PHP_EOL;

        $divisionIndex++;

        // Generate division content
        $divisions .= $this->getDivisionShips(
            0,
            $ships,
            ShipsSelector::BIG_SHIPS_TYPES,
            $side,
            $divisionsHeading,
            $year
        );

        // Division with small ships
        $divisions .= $this->getDivisionData(
            $divisionIndex,
            $sideColor,
            $divisionsHeading,
            static::DIVISION_SPACING,
            $side === Scenario::ALLIED_SIDE ? $shipQuantity->getAlliedSmall() : $shipQuantity->getAxisSmall()
        ) . PHP_EOL;

        $divisionIndex++;

        // Generate division content
        $divisions .= $this->getDivisionShips(1, $ships, ['DD'], $side, $divisionsHeading, $year);

        return $divisions;
    }

    private function getDivisionData(
        int $divId,
        string $sideColor,
        int $formationHeading,
        int $formationSpacing,
        int $shipCount
    ): string {
        return <<<EOT
            [DIVISION$divId]
            DIVISIONNAME=Division $divId
            SIDE=$sideColor
            FORMATION=Column
            FORMATIONHEADING=$formationHeading
            FORMATIONSPACING=$formationSpacing
            SPEED=16
            SHIPCNT=$shipCount
            FLAGSHIPINDEX=0
            ENCUMBERED=0
        EOT;
    }

    private function getDivisionShips(
        int $divisionCount,
        array $ships,
        array $authorizedTypes,
        string $side,
        int $divisionsHeading,
        int $year
    ): string {
        $shipCount = -1;
        $divisionData = '';

        foreach ($ships as $navy => $types) {
            foreach ($types as $type => $elements) {
                if (false === in_array($type, $authorizedTypes, true)) {
                    continue;
                }

                foreach ($elements as $ship) {
                    $shipCount++;
                    $shipData = "[DIVISION${divisionCount}SHIP{$shipCount}]" . PHP_EOL;

                    foreach (
                        $this->textFileReader->getFileContent(
                            'blablba' . $navy . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $ship['class'] . '.txt'
                        ) as $line
                    ) {
                        $shipData .= "[DIVISION${divisionCount}SHIP{$shipCount}]";
                        $shipData .= $this->decorateLine($line, $ship, $navy, $year, $side, $divisionsHeading);
                    }

                    $divisionData .= $shipData . PHP_EOL;
                }
            }
        }

        return $divisionData;
    }

    private function decorateLine(string $line, array $ship, string $navy, int $year, string $side, int $divisionsHeading): string
    {
        $lineArray = explode('=', $line);
        if (2 !== count($lineArray)) {
            return $line;
        }

        [$x, $z] = $this->getShipLocation($divisionsHeading, $side);

        switch ($lineArray[0]) {
            case 'XPOSITION':
                $line = 'XPOSITION=' .  $x;
                break;
            case 'ZPOSITION':
                $line = 'ZPOSITION=' .  $z;
                break;
            case 'NAME':
                $line = 'NAME=' .  $ship['name'];
                break;
            case 'SHORTNAME':
                $line = 'SHORTNAME=' .  substr($ship['name'], 0, 10);
                break;
            case 'CREWQUALITY':
                $line = 'CREWQUALITY=' .  array_rand(Ship::CREW_QUALITY);
                break;
            case 'CREWFATIGUE':
                $line = 'CREWFATIGUE=' .  array_rand(Ship::CREW_FATIGUE_LEVEL);
                break;
            case 'NIGHTTRAINING':
                $line = 'NIGHTTRAINING=' .  array_rand(Ship::CREW_NIGHT_TRAINING);
                break;
            case 'RADARTYPE':
                $line = 'RADARTYPE=' .  array_rand(ScenarioEnv::RADAR_LEVELS[$year][$navy]);
                break;
        }

        return $line . PHP_EOL;
    }

    private function getShipLocation(int $divisionsHeading, string $side): array
    {
        static $x = 0;
        static $z = 0;
        static $previousSide = '';
        static $previousHeading = 0;

        if ($previousSide === '' && $side === Scenario::ALLIED_SIDE) {
            // First iteration (for the allied ships)
            $x = static::ALLIED_X;
            $z = static::ALLIED_Z;
            $previousSide = Scenario::ALLIED_SIDE;
            $previousHeading = $divisionsHeading; // Use to keep a local trace of the allied heading
        } elseif ($side !== $previousSide) {
            // First iteration for the axis ships
            $previousSide = Scenario::AXIS_SIDE;
            [$x, $z] = $this->generateAxisStartLocation($previousHeading, static::ALLIED_X, static::ALLIED_Z);
        } else {
            [$x, $z] = $this->getUpdatedCoordinates($divisionsHeading, $x, $z);
        }

        return [$x, $z];
    }

    private function getUpdatedCoordinates(int $divisionsHeading, int $x, int $z): array
    {
        switch ($divisionsHeading) {
            case 0:
                $z -= static::DIVISION_SPACING;
                break;
            case 90:
                $x -= static::DIVISION_SPACING;
                break;
            case 180:
                $z += static::DIVISION_SPACING;
                break;
            case 270:
                $x += static::DIVISION_SPACING;
                break;
            default:
                throw new \InvalidArgumentException("Unknown division heading : '$divisionsHeading'");
        }

        return [$x, $z];
    }

    private function generateAxisStartLocation(int $alliedHeading, int $alliedX, int $alliedZ): array
    {
        $distance = random_int(13000, 27000);

        switch ($alliedHeading) {
            case 0:
                $alliedZ += $distance;
                break;
            case 90:
                $alliedX += $distance;
                break;
            case 180:
                $alliedZ -= $distance;
                break;
            case 270:
                $alliedX -= $distance;
                break;
            default:
                throw new \InvalidArgumentException("Unknown division heading : '$alliedHeading'");
        }

        return [$alliedX, $alliedZ];
    }

    private function getBigShipCount(int $shipQuantity): int
    {
        switch ($shipQuantity) {
            case 2:
                return 2;
            case 3:
                return array_rand([1, 3]); // 1 OR 3
            case 4:
                return 1;
            case 5:
                return array_rand([1, 2]);
            case 6:
            case 7:
                return array_rand([2, 3]);
            case 8:
                return random_int(2, 4); // 2 to 4
            default:
                throw new \InvalidArgumentException("Unsupported ship qty: {$shipQuantity}");
        }
    }
}
