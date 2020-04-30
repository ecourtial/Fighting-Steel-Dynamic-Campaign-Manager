<?php

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

declare(strict_types=1);

namespace App\NameSwitcher\Validator;

use App\Core\Tas\Scenario\Scenario;
use App\Core\Tas\Scenario\ScenarioRepository;
use App\NameSwitcher\Dictionary\Dictionary;
use App\NameSwitcher\Dictionary\DictionaryFactory;

class ScenarioValidator
{
    protected DictionaryValidator $dictionaryValidator;
    protected ScenarioRepository $scenarioRepository;
    protected DictionaryFactory $dictionaryFactory;

    public function __construct(
        DictionaryValidator $dictionaryValidator,
        ScenarioRepository $scenarioRepository,
        DictionaryFactory $dictionaryFactory
    ) {
        $this->dictionaryValidator = $dictionaryValidator;
        $this->scenarioRepository = $scenarioRepository;
        $this->dictionaryFactory = $dictionaryFactory;
    }

    /** @return string[] */
    public function validate(string $scenarioName): array
    {
        // Load the scenario data (performs a lot of controls...)
        try {
            $scenario = $this->scenarioRepository->getOneWillAllData($scenarioName);
        } catch (\Throwable $exception) {
            return [$exception->getMessage()];
        }

        // Validate the dictionary format and content
        $errors = $this->dictionaryValidator->validate($scenario->getDictionaryPath());

        if ([] !== $errors) {
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

        // Finally check that all the ships are present in the dictionary
        $dictionary = $this->dictionaryFactory->getDictionary($scenario->getDictionaryPath());
        $result = $this->checkTasShipInDictionary($scenario, $dictionary);
        $errors = array_merge($errors, $result);

        return $errors;
    }

    /**
     * Check that all the ships in TAS are present in the dictionary file
     *
     * @return string[]
     */
    private function checkTasShipInDictionary(
        Scenario $scenario,
        Dictionary $dictionary
    ): array {
        $errors = [];

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

        return $errors;
    }
}
