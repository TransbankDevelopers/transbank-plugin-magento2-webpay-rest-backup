<?php

/**
 * Class CommitWebpayM22
 *
 * @category
 * @package Transbank\Webpay\Controller\Transaction
 *
 */


namespace Transbank\Webpay\Controller\Transaction;

use \Magento\Sales\Model\Order;

use Transbank\Webpay\WebpayPlus;
use Transbank\PluginsUtils\LogHandler;

class CommitWebpayM22 extends \Magento\Framework\App\Action\Action
{
    private $paymentTypeCodearray = array(
        "VD" => "Venta Debito",
        "VN" => "Venta Normal",
        "VC" => "Venta en cuotas",
        "SI" => "3 cuotas sin interés",
        "S2" => "2 cuotas sin interés",
        "NC" => "N cuotas sin interés",
    );

    private $cart;

    private $checkoutSession;

    private $resultJsonFactory;

    private $configProvider;

    private $log;
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    private $resultRawFactory;

    private $messageManager;


    public function __construct(\Magento\Framework\App\Action\Context $context,
                                \Magento\Checkout\Model\Cart $cart,
                                \Magento\Checkout\Model\Session $checkoutSession,
                                \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
                                \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
                                \Transbank\Webpay\Model\Config\ConfigProvider $configProvider)
    {
        parent::__construct($context);
        $this->cart = $cart;
        $this->checkoutSession = $checkoutSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->messageManager = $context->getMessageManager();
        $this->configProvider = $configProvider;
        $this->log = new LogHandler('magento');
    }

    public function execute()
    {
        $config = $this->configProvider->getPluginConfig();
        $orderStatusCanceled = $config['error_pay'];

        try {
            $order = $this->getOrder();
            $tokenWs = isset($_POST['token']) ? $_POST['token'] : null;
            if ($tokenWs != $this->checkoutSession->getTokenWs()) {
                throw new \Exception('Token inválido');
            }

            $paymentOk = $this->checkoutSession->getPaymentOk();

            if ($paymentOk == 'WAITING') {
                $result = WebpayPlus\Transaction::commit($tokenWs, $config);

                $this->checkoutSession->setResultWebpay($result);

                if (isset($result->buyOrder) && isset($result->responseCode)) {
                    $this->checkoutSession->setPaymentOk('SUCCESS');

                    $authorizationCode = $result->getAuthorizationCode();
                    $payment = $order->getPayment();
                    $payment->setLastTransId($authorizationCode);
                    $payment->setTransactionId($authorizationCode);
                    $payment->setAdditionalInformation([\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array)$result]);

                    $orderStatus = $config['successfully_pay'];
                    $order->setState($orderStatus)->setStatus($orderStatus);
                    $order->addStatusToHistory($order->getStatus(), json_encode($result));
                    $order->save();

                    $this->checkoutSession->getQuote()->setIsActive(false)->save();
                    $message = $this->getSuccessMessage($result);
                    $this->messageManager->addSuccess(__($message));
                    return $this->resultRedirectFactory->create()->setPath('checkout/onepage/success');

                } else {
                    $this->checkoutSession->setPaymentOk('FAIL');

                    $order->setState($orderStatusCanceled)->setStatus($orderStatusCanceled);
                    $order->addStatusToHistory($order->getStatus(), json_encode($result));
                    $order->save();
                    $this->checkoutSession->restoreQuote();
                    $message = $this->getRejectMessage($result);
                    $this->messageManager->addError(__($message));
                    return $this->resultRedirectFactory->create()->setPath('checkout/cart');
                }
            } else {
                $result = $this->checkoutSession->getResultWebpay();
                if ($paymentOk == 'SUCCESS') {
                    $message = $this->getSuccessMessage($result);
                    $this->messageManager->addSuccess(__($message));
                    return $this->resultRedirectFactory->create()->setPath('checkout/onepage/success');
                } else if ($paymentOk == 'FAIL') {
                    $this->checkoutSession->restoreQuote();
                    $message = $this->getRejectMessage($result);
                    $this->messageManager->addError(__($message));
                    return $this->resultRedirectFactory->create()->setPath('checkout/cart');
                }
            }
        } catch (\Exception $e) {
            $message = 'Error al confirmar transacción: ' . $e->getMessage();
            $this->log->logError($message);
            $this->checkoutSession->restoreQuote();
            $this->messageManager->addError(__($message));
            if ($order != null) {
                $order->setState($orderStatusCanceled)->setStatus($orderStatusCanceled);
                $order->addStatusToHistory($order->getStatus(), $message);
                $order->save();
            }
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }
    }

    private function getSuccessMessage($result)
    {
        if ($result->paymentTypeCode == "SI" ||
        $result->paymentTypeCode == "S2" ||
        $result->paymentTypeCode == "NC" ||
        $result->paymentTypeCode == "VC" ) {
            $tipoCuotas = $this->paymentTypeCodearray[$result->paymentTypeCode];
        } else {
            $tipoCuotas = "Sin Cuotas";
        }

        if ($result->responseCode == 0) {
            $transactionResponse = "Transacci&oacute;n Aprobada";
        } else {
            $transactionResponse = "Transacci&oacute;n Rechazada";
        }

        if($result->detailOutput->paymentTypeCode == "VD"){
            $paymentType = "Débito";
        } else {
            $paymentType = "Crédito";
        }
        $message = "<h2>Detalles del pago con Webpay</h2>
        <p>
            <br>
            <b>Respuesta de la Transacci&oacute;n: </b>{$transactionResponse}<br>
            <b>C&oacute;digo de la Transacci&oacute;n: </b>{$result->responseCode}<br>
            <b>Monto:</b> $ {$result->amount}<br>
            <b>Order de Compra: </b> {$result->buyOrder}<br>
            <b>Fecha de la Transacci&oacute;n: </b>".date('d-m-Y', strtotime($result->accountingDate))."<br>
            <b>Hora de la Transacci&oacute;n: </b>".date('H:i:s', strtotime($result->accountingDate))."<br>
            <b>Tarjeta: </b>************{$result->cardDetail->cardNumber}<br>
            <b>C&oacute;digo de autorizacion: </b>{$result->authorizationCode}<br>
            <b>Tipo de Pago: </b>{$paymentType}<br>
            <b>Tipo de Cuotas: </b>{$tipoCuotas}<br>
            <b>N&uacute;mero de cuotas: </b>{$result->installmentsNumber}
        </p>";

        return $message;

    }

    private function getRejectMessage($result) {
        if  (isset($result->detailOutput)) {
            $message = "<h2>Transacci&oacute;n rechazada con Webpay</h2>
            <p>
                <br>
                <b>Respuesta de la Transacci&oacute;n: </b>{$result->detailOutput->responseCode}<br>
                <b>Monto:</b> $ {$result->detailOutput->amount}<br>
                <b>Order de Compra: </b> {$result->detailOutput->buyOrder}<br>
                <b>Fecha de la Transacci&oacute;n: </b>".date('d-m-Y', strtotime($result->transactionDate))."<br>
                <b>Hora de la Transacci&oacute;n: </b>".date('H:i:s', strtotime($result->transactionDate))."<br>
                <b>Tarjeta: </b>************{$result->cardDetail->cardNumber}<br>
                <b>Mensaje de Rechazo: </b>{$result->detailOutput->responseDescription}
            </p>";
            return $message;
        } else if (isset($result['error'])) {
            $error = $result['error'];
            $detail = isset($result['detail']) ? $result['detail'] : 'Sin detalles';
            $message = "<h2>Transacci&oacute;n fallida con Webpay</h2>
            <p>
                <br>
                <b>Respuesta de la Transacci&oacute;n: </b>{$error}<br>
                <b>Mensaje: </b>{$detail}
            </p>";
            return $message;
        } else {
            $message = "<h2>Transacci&oacute;n Fallida</h2>";
            return $message;
        }
    }

    private function getOrder() {
        $orderId = $this->checkoutSession->getLastOrderId();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager->create('\Magento\Sales\Model\Order')->load($orderId);
    }
}
