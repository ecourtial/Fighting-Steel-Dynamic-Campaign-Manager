<?php

declare(strict_types=1);

namespace App\ScenarioGenerator\Engine\Ships;

use App\NameSwitcher\Dictionary\DictionaryReader;

class DictionaryExtractor
{
    private DictionaryReader $dictionaryReader;

    /** @var string[][][][] */
    private array $dictionary = [];

    public function __construct(DictionaryReader $dictionaryReader)
    {
        $this->dictionaryReader = $dictionaryReader;
    }

    /** @return  string[][][][] */
    public function getShipDictionary(string $filename): array
    {
        if ([] !== $this->dictionary) {
            return $this->dictionary;
        }

        foreach ($this->dictionaryReader->extractData($filename) as $ship) {
            if ('Yes' === $ship['AvailableForRandom']) {
                $navy = trim($ship['Navy']);
                $type = trim($ship['Type']);
                $class = trim($ship['Class']);
                $name = trim($ship['Name']);

                if (false === array_key_exists($navy, $this->dictionary)) {
                    $this->dictionary[$navy] = [];
                }

                if (false === array_key_exists($type, $this->dictionary[$navy])) {
                    $this->dictionary[$navy][$type] = [];
                }

                $this->dictionary[$navy][$type][$name] = ['name' => $name, 'class' => $class, 'type' => $type];
            }
        }

        return $this->dictionary;
    }
}
