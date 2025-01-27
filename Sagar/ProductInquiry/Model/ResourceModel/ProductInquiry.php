<?php
namespace Sagar\ProductInquiry\Model\ResourceModel;


class ProductInquiry extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define Maintable and primarykey
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sagar_product_inquiry', 'entity_id');
    }
}
