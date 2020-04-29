<?php

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       05/04/2020 (dd-mm-YYYY)
 */

declare(strict_types=1);

namespace App\NameSwitcher\Switcher;

use App\Core\Fs\FsShipInterface;
use App\NameSwitcher\Dictionary\Dictionary;
use App\NameSwitcher\Transformer\Ship;

class ClassSwitcher extends BasicSwitcher
{
    /** @var int[] */
    protected array $classesQty;

    /** @var \App\NameSwitcher\Transformer\Ship[] */
    protected array $correspondence;

    /**
     * Is actually \App\Core\Fs\Scenario\Ship\Ship[] $fsShips
     * but PHPStan has issue with interpreting interfaces
     *
     * @param \App\Core\Fs\FsShipInterface[] $fsShips
     *
     * @return \App\NameSwitcher\Transformer\Ship[]
     */
    public function switch(Dictionary $dictionary, array $fsShips, string $playerSide): array
    {
        $this->initialize();

        // Reminder. At this point, the Name in the FS file is the same as in TAS.
        foreach ($fsShips as $fsShip) {
            if ($fsShip->getSide() === $playerSide) {
                // Ship is on our side: we just switch
                $this->correspondence[$fsShip->getName()] = $this->createBasicSwitch($dictionary, $fsShip);
            } else {
                // Ship is enemy: we obfuscate
                $this->correspondence[$fsShip->getName()] = $this->addNewCorrespondence($fsShip, $dictionary);
            }
        }

        return $this->correspondence;
    }

    protected function initialize(): void
    {
        $this->classesQty = [];
        $this->correspondence = [];
    }

    /**
     * Is actually \App\Core\Fs\Scenario\Ship\Ship[] $fsShips
     * but PHPStan has issue with interpreting interfaces
     */
    protected function addNewCorrespondence(FsShipInterface $fsShip, Dictionary $dictionary): Ship
    {
        $class = $fsShip->getClass();
        $currentName = $fsShip->getName();

        if (true === array_key_exists($class, $this->classesQty)) {
            $this->classesQty[$class]++;
        } else {
            $this->classesQty[$class] = 1;
        }

        $newClassName = $this->truncate($class);
        $newClassName = $newClassName . '#' . $this->classesQty[$class];

        return new Ship(
            $currentName,
            $dictionary->getShipsList()[$currentName]->getFsName(),
            $newClassName
        );
    }

    protected function truncate(string $value): string
    {
        return substr($value, 0, 7);
    }
}
