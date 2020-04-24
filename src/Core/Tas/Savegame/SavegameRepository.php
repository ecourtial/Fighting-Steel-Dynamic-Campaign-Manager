<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       23/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

namespace App\Core\Tas\Savegame;

use App\Core\Exception\InvalidInputException;
use App\Core\Tas\Savegame\Fleet\Fleet;
use App\Core\Tas\Savegame\Fleet\FleetExtractor;
use App\Core\Tas\Scenario\Scenario;

class SavegameRepository
{
    private SavegameReader $savegameReader;
    private FleetExtractor $fleetExtractor;
    private string $tasDirectory;

    public function __construct(
        SavegameReader $savegameReader,
        FleetExtractor $fleetExtractor,
        string $tasDirectory
    ) {
        $this->savegameReader = $savegameReader;
        $this->fleetExtractor = $fleetExtractor;
        $this->tasDirectory = $tasDirectory;
    }

    /** @return string[] */
    public function getList(): array
    {
        /**
         * Will return an array of string for the available savegames, on the following pattern:
         * 'tasScenarioName' => 'saveX' where X is the number of the slot.
         */
        $saveGames = [];
        $folderContent = scandir($this->tasDirectory);

        foreach ($folderContent as $element) {
            $saveGameFullPath = $this->tasDirectory . DIRECTORY_SEPARATOR . $element;
            if (
                is_dir($saveGameFullPath)
                && preg_match(Savegame::PATH_REGEX, $element)
            ) {
                try {
                    $saveGame = $this->savegameReader->extract($saveGameFullPath);
                    $saveGames[$saveGame->getScenarioName()] = $element;
                } catch (\Throwable $exception) {
                    // Log
                }
            }
        }

        return $saveGames;
    }

    // By default return only the object with its metadata
    public function getOne(string $key, bool $fullData = false): Savegame
    {
        if (0 === preg_match(Savegame::PATH_REGEX, $key)) {
            throw new InvalidInputException("Savegame key '$key' is not a valid format");
        }

        $path = $this->tasDirectory . DIRECTORY_SEPARATOR . $key;
        $save = $this->savegameReader->extract($path);

        if ($fullData) {
            $axisShipsInPort = $this->fleetExtractor->getShipsInPort($path, Scenario::AXIS_SIDE);
            $alliedShipsInPort = $this->fleetExtractor->getShipsInPort($path, Scenario::ALLIED_SIDE);
            $axisFleets = $this->fleetExtractor->extractFleets($path, Scenario::AXIS_SIDE);
            $alliedFleets = $this->fleetExtractor->extractFleets($path, Scenario::ALLIED_SIDE);

            $save->setAxisShipsInPort($axisShipsInPort);
            $save->setAxisShipsAtSea($axisFleets);
            $save->setAlliedShipsInPort($alliedShipsInPort);
            $save->setAlliedShipsAtSea($alliedFleets);

            // Location and other data
            $data = [];
            $this->getLocationsFromShipsInport(Scenario::AXIS_SIDE, $axisShipsInPort, $data);
            $this->getLocationsFromShipsInport(Scenario::ALLIED_SIDE, $alliedShipsInPort, $data);

            $this->getLocationsFromFleets(Scenario::AXIS_SIDE, $axisFleets, $data);
            $this->getLocationsFromFleets(Scenario::ALLIED_SIDE, $alliedFleets, $data);

            $save->setShipsData($data);
        }

        return $save;
    }

    /**
     * @param Fleet[]    $fleets
     * @param string[][] $data
     */
    private function getLocationsFromShipsInport(string $side, array $ships, array &$data)
    {
        foreach ($ships as $ship => $location) {
            $data[$ship] = [
                'location' => $location,
                'side' => $side,
            ];
        }
    }

    /**
     * @param Fleet[]    $fleets
     * @param string[][] $data
     */
    private function getLocationsFromFleets(string $side, array $fleets, array &$data)
    {
        foreach ($fleets as $fleet) {
            $currentLocation = $fleet->getLl();
            foreach ($fleet->getDivisions() as $divisionName => $division) {
                foreach ($division as $ship) {
                    $data[$ship] = [
                        'location' => $currentLocation,
                        'fleet' => $fleet->getId(),
                        'division' => $divisionName,
                        'side' => $side,
                    ];
                }
            }
        }
    }
}
