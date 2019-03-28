<?php
namespace devtrust\Autoregister\Observer;

use Magento\Sales\Model\Order;
use Magento\Customer\Model\Customer;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class GuestRegister implements \Magento\Framework\Event\ObserverInterface
{
    private $storeManager;
    private $helperData;
    private $addressData;
    private $scopeConfig;
    private $postObject;
    private $transportBuilder;
    private $messageManager;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;
    private $orderSales;
    private $customerData;
    
    /**
     * @param \Magento\Framework\App\Action\Context      $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\CustomerFactory    $customerFactory
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \devtrust\Autoregister\Helper\Data $helperData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\DataObject $postObject,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Sales\Model\Order $orderSales,
        \Magento\Customer\Model\Customer $customerData
    ) {
        $this->addressData      = $addressFactory;
        $this->storeManager     = $storeManager;
        $this->customerFactory  = $customerFactory;
        $this->helperData       = $helperData;
        $this->scopeConfig      = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->postObject       = $postObject;
        $this->messageManager   = $messageManager;
        $this->orderSales       = $orderSales;
        $this->customerData     = $customerData;
    }
    private function randomPassword()
    {
        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        $pass = [];
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass);
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $observer->getEvent()->getOrderIds();
        
        $status = $this->helperData->getGeneralConfig('status');
        if ($status == 1) {
            try {
                    $order = $this->helperData->getOrder();
                    $orderId = $order->getEntityId();
                    $websiteId  = $this->storeManager->getWebsite()->getWebsiteId();
                    $BillingAddress = $order->getBillingAddress();
             
                if ($order->getCustomerIsGuest()) {
                    $customer = $this->customerFactory->create();
                    $customer->setWebsiteId($websiteId);
                    $customer_data = $customer->loadByEmail($BillingAddress->getEmail());
                    if (!$customer_data->getId()) {
                        $password = $this->randomPassword();
                        $customer->setEmail($BillingAddress->getEmail());
                        $customer->setFirstname($BillingAddress->getFirstname());
                        $customer->setLastname($BillingAddress->getLastname());
                        $customer->setPassword($password);
                        $customer->setGroupId("1");
                        if ($customer->save()) {
                            ##assing order to customer
                            $order = $this->orderSales->load($orderId);
                            $order->setCustomerId($customer->getEntityId());
                            $order->setCustomerIsGuest(0);
                            $order->setCustomerGroupId(1);
                            $order->setCustomerFirstname($BillingAddress->getFirstname());
                            $order->setCustomerLastname($BillingAddress->getLastname());
                            $order->save();
                            
                            $address = $this->addressData->create();
                            $address->setCustomerId($customer->getId())
                                ->setFirstname($BillingAddress->getFirstname())
                                ->setLastname($BillingAddress->getLastname())
                                ->setCountryId($BillingAddress->getCountryID())
                                ->setPostcode($BillingAddress->getPostcode())
                                ->setCity($BillingAddress->getCity())
                                ->setRegion($BillingAddress->getRegion())
                                ->setRegion($BillingAddress->getRegionId())
                                ->setTelephone($BillingAddress->getTelephone())
                                ->setCompany($BillingAddress->getCompany())
                                ->setStreet($BillingAddress->getStreet())
                                ->setIsDefaultBilling('1')
                                ->setIsDefaultShipping('1')
                                ->setSaveInAddressBook('1');
                            $address->save();
                    ##Send mail
                            $vars = [
                            'name' => $customer->getData('firstname'),
                            'email' => $customer->getData('email'),
                            'pass' => $customer->getData('password')
                            ];
                            $data_post = $this->postObject->setData($vars);
                            $this->sendregistrationMail($data_post);
                        }
                    } else {
                        ##assing order to exists customer
                        $customer_Info=$this->customerData->load($customer_data->getId());
                
                         $order = $this->orderSales->load($orderId);
                         $order->setCustomerId($customer_data->getId());
                         $order->setCustomerIsGuest(0);
                         $order->setCustomerGroupId(1);
                         $order->setCustomerFirstname($customer_Info->getFirstname());
                         $order->setCustomerLastname($customer_Info->getLastname());
                         $order->save();
                    }
                }
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('An error occurred on the server. Please try again..')
                );
            }
        }
    }
    private function sendregistrationMail($data_post)
    {
        try {
            $transport = $this->transportBuilder
                ->setTemplateIdentifier('devtrust_Autoregister_email_template')
                ->setTemplateOptions([
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                ])
                ->setTemplateVars(['data' => $data_post])
                ->setFrom([
                    'email' => $this->scopeConfig
                    ->getValue('trans_email/ident_general/email', ScopeInterface::SCOPE_STORE),
                    'name' => $this->scopeConfig
                    ->getValue('trans_email/ident_general/name', ScopeInterface::SCOPE_STORE)
                ])
                ->addTo($data_post->getData('email'))
                ->getTransport();
                $transport->sendMessage();
        } catch (\Exception $e) {
            $this->messageManager->addError(
                __('Due to some issue the email not sent.')
            );
        }
    }
}
