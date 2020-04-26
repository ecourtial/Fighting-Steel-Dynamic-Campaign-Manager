<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       23/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

namespace App\Core\Tas\Savegame\Fleet;

use App\Core\Exception\InvalidInputException;
use App\Core\File\IniReader;
use App\Core\Tas\Savegame\Savegame;
use App\Core\Tas\Scenario\Scenario;

class FleetUpdater
{
    public const TO_PORT_ACTION = 'to_port';
    public const AT_SEA_ACTION = 'at_sea';
    public const DETACH_ACTION = 'detach';

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
     * @param array[] $params
     */
    private function putAtSea(Savegame $savegame, array $ships, array $params): void
    {
        $side = $this->validateSide($savegame, $ships);
        $shipsToMove = [];

//        dd($savegame);

        foreach ($ships as $ship) {
            $shipsToMove[$ship] = $savegame->getShipData($ship);
            $savegame->removeShipInPort($ship, $side);
        }

        dd($savegame->getAxisShipsInPort());


        $savegame->setShipsDataChanged($side, true);
    }

    private function validateSide($savegame, array $ships): string
    {
        $side = '';
        // Check the side
        foreach ($ships as $ship) {
            $currentSide = $savegame->getShipData($ship)['side'];
            if ($side === '') {
                $side = $currentSide;

                continue;
            }

            if ($currentSide !== $side) {
                throw new InvalidInputException('Ships must be on the same side');
            }
        }

        return $side;
    }
}
