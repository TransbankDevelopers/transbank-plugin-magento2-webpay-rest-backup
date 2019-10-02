<?php

/**
 * Class CallLogHandler
 *
 * @category
 * @package Transbank\Webpay\Controller
 *
 */


namespace Transbank\Webpay\Controller;

use Transbank\PluginsUtils\LogHandler;

class Index extends \Magento\Backend\App\Action
{
    public function __construct(\Magento\Backend\App\Action\Context $context) {
        parent::__construct($context);
    }

    public function execute() {
        $log = new LogHandler('magento');
        if ($_POST["action_check"] == 'true') {
            $log->setLockStatus(true);
            $log->setparamsconf($_POST['days'], $_POST['size']);
        } else {
            $log->setLockStatus(false);
        }
    }

}
