<?php

declare(strict_types=1);

namespace App\ScenarioGenerator;

use App\Core\Tas\Scenario\Scenario;
use App\Core\File\TextFileReader;

class BodyGenerator
{
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

    private function getRadarLevel(int $year, string $navy): string
    {


//        public const GB = 'RN';
//        public const GE = 'KM';
//        public const FR = 'MN';
//        public const IT = 'RM';
//        public const JP = 'IJN';
//        public const US = 'USN';

        switch ($navy) {
            case ScenarioEnv::GB :

                break;
            case ScenarioEnv::GE :
                break;
            case ScenarioEnv::FR :
                break;
            case ScenarioEnv::IT :
                break;
            case ScenarioEnv::JP :
                break;
            case ScenarioEnv::US :
                break;
            default: throw new \InvalidArgumentException("Unknown navy '$navy'");
        }
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

        // remplacer le premier foreach par une fonction avec side en param
        foreach (Scenario::SIDES as $side) {
            foreach ($ships[$side] as $navy => $type) {
                foreach ($ships[$side][$navy][$type] as $ship) {
                    /**
                     * Data to handle :
                     * X, Y et Z position
                     * Ship name
                     * Ship shortname
                     * CREWQUALITY // random
                     * CREWFATIGUE // random
                     * NIGHTTRAINING // defined
                     * RADARTYPE
                     *
                     * using preg_replace ?
                     */
                    $shipData = '';
                    foreach ($this->textFileReader->getFileContent(
                        'blablba' . $navy . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $ship['class'] . '.txt'
                    ) as $line) {
                        $shipData .= $this->decorateLine($line, $ship, $navy, $year) . PHP_EOL;
                    }

                }
            }
        }


        return $body;
    }

    private function decorateLine(string $line, array $ship, string $navy, int $year): string
    {

    }
}
