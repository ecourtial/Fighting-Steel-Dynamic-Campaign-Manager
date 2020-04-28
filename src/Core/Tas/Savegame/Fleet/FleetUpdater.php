<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       23/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

namespace App\Core\Tas\Savegame\Fleet;

use App\Core\Exception\InvalidInputException;
use App\Core\Tas\Map\MapService;
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
     * @param array[]  $params
     */
    private function putAtSea(Savegame $savegame, array $ships, array $params): void
    {
        $side = $this->validateSameData($savegame, $ships, 'SIDE');
        $port = $this->validateSameData($savegame, $ships, 'LOCATION');
        $shipsToMove = [];

        // Remove the ship from port
        foreach ($ships as $ship) {
            $shipsToMove[$ship] = $savegame->getShipData($ship);
            $shipsToMove[$ship]['LOCATION'] = '';
            unset($shipsToMove[$ship]['SIDE']);
            $savegame->removeShipInPort($ship, $side);
        }

        // Create the fleet
        $tfId = 'TF' . ($savegame->getMaxTfNumber($side) + 1);
        $location = $this->portService->getPortFirstWayPoint($port);
        $fleet = $this->createFleet($location, $tfId, $params);

        // Create the division
        $this->createDivision($savegame, $fleet, $side, $shipsToMove);

        // Update the savegame
        $savegame->addFleet($side, $fleet);
        $savegame->incrementMaxTfNumber($side);
        $savegame->setShipsDataChanged($side, true);
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
        $tfId = 'TF' . ($savegame->getMaxTfNumber($side) + 1);
        $location = array_values($params['waypoints'])[0];
        $fleet = $this->createFleet($location, $tfId, $params);

        // Process the ships
        foreach ($ships as $ship) {
            // Get data
            $shipData = $savegame->getShipData($ship);

            if (false === in_array($shipData['FLEET'], $fleetsToCheck, true)) {
                $fleetsToCheck[] = $shipData['FLEET'];
            }

            $shipsToMove[$ship] = $savegame->getFleets($side)[$shipData['FLEET']]
                ->getShipDataFromDivision($shipData['DIVISION'], $ship);

            // Remove from former fleet-division
            $savegame->getFleets($side)[$shipData['FLEET']]->removeShipFromDivision($shipData['DIVISION'], $ship);

            // Update data
            $savegame->setShipData($ship, $shipsToMove[$ship]);
        }

        // Create the division
        $this->createDivision($savegame, $fleet, $side, $shipsToMove);

        // cleanup fleets and divisions
        $this->cleanUpFleetsAndDivisions($fleetsToCheck, $side, $savegame);

        // Update the savegame
        $savegame->addFleet($side, $fleet);
        $savegame->incrementMaxTfNumber($side);
        $savegame->setShipsDataChanged($side, true);
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
            $shipData = $savegame->getShipData($ship);

            if (false === in_array($shipData['FLEET'], $fleetsToCheck, true)) {
                $fleetsToCheck[] = $shipData['FLEET'];
            }

            $newShipData = $savegame->getFleets($side)[$shipData['FLEET']]->getShipDataFromDivision(
                $shipData['DIVISION'],
                $ship
            );
            $newShipData['LOCATION'] = $params['port'];
            $newShipData['SIDE'] = $side;

            $savegame->setShipData($ship, $newShipData);
            unset($newShipData['SIDE']);


            // Remove her from her division
            $savegame->getFleets($side)[$shipData['FLEET']]->removeShipFromDivision(
                $shipData['DIVISION'],
                $ship
            );

            // Put her in port
            $shipsInPort = $savegame->getShipsInPort($side);
            $shipsInPort[$ship] = $newShipData;
            $savegame->setShipsInPort($side, $shipsInPort);
        }

        // cleanup fleets and divisions
        $this->cleanUpFleetsAndDivisions($fleetsToCheck, $side, $savegame);

        // Update the savegame
        $savegame->setShipsDataChanged($side, true);
    }

    private function validateSameData($savegame, array $ships, string $key): string
    {
        $value = '';

        foreach ($ships as $ship) {
            $currentValue = $savegame->getShipData($ship)[$key];
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


    private function createFleet(string $location, string $tfId, array $params): Fleet
    {
        $fleet = new Fleet();
        $fleet->setLl($location);
        $fleet->setCaseCount('1');
        $fleet->setProb('100');
        $fleet->setName($tfId);
        $fleet->setId($tfId);
        $fleet->setMission($params['mission']);
        $fleet->setSpeed($params['speed']);

        foreach ($params['waypoints'] as $waypoint) {
            $fleet->addWaypoint($waypoint);
        }

        return $fleet;
    }

    private function createDivision(
        Savegame $savegame,
        Fleet $fleet,
        string $side,
        array $shipsToMove
    ): void {
        $division = $fleet->getId() . 'DIVISION0';
        $fleet->addDivision($division);

        foreach ($shipsToMove as $ship => $shipData) {
            unset($shipData['LOCATION']);
            $fleet->addShipToDivision($division, $ship);
            foreach ($shipData as $key => $value) {
                $fleet->addDataToShipInDivision($division, $ship, $key, $value);
            }
            $savegame->setShipData(
                $ship,
                [
                    'LOCATION' => $fleet->getLl(),
                    'SIDE' => $side,
                    'FLEET' => $fleet->getId(),
                    'DIVISION' => $division,
                ]
            );
        }
    }

    private function cleanUpFleetsAndDivisions(array $fleetsToCheck, string $side, Savegame $savegame): void
    {
        // Disband any empty division and any empty TF
        foreach ($fleetsToCheck as $fleet) {
            foreach ($savegame->getFleets($side)[$fleet]->getDivisions() as $divisionName => $division) {
                if ([] == $division) {
                    $savegame->getFleets($side)[$fleet]->removeDivision($divisionName);
                }
            }

            if ($savegame->getFleets($side)[$fleet]->getDivisions() == []) {
                $savegame->removeFleet($side, $fleet);
            }
        }
    }
}
