<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       23/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

namespace App\Core\Tas\Savegame;

use App\Core\Exception\CoreException;
use App\Core\Exception\InvalidInputException;
use App\Core\File\IniReader;

class SavegameReader
{
    private IniReader $iniReader;

    public function __construct(IniReader $iniReader)
    {
        $this->iniReader = $iniReader;
    }

    public function extract(string $scenarioDirectory): Savegame
    {
        $path = $scenarioDirectory . DIRECTORY_SEPARATOR . 'ScenarioInfo.cfg';
        $data = $this->initData();
        $lastKey = array_key_last($data);

        foreach ($this->iniReader->getData($path) as $line) {
            if (array_key_exists($line['key'], $data)) {
                $data[$line['key']] = $line['value'];
            }

            if ($line['key'] === $lastKey) {
                $this->validateData($data, $path);
                $save = new Savegame($data);
                $save->setPath($scenarioDirectory);

                return $save;
            }
        }

        throw new CoreException("Error while parsing the scenario '$path'");
    }

    private function initData(): array
    {
        $data = Savegame::FIELDS_NAME;
        $data = array_flip($data);
        foreach ($data as &$element) {
            $element = '';
        }
        unset($element);

        return $data;
    }

    private function validateData(array $data, string $path): void
    {
        foreach ($data as $key => $entry) {
            if ('' === $entry) {
                throw new InvalidInputException("Error while parsing the scenario '$path': element '$key' is empty");
            }
        }
    }
}
