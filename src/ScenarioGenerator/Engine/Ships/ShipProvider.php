<?php

declare(strict_types=1);

namespace App\ScenarioGenerator\Engine\Ships;

class ShipProvider
{
    public const DATA_SOURCE_DICTIONARY = 'FSP10.3_Ship_List.csv';
    public const BIG_SHIPS_TYPES = ['BB', 'BC', 'CA', 'CL'];

    private DictionaryExtractor $dictionaryExtractor;
    private string $dictionaryFile;

    /** @var string[][][][] */
    private array $ships = [];

    public function __construct(DictionaryExtractor $dictionary, string $projectRootDir)
    {
        $this->dictionaryExtractor = $dictionary;
        $this->dictionaryFile = $projectRootDir . DIRECTORY_SEPARATOR . 'src'
            . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR . static::DATA_SOURCE_DICTIONARY;
    }

    /** @return string[] */
    public function getBigShip(string $side): array
    {
        if ([] === $this->ships) {
            $this->ships = $this->dictionaryExtractor->getShipDictionary($this->dictionaryFile);
        }

        $types = static::BIG_SHIPS_TYPES;
        shuffle($types);

        /**
         * The Regia Marina did not have any BC
         * OR
         * in the game, the french do not have CL
         * OR
         * no more BC available (very few in the game)
         */
        $selectedType = null;

        foreach ($types as $type) {
            if (true === array_key_exists($type, $this->ships[$side])) {
                $selectedType = $type;

                break;
            }
        }

        if (null === $selectedType) {
            throw new \LogicException("Impossible to find any big ship for the navy '$side'");
        }

        $shipSubset = $this->ships[$side][$selectedType];
        $ship = $shipSubset[array_rand($shipSubset)];
        unset($this->ships[$side][$selectedType][$ship['name']]);

        if ([] === $this->ships[$side][$selectedType]) {
            unset($this->ships[$side][$selectedType]);
        }

        return $ship;
    }

    /** @return string[] */
    public function getDestroyer(string $side): array
    {
        $shipSubset = $this->ships[$side]['DD'];
        $ship = $shipSubset[array_rand($shipSubset)];
        unset($this->ships[$side]['DD'][$ship['name']]);

        return $ship;
    }
}
