<?php

declare(strict_types=1);
/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       10/04/2020 (dd-mm-YYYY)
 */

namespace App\Tests;

trait GeneratorTrait
{
    protected function generate(array $yield_values)
    {
        return $this->returnCallback(function () use ($yield_values) {
            foreach ($yield_values as $value) {
                yield $value;
            }
        });
    }
}
