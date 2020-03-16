<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       16/03/2020 (dd-mm-YYYY)
 */

namespace App\Core\Exception;

class MissingTasScenarioException extends \Exception
{
    public function __construct(string $name)
    {
        parent::__construct("Scenario '{$name}' not found", 0, null);
    }
}
