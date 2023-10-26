<?php

namespace Nati\OutsideSales\Services;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Ideris {

    const XML_PATH_IDERIS_URL = 'outsidesales/general_settings/ideris_url';
    const XML_PATH_IDERIS_ACCESS_KEY = 'outsidesales/general_settings/ideris_access_key';

    protected $_httpClient;
    protected $_scopeConfig;

    public function __construct(
        Curl $httpClient, 
        ScopeConfigInterface $scopeConfig
    ) {
        $this->_httpClient = $httpClient;
        $this->_scopeConfig = $scopeConfig;
    }

    public function getIderisUrl()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_IDERIS_URL,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getIderisAccessKey()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_IDERIS_ACCESS_KEY,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function token()
    {
        // Passa a chave JSON via POST para gerar o token de acesso a API
        $this->_httpClient->setHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ]);

        // Codifica os dados como JSON e os envia via POST
        $jsonData = json_encode(['login_token' => $this->getIderisAccessKey()]);
        $this->_httpClient->post($this->getIderisUrl() .'/Login', $jsonData);

        //Verifica se o token foi gerado com sucesso
        if($this->_httpClient->getStatus() != 200) {
            throw new \Exception('Não foi possível gerar o token de acesso a API. '. $this->_httpClient->getStatus() .' - '. $this->_httpClient->getBody() .' --- '. $this->getIderisUrl() .'/Login --- '. $jsonData);
        }

        return trim(str_replace('"','',$this->_httpClient->getBody()));
    }

    public function sales($period_init, $period_end, $offset = 0, $limit = 50)
    {
        $this->_httpClient->setHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => $this->token()
        ]);

        $this->_httpClient->get($this->getIderisUrl() .'/ListaPedido?dataInicialAtualizacao='. urlencode($period_init) .'&dataFinalAtualizacao='. urlencode($period_end) .'&offset='. $offset .'&limit='. $limit);
        
        //Verifica se a consulta foi realizada com sucesso
        if($this->_httpClient->getStatus() != 200) {
            throw new \Exception('Não foi possível consultar a API. '. $this->_httpClient->getStatus() .' - '. $this->_httpClient->getBody());
        }

        return json_decode($this->_httpClient->getBody());
    }

    public function order($order_id)
    {
        $this->_httpClient->setHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => $this->token()
        ]);

        $this->_httpClient->get($this->getIderisUrl() .'/Pedido/'. $order_id);

        //Verifica se a consulta foi realizada com sucesso
        if($this->_httpClient->getStatus() != 200) {
            throw new \Exception('Não foi possível consultar a API.');
        }

        return json_decode($this->_httpClient->getBody());
    }
}