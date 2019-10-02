<?php

/**
 * Class Index
 *
 * @category
 * @package Transbank\Webpay\Controller\Adminhtml\Request
 *
 */


namespace Transbank\Webpay\Controller\Adminhtml\Request;

use Transbank\PluginsUtils\HealthCheck;

class Index extends \Magento\Backend\App\Action
{
    var $configProvider;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Transbank\Webpay\Model\Config\ConfigProvider $configProvider) {
        parent::__construct($context);
        $this->configProvider = $configProvider;
    }

    public function execute() {
        if($_POST['type'] == 'checkInit') {
            try {
                $config = $this->configProvider->getPluginConfig();
                $healthcheck = new HealthCheck($config, false);
                $response = $healthcheck->getInitTransaction();
                echo json_encode(['success' => true, 'msg' => json_decode($response)]);
            } catch (\Exception $e) {
                echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
            }
        }
    }

}
