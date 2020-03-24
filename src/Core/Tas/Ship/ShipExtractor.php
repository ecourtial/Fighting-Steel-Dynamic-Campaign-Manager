<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       16/03/2020 (dd-mm-YYYY)
 */

namespace App\Core\Tas\Ship;

use App\Core\File\IniReader;
use App\Core\Tas\Scenario\Scenario;

class ShipExtractor
{
    protected IniReader $iniReader;

    public function __construct(IniReader $iniReader)
    {
        $this->iniReader = $iniReader;
    }

    /** @return Ship[] */
    public function extract(Scenario $scenario, string $side): array
    {
        Scenario::validateSide($side);
        $filePath = $scenario->getFullPath() . DIRECTORY_SEPARATOR . $side . 'Ships.cfg';
        $ships = [];
        $currentName = '';

        // WILL NEED REFACTO IF WE WANT MORE FIELDS. See the FS Extractor?
        foreach ($this->iniReader->getData($filePath) as $line) {
            if ('NAME' === $line['key']) {
                $currentName = $line['value'];

                continue;
            }

            if ('' !== $currentName && 'TYPE' === $line['key']) {
                $ships[] = new Ship($currentName, $line['value']);
            } else {
                $currentName = ''; // Safety
            }
        }

        return $ships;
    }
}
