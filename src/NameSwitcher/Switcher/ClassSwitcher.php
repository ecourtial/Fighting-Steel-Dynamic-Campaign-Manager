<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       05/04/2020 (dd-mm-YYYY)
 */

namespace App\NameSwitcher\Switcher;

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
                $currentName = $fsShip->getName();
                $this->correspondence[$currentName] = $this->addNewCorrespondence(
                    $fsShip->getClass(),
                    $currentName,
                    $dictionary->getShipsList()[$currentName]->getFsName()
                );
            }
        }

        return $this->correspondence;
    }

    private function addNewCorrespondence(string $class, string $currentName, string $fsName): Ship
    {
        if (true === array_key_exists($class, $this->classesQty)) {
            $this->classesQty[$class]++;
        } else {
            $this->classesQty[$class] = 1;
        }

        $newClassName = substr($class, 0, 7);
        $newClassName = $newClassName . '#' . $this->classesQty[$class];

        return new Ship(
            $currentName,
            $fsName,
            $newClassName
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
