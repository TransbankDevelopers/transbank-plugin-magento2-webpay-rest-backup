<?php

/**
 * Class Index
 *
 * @category
 * @package Transbank\Webpay\Controller\Adminhtml\CreatePdf
 *
 */


namespace Transbank\Webpay\Controller\Adminhtml\CreatePdf;

use Transbank\PluginsUtils\HealthCheck;
use Transbank\PluginsUtils\ReportPDFLog;

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
        $config = $this->configProvider->getPluginConfig();
        $healthcheck = new HealthCheck($config, false);
        $json = $healthcheck->printFullResume();
        $temp = json_decode($json);
        $document = $_GET["document"];
        if ($document == "report") {
            unset($temp->php_info);
        } else {
            $temp = array('php_info' => $temp->php_info);
        }
        $json = json_encode($temp);
        $rl = new ReportPdfLog($document, 'magento');
        $rl->getReport($json);
    }
}
