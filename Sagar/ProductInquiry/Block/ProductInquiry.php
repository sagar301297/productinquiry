<?php

namespace Sagar\ProductInquiry\Block;

use Magento\Catalog\Block\Product\View\AbstractView;
use Magento\Catalog\Block\Product\Context;
use Magento\Framework\Stdlib\ArrayUtils;

class ProductInquiry extends AbstractView
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var \Sagar\ProductInquiry\Helper\Helper
     */
    protected $helper;

    /**
     * Data constructor.
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param \Sagar\ProductInquiry\Helper\Helper $helper
     * @param Context $context
     * @param ArrayUtils $arrayUtils
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Sagar\ProductInquiry\Helper\Helper $helper,
        Context $context,
        ArrayUtils $arrayUtils,
        array $data = []

    ) {
        $this->_customerSession = $customerSession->create();
        $this->helper = $helper;
        parent::__construct($context, $arrayUtils, $data);
    }
    /**
     * Get the current product Id
     *
     * @return int
     */
    public function getProductId()
    {
        return $this->getProduct()->getId();
    }

    /**
     * Get user email and username
     *
     * @return array
     */
    public function getCustomerDetails()
    {
        $customer = $this->_customerSession->getCustomer();
        $customerEmail = !empty(trim((string)$customer->getEmail())) ? $customer->getEmail() : '';
        $customerName = !empty(trim((string)$customer->getName())) ? $customer->getName() : ''; // Assuming `getName()` returns the full name

        return [
            'email' => $customerEmail,
            'name' => $customerName
        ];
    }
    public function IsModuleEnable()
    {
        return $this->helper->isModuleEnabled();
    }
}
