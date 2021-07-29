<?php

declare(strict_types=1);

namespace App\ScenarioGenerator\Engine;

use App\Core\Tas\Scenario\Scenario;
use App\ScenarioGenerator\Engine\Ships\ShipProvider;
use App\ScenarioGenerator\Engine\Ships\ShipQuantity;

class ShipsSelector
{
    private ShipProvider $shipProvider;
    private SideGenerator $sideGenerator;

    /** @var string[][][][] */
    private array $ships;

    public function __construct(ShipProvider $shipProvider, SideGenerator $sideGenerator)
    {
        $this->shipProvider = $shipProvider;
        $this->sideGenerator = $sideGenerator;
    }

    /** @return string[][][][] */
    public function getShips(
        string $code,
        int $period,
        ShipQuantity $shipQuantity,
        bool $mixedNavies
    ): array {
        $this->ships = [
            Scenario::ALLIED_SIDE => [],
            Scenario::AXIS_SIDE => [],
        ];

        // Select the navies
        $allied = $this->sideGenerator->getSides($code, $period, Scenario::ALLIED_SIDE, $mixedNavies);
        $axis = $this->sideGenerator->getSides($code, $period, Scenario::AXIS_SIDE, $mixedNavies);

        $this->selectShips(
            Scenario::ALLIED_SIDE,
            $shipQuantity->getAlliedBig(),
            $shipQuantity->getAlliedSmall(),
            $allied
        );
        $this->selectShips(
            Scenario::AXIS_SIDE,
            $shipQuantity->getAxisBig(),
            $shipQuantity->getAxisSmall(),
            $axis
        );

        return $this->ships;
    }

    /** @param string[] $sides */
    private function selectShips(string $mainSide, int $bigShipCount, int $destroyerCount, array $sides): void
    {
        $requirements = ['big' => $bigShipCount, 'small' => $destroyerCount];

        foreach ($requirements as $type => $qty) {
            for ($count = 0; $count < $qty; $count++) {
                $this->addShip($mainSide, $type, $sides);
            }
        }
    }

    /**
     * @param string[] $sides
     */
    private function addShip(string $mainSide, string $type, array $sides): void
    {
        // Get one random side and get the ship
        $side = $sides[array_rand($sides)];

        if ('big' === $type) {
            $ship = $this->shipProvider->getBigShip($side);
        } else {
            $ship = $this->shipProvider->getDestroyer($side);
        }

        // Add the ship
        if (false === array_key_exists($side, $this->ships[$mainSide])) {
            $this->ships[$mainSide][$side] = [];
        }

        $this->ships[$mainSide][$side][] = $ship;
    }
}
