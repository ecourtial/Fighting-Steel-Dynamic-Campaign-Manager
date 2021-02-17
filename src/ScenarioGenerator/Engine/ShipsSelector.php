<?php

declare(strict_types=1);

namespace App\ScenarioGenerator\Engine;

use _HumbugBox09702017065e\Symfony\Component\Console\Exception\LogicException;
use App\Core\Tas\Scenario\Scenario;


class ShipsSelector
{
    public const BIG_SHIPS_TYPES = ['BB', 'BC', 'CA', 'CL'];
    public const DATA_SOURCE_DICTIONARY = 'FSP10.3_Ship_List.csv';

    private DictionaryExtractor $dictionaryExtractor;
    private string $dictionaryFile;

    public function __construct(DictionaryExtractor $dictionary, string $projectRootDir)
    {
        $this->dictionaryExtractor = $dictionary;
        $this->dictionaryFile = $projectRootDir . DIRECTORY_SEPARATOR . 'src'
            . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR . static::DATA_SOURCE_DICTIONARY;
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
            $allied = [$allied[array_rand($allied)]];
            $axis = [$axis[array_rand($axis)]];
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
        $shipDictionary = $this->dictionaryExtractor->getShipDictionary($this->dictionaryFile);
        $ships = [];

        // 1- Extract big ships
        for ($count = 0; $count < $bigShipCount; $count++) {
            $side = $sides[array_rand($sides)];

            $types = static::BIG_SHIPS_TYPES;
            $type = $types[array_rand($types)];


            /**
             * The Regia Marina did not have any BC
             * OR
             * In the game, the french do not have CL
             * OR
             * No more BC available (very few in the game)
             */
            if (false === array_key_exists($type, $shipDictionary[$side])) {
                $type = null;
                while (null === $type) {
                    $tmpTypes = $types;
                    $tmpTypes = array_flip($tmpTypes);
                    unset($tmpTypes[$type]);
                    $flipped = array_flip($tmpTypes);
                    $type = $flipped[array_rand($flipped)];
                    if (false === array_key_exists($type, $shipDictionary[$side])) {
                        $type = null;
                    }
                }
                if (null === $type) {
                    throw new LogicException("Impossible to find any valid ship for the navy '$side'");
                }
            }

            if (false === array_key_exists($side, $ships)) {
                $ships[$side] = [];
            }

            $shipSubset = $shipDictionary[$side][$type];
            $ship = $shipSubset[array_rand($shipSubset)];

            $ships[$side][] = $ship;
            unset($shipDictionary[$side][$type][$ship['name']]);
            if ([] === $shipDictionary[$side][$type]) {
                unset($shipDictionary[$side][$type]);
            }
        }

        // 2- Extract DDs
        for ($count = 0; $count < $destroyerCount; $count++) {
            $side = $sides[array_rand($sides)];
            $shipSubset = $shipDictionary[$side]['DD'];
            $ship = $shipSubset[array_rand($shipSubset)];
            $ships[$side][] = $ship;
            unset($shipDictionary[$side]['DD'][$ship['name']]);
        }

        return $ships;
    }
}
