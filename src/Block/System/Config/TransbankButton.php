<?php

/**
 * Class TransbankButton
 *
 * @category
 * @package Transbank\WebpayRest\Block\System\Config
 *
 */


namespace Transbank\WebpayRest\Block\System\Config;

use Transbank\PluginsUtils\HealthCheck;
use Transbank\PluginsUtils\LogHandler;

class TransbankButton extends \Magento\Config\Block\System\Config\Form\Field
{

    var $printData;

    protected $_template = 'system/config/button.phtml';

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Transbank\Webpay\Model\Config\ConfigProvider $configProvider)
    {
        parent::__contruct($context);
        $config = $configProvider->getPluginConfig();
        $healthcheck = new HealthCheck($config, false);
        $data = json_decode($healthcheck->printFullResume());

        $log = new LogHandler('magento');
        $resume = $log->getResume();

        $this->printData = array(
            'url_request' => $context->getUrlBuilder()->getUrl("admin_webpay/Request/index"),
            'url_call_log_handler' => $context->getUrlBui´lder()->getUrl("admin_webpay/CallLogHandler/index"),
            'url_create_pdf_report' => $context->getUrlBuilder()->getUrl("admin_webpay/CreatePdf/index") . '?document=report',
            'url_create_pdf_php_info' => $context->getUrlBuilder()->getUrl("admin_webpay/CreatePdf/index") . '?document=php_info',
            'php_status' => $data->server_resume->php_version->status,
            'php_version' => $data->server_resume->php_version->version,
            'server_version' => $data->server_resume->server_version->server_software,
            'ecommerce' => $data->server_resume->plugin_info->ecommerce,
            'ecommerce_version' => $data->server_resume->plugin_info->ecommerce_version,
            'current_plugin_version' => $data->server_resume->plugin_info->current_plugin_version,
            'last_plugin_version' => $data->server_resume->plugin_info->last_plugin_version,
            'php_info' => $data->php_info->string->content,
            'lockfile' => isset($resume['lock_file']['status']) ? $resume['lock_file']['status'] : NULL,
            'logs' => isset($resume['last_log']['log_content']) ? $resume['last_log']['log_content'] : NULL,
            'log_file' => isset($resume['last_log']['log_file']) ? $resume['last_log']['log_file'] : NULL,
            'log_weight' => isset($resume['last_log']['log_weight']) ? $resume['last_log']['log_weight'] : NULL,
            'log_regs_lines' => isset($resume['last_log']['log_regs_lines']) ? $resume['last_log']['log_regs_lines'] : NULL,
            'log_days' => $resume['validate_lock_file']['max_logs_days'],
            'log_size' => $resume['validate_lock_file']['max_log_weight'],
            'log_dir' => $resume['log_dir'],
            'logs_count' => $resume['logs_count']['log_count'],
            'logs_list' => isset($resume['logs_list']) ? $resume['logs_list'] : array('no hay archivos de registro')

        );
    }

}
