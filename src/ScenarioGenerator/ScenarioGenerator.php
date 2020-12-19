<?php

declare(strict_types=1);

namespace App\ScenarioGenerator;

use App\ScenarioGenerator\Exception\TheaterNotFoundException;

class ScenarioGenerator
{
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
            throw new TheaterNotFoundException("The theater '{$code}' does not exist.");
        }

        if (false === array_key_exists($period, ScenarioEnv::SELECTOR[$code]['periods'])) {
            throw new TheaterNotFoundException("The period '{$period}' does not exist for this theater.");
        }

        $year = $this->getYear($code, $period);
        $month = $this->getMonth($code, $period, $year);
        $ships = $this->shipsSelector->getShips($code, $period, $this->getShipsQuantities(), $mixedNavies);
        $header = $this->contextGenerator->getHeaderData($year, $month);
        //$this->outputData($header, $ships);
    }

    /** @return int[] */
    private function getShipsQuantities(): array
    {
        return [\random_int(2, 8), \random_int(2, 8)];
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
