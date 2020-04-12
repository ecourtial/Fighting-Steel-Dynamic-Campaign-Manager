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
    protected string $filePath;

    public function __construct(IniReader $iniReader, string $fsDirectory)
    {
        parent::__construct($iniReader);
        $this->filePath = $fsDirectory . DIRECTORY_SEPARATOR . 'Scenarios' . DIRECTORY_SEPARATOR . '_End Of Engagement.sce';
    }

    /**
     * Note: in to be more accurate, it returns an array of \App\Core\Fs\BattleReport\Ship
     * but PHPStan does not understand it.
     *
     * @return \App\Core\Fs\FsShipInterface[]
     */
    public function extract(): array
    {
        return $this->extractShips($this->filePath, 'STATUS');
    }

    protected function getEmptyValues(): array
    {
        return \array_flip(Ship::FIELDS_NAME);
    }

    protected function createShip(array $data): Ship
    {
        return  new Ship($data);
    }
}
