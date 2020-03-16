<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       29/02/2020 (dd-mm-YYYY)
 */

namespace App\Core\Exception;

class FileNotFoundException extends \Exception
{
    public function __construct(string $filename)
    {
        parent::__construct("Impossible to read the content of the file '{$filename}'.", 0, null);
    }
}
