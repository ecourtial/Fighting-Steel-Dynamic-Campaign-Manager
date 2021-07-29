<?php

declare(strict_types=1);

namespace App\ScenarioGenerator\Engine;

use App\Core\File\TextFileReader;
use App\Core\Tas\Scenario\Scenario;
use App\ScenarioGenerator\Engine\Ships\ShipProvider;
use App\ScenarioGenerator\Engine\Ships\ShipQuantity;

class BodyGenerator
{
    private ShipsSelector $shipsSelector;
    private TextFileReader $textFileReader;
    private string $shipsDir;
    private CoordinatesCalculator $coordinatesCalculator;
    private Tools $tools;
    private int $currentDivisionIndex;

    public function __construct(
        ShipsSelector $shipsSelector,
        TextFileReader $textFileReader,
        CoordinatesCalculator $coordinatesCalculator,
        Tools $tools,
        string $projectRootDir
    ) {
        $this->shipsSelector = $shipsSelector;
        $this->textFileReader = $textFileReader;
        $this->coordinatesCalculator = $coordinatesCalculator;
        $this->tools = $tools;

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
        $this->currentDivisionIndex = 0;

        return $this->generateBody(
            $shipQuantity,
            $year,
            $this->shipsSelector->getShips($code, $period, $shipQuantity, $mixedNavies)
        );
    }

    private function getShipsQuantities(): ShipQuantity
    {
        $alliedTotal = $this->tools->getRandomShipQty();
        $axisTotal = $this->tools->getRandomShipQty();

        return new ShipQuantity(
            $alliedTotal,
            $axisTotal,
            $this->tools->getBigShipCount($alliedTotal),
            $this->tools->getBigShipCount($axisTotal)
        );
    }

    /** @param string[][][][] $ships */
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

    /** @param string[][][][] $ships */
    private function getDivisions(
        int $year,
        ShipQuantity $shipQuantity,
        string $side,
        array $ships
    ): string {
        $sideColor = Scenario::ALLIED_SIDE === $side ? 'Blue' : 'Red';
        $divisions = '';

        $divisionsHeading = $this->tools->getRandomHeading();

        // Division with big ships
        $divisions .= $this->getDivisionData(
            $this->currentDivisionIndex,
            $sideColor,
            $divisionsHeading,
            Scenario::ALLIED_SIDE === $side ? $shipQuantity->getAlliedBig() : $shipQuantity->getAxisBig()
        ) . PHP_EOL;

        // Generate division content
        $divisions .= $this->getDivisionShips(
            $this->currentDivisionIndex,
            $ships,
            ShipProvider::BIG_SHIPS_TYPES,
            $side,
            $divisionsHeading,
            $year
        );

        $this->currentDivisionIndex++;

        // If we do not need small ships let's stop
        if (
            Scenario::ALLIED_SIDE === $side && 0 === $shipQuantity->getAlliedSmall()
            || Scenario::AXIS_SIDE === $side && 0 === $shipQuantity->getAxisSmall()
        ) {
            return $divisions;
        }

        // Division with small ships
        $divisions .= $this->getDivisionData(
            $this->currentDivisionIndex,
            $sideColor,
            $divisionsHeading,
            Scenario::ALLIED_SIDE === $side ? $shipQuantity->getAlliedSmall() : $shipQuantity->getAxisSmall()
        ) . PHP_EOL;

        // Generate division content
        $divisions .= $this->getDivisionShips($this->currentDivisionIndex, $ships, ['DD'], $side, $divisionsHeading, $year);
        $this->currentDivisionIndex++;

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

    /**
     * @param string[][][][] $ships
     * @param string[]       $authorizedTypes
     */
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

    /** @param string[] $ship */
    private function decorateLine(string $line, array $ship, string $navy, int $year, string $side, int $divisionsHeading): string
    {
        $lineArray = explode('=', $line);

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
                $line = 'CREWQUALITY=' . $this->tools->getRandomCrewQuality();
                break;
            case 'CREWFATIGUE':
                $line = 'CREWFATIGUE=' . $this->tools->getRandomCrewFatigue();
                break;
            case 'NIGHTTRAINING':
                $line = 'NIGHTTRAINING=' . $this->tools->getRandomCrewNightTraining();
                break;
            case 'RADARTYPE':
                $line = 'RADARTYPE=' . $this->tools->getRandomRadarLevel($year, $navy);
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
}
