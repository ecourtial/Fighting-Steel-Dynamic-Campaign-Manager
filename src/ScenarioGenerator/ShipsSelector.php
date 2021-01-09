<?php

declare(strict_types=1);

namespace App\ScenarioGenerator;

use App\Core\Tas\Scenario\Scenario;
use Wizaplace\Etl\Extractors\Csv as CsvExtractor;

class ShipsSelector
{
    public const BIG_SHIPS_TYPES = ['BB', 'BC', 'CA', 'CL'];

    private CsvExtractor $csvExtractor;

    public function __construct(CsvExtractor $csvExtractor)
    {
        $this->csvExtractor = $csvExtractor;
    }

    /** @return string[] */
    public function getShips(
        string $code,
        int $period,
        ShipQuantity $shipQuantity,
        bool $mixedNavies
    ): array {
        $allied = ScenarioEnv::SELECTOR[$code]['periods'][$period][Scenario::ALLIED_SIDE];
        $axis = ScenarioEnv::SELECTOR[$code]['periods'][$period][Scenario::AXIS_SIDE];

        shuffle($allied);
        shuffle($axis);

        // Only one navy per side
        if (false === $mixedNavies) {
            $allied = [array_rand($allied)];
            $axis = [array_rand($axis)];
        }

        return [
            Scenario::ALLIED_SIDE => $this->selectShips(
                $shipQuantity->getAlliedBig(),
                $shipQuantity->getAlliedSmall(),
                $allied
            ),
            Scenario::AXIS_SIDE => $this->selectShips(
                $shipQuantity->getAxisBig(),
                $shipQuantity->getAxisSmall(),
                $axis
            ),
        ];
    }

    private function selectShips(int $bigShipCount, int $destroyerCount, array $sides): array
    {
        $shipDictionary = $this->getShipDictionary();
        $ships = [];

        // 1- Extract big ships
        for ($count = 0; $count < $bigShipCount; $count++) {
            $side = array_rand($sides);
            $type = array_rand(static::BIG_SHIPS_TYPES);

            /**
             * The Regia Marina did not have any BC
             * OR
             * No more BC available (very few in the game)
             */
            if (false === array_key_exists($type, $ships[$side])) {
                $type = array_rand(['BB', 'CA', 'CL']);
            }

            if (false === array_key_exists($side, $ships)) {
                $ships[$side] = [];
            }

            $ship = array_rand($shipDictionary[$side][$type]);
            $ships[$side][] = $ship;
            unset($shipDictionary[$side][$type][$ship['name']]);
            if ([] === $shipDictionary[$side][$type]) {
                unset($shipDictionary[$side][$type]);
            }
        }

        // 2- Extract DDs
        for ($count = 0; $count < $destroyerCount; $count++) {
            $side = array_rand($sides);
            $ship = array_rand($shipDictionary[$side]['DD']);
            $ships[$side][] = $ship;
            unset($shipDictionary[$side]['DD'][$ship['name']]);
        }

        return $ships;
    }

    /** @return string[][][] */
    private function getShipDictionary(): array
    {
        static $ships = [];

        if ([] !== $ships) {
            return $ships;
        }

        $this->csvExtractor
            ->input('Data' . DIRECTORY_SEPARATOR . 'FSP10.3_Ship_List.csv')
            ->options(['delimiter' => ';', 'throwError' => true]);

        foreach ($this->csvExtractor->extract() as $row) {
            /* @var \Wizaplace\Etl\Row $row */
            $ship = $row->toArray();

            if ('Yes' === $ship['AvailableForRandom']) {
                $navy  = trim($ship['Navy']);
                $type  = trim($ship['Type']);
                $class = trim($ship['Class']);
                $name  = trim($ship['Name']);

                if (false === array_key_exists($navy, $ships)) {
                    $ships[$navy] = [];
                }

                if (false === array_key_exists($type, $ships[$navy])) {
                    $ships[$navy][$type] = [];
                }

                $ships[$navy][$type][$name] = ['name' => $name, 'class' => $class, 'type' => $type];
            }
        }

        return $ships;
    }
}
