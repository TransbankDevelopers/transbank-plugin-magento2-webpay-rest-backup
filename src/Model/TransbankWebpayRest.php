<?php

/**
 * Class TransbankWebpayRest
 *
 * @category
 * @package Transbank\WebpayRest\Model
 *
 */


namespace Transbank\Webpay\Model;

use Transbank\Webpay\Options;
use Transbank\Webpay\WebpayPlus;

class TransbankWebpayRest
{
    var $opt;
    var $returnUrl;
    var $result;
    var $logger;

    function __construct($config)
    {
        $this->logger = new LogHandler();

        if (isset($config) && $config['environment'] != 'TEST') {
            $this->returnUrl = $config['return_url'];
            $preOpt = new Options($config['api_key'], $config['commerce_code']);
            $this->opt = $preOpt->setIntegraionType($config['environment']);
        } else {
            $this->opt = Options::configureForTesting();
        }

    }

    public function createTransaction($buyOrder, $sessionId, $amount){
        try {
            $txDate = date('Y-m-d');
            $txTime = date('H:i:s');
            $this->logger->logInfo(
                'createTransaction:'.
                '- amount: '.$amount.
                ', sessionId: '.$sessionId.
                ', buyOrder: '.$buyOrder.
                ', txtDate: '.$txDate.
                ', txTime: '.$txTime
            );

            $transactionResult = WebpayPlus\Transaction::create($buyOrder, $sessionId, $amount, $this->returnUrl, $this->opt);

            $this->logger->logInfo('createTransaction: - result:'. json_encode($transactionResult));
            if (isset($transactionResult) && isset($transactionResult->url) && isset($transactionResult->token))
            {
                $this->result = array(
                  'url' => $transactionResult->url,
                  'token_ws' => $transactionResult->token
                );
            } else {
                throw new \Exception("No se ha creado la transacción  para monto: ".$amount." session: ".$sessionId." orden de compra: ".$buyOrder);
            }
        } catch (\Exception $e) {
            $this->result = array(
                'error' => 'Error al crear la transacción ',
                'detail' => $e->getMessage()
            );
            $this->logger->logError(json_encode($this->result));
        }
        return $this->result;
    }

    public function commitTransaction($token)
    {
        try {
            $txDate = date('Y-m-d');
            $txTime = date('H:i:s');
            $this->logger->logInfo(
                'createTransaction:'.
                '- token: '.$token.
                ', txtDate: '.$txDate.
                ', txTime: '.$txTime
            );
            if (!isset($token) || $token == null || $token == " ") {
                throw new \Exception("El token de Webpay es requerido");
            }
            $this->result = WebpayPlus\Transaction::commit($token);

        } catch (\Exception $e) {
            $this->result = array(
                'error' => 'Error al confirmar la transacción ',
                'detail' => $e->getMessage()
            );
            $this->logger->logError(json_encode($this->result));
        }

        return $this->result;
    }

}
