<?php

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       05/04/2020 (dd-mm-YYYY)
 */

declare(strict_types=1);

namespace App\Core\Traits;

use App\Core\Exception\InvalidInputException;
use App\Core\Tas\Scenario\Scenario;

trait SideValidationTrait
{
    private function validateSide(string $givenSide): void
    {
        if (false === in_array($givenSide, Scenario::SIDES, true)) {
            throw new InvalidInputException("Side '$givenSide' is unknown");
        }
    }
}
