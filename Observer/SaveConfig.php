<?php

namespace AV\MassPriceUpdater\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class SaveConfig implements ObserverInterface
{
    /*
    *   Start the update process, after save the config
    */

    public function execute(EventObserver $observer)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager->get('AV\MassPriceUpdater\Model\Updater\MassUpdater');
    }
}
