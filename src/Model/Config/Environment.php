<?php

/**
 * Class Environment
 *
 * @category
 * @package Transbank\WebpayRest\Model\Config
 *
 */


namespace Transbank\WebpayRest\Model\Config;


class Environment implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {
        return [['value' => 'TEST', 'label' => __('TEST')],
            ['value' => 'LIVE', 'label' => __('LIVE')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray() {
        return ['TEST' => __('TEST'),
            'LIVE' => __('LIVE')];
    }
}
