<?php

declare(strict_types=1);

namespace App\ScenarioGenerator\Engine\Ships;

use App\NameSwitcher\Dictionary\DictionaryReader;

class DictionaryExtractor
{
    private DictionaryReader $dictionaryReader;

    public function __construct(DictionaryReader $dictionaryReader)
    {
        $this->dictionaryReader = $dictionaryReader;
    }

    /** @return  string[][][][][] */
    public function getShipDictionary(string $filename): array
    {
        static $ships = [];

        if ([] !== $ships) {
            return $ships;
        }

        foreach ($this->dictionaryReader->extractData($filename) as $ship) {
            if ('Yes' === $ship['AvailableForRandom']) {
                $navy = trim($ship['Navy']);
                $type = trim($ship['Type']);
                $class = trim($ship['Class']);
                $name = trim($ship['Name']);

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
