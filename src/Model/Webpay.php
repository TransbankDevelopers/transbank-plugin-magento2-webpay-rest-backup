<?php

/**
 * Class WebpayRest
 *
 * @category
 * @package Transbank\WebpayRest\Model
 *
 */


namespace Transbank\Webpay\Model;


class Webpay extends \Magento\Payment\Model\Method\AbstractMethod
{
    const CODE = 'transbank_webpay';

    protected $_code = self::CODE;

    protected $_supportedCurrencyCodes = array('CLP');
    protected $_isGateway = true;
    protected $_canCapture = true;
    protected $_canRefund = true;
    protected $_canAuthorize = true;

    /**
     * Availability for currency
     *
     * @param string $currencyCode
     * @return bool
     */
    public function canUseForCurrency($currencyCode) {
        if (!in_array($currencyCode, $this->_supportedCurrencyCodes)) {
            return false;
        }
        return true;
    }

    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount) {

        if (!$this->canCapture()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The capture action is not available.'));
        }

        return $this;
    }

    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount) {

        if (!$this->canAuthorize()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The authorize action is not available.'));
        }

        return $this;
    }

    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount) {

        if (!$this->canRefund()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The refund action is not available.'));
        }

        return $this;
    }

}
