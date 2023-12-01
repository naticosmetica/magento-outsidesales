<?php

namespace Nati\OutsideSales\Services;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Bling {

    const XML_PATH_BLING_URL = 'outsidesales_bling/general_settings_bling/bling_url';
    const XML_PATH_BLING_CLIENT_ID = 'outsidesales_bling/general_settings_bling/bling_client_id';
    const XML_PATH_BLING_SECRET_ID = 'outsidesales_bling/general_settings_bling/bling_secret_id';

    protected $_httpClient;
    protected $_scopeConfig;

    public function __construct(
        Curl $httpClient, 
        ScopeConfigInterface $scopeConfig
    ) {
        $this->_httpClient = $httpClient;
        $this->_scopeConfig = $scopeConfig;
    }

    public function getBlingUrl()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_BLING_URL,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getBlingClientId()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_BLING_CLIENT_ID,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getBlingSecretId()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_BLING_SECRET_ID,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function refreshToken($refresh_token = null) {

        // Passa a chave JSON via POST para gerar o token de acesso a API, add bearer token
        $this->_httpClient->setHeaders([
            'Authorization' => 'Basic '. base64_encode($this->getBlingClientId() .':'. $this->getBlingSecretId())
        ]);

        $this->_httpClient->post($this->getBlingUrl() .'/oauth/token', [
            'grant_type' => 'refresh_token', 
            'refresh_token' => $refresh_token
        ]);

        //Verifica se a consulta foi realizada com sucesso
        if($this->_httpClient->getStatus() != 200) {
            throw new \Exception('Não foi possível gerar o token de acesso.');
        }

        return json_decode($this->_httpClient->getBody());
    }

    public function getBlingAccessToken()
    {
        //Consulta banco de dados nati_refresh_tokens para pegar o token
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('nati_refresh_tokens');

        $result = $connection->fetchAll("SELECT * FROM " . $tableName . " WHERE provider = 'bling' LIMIT 1");

        if(empty($result[0]['token'])) {
            throw new \Exception('Não existe token bling cadastrado.');
        }

        // Verifica se a data next_cicle é menor que a data atual, se for, gera um novo token
        if(!empty($result[0]['next_cicle']) && strtotime($result[0]['next_cicle']) < strtotime(date('Y-m-d H:i:s'))) {

            $token = $this->refreshToken($result[0]['refresh_token']);

            // Faz um update com o access_token e o refresh_token e next_cicle no banco de dados
            if(!empty($token)) {
                $connection->query("UPDATE ". $tableName ."
                SET token = '". $token->access_token ."', 
                    refresh_token = '". $token->refresh_token ."', 
                    next_cicle = '". date('Y-m-d H:i:s ', strtotime('now + '. $result[0]['cicle'] .' seconds')) ."' 
                WHERE provider = 'bling' 
                LIMIT 1");
            }

            return $token->access_token;
        }

        return $result[0]['token'];
    }

    public function getByOrder($id)
    {
        // Passa a chave JSON via POST para gerar o token de acesso a API, add bearer token
        $this->_httpClient->setHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '. $this->getBlingAccessToken()
        ]);

        $this->_httpClient->get($this->getBlingUrl() .'/pedidos/vendas?numero='. $id);

        //Verifica se a consulta foi realizada com sucesso
        if($this->_httpClient->getStatus() != 200) {
            throw new \Exception('Não foi possível consultar o frete informado.');
        }

        return json_decode($this->_httpClient->getBody());
    }
}