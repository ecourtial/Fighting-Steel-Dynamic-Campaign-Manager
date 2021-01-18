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
    private string $shipsDir;

    public function __construct(ShipsSelector $shipsSelector, TextFileReader $textFileReader, string $projectRootDir)
    {
        $this->shipsSelector = $shipsSelector;
        $this->textFileReader = $textFileReader;
        $this->shipsDir = $projectRootDir . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Data' .
            DIRECTORY_SEPARATOR . 'ships' . DIRECTORY_SEPARATOR;
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

        $body .= $this->getDivisions($year, $shipQuantity, Scenario::ALLIED_SIDE, $ships, static::DIVISION_HEADING[array_rand(static::DIVISION_HEADING)]);
        $body .= $this->getDivisions($year, $shipQuantity, Scenario::AXIS_SIDE, $ships, static::DIVISION_HEADING[array_rand(static::DIVISION_HEADING)]);

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
        static $divisionCount = 0;
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
            $divisionCount,
            $ships,
            ShipsSelector::BIG_SHIPS_TYPES,
            $side,
            $divisionsHeading,
            $year
        );

        // If we do not need small ships let's stop
        if (
            Scenario::ALLIED_SIDE === $side && $shipQuantity->getAlliedSmall() === 0
            || Scenario::AXIS_SIDE === $side && $shipQuantity->getAxisSmall() === 0
        ) {
            return $divisions;
        }

        $divisionCount++;

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
        $divisions .= $this->getDivisionShips($divisionCount, $ships, ['DD'], $side, $divisionsHeading, $year);
        $divisionCount++;

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

        foreach ($ships as $currentSide => $naviesOfThisSide) {
            if ($side !== $currentSide) {
                continue;
            }

            foreach ($naviesOfThisSide as $navy => $shipsForThisNavy) {
                foreach ($shipsForThisNavy as $ship) {
                    $type = $ship['type'];

                    if (false === in_array($type, $authorizedTypes, true)) {
                        continue;
                    }

                    $shipCount++;
                    $shipData = "[DIVISION${divisionCount}SHIP{$shipCount}]" . PHP_EOL;

                    foreach (
                        $this->textFileReader->getFileContent(
                            $this->shipsDir . $navy . DIRECTORY_SEPARATOR . $type
                            . DIRECTORY_SEPARATOR . str_replace(' ', '', $ship['class']) . '.txt'
                        ) as $line
                    ) {
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

        switch ($lineArray[0]) {
            case 'XPOSITION':
                $line = 'XPOSITION=' .  $this->getShipLocation($divisionsHeading, $side, $ship['name'])['x'];
                break;
            case 'ZPOSITION':
                $line = 'ZPOSITION=' .  $this->getShipLocation($divisionsHeading, $side, $ship['name'])['z'];
                break;
            case 'NAME':
                $line = 'NAME=' .  $ship['name'];
                // First call to generate
                $this->getShipLocation($divisionsHeading, $side, $ship['name']);
                break;
            case 'SHORTNAME':
                $line = 'SHORTNAME=' .  substr($ship['name'], 0, 10);
                break;
            case 'CREWQUALITY':
                $line = 'CREWQUALITY=' .  Ship::CREW_QUALITY[array_rand(Ship::CREW_QUALITY)];
                break;
            case 'CREWFATIGUE':
                $line = 'CREWFATIGUE=' .  Ship::CREW_FATIGUE_LEVEL[array_rand(Ship::CREW_FATIGUE_LEVEL)];
                break;
            case 'NIGHTTRAINING':
                $line = 'NIGHTTRAINING=' .  Ship::CREW_NIGHT_TRAINING[array_rand(Ship::CREW_NIGHT_TRAINING)];
                break;
            case 'RADARTYPE':
                $levels = ScenarioEnv::RADAR_LEVELS[$year][$navy];
                $line = 'RADARTYPE=' .  $levels[array_rand($levels)];
                break;
        }

        return $line . PHP_EOL;
    }

    private function getShipLocation(int $divisionsHeading, string $side, string $shipName): array
    {
        static $x = 0;
        static $z = 0;
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
            $previousSide = Scenario::ALLIED_SIDE;
            $previousHeading = $divisionsHeading; // Use to keep a local trace of the allied heading
        } elseif ($side !== $previousSide) {
            // First iteration for the axis ships
            $previousSide = Scenario::AXIS_SIDE;
            [$x, $z] = $this->generateAxisStartLocation($previousHeading, static::ALLIED_X, static::ALLIED_Z);
        } else {
            [$x, $z] = $this->getUpdatedCoordinates($divisionsHeading, $x, $z);
        }

        $coords[$shipName] = ['x' => $x, 'z' => $z];

        return $coords[$shipName];
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
                $values = [1, 3];
                return $values[array_rand($values)]; // 1 OR 3
            case 4:
                return 1;
            case 5:
                $values = [1, 2];
                return $values[array_rand($values)]; // 1 OR 2
            case 6:
            case 7:
                $values = [2, 3];
                return $values[array_rand($values)]; // 2 OR 3
            case 8:
                return random_int(2, 4); // 2 to 4
            default:
                throw new \InvalidArgumentException("Unsupported ship qty: {$shipQuantity}");
        }
    }
}
