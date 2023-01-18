<?php

namespace Liquido\PayIn\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use Magento\Sales\Model\Order;
use Magento\Framework\App\ObjectManager;
 
class Data extends AbstractHelper
{
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product $product,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Sales\Model\Order $order
    ) {
        $this->_storeManager = $storeManager;
        $this->_product = $product;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->cartManagementInterface = $cartManagementInterface;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->order = $order;
        parent::__construct($context);
    }

    /*
    * create order programmatically
    */
    public function createOrder($orderData) {
        $store=$this->_storeManager->getStore();
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        $customer=$this->customerFactory->create();
        $customer->setWebsiteId($websiteId);
        $customer->loadByEmail($orderData['email']);// load customet by email address
        if(!$customer->getEntityId()){
            //If not avilable then create this customer 
            $customer->setWebsiteId($websiteId)
                    ->setStore($store)
                    ->setFirstname($orderData['shipping_address']['firstname'])
                    ->setLastname($orderData['shipping_address']['lastname'])
                    ->setEmail($orderData['email']) 
                    ->setPassword($orderData['email']);
            $customer->save();
        }
        
        $cartId = $this->cartManagementInterface->createEmptyCart(); //Create empty cart
        $quote = $this->cartRepositoryInterface->get($cartId); // load empty cart quote
        $quote->setStore($store);
          // if you have allready buyer id then you can load customer directly 
        $customer= $this->customerRepository->getById($customer->getEntityId());
        $quote->setCurrency();
        $quote->assignCustomer($customer); //Assign quote to customer
        

        $amount = 0.0;

        //add items in quote
        foreach($orderData['items'] as $item){
            $product=$this->_product->load($item['product_id']);
            $product->setPrice($item['price']);
            $quote->addProduct($product, intval($item['qty']));

            $amount += $item['price'] * $item['qty'];
        }

        //Set Address to quote
        $quote->getBillingAddress()->addData($orderData['shipping_address']);
        $quote->getShippingAddress()->addData($orderData['shipping_address']);

        // Collect Rates and Set Shipping & Payment Method
        $shippingAddress=$quote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true)
                        ->collectShippingRates()
                        // ->setShippingMethod('freeshipping_freeshipping');
                        ->setShippingMethod('flatrate_flatrate'); //shipping method

        $quote->setPaymentMethod('checkmo'); //payment method
        $quote->setInventoryProcessed(false); //not effetc inventory

        // Set Sales Order Payment
        $quote->getPayment()->importData(['method' => 'checkmo']);
        $quote->save(); //Now Save quote and your quote is ready
        
        // Collect Totals
        $quote->collectTotals();
    
        // Create Order From Quote
        $quote = $this->cartRepositoryInterface->get($quote->getId());

        // ******************
        $totals = 0;
        foreach ($quote->getAllAddresses() as $address) {
            if ($address->getAddressType() == "shipping") {
                $address->setShippingMethod('flatrate_flatrate');
                $address->setCollectShippingRates(true);
                $address->setSubtotal($totals);
                $address->setBaseSubtotal($totals);
                $address->setSubtotalInclTax($totals);
                $address->setGrandTotal($amount);
                $address->setShippingAmount(0);
                $address->setBaseShippingAmount(0);
                $address->setShippingInclTax(0);
                $address->setBaseShippingInclTax(0);
                $address->setBaseGrandTotal($amount);
                $address->setDiscountAmount(0);
                $address->setBaseDiscountAmount(0);
                $address->save();
            }
        }
        // ******************

        $orderId = $this->cartManagementInterface->placeOrder($quote->getId());
        $order = $this->order->load($orderId);
       
        $order->setEmailSent(0);
        $increment_id = $order->getRealOrderId();
        if($order->getEntityId()){
            $result['order_id']= $order->getRealOrderId();
        }else{
            $result=['error'=>1,'msg'=>'Error while placing order'];
        }
        return $result;
    }

    public function updateOrder($incrementId, $orderData)
    {
        try {
            $objectManager = ObjectManager::getInstance();
            $collection = $objectManager->create('Magento\Sales\Model\Order');
            $order = $collection->loadByIncrementId($incrementId);
            $order->setCustomerFirstName($orderData->getData("customerFirstName"));
            $order->setCustomerLastName($orderData->getData("customerLastName"));
            $order->setCustomerEmail($orderData->getData("customerEmail"));
            $order->save();
        } catch (\Exception $e) {
            echo "Not found";
            echo $e;
        }
    }

}
 
?>