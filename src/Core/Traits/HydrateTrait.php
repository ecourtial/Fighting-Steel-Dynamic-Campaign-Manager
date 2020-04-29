<?php

/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

declare(strict_types=1);

namespace App\Core\Traits;

use App\Core\Exception\CoreException;
use App\Core\Exception\InvalidInputException;

trait HydrateTrait
{
    /** @param string[] $data */
    private function hydrate(array $data): void
    {
        if (count($data) !== count(static::FIELDS_NAME)) {
            throw new InvalidInputException('Invalid attribute quantity in ' . __CLASS__);
        }

        foreach ($data as $key => $value) {
            if (false === in_array($key, static::FIELDS_NAME, true)) {
                throw new InvalidInputException("The attribute '{$key}' is unknown in " . __CLASS__);
            }

            $methodName = 'set' . $key;
            if (false === method_exists($this, $methodName)) {
                throw new CoreException("Method '{$methodName}' does not exist in " . __CLASS__);
            }

            $this->$methodName($value);
        }
    }
}
