<?php

namespace AV\MassPriceUpdater\Model\Config\Source;

class PriceType implements \Magento\Framework\Option\ArrayInterface
{
    /*
    *   Add price types to price updater
     *  @return array
    */

    public function toOptionArray()
    {
        return [
            ['value' => 'fixed', 'label' => __('Fixed')],
            ['value' => 'percentage', 'label' => __('Percentage')]
        ];
    }
}