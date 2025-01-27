<?php
namespace Sagar\ProductInquiry\Model\ResourceModel\ProductInquiry;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Sagar\ProductInquiry\Model\ProductInquiry::class,
            \Sagar\ProductInquiry\Model\ResourceModel\ProductInquiry::class
        );
    }
}
