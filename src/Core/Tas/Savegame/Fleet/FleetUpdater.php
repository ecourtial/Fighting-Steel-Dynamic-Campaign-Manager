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

class FleetUpdater
{
    public const TO_PORT_ACTION = 'to_port';
    public const AT_SEA_ACTION = 'at_sea';
    public const DETACH_ACTION = 'detach';

    private IniReader $iniReader;
    private string $tasDirectory;

    public function __construct(IniReader $iniReader, string $tasDirectory)
    {
        $this->iniReader = $iniReader;
        $this->tasDirectory = $tasDirectory;
    }

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

    private function putAtSea(Savegame $savegame, array $ships, array $params): void
    {
        // D'abord, rajouter dans les infos les notions de type, endurance etc.

        // On va louper sur les navires pour construire la division de la TF
        $side = $savegame->getShipData($ship)['side'];
        $path = $savegame->getPath();
    }
}
