<?php
/**
 * (c) Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Vespolina\InventoryBundle\Model;

use Vespolina\InventoryBundle\Model\InventoryManagerInterface;

/**
 * @author Richard Shank <develop@zestic.com>
 */
abstract class InventoryManager implements InventoryManagerInterface
{
    /**
     * @inheritdoc
     */
    public function addToInventory(InventoryInterface $inventory, $itemCnt, $location = null)
    {
        if ($location) {
            throw new \Exception('not implemented');
        }

        $loadedInventory = $this->lockAndLoad($inventory);

        $ohp = new \ReflectionProperty($this->inventoryClass, 'onHand');
        $ohp->setAccessible(true);
        $onHand = $ohp->getValue($loadedInventory) + $itemCnt;
        $ohp->setValue($loadedInventory, $onHand);

        $ap = new \ReflectionProperty($this->inventoryClass, 'available');
        $ap->setAccessible(true);
        $available = $ap->getValue($loadedInventory) + $itemCnt;
        $ap->setValue($loadedInventory, $available);

        $changes = array(
            'onHand' => $loadedInventory->getOnHand(),
            'available' => $loadedInventory->getAvailable(),
        );
        if ($this->saveAndUnlock($loadedInventory, $changes)) {
            unset($inventory);

            return $loadedInventory;
        }

        // todo: exit on failure
        // todo: remove from unit of work or does unsetting do it?

    }
}
