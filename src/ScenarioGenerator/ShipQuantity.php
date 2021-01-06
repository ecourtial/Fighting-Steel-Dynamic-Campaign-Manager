<?php

declare(strict_types=1);

namespace App\ScenarioGenerator;


class ShipQuantity
{
    private int $total;
    private int $big;
    private int $alliedTotal;
    private int $axisTotal;
    private int $alliedBig;
    private int $axisBig;

    public function __construct(int $alliedTotal, int $axisTotal, int $alliedBig, int $axisBig)
    {
        $this->alliedTotal = $alliedTotal;
        $this->axisTotal = $axisTotal;
        $this->alliedBig = $alliedBig;
        $this->axisBig = $axisBig;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getBig(): int
    {
        return $this->big;
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
}
