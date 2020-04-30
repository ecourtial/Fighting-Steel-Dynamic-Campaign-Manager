<?php

/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

declare(strict_types=1);

namespace App\Core\Exception;

class FileNotFoundException extends \Exception
{
    public function __construct(string $filename)
    {
        parent::__construct("Impossible to read the content of the file '{$filename}'.");
    }
}
