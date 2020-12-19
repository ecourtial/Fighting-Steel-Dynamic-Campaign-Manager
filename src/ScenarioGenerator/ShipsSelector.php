<?php

declare(strict_types=1);

namespace App\ScenarioGenerator;

class ShipsSelector
{
    /** @return string[] */
    public function getShips(string $code, int $period, array $shipQuantities, bool $mixedNavies): array
    {
        return [
            'Allied' => [
                'Iowa',
                'Nelson',
            ],
            'Axis' => [
                'Tirpitz',
                'Gneisenau',
                'Prinz Eugen',
            ],
        ];
    }
}
