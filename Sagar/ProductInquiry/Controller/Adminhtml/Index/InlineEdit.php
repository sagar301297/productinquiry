<?php

namespace Sagar\ProductInquiry\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Sagar\ProductInquiry\Model\ProductInquiryFactory;
use Sagar\ProductInquiry\Helper\Helper;

class InlineEdit extends Action
{
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;
    /**
     * @var ProductInquiryFactory
     */
    protected $inquiryFactory;
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * InlineEdit constructor.
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param ProductInquiryFactory $inquiryFactory
     * @param Helper $helper
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        ProductInquiryFactory $inquiryFactory,
        Helper $helper
        
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->inquiryFactory = $inquiryFactory;
        $this->helper = $helper;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $resultJson = $this->jsonFactory->create();
        $response = ['messages' => [], 'error' => false];

        $postData = $this->getRequest()->getParam('items', []);
        if (empty($postData)) {
            $response['messages'][] = __('Invalid data.');
            $response['error'] = true;
            return $resultJson->setData($response);
        }

        try {
            foreach ($postData as $entityId => $data) {
                $model = $this->inquiryFactory->create()->load($entityId);
                if ($model->getId()) {
                    $model->addData($data);
                    $model->save();
                    $data['template_identifier'] = $this->helper->getConfig("productinquiry/email/reply_template");
                    $data['template_options'] = [
                        'area' => \Magento\Framework\App\Area::AREA_ADMINHTML
                    ];
                    $data['template_vars'] = [
                        'customer_name' => $model->getName(),
                        'reply_message' => $model->getAdminReply()
                    ];
                    $SalesRepresentativeEmail = $this->helper->getConfig("trans_email/ident_sales/email");
                    $SalesRepresentativeName = $this->helper->getConfig("trans_email/ident_sales/name");
                    $data['send_from'] = [
                        'email' => $SalesRepresentativeEmail,
                        'name' => $SalesRepresentativeName];
                    $data['send_to'] = $model->getEmail();
                    // Send email to customer
                    $this->helper->sendEmail($data);
                }
            }
        } catch (\Exception $e) {
            $response['messages'][] = $e->getMessage();
            $response['error'] = true;
        }

        return $resultJson->setData($response);
    }
    
}
