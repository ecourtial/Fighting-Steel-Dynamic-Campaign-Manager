<?php

/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

declare(strict_types=1);

namespace App\Core\Tas\Exception;

class MissingPortException extends \Exception
{
    public function __construct(string $name)
    {
        parent::__construct("Port '{$name}' not found");
    }
}
