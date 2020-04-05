<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       16/03/2020 (dd-mm-YYYY)
 */

namespace App\Core\Tas\Ship;

use App\Core\Exception\InvalidInputException;
use App\Core\Fs\Scenario\Ship\Ship as FsShip;

class Ship
{
    private string $name;
    private string $type;

    public function __construct(string $name, string $type)
    {
        $this->name = $name;

        if (false === in_array($type, FsShip::SHIP_TYPES, true)) {
            throw new InvalidInputException("Ship type '{$type}' is unknown");
        }

        $this->type = $type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
