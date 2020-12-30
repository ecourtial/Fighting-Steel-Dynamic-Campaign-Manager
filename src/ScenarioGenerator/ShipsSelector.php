<?php

declare(strict_types=1);

namespace App\ScenarioGenerator;

use App\Core\Tas\Scenario\Scenario;
use Wizaplace\Etl\Extractors\Csv as CsvExtractor;

class ShipsSelector
{
    private CsvExtractor $csvExtractor;

    public function __construct(CsvExtractor $csvExtractor)
    {
        $this->csvExtractor = $csvExtractor;
    }

    /** @return string[] */
    public function getShips(string $code, int $period, array $shipQuantities, bool $mixedNavies): array
    {
        $allied = ScenarioEnv::SELECTOR[$code]['periods'][$period][Scenario::ALLIED_SIDE];
        $axis = ScenarioEnv::SELECTOR[$code]['periods'][$period][Scenario::AXIS_SIDE];

        shuffle($allied);
        shuffle($axis);

        // Only one navy per side
        if (false === $mixedNavies) {
            $allied = [array_rand($allied)];
            $axis = [array_rand($axis)];
        }

        $alliedBigShipCount = $this->getBigShipCount($shipQuantities[Scenario::ALLIED_SIDE]);
        $axisBigShipCount = $this->getBigShipCount($shipQuantities[Scenario::AXIS_SIDE]);

        return [
            Scenario::ALLIED_SIDE => $this->selectShips(
                $alliedBigShipCount,
                $shipQuantities[Scenario::ALLIED_SIDE] - $alliedBigShipCount,
                $allied
            ),
            Scenario::AXIS_SIDE => $this->selectShips(
                $axisBigShipCount,
                $shipQuantities[Scenario::AXIS_SIDE] - $axisBigShipCount,
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
            $type = array_rand(['BB', 'BC', 'CA', 'CL']);

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

                $ships[$navy][$type][$name] = ['name' => $name, 'class' => $class];
            }
        }

        return $ships;
    }
}
