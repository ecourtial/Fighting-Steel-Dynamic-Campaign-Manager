<?php

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       23/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

declare(strict_types=1);

namespace App\Core\Tas\Savegame;

use App\Core\Exception\InvalidInputException;
use App\Core\Tas\Savegame\Fleet\FleetExtractor;
use App\Core\Tas\Savegame\Fleet\FleetWriter;
use App\Core\Tas\Savegame\Fleet\TaskForce;
use App\Core\Tas\Scenario\Scenario;
use Psr\Log\LoggerInterface;

class SavegameRepository
{
    private SavegameReader $savegameReader;
    private FleetExtractor $fleetExtractor;
    private FleetWriter $fleetWriter;
    private string $tasDirectory;
    private LoggerInterface $logger;

    public function __construct(
        SavegameReader $savegameReader,
        FleetExtractor $fleetExtractor,
        FleetWriter $fleetWriter,
        string $tasDirectory,
        LoggerInterface $logger
    ) {
        $this->savegameReader = $savegameReader;
        $this->fleetExtractor = $fleetExtractor;
        $this->tasDirectory = $tasDirectory;
        $this->fleetWriter = $fleetWriter;
        $this->logger = $logger;
    }

    /** @return string[] */
    public function getList(): array
    {
        /**
         * Will return an array of string for the available savegames, on the following pattern:
         * 'tasScenarioName' => 'SaveX' where X is the number of the slot.
         */
        $saveGames = [];
        $folderContent = scandir($this->tasDirectory);

        foreach ($folderContent as $element) {
            $saveGameFullPath = $this->tasDirectory . DIRECTORY_SEPARATOR . $element;

            if (preg_match(Savegame::PATH_REGEX, $element)) {
                try {
                    $saveGame = $this->savegameReader->extract($saveGameFullPath);
                    $saveGames[$saveGame->getScenarioName()] = $element;
                } catch (\Throwable $exception) {
                    // We don't want any error to pop up since SaveGames and Scenarios are in the same folder
                    $this->logger->error(
                        "Error when trying to load the savegame '$element'. Error was: " . $exception->getMessage()
                    );
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

            $save->getNavalData()->setShipsInPort('Axis', $axisShipsInPort);
            $save->getNavalData()->setShipsAtSea('Axis', $axisFleets);
            $save->getNavalData()->setShipsInPort('Allied', $alliedShipsInPort);
            $save->getNavalData()->setShipsAtSea('Allied', $alliedFleets);

            // Location and other data
            $data = [];
            $this->getLocationsFromShipsInPort(Scenario::AXIS_SIDE, $axisShipsInPort, $data);
            $this->getLocationsFromShipsInPort(Scenario::ALLIED_SIDE, $alliedShipsInPort, $data);

            $this->getLocationsFromFleets(Scenario::AXIS_SIDE, $axisFleets, $data);
            $this->getLocationsFromFleets(Scenario::ALLIED_SIDE, $alliedFleets, $data);

            $save->getNavalData()->setShipsData($data);
        }

        return $save;
    }

    public function persist(Savegame $savegame): void
    {
        if ($savegame->getNavalData()->isShipsDataChanged(Scenario::ALLIED_SIDE)) {
            $this->fleetWriter->update($savegame, Scenario::ALLIED_SIDE);
        }

        if ($savegame->getNavalData()->isShipsDataChanged(Scenario::AXIS_SIDE)) {
            $this->fleetWriter->update($savegame, Scenario::AXIS_SIDE);
        }
    }

    /**
     * @param string[][] $ships
     * @param string[][] $data
     */
    private function getLocationsFromShipsInPort(string $side, array $ships, array &$data): void
    {
        foreach ($ships as $ship => $info) {
            $info['SIDE'] = $side;
            $data[$ship] = $info;
        }
    }

    /**
     * @param TaskForce[] $fleets
     * @param string[][]  $data
     */
    private function getLocationsFromFleets(string $side, array $fleets, array &$data): void
    {
        foreach ($fleets as $fleet) {
            $currentLocation = $fleet->getLl();
            foreach ($fleet->getFleetData()->getDivisions() as $divisionName => $division) {
                foreach ($division as $ship => $shipData) {
                    $data[$ship] = [
                        'LOCATION' => $currentLocation,
                        'FLEET' => $fleet->getId(),
                        'DIVISION' => $divisionName,
                        'SIDE' => $side,
                    ];
                }
            }
        }
    }
}
