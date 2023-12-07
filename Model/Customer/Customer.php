<?php

namespace Nati\OutsideSales\Model\Customer;

use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\AddressFactory;
use Magento\Directory\Model\RegionFactory;

class Customer {

    protected $_customerCollectionFactory;
    protected $_customerFactory;
    protected $_addressFactory;
    protected $_regionFactory;

    public function __construct(
        CollectionFactory $customerCollectionFactory, 
        CustomerFactory $customerFactory,
        AddressFactory $addressFactory,
        RegionFactory $regionFactory
    ) {
        $this->_customerCollectionFactory = $customerCollectionFactory;
        $this->_customerFactory = $customerFactory;
        $this->_addressFactory = $addressFactory;
        $this->_regionFactory = $regionFactory;
    }

    public function getIdCustomerForDocument($document)
    {
        // Filtrar coleção de clientes pelo CPF/CNPJ
        $collection = $this->_customerCollectionFactory->create();
        $collection->addAttributeToFilter('taxvat', $document);
        $customer = $collection->getFirstItem();
        $customer_id = $customer->getId();

        if (!empty($customer_id)) {
            return $customer_id;
        }

        return null;
    }

    public function getIdCustomerForEmail($email)
    {
        // Filtrar coleção de clientes pelo CPF/CNPJ
        $collection = $this->_customerCollectionFactory->create();
        $collection->addAttributeToFilter('email', $email);
        $customer = $collection->getFirstItem();
        $customer_id = $customer->getId();

        if (!empty($customer_id)) {
            return $customer_id;
        }

        return null;
    }

    public function getCustomer($customerId)
    {
        $customer = $this->_customerFactory->create()->load($customerId);
        if(!empty($customer->getId())) {
            return $customer;
        }

        return null;
    }

    public function createCustomer($data)
    {
        // Caso contrário, crie um novo cliente
        $customer = $this->_customerFactory->create();
        $customer->setFirstname($data['firstname'])
                    ->setLastname($data['lastname'] ?? $data['firstname'])
                    ->setEmail($data['document'] . '_account@temporary.com.br')
                    ->setTaxvat($data['document'])
                    ->save();
        
        return $customer->getId();
    }

    public function addAddressesToCustomer($customerId, $data)
    {
        $customer = $this->_customerFactory->create()->load($customerId);

        // Adicionar endereço de entrega (envio)
        if(!empty($data)) {

            // Verifica se endereço já existe
            $customerAddresses = $customer->getAddresses();

            foreach ($customerAddresses as $existingAddress) {
                if ($existingAddress->getStreet()[0] == $data['street'] && $existingAddress->getPostcode() == $data['postcode']) {
                    return;
                }
            }

            // Obtendo region_id para o endereço de cobrança
            $regionId = $this->_regionFactory->create()
                            ->loadByName($data['region'], 'BR')
                            ->getId();
            
            // Realiza o cadastro do endereço de cobrança
            $address = $this->_addressFactory->create();
            $address->setShouldIgnoreValidation(true);
            $address->setCustomerId($customer->getId())
                            ->setFirstname($data['firstname'])
                            ->setLastname($data['lastname'] ?? $data['firstname'])
                            ->setPostcode($data['postcode'])
                            ->setCity($data['city'])
                            ->setStreet($data['street'])
                            ->setRegion($data['region'])
                            ->setRegionId($regionId)
                            ->setCountryId('BR')
                            ->setIsDefaultShipping('1')
                            ->save();
        }
    }
}