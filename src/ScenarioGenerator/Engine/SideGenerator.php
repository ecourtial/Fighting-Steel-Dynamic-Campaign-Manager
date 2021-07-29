<?php

declare(strict_types=1);

namespace App\ScenarioGenerator\Engine;

use App\Core\Tas\Scenario\Scenario;

class SideGenerator
{
    /** @return string[] */
    public function getSides(string $code, int $period, string $side, bool $mixedNavies): array
    {
        if (Scenario::ALLIED_SIDE === $side) {
            $sides = ScenarioEnv::SELECTOR[$code]['periods'][$period][Scenario::ALLIED_SIDE];
        } elseif (Scenario::AXIS_SIDE === $side) {
            $sides = ScenarioEnv::SELECTOR[$code]['periods'][$period][Scenario::AXIS_SIDE];
        } else {
            throw new \InvalidArgumentException("Unknown side '$side'");
        }

        shuffle($sides);

        if (false === $mixedNavies) {
            $sides = [$sides[array_rand($sides)]];
        }

        return $sides;
    }
}
