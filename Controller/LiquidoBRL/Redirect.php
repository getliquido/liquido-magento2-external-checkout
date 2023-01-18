<?php

namespace Liquido\PayIn\Controller\LiquidoBRL;

use \Magento\Framework\App\ActionInterface;
use \Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\ResultFactory;
use \Liquido\PayIn\Helper\Data;
// use \Liquido\PayIn\Util\JWT;

class Redirect implements ActionInterface
{
    private Http $request;
    private ResultFactory $resultFactory;
    private Data $data;

    public function __construct(
        Http $request,
        ResultFactory $resultFactory,
        Data $data
    ) {
        $this->request = $request;
        $this->resultFactory = $resultFactory;
        $this->data = $data;
    }

    public function execute()
    {

        // https://10015.io/tools/jwt-encoder-decoder

        /* if ($this->request->getParam('token') == ""){
            echo "Invalid access.";
            return;            
        }

        $amount = 0.0;

        try {
            
            $jwt_token = $this->request->getParam('token');

            $secret = "secret-key-ABC-0192"; // ***********************
 
            // $payload = [
            //     "amount" => 19.00,
            //     "purchase" => "Plano Básico",
            //     "client" => "client@email.com"
            // ];
            // $jwt_token = JWT::encode($payload, $secret);
            // echo $jwt_token;

            $decoded = JWT::decode($jwt_token, $secret);
            // echo "<pre>";
            // print_r($decoded);
            // echo "</pre>";

            $amount = $decoded["amount"];
 
        } catch (\Exception $e) {
            // echo $e->getMessage();
            echo "Invalid token.";
            return;
        } */

        // *****************

        if ($this->request->getParam('product_id') == ""){
            echo "Invalid access.";
            return;            
        }

        $product_id = $this->request->getParam('product_id');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $objectManager->create('Magento\Catalog\Model\Product')->load($product_id);

        $price = $product->getPrice();

        $orderInfo = [
            'currency_id'  => 'BRL',
            'email'        => 'liquido@liquido.com', // buyer email id
            'shipping_address' => [
                'firstname'    => 'LIQUIDO BRL',
                'lastname'     => 'PAGAMENTOS DIGITAIS LTDA',
                'prefix' => '',
                'suffix' => '',
                'street' => 'Alameda Santos, 2300',
                'city' => 'São Paulo',
                'country_id' => 'BR',
                'region' => 'SP',
                // 'region_id' => '12', // State region id
                'postcode' => '01418200',
                'telephone' => '+55 11 947957681',
                // 'fax' => '12345',
                // 'save_in_address_book' => 1
            ],
            'items'=> [
                ['product_id'=>$product_id, 'qty'=>1, 'price'=>$price],
            ]
        ];

        // $orderData = $this->data->createOrder($orderInfo);
        $this->data->createOrder($orderInfo);

        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $redirect->setUrl('/checkout/liquidobrl/index');

        return $redirect;
    }
}
