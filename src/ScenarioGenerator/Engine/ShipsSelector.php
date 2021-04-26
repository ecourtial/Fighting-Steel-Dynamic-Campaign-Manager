<?php

declare(strict_types=1);

namespace App\ScenarioGenerator\Engine;

use App\Core\Tas\Scenario\Scenario;
use App\ScenarioGenerator\Engine\Ships\ShipProvider;
use App\ScenarioGenerator\Engine\Ships\ShipQuantity;

class ShipsSelector
{
    private ShipProvider $shipProvider;
    private array $ships;

    public function __construct(ShipProvider $shipProvider)
    {
        $this->shipProvider = $shipProvider;
    }

    /** @return string[] */
    public function getShips(
        string $code,
        int $period,
        ShipQuantity $shipQuantity,
        bool $mixedNavies
    ): array {
        // Select the navies
        $allied = ScenarioEnv::SELECTOR[$code]['periods'][$period][Scenario::ALLIED_SIDE];
        $axis = ScenarioEnv::SELECTOR[$code]['periods'][$period][Scenario::AXIS_SIDE];

        shuffle($allied);
        shuffle($axis);

        // Only one navy per side
        if (false === $mixedNavies) {
            $allied = [$allied[array_rand($allied)]];
            $axis = [$axis[array_rand($axis)]];
        }

        return [
            Scenario::ALLIED_SIDE => $this->selectShips(
                $shipQuantity->getAlliedBig(),
                $shipQuantity->getAlliedSmall(),
                $allied
            ),
            Scenario::AXIS_SIDE => $this->selectShips(
                $shipQuantity->getAxisBig(),
                $shipQuantity->getAxisSmall(),
                $axis
            ),
        ];
    }

    private function selectShips(int $bigShipCount, int $destroyerCount, array $sides): array
    {
        $this->ships = [];
        $requirements = ['big' => $bigShipCount, 'small' => $destroyerCount];

        foreach ($requirements as $type => $qty) {
            for ($count = 0; $count < $qty; $count++) {
                $this->addShip($type, $sides, $qty);
            }
        }

        return $this->ships;
    }

    private function addShip(string $type, array $sides): void
    {
        // Get one random side and get the ship
        $side = $sides[array_rand($sides)];

        if ('big' === $type) {
            $ship = $this->shipProvider->getBigShip($side);
        } else {
            $ship = $this->shipProvider->getDestroyer($side);
        }

        // Add the ship
        if (false === array_key_exists($side, $this->ships)) {
            $this->ships[$side] = [];
        }

        $this->ships[$side][] = $ship;
    }
}
