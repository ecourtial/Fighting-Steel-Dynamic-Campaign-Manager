<?php

declare(strict_types=1);

/**
 * This class validate the FORMAT of a dictionary
 * THEN the all the crossed data are present in:
 * - the dictionary
 * - the TAS AlliedShips.cfg and AxisShips.cfg
 * - the .scn file of the scenario
 *
 * Remember that the lead file are the TAS AlliedShips.cfg and AxisShips.cfg
 *
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

namespace App\NameSwitcher\Validator;

use App\Core\Tas\Scenario\Scenario;
use App\Core\Tas\Scenario\ScenarioRepository;
use App\NameSwitcher\Model\Dictionary;
use App\NameSwitcher\Reader\DictionaryReader;

class ScenarioValidator
{
    protected DictionaryValidator $dictionaryValidator;
    protected ScenarioRepository $scenarioRepository;
    protected DictionaryReader $dictionaryReader;

    public function __construct(
        DictionaryValidator $dictionaryValidator,
        ScenarioRepository $scenarioRepository,
        DictionaryReader $dictionaryReader
    ) {
        $this->dictionaryValidator = $dictionaryValidator;
        $this->scenarioRepository = $scenarioRepository;
        $this->dictionaryReader = $dictionaryReader;
    }

    /** @return string[] */
    public function validate(string $scenarioName, string $dictionaryFullPath): array
    {
        // First validate the dictionary format and content
        $errors = $this->dictionaryValidator->validate($dictionaryFullPath);
        if ([] !== $errors) {
            return $errors;
        }

        // Store the dictionary content and initialize it
        $dictionaryContent = [];
        foreach ($this->dictionaryReader->extractData($dictionaryFullPath) as $entry) {
            $dictionaryContent[] = $entry;
        }
        $dictionary = new Dictionary($dictionaryContent);

        // Load the scenario data (performs a lot of controls...)
        try {
            $scenario = $this->scenarioRepository->getOneWillAllData($scenarioName);
        } catch (\Throwable $exception) {
            $errors[] = $exception->getMessage();
        }

        // Note that the second condition is because the logic is not understood by PHPStan
        if ([] !== $errors || false === isset($scenario)) {
            return $errors;
        }

        // Check that all the tas ships in TAS are present in the .scn file
        foreach (Scenario::SIDES as $side) {
            foreach ($scenario->getTasShips($side) as $ship) {
                if (false === array_key_exists($ship->getName(), $scenario->getFsShips())) {
                    $errors[] = "Tas Ship '{$ship->getName()}' is not present in the FS file";
                }
            }
        }

        $this->checkTasShipInDictionary($errors, $scenario, $dictionary);

        return $errors;
    }

    /**
     * Check that all the ships in TAS are present in the dictionary file
     *
     * @param string[] $errors
     */
    protected function checkTasShipInDictionary(
        array &$errors,
        Scenario $scenario,
        Dictionary $dictionary
    ): void {
        foreach (Scenario::SIDES as $side) {
            foreach ($scenario->getTasShips($side) as $ship) {
                if (
                    false === array_key_exists(
                        $ship->getName(),
                        $dictionary->getShipsList()
                    )
                ) {
                    $errors[] = "Tas Ship '{$ship->getName()}' is not present in the dictionary file";
                }
            }
        }
    }
}
