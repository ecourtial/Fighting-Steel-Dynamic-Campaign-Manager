<?php

declare(strict_types=1);

/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

namespace App\Core\Traits;

use App\Core\Exception\CoreException;
use App\NameSwitcher\Exception\InvalidShipDataException;

trait HydrateTrait
{
    /** @param string[] $data */
    private function hydrate(array $data): void
    {
        if (count($data) !== count(static::FIELDS_NAME)) {
            throw new InvalidShipDataException('Invalid ship attribute quantity');
        }

        foreach ($data as $key => $value) {
            if (false === in_array($key, static::FIELDS_NAME, true)) {
                throw new InvalidShipDataException("The attribute '{$key}' is unknown");
            }

            $methodName = 'set' . $key;
            if (false === method_exists($this, $methodName)) {
                throw new CoreException("Method '{$methodName}' does not exist in " . __CLASS__);
            }

            $this->$methodName($value);
        }
    }
}
