<?php

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       23/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

declare(strict_types=1);

namespace App\Core\Tas\Savegame\Fleet;

use App\Core\Exception\InvalidInputException;
use App\Core\Tas\Port\PortService;
use App\Core\Tas\Savegame\Savegame;
use App\Core\Tas\Ship\Ship;

class FleetUpdater
{
    public const TO_PORT_ACTION = 'to_port';
    public const AT_SEA_ACTION = 'at_sea';
    public const DETACH_ACTION = 'detach';

    private PortService $portService;

    public function __construct(PortService $portService)
    {
        $this->portService = $portService;
    }

    /**
     * @param string[] $ships
     * @param mixed[]  $params
     */
    public function action(
        Savegame $savegame,
        string $action,
        array $ships,
        array $params = []
    ): void {
        if ([] === $ships) {
            return;
        }

        switch ($action) {
            case static::AT_SEA_ACTION:
                $this->putAtSea($savegame, $ships, $params);
                break;
            case static::TO_PORT_ACTION:
                $this->putInPort($savegame, $ships, $params);
                break;
            case static::DETACH_ACTION:
                $this->detach($savegame, $ships, $params);
                break;
            default:
                throw new InvalidInputException("Unknown ship action: '$action'");
        }
    }

    /**
     * @param string[] $ships
     * @param string[] $params
     */
    private function putAtSea(Savegame $savegame, array $ships, array $params): void
    {
        $side = $this->validateSameData($savegame, $ships, 'SIDE');
        $port = $this->validateSameData($savegame, $ships, 'LOCATION');
        $shipsToMove = [];

        // Remove the ship from port
        foreach ($ships as $ship) {
            $shipsToMove[$ship] = $savegame->getNavalData()->getShipData($ship);
            $shipsToMove[$ship]['LOCATION'] = '';
            unset($shipsToMove[$ship]['SIDE']);
            $savegame->getNavalData()->removeShipInPort($ship, $side);
        }

        // Create the fleet
        $tfId = 'TF' . ($savegame->getNavalData()->getMaxTfNumber($side) + 1);
        $fleet = $this->createFleet($this->portService->getPortFirstWaypoint($port), $tfId, $params);

        // Create the division
        $this->createDivision($savegame, $fleet, $side, $shipsToMove);

        // Update the savegame
        $savegame->getNavalData()->addFleet($side, $fleet);
        $savegame->getNavalData()->incrementMaxTfNumber($side);
        $savegame->getNavalData()->setShipsDataChanged($side, true);
    }

    /**
     * You can detach any ship from the same FLEET
     *
     * @param string[] $ships
     * @param array[]  $params
     */
    private function detach(Savegame $savegame, array $ships, array $params): void
    {
        $side = $this->validateSameData($savegame, $ships, 'SIDE');
        $this->validateSameData($savegame, $ships, 'FLEET');
        $shipsToMove = [];
        $fleetsToCheck = [];

        // Create the new fleet
        $tfId = 'TF' . ($savegame->getNavalData()->getMaxTfNumber($side) + 1);
        $location = $params['waypoints'][0];
        $fleet = $this->createFleet($location, $tfId, $params);

        // Process the ships
        foreach ($ships as $ship) {
            // Get data
            $shipData = $savegame->getNavalData()->getShipData($ship);

            if (false === in_array($shipData['FLEET'], $fleetsToCheck, true)) {
                $fleetsToCheck[] = $shipData['FLEET'];
            }

            $shipsToMove[$ship] = $savegame->getNavalData()->getFleets($side)[$shipData['FLEET']]
                ->getFleetData()->getShipDataFromDivision($shipData['DIVISION'], $ship);

            // Remove from former fleet-division
            $savegame->getNavalData()
                ->getFleets($side)[$shipData['FLEET']]
                ->getFleetData()
                ->removeShipFromDivision($shipData['DIVISION'], $ship);
        }

        // Create the division
        $this->createDivision($savegame, $fleet, $side, $shipsToMove);

        // cleanup fleets and divisions
        $this->cleanUpFleetsAndDivisions($fleetsToCheck, $side, $savegame);

        // Update the savegame
        $savegame->getNavalData()->addFleet($side, $fleet);
        $savegame->getNavalData()->incrementMaxTfNumber($side);
        $savegame->getNavalData()->setShipsDataChanged($side, true);
    }

    /**
     * You can put in port any ship from the same FLEET
     *
     * @param string[] $ships
     * @param array[]  $params
     */
    private function putInPort(Savegame $savegame, array $ships, array $params): void
    {
        $side = $this->validateSameData($savegame, $ships, 'SIDE');
        $this->validateSameData($savegame, $ships, 'LOCATION');
        $fleetsToCheck = [];

        foreach ($ships as $ship) {
            // Prepare and update data
            $shipData = $savegame->getNavalData()->getShipData($ship);

            if (false === in_array($shipData['FLEET'], $fleetsToCheck, true)) {
                $fleetsToCheck[] = $shipData['FLEET'];
            }

            $newShipData = $savegame->getNavalData()
                ->getFleets($side)[$shipData['FLEET']]
                ->getFleetData()
                ->getShipDataFromDivision(
                    $shipData['DIVISION'],
                    $ship
                );
            $newShipData['LOCATION'] = $params['port'];
            $newShipData['SIDE'] = $side;

            $savegame->getNavalData()->setShipData($ship, $newShipData);
            unset($newShipData['SIDE']);

            // Remove her from her division
            $savegame->getNavalData()->getFleets($side)[$shipData['FLEET']]->getFleetData()->removeShipFromDivision(
                $shipData['DIVISION'],
                $ship
            );

            // Put her in port
            $shipsInPort = $savegame->getNavalData()->getShipsInPort($side);
            $shipsInPort[$ship] = $newShipData;
            $savegame->getNavalData()->setShipsInPort($side, $shipsInPort);
        }

        // cleanup fleets and divisions
        $this->cleanUpFleetsAndDivisions($fleetsToCheck, $side, $savegame);

        // Update the savegame
        $savegame->getNavalData()->setShipsDataChanged($side, true);
    }

    /** @param string[] $ships */
    private function validateSameData(Savegame $savegame, array $ships, string $key): string
    {
        $value = '';

        foreach ($ships as $ship) {
            $currentValue = $savegame->getNavalData()->getShipData($ship)[$key];
            if ('' === $value) {
                $value = $currentValue;

                continue;
            }

            if ($currentValue !== $value) {
                throw new InvalidInputException('Ships must be on the same ' . $key);
            }
        }

        return $value;
    }

    /** @param mixed[] $params */
    private function createFleet(string $location, string $tfId, array $params): TaskForce
    {
        $fleet = new TaskForce($tfId);
        $fleet->setLl($location);
        $fleet->setCaseCount('1');
        $fleet->setProb('100');
        $fleet->setName($tfId);
        $fleet->setMission($params['mission']);
        $fleet->setSpeed($params['speed']);

        foreach ($params['waypoints'] as $waypoint) {
            $fleet->addWaypoint($waypoint);
        }

        return $fleet;
    }

    /** @param string[][] $shipsToMove */
    private function createDivision(
        Savegame $savegame,
        TaskForce $fleet,
        string $side,
        array $shipsToMove
    ): string {
        $division = $fleet->getId() . 'DIVISION0';

        foreach ($shipsToMove as $ship => $shipData) {
            unset($shipData['LOCATION']);
            foreach ($shipData as $key => $value) {
                $fleet->getFleetData()->addDataToShipInDivision($division, $ship, $key, $value);
            }
            $savegame->getNavalData()->setShipData(
                $ship,
                [
                    'LOCATION' => $fleet->getLl(),
                    'SIDE' => $side,
                    'FLEET' => $fleet->getId(),
                    'DIVISION' => $division,
                ]
            );
        }

        return $division;
    }

    /** @param string[] $fleetsToCheck */
    private function cleanUpFleetsAndDivisions(array $fleetsToCheck, string $side, Savegame $savegame): void
    {
        // Disband any empty division and any empty TF
        foreach ($fleetsToCheck as $fleet) {
            foreach ($savegame->getNavalData()->getFleets($side)[$fleet]->getFleetData()->getDivisions() as $divisionName => $division) {
                if ([] == $division) {
                    $savegame->getNavalData()->getFleets($side)[$fleet]->getFleetData()->removeDivision($divisionName);
                }
            }

            if ($savegame->getNavalData()->getFleets($side)[$fleet]->getFleetData()->getDivisions() == []) {
                $savegame->getNavalData()->removeFleet($side, $fleet);
            }
        }
    }
}
