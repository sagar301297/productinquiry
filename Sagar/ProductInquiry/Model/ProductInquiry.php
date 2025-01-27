<?php

namespace Sagar\ProductInquiry\Model;

class ProductInquiry extends \Magento\Framework\Model\AbstractModel
{

    /**
     * ProductInquiry Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Sagar\ProductInquiry\Model\ResourceModel\ProductInquiry::class);
    }
}
