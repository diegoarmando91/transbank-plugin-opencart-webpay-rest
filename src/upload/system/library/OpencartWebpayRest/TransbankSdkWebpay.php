<?php
namespace OpencartWebpayRest;

require_once(__DIR__ . '/../transbank/vendor/autoload.php');

use Transbank\Webpay\Configuration;
use Transbank\Webpay\Webpay;
use Transbank\Webpay\WebpayPlus;
use Transbank\Webpay\WebpayPlus\Transaction;

class TransbankSdkWebpay
{
    
    const PLUGIN_VERSION = '1.0.0'; //version of plugin payment
    
    function __construct($config = null, $log = null)
    {
        $this->log = $log;
        if (isset($config)) {
            $environment = isset($config["MODO"]) ? $config["MODO"] : 'INTEGRACION';
            if ($environment != "INTEGRACION") {
                WebpayPlus::setApiKey($config['API_KEY']);
                WebpayPlus::setCommerceCode($config['COMMERCE_CODE']);
                WebpayPlus::setIntegrationType($environment);
            }
        }
    }
    
    public function initTransaction($amount, $sessionId, $buyOrder, $returnUrl)
    {
        $result = [];
        try {
            $txDate = date('d-m-Y');
            $txTime = date('H:i:s');
            $this->log->logInfo('initTransaction - amount: ' . $amount . ', sessionId: ' . $sessionId . ', buyOrder: ' . $buyOrder . ', txDate: ' . $txDate . ', txTime: ' . $txTime);
            
            $response = Transaction::create($buyOrder, $sessionId, $amount, $returnUrl);
            $this->log->logInfo('initTransaction - initResult: ' . json_encode($response));
            if (isset($response) && isset($response->url) && isset($response->token)) {
                $result = [
                    "url" => $response->url,
                    "token_ws" => $response->token
                ];
            } else {
                throw new \Exception('No se ha creado la transacción para, amount: ' . $amount . ', sessionId: ' . $sessionId . ', buyOrder: ' . $buyOrder);
            }
        } catch (\Exception $e) {
            $result = [
                "error" => 'Error al crear la transacción',
                "detail" => $e->getMessage()
            ];
            $this->log->logError(json_encode($result));
        }
        
        return $result;
    }
    
    public function commitTransaction($tokenWs)
    {
        $result = [];
        try {
            $this->log->logInfo('getTransactionResult - tokenWs: ' . $tokenWs);
            if ($tokenWs == null) {
                throw new \Exception("El token webpay es requerido");
            }
            
            return Transaction::commit($tokenWs);
        } catch (\Exception $e) {
            $result = [
                "error" => 'Error al confirmar la transacción',
                "detail" => $e->getMessage()
            ];
            $this->log->logError(json_encode($result));
        }
        
        return $result;
    }
}
