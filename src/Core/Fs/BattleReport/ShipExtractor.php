<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       05/04/2020 (dd-mm-YYYY)
 */

namespace App\Core\Fs\BattleReport;

use App\Core\File\IniReader;
use App\Core\Fs\AbstractShipExtractor;

class ShipExtractor extends AbstractShipExtractor
{
    protected string $scenarioDirectory;

    public function __construct(IniReader $iniReader, string $fsDirectory)
    {
        parent::__construct($iniReader);
        $this->scenarioDirectory = $fsDirectory . DIRECTORY_SEPARATOR . 'Scenarios';
    }

    /**
     * Note: in to be more accurate, it returns an array of \App\Core\Fs\BattleReport\Ship
     * but PHPStan does not understand it.
     *
     * @return \App\Core\Fs\FsShipInterface[]
     */
    public function extract(): array
    {
        $filePath = $this->scenarioDirectory . DIRECTORY_SEPARATOR . '_End Of Engagement.sce';

        return $this->extractShips($filePath, 'STATUS');
    }

    protected function getEmptyValues(): array
    {
        $values = \array_flip(Ship::FIELDS_NAME);
        foreach ($values as &$value) {
            $value = '';
        }
        unset($value);

        return $values;
    }

    protected function createShip(array $data): Ship
    {
        return  new Ship($data);
    }
}
