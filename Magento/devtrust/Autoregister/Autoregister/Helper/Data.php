<?php
/**
 * Copyright © 2018 Devtrust . All rights reserved.
 */
namespace devtrust\Autoregister\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Checkout\Model\Session;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    const XML_PATH_DEVAUTOREGISTER = 'devtrust_autoregister/';
    
    private $checkoutSession;
    private $orderFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Session $checkoutSessionData,
        \Magento\Sales\Model\OrderFactory $orderFactoryData
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSessionData;
        $this->orderFactory     = $orderFactoryData;
    }

    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getGeneralConfig($code, $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_DEVAUTOREGISTER .'general/'. $code, $storeId);
    }

    public function getOrder()
    {
        if ($this->checkoutSession->getLastRealOrderId()) {
             $order = $this->orderFactory->create()->loadByIncrementId($this->checkoutSession->getLastRealOrderId());
            return $order;
        }
        return false;
    }
}
