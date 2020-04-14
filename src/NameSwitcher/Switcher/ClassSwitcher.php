<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       05/04/2020 (dd-mm-YYYY)
 */

namespace App\NameSwitcher\Switcher;

use App\Core\Exception\CoreException;
use App\Core\Fs\FsShipInterface;
use App\NameSwitcher\Dictionary\Dictionary;
use App\NameSwitcher\Transformer\Ship;

class ClassSwitcher implements SwitcherInterface
{
    /** @var int[] */
    private array $classesQty;

    /** @var \App\NameSwitcher\Transformer\Ship[] */
    private array $correspondence;

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
        $this->classesQty = [];
        $this->correspondence = [];

        // Reminder. At this point, the Name in the FS file is the same as in TAS.
        foreach ($fsShips as $fsShip) {
            if ($fsShip->getSide() === $playerSide) {
                // Ship is on our side: we just switch
                $this->correspondence[$fsShip->getName()] = $this->createBasicSwitch($dictionary, $fsShip);
            } else {
                // Ship is enemy: we obfuscate
                $class = $fsShip->getClass();
                $currentName = $fsShip->getName();
                $fsName = $dictionary->getShipsList()[$currentName]->getFsName();

                if (true === array_key_exists($class, $this->classesQty)) {
                    if (1 === $this->classesQty[$class]) {
                        // Second time we encounter this class
                        $this->createSecondCorrespondence($class, $currentName, $fsName);
                    } else {
                        // This class has been encountered > 2 times
                        $this->addNewCorrespondence($class, $currentName, $fsName);
                    }
                } else {
                    // First time we encounter this class
                    $this->correspondence[$fsShip->getName()] = $this->createFirstCorrespondence($dictionary, $fsShip);
                }
            }
        }

        return $this->correspondence;
    }

    private function addNewCorrespondence(string $class, string $currentName, string $fsName): Ship
    {
        $this->classesQty[$class]++;
        $newClassName = substr($class, 0, 8);
        $newClassName = $newClassName . '#' . $this->classesQty[$class];

        return new Ship(
            $currentName,
            $fsName,
            $newClassName
        );
    }

    private function createSecondCorrespondence(string $class, string $currentName, string $fsName): Ship
    {
        $newClassName = substr($class, 0, 8);
        $previousShip = $newClassName . '#' . 1;

        // 1- Update the previously registered ship
        foreach ($this->correspondence as $ship) {
            /** @var \App\NameSwitcher\Transformer\Ship $ship */
            if ($ship->getShortName() === $class) {
                $ship->setShortName($previousShip);
                $previousShip = '';

                break;
            }
        }

        if ('' === $previousShip) {
            throw new CoreException("The ship with class '$class' was not found");
        }

        // 2- Add the new one
        return $this->addNewCorrespondence($class, $currentName, $fsName);
    }

    /**
     * Is actually \App\Core\Fs\Scenario\Ship\Ship
     * but PHPStan has issue with interpreting interfaces
     */
    private function createFirstCorrespondence(Dictionary $dictionary, FsShipInterface $fsShip): Ship
    {
        $this->classesQty[$fsShip->getClass()] = 1;

        return new Ship(
            $fsShip->getName(),
            $dictionary->getShipsList()[$fsShip->getName()]->getFsName(),
            $dictionary->getShipsList()[$fsShip->getName()]->getClass()
        );
    }

    /**
     * Is actually \App\Core\Fs\Scenario\Ship\Ship
     * but PHPStan has issue with interpreting interfaces
     */
    private function createBasicSwitch(Dictionary $dictionary, FsShipInterface $fsShip): Ship
    {
        return new Ship(
            $fsShip->getName(),
            $dictionary->getShipsList()[$fsShip->getName()]->getFsName(),
            $dictionary->getShipsList()[$fsShip->getName()]->getFsShortName()
        );
    }
}
