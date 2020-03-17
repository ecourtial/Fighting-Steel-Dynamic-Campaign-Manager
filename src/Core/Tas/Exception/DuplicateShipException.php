<?php

declare(strict_types=1);

/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

namespace App\Core\Tas\Exception;

class DuplicateShipException extends \Exception
{
    public function __construct(string $shipName, string $side)
    {
        $message = "Duplicate ship entry with name '{$shipName}' in side '{$side}'";
        parent::__construct($message, 0, null);
    }
}
