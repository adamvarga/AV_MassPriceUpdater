<?php

namespace AV\MassPriceUpdater\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class SaveConfig implements ObserverInterface
{
    /*
    *   Start the update process, after save the config
    */

    protected $massUpdater;

    public function __construct(
        \AV\MassPriceUpdater\Model\Updater\MassUpdater $massUpdater
    ) {
        $this->_massUpdater = $massUpdater;
    }

    public function execute(EventObserver $observer)
    {
        return $this->_massUpdater;
    }
}
