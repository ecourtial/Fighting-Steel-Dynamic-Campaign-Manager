<?php

declare(strict_types=1);

namespace App\ScenarioGenerator\Engine\Ships;

class ShipQuantity
{
    private int $alliedTotal;
    private int $axisTotal;
    private int $alliedBig;
    private int $axisBig;
    private int $alliedSmall;
    private int $axisSmall;

    public function __construct(int $alliedTotal, int $axisTotal, int $alliedBig, int $axisBig)
    {
        $this->alliedTotal = $alliedTotal;
        $this->axisTotal = $axisTotal;
        $this->alliedBig = $alliedBig;
        $this->axisBig = $axisBig;
        $this->alliedSmall = $alliedTotal - $alliedBig;
        $this->axisSmall = $axisTotal - $axisBig;
    }

    public function getAlliedTotal(): int
    {
        return $this->alliedTotal;
    }

    public function getAxisTotal(): int
    {
        return $this->axisTotal;
    }

    public function getAlliedBig(): int
    {
        return $this->alliedBig;
    }

    public function getAxisBig(): int
    {
        return $this->axisBig;
    }

    public function getAlliedSmall(): int
    {
        return $this->alliedSmall;
    }

    public function getAxisSmall(): int
    {
        return $this->axisSmall;
    }
}
