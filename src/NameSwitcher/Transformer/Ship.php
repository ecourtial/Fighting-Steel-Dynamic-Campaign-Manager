<?php

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       06/04/2020 (dd-mm-YYYY)
 */

declare(strict_types=1);

namespace App\NameSwitcher\Transformer;

class Ship
{
    // The name in TAS, as expected to be find in the scenario generated by TAS
    protected string $originalName;
    // The name to set
    protected string $name;
    // The Shortname to set
    protected string $shortName;

    public function __construct(string $originalName, string $name, string $shortName)
    {
        $this->originalName = $originalName;
        $this->name = $name;
        $this->shortName = $shortName;
    }

    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getShortName(): string
    {
        return $this->shortName;
    }
}
