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
use App\Core\Tas\Savegame\Savegame;

class FleetUpdater
{
    public const TO_PORT_ACTION = 'to_port';
    public const AT_SEA_ACTION = 'at_sea';
    public const DETACH_ACTION = 'detach';

    private MapService $mapService;

    public function __construct(MapService $mapService)
    {
        $this->mapService = $mapService;
    }

    // @TODO revoir si on pourrait pas dégager ca, appeler directement les méthodes.
    public function action(
        Savegame $savegame,
        string $action,
        array $ships,
        array $params = []
    ): void {
        switch ($action) {
            case static::AT_SEA_ACTION:
                $this->putAtSea($savegame, $ships, $params);
                break;
            case static::TO_PORT_ACTION:

                break;
            case static::DETACH_ACTION:

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
        $location = $this->mapService->getFirstWaypointData($port);

        $fleet = new Fleet();
        $fleet->setLl($location);
        $fleet->setCaseCount('1');
        $fleet->setProb('100');
        $fleet->setName($tfId);
        $fleet->setId($tfId);
        $fleet->setLastDivisionCount(0);
        $fleet->setMission($params['mission']);
        $fleet->setSpeed($params['speed']);

        foreach ($params['waypoints'] as $waypoint) {
            $fleet->addWaypoint($waypoint);
        }

        // Create the division
        $division = $tfId . 'DIVISION0';
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
                    'LOCATION' => $location,
                    'SIDE' => $side,
                    'FLEET' => $tfId,
                    'DIVISION' => $division,
                ]
            );
        }

        $savegame->addFleet($side, $fleet);
        $savegame->incrementMaxTfNumber($side);
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
}
