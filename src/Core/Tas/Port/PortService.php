<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       22/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

namespace App\Core\Tas\Port;

use App\Core\File\IniReader;
use App\Core\Tas\Exception\MissingPortException;

class PortService
{
    private IniReader $iniReader;
    private array $portsData = [];

    public function __construct(IniReader $iniReader)
    {
        $this->iniReader = $iniReader;
    }

    public function getPortData(string $port): array
    {
        if ($this->portsData === []) {
            $this->loadData();
        }

        if (false === array_key_exists($port, $this->portsData)) {
            throw new MissingPortException($port);
        }

        return $this->portsData[$port];
    }

    public function getPortFirstWayPoint(string $port): string
    {
        return 'AHAH';
    }

    private function loadData(): void
    {
        
    }
}
