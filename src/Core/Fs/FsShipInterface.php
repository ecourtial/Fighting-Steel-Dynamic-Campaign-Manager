<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       05/04/2020 (dd-mm-YYYY)
 */

namespace App\Core\Fs;

interface FsShipInterface
{
    public function getName(): string;

    public function getClass(): string;

    public function getSide(): ?string;
}
