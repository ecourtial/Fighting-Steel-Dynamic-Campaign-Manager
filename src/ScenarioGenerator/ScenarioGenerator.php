<?php

declare(strict_types=1);

namespace App\ScenarioGenerator;

use App\Core\Tas\Scenario\Scenario;

class ScenarioGenerator
{
    private const DATE_PATTERN = 'Y-m-d-H-i-s';

    private ContextGenerator $contextGenerator;
    private ShipsSelector $shipsSelector;

    public function __construct(ContextGenerator $contextGenerator, ShipsSelector $shipsSelector)
    {
        $this->contextGenerator = $contextGenerator;
        $this->shipsSelector = $shipsSelector;
    }

    public function generate(string $code, int $period, bool $mixedNavies)
    {
        if (false === array_key_exists($code, ScenarioEnv::SELECTOR)) {
            throw new \InvalidArgumentException("The theater '{$code}' does not exist.");
        }

        if (false === array_key_exists($period, ScenarioEnv::SELECTOR[$code]['periods'])) {
            throw new \InvalidArgumentException("The period '{$period}' does not exist for this theater.");
        }

        $scenarioName = 'randomScenar' . date(static::DATE_PATTERN);
        $year = $this->getYear($code, $period);
        $month = $this->getMonth($code, $period, $year);
        $ships = $this->shipsSelector->getShips($code, $period, $this->getShipsQuantities(), $mixedNavies);
        $header = $this->contextGenerator->getHeaderData($year, $month, $scenarioName);

        // Use external class to create the content before...
        // Reminder : according to the country, the year, replace the radar setting in the ship
        //$this->outputData($header, $ships);
    }

    /** @return int[] */
    private function getShipsQuantities(): array
    {
        return [Scenario::ALLIED_SIDE => \random_int(2, 8), Scenario::AXIS_SIDE => \random_int(2, 8)];
    }

    private function getYear(string $code, int $period): int
    {
        return array_rand(array_keys(ScenarioEnv::SELECTOR[$code]['periods'][$period]['years']));
    }

    private function getMonth(string $code, int $period, int $year): int
    {
        return array_rand(ScenarioEnv::SELECTOR[$code]['periods'][$period]['years'][$year]);
    }
}
