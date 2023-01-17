<?php

namespace Liquido\PayIn\Controller\LiquidoBRL;

use \Magento\Framework\App\ActionInterface;
use \Magento\Framework\App\Request\Http;
use \Magento\Framework\View\Result\PageFactory;
use \Liquido\PayIn\Helper\Data;
class Index implements ActionInterface
{
    private Http $request;
    private PageFactory $resultPageFactory;
    private Data $data;

    public function __construct(
        Http $request,
        PageFactory $resultPageFactory,
        Data $data
    ) {
        $this->request = $request;
        $this->resultPageFactory = $resultPageFactory;
        $this->data = $data;
    }

    public function execute()
    {
        return $this->resultPageFactory->create();
    }
}
