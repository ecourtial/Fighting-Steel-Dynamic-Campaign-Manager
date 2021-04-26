<?php

declare(strict_types=1);

namespace App\ScenarioGenerator\Engine;

use App\Core\File\TextFileReader;
use App\Core\Fs\Scenario\Ship\Ship;
use App\Core\Tas\Scenario\Scenario;
use App\ScenarioGenerator\Engine\Ships\ShipProvider;
use App\ScenarioGenerator\Engine\Ships\ShipQuantity;

class BodyGenerator
{
    private ShipsSelector $shipsSelector;
    private TextFileReader $textFileReader;
    private string $shipsDir;
    private CoordinatesCalculator $coordinatesCalculator;

    public function __construct(
        ShipsSelector $shipsSelector,
        TextFileReader $textFileReader,
        CoordinatesCalculator $coordinatesCalculator,
        string $projectRootDir
    ) {
        $this->shipsSelector = $shipsSelector;
        $this->textFileReader = $textFileReader;
        $this->coordinatesCalculator = $coordinatesCalculator;

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

        $body .= $this->getDivisions($year, $shipQuantity, Scenario::ALLIED_SIDE, $ships);
        $body .= $this->getDivisions($year, $shipQuantity, Scenario::AXIS_SIDE, $ships);

        return $body;
    }

    private function getDivisions(
        int $year,
        ShipQuantity $shipQuantity,
        string $side,
        array $ships
    ): string {
        static $divisionIndex = 0;
        $sideColor = Scenario::ALLIED_SIDE === $side ? 'Blue' : 'Red';
        $divisions = '';

        $divisionsHeading = CoordinatesCalculator::DIVISION_HEADING[array_rand(CoordinatesCalculator::DIVISION_HEADING)];

        // Division with big ships
        $divisions .= $this->getDivisionData(
            $divisionIndex,
            $sideColor,
            $divisionsHeading,
            Scenario::ALLIED_SIDE === $side ? $shipQuantity->getAlliedBig() : $shipQuantity->getAxisBig()
        ) . PHP_EOL;

        // Generate division content
        $divisions .= $this->getDivisionShips(
            $divisionIndex,
            $ships,
            ShipProvider::BIG_SHIPS_TYPES,
            $side,
            $divisionsHeading,
            $year
        );

        $divisionIndex++;

        // If we do not need small ships let's stop
        if (
            Scenario::ALLIED_SIDE === $side && 0 === $shipQuantity->getAlliedSmall()
            || Scenario::AXIS_SIDE === $side && 0 === $shipQuantity->getAxisSmall()
        ) {
            return $divisions;
        }

        // Division with small ships
        $divisions .= $this->getDivisionData(
            $divisionIndex,
            $sideColor,
            $divisionsHeading,
            Scenario::ALLIED_SIDE === $side ? $shipQuantity->getAlliedSmall() : $shipQuantity->getAxisSmall()
        ) . PHP_EOL;

        // Generate division content
        $divisions .= $this->getDivisionShips($divisionIndex, $ships, ['DD'], $side, $divisionsHeading, $year);
        $divisionIndex++;

        return $divisions;
    }

    private function getDivisionData(
        int $divId,
        string $sideColor,
        int $formationHeading,
        int $shipCount
    ): string {
        $formationSpacing = CoordinatesCalculator::DIVISION_SPACING;

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
                $line = 'XPOSITION=' . $this->coordinatesCalculator->getShipLocation($divisionsHeading, $side, $ship['name'])['x'];
                break;
            case 'ZPOSITION':
                $line = 'ZPOSITION=' . $this->coordinatesCalculator->getShipLocation($divisionsHeading, $side, $ship['name'])['z'];
                break;
            case 'NAME':
                $line = 'NAME=' . $ship['name'];
                break;
            case 'SHORTNAME':
                $line = 'SHORTNAME=' . substr($ship['name'], 0, 10);
                break;
            case 'CREWQUALITY':
                $line = 'CREWQUALITY=' . Ship::CREW_QUALITY[array_rand(Ship::CREW_QUALITY)];
                break;
            case 'CREWFATIGUE':
                $line = 'CREWFATIGUE=' . Ship::CREW_FATIGUE_LEVEL[array_rand(Ship::CREW_FATIGUE_LEVEL)];
                break;
            case 'NIGHTTRAINING':
                $line = 'NIGHTTRAINING=' . Ship::CREW_NIGHT_TRAINING[array_rand(Ship::CREW_NIGHT_TRAINING)];
                break;
            case 'RADARTYPE':
                $levels = ScenarioEnv::RADAR_LEVELS[$year][$navy];
                $line = 'RADARTYPE=' . $levels[array_rand($levels)];
                break;
            case 'YARDSXPOSITION':
                $line = 'YARDSXPOSITION=' . $this->coordinatesCalculator->getShipLocation($divisionsHeading, $side, $ship['name'])['x_y'];
                break;
            case 'YARDSZPOSITION':
                $line = 'YARDSZPOSITION=' . $this->coordinatesCalculator->getShipLocation($divisionsHeading, $side, $ship['name'])['z_y'];
                break;
        }

        return $line . PHP_EOL;
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
