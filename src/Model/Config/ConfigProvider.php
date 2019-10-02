<?php

/**
 * Class ConfigProvider
 *
 * @category
 * @package Transbank\WebpayRest\Model\Config
 *
 */


namespace Transbank\WebpayRest\Model\Config;


class ConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
    var $scopeConfigInterface;
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface) {
        $this->scopeConfigInterface = $scopeConfigInterface;
    }

    public function getConfig()
    {
        return [
            'pluginConfigWebpay' => array(
                'createTransactionUrl' => 'transaction/create'
            )
        ];
    }

    public function getPluginConfig()
    {
        $baseConf = 'payment/transbank_webpay/security_parameters';
        $config = array(
            'environment' => $this->scopeConfigInterface->getValue($baseConf.'environment'),
            'commerce_code' =>  $this->scopeConfigInterface->getValue($baseConf.'commerce_code'),
            'api_key' => $this->scopeConfigInterface->getValue($baseConf.'api_key'),
            'return_url' => 'checkout/transaction/end_payment',
            'ecommerce' => 'magento',
            'order_status' => $this->scopeConfigInterface->getValue($baseConf.'order_status'),
            'successfully_pay' => $this->scopeConfigInterface->getValue($baseConf.'successfully_pay'),
            'error_pay' => $this->scopeConfigInterface->getValue($baseConf.'error_pay')
        );

        return $config;
    }
}
