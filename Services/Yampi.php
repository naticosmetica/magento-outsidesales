<?php

namespace Nati\OutsideSales\Services;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Yampi {

    const XML_PATH_YAMPI_URL = 'outsidesales_yampi/general_settings_yampi/yampi_url';
    const XML_PATH_YAMPI_ACCESS = 'outsidesales_yampi/general_settings_yampi/yampi_access';
    const XML_PATH_YAMPI_ACCESS_KEY = 'outsidesales_yampi/general_settings_yampi/yampi_access_key';

    protected $_httpClient;
    protected $_scopeConfig;

    public function __construct(
        Curl $httpClient, 
        ScopeConfigInterface $scopeConfig
    ) {
        $this->_httpClient = $httpClient;
        $this->_scopeConfig = $scopeConfig;
    }

    public function getYampiUrl()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_YAMPI_URL,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getYampiAccessKey()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_YAMPI_ACCESS_KEY,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getYampiAccess()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_YAMPI_ACCESS,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function sales($period_init, $period_end, $page = 0, $limit = 50, $periodType = '')
    {
        $this->_httpClient->setHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'User-Token' => $this->getYampiAccess(),
            'User-Secret-Key' => $this->getYampiAccessKey()
        ]);

        $date_filter = 'created_at';
        $status_filter = [
            'status_id' => [4,5,6,7,10,12]
        ];

        //Altera os filtros de acordo com o tipo de periodo
        if($periodType === 'Atualizacao') {
            $date_filter = 'updated_at';
            $status_filter = [];
        }

        $this->_httpClient->get($this->getYampiUrl() .'/orders?date='. $date_filter .':'. urlencode(substr($period_init,0,10)) .'|'. urlencode(substr($period_end,0,10)) .'&'. http_build_query($status_filter) .'&page='. $page .'&limit='. $limit);
        
        //Verifica se a consulta foi realizada com sucesso
        if($this->_httpClient->getStatus() != 200) {
            throw new \Exception('Não foi possível consultar a API da Yampi. '. $this->_httpClient->getStatus() .' - '. $this->_httpClient->getBody());
        }

        return json_decode($this->_httpClient->getBody());
    }

    public function order($order_id)
    {
        $this->_httpClient->setHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'User-Token' => $this->getYampiAccess(),
            'User-Secret-Key' => $this->getYampiAccessKey()
        ]);

        $this->_httpClient->get($this->getYampiUrl() .'/orders/'. $order_id .'?include=items,customer,marketplace,status,shipping_address,transactions,seller,labels,services');

        //Verifica se a consulta foi realizada com sucesso
        if($this->_httpClient->getStatus() != 200) {
            throw new \Exception('Não foi possível consultar a API da Yampi.');
        }

        return json_decode($this->_httpClient->getBody());
    }
}