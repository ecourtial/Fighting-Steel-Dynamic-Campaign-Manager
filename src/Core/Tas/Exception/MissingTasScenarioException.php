<?php

/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

declare(strict_types=1);

namespace App\Core\Tas\Exception;

class MissingTasScenarioException extends \Exception
{
    public function __construct(string $name)
    {
        parent::__construct("Scenario '{$name}' not found");
    }
}
