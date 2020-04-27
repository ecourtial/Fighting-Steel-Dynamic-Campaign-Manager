<?php

declare(strict_types=1);

namespace App\Core\Traits;

use App\Core\Exception\InvalidInputException;
use App\Core\Tas\Scenario\Scenario;

trait UnknownSideTrait
{
    public function validateSide(string $givenSide): void
    {
        if (false === in_array($givenSide, Scenario::SIDES, true)) {
            throw new InvalidInputException("Side '$givenSide' is unknown");
        }
    }
}
