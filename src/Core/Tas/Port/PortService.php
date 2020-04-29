<?php

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       23/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

declare(strict_types=1);

namespace App\Core\Tas\Port;

use App\Core\Exception\InvalidInputException;
use App\Core\File\IniReader;
use App\Core\Tas\Exception\MissingPortException;

class PortService
{
    private IniReader $iniReader;
    private string $tasScenarioDirectory;
    /** @var mixed[] */
    private array $data = [];

    public function __construct(IniReader $iniReader, string $tasScenarioDirectory)
    {
        $this->iniReader = $iniReader;
        $this->tasScenarioDirectory = $tasScenarioDirectory;
    }

    public function getPortFirstWaypoint(string $port): string
    {
        return $this->getPortData($port, 'FWP');
    }

    public function getPortData(string $port, string $key): string
    {
        $this->checkDataIsLoaded();
        $this->checkPortExists($port);
        $this->checkKeyExists($port, $key);

        return $this->data[$port][$key];
    }

    private function checkDataIsLoaded(): void
    {
        if ([] === $this->data) {
            $path = $this->tasScenarioDirectory . DIRECTORY_SEPARATOR . 'PortsConfig.cfg';
            $firstLine = true;
            $currentName = '';

            foreach ($this->iniReader->getData($path, false) as $element) {
                if ($firstLine === true) {
                    $this->checkVersion($element);
                    $firstLine = false;
                }

                if ('NAME' === $element['key']) {
                    $currentName = $element['value'];

                    continue;
                }

                if ('FWP' === $element['key'] && '' !== $currentName) {
                    $this->data[$currentName]['FWP'] = $element['value'];

                    continue;
                }
            }
        }
    }

    /**
     * Later, when mutliple version, turn it to getVersion()
     *
     * @param string[] $element
     */
    private function checkVersion(array $element): void
    {
        if ('VERSION' !== $element['key'] || 0 === preg_match('/^[1-9]{1}\.[0-9]{1}$/', $element['value'])) {
            throw new InvalidInputException("Invalid ports file version: '{$element['value']}'");
        }
    }

    private function checkPortExists(string $port): void
    {
        if (false === array_key_exists($port, $this->data)) {
            throw new MissingPortException($port);
        }
    }

    private function checkKeyExists(string $port, string $key): void
    {
        if (false === array_key_exists($key, $this->data[$port])) {
            throw new InvalidInputException("Unknown key '$key'");
        }
    }
}
