<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       16/03/2020 (dd-mm-YYYY)
 */

namespace App\Core\Fs\Scenario\Ship;

use App\Core\Fs\AbstractShipExtractor;
use App\Core\Tas\Scenario\Scenario;

class ShipExtractor extends AbstractShipExtractor
{
    /**
     * Note: in to be more accurate, it returns an array of \App\Core\Fs\Scenario\Ship\Ship
     * but PHPStan does not understand it.
     *
     * @return \App\Core\Fs\FsShipInterface[]
     */
    public function extract(Scenario $scenario): array
    {
        $filePath = $scenario->getFullPath() . DIRECTORY_SEPARATOR . 'GR.scn';

        return $this->extractShips($filePath, 'CLASS');
    }

    protected function createShip(array $data): Ship
    {
        return  new Ship($data);
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
}
