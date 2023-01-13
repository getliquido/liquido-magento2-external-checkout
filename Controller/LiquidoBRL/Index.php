<?php

namespace Liquido\PayIn\Controller\LiquidoBRL;

use \Magento\Framework\App\ActionInterface;
use \Magento\Framework\View\Result\PageFactory;
use \Liquido\PayIn\Helper\Data;

class Index implements ActionInterface
{
    private PageFactory $resultPageFactory;
    private Data $data;

    public function __construct(
        PageFactory $resultPageFactory,
        Data $data
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->data = $data;
    }

    public function execute()
    {

        $orderInfo =[
            'currency_id'  => 'BRL',
            'email'        => 'test@test.com', //customer email id
            'address' =>[
                'firstname'    => 'Rakesh',
                'lastname'     => 'Testname',
                'prefix' => '',
                'suffix' => '',
                'street' => 'B1 Abcd street',
                'city' => 'Los Angeles',
                'country_id' => 'US',
                'region' => 'California',
                'region_id' => '12', // State region id
                'postcode' => '45454',
                'telephone' => '1234512345',
                'fax' => '12345',
                'save_in_address_book' => 1
            ],
            /*
            'items'=>
                [
                    ['product_id'=>'1','qty'=>1], //simple product
                    ['product_id'=>'67','qty'=>2,'super_attribute' => array(93=>52,142=>167)] //configurable product, pass super_attribte for configurable product
                ]
                */
        ];
        
        $orderData = $this->data->createOrder($orderInfo);

        return $this->resultPageFactory->create();
    }
}
