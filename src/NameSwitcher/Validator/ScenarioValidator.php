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

class ScenarioValidator
{
    private DictionaryValidator $dictionaryValidator;
    private ScenarioRepository $scenarioRepository;

    public function __construct(
        DictionaryValidator $dictionaryValidator,
        ScenarioRepository $scenarioRepository
    ) {
        $this->dictionaryValidator = $dictionaryValidator;
        $this->scenarioRepository = $scenarioRepository;
    }

    /** @return string[] */
    public function validate(string $scenarioName, string $dictionaryFullPath): array
    {
        // First validate the dictionary format and content
        $errors = $this->dictionaryValidator->validate($dictionaryFullPath);
        if ([] !== $errors) {
            return $errors;
        }

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
        foreach(Scenario::SIDES as $side) {
            foreach ($scenario->getTasShips($side) as $ship) {
                if (false === array_key_exists($ship->getName(), $scenario->getFsShips())) {
                    $errors[] = "Tas Ship '{$ship->getName()}' is not present in the FS file";
                }
            }
        }

        // Check that all the allied ships in TAS are present in the dictionary file

        // Check that all the axis ships in TAS are present in the dictionary file

        return $errors;
    }
}
