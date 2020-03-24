<?php

declare(strict_types=1);

/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

namespace App\Core\Tas\Exception;

class MissingTasScenarioException extends \Exception
{
    public function __construct(string $name)
    {
        parent::__construct("Scenario '{$name}' not found", 0, null);
    }
}
