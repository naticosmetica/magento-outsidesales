<?php

namespace Nati\OutsideSales\Services;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class FreteRapido {

    const XML_PATH_FRETERAPIDO_URL = 'outsidesales_freterapido/general_settings_freterapido/freterapido_url';
    const XML_PATH_FRETERAPIDO_ACCESS_KEY = 'outsidesales_freterapido/general_settings_freterapido/freterapido_access_key';

    protected $_httpClient;
    protected $_scopeConfig;

    public function __construct(
        Curl $httpClient, 
        ScopeConfigInterface $scopeConfig
    ) {
        $this->_httpClient = $httpClient;
        $this->_scopeConfig = $scopeConfig;
    }

    public function getFreteRapidoUrl()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_FRETERAPIDO_URL,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getFreteRapidoAccessKey()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_FRETERAPIDO_ACCESS_KEY,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getByOrder($order_id)
    {
        $this->_httpClient->get($this->getFreteRapidoUrl() .'/quote/find?order_number='. $order_id .'&token='. $this->getFreteRapidoAccessKey());

        //Verifica se a consulta foi realizada com sucesso
        if($this->_httpClient->getStatus() != 200) {
            throw new \Exception('Não foi possível consultar o frete informado.');
        }

        return json_decode($this->_httpClient->getBody());
    }
}