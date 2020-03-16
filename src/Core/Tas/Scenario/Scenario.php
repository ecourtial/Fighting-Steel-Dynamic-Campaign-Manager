<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       16/03/2020 (dd-mm-YYYY)
 */

namespace App\Core\Tas\Scenario;

class Scenario
{
    private string $name;
    private string $fullPath;

    public function __construct(string $name, string $fullPath)
    {
        $this->name = $name;
        $this->fullPath = $fullPath;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFullPath(): string
    {
        return $this->fullPath;
    }
}
