<?php

namespace AV\MassPriceUpdater\Model\Config\Source;

class PriceAttribute implements \Magento\Framework\Option\ArrayInterface
{
    /*
    *   Add price attribute(s) to price updater
     *  @return array
    */

    public function toOptionArray()
    {
        return [
            ['value' => 'price', 'label' => __('Regular Price')],
            ['value' => 'special_price', 'label' => __('Special Price')]
        ];
    }
}