<?php

namespace Liquido\PayIn\Block\Brl;

use \Magento\Framework\View\Element\Template;

use \Liquido\PayIn\Util\Brl\LiquidoBrlPayInMethod;

class LiquidoBrlPixForm extends Template
{
    public function getPixPayInMethodName()
    {
        return LiquidoBrlPayInMethod::PIX["title"];
    }
}
