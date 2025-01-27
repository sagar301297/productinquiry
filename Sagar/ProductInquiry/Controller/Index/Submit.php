<?php
declare(strict_types=1);

namespace Sagar\ProductInquiry\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Sagar\ProductInquiry\Model\ProductInquiryFactory;
use Sagar\ProductInquiry\Helper\Helper;

class Submit extends Action
{
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var ProductInquiryFactory
     */
    protected $productInquiryFactory;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Constructor
     *
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param ProductInquiryFactory $productInquiryFactory
     * @param Helper $helper
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        ProductInquiryFactory $productInquiryFactory,
        Helper $helper,
        ProductRepositoryInterface $productRepository
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->productInquiryFactory = $productInquiryFactory;
        $this->helper = $helper;
        $this->productRepository = $productRepository;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $resultJson = $this->jsonFactory->create();
        $response = ['success' => false, 'message' => __('Something went wrong. Please try again later.')];

        try {
            $postData = $this->validateAndPrepareData();

            //Save Data
            $this->saveProductInquiry($postData);

            $emailData = $this->prepareEmailData($postData);
            //Send Email
            $this->helper->sendEmail($emailData);
            
            $response = ['success' => true, 'message' => __('Thank you for your inquiry. We will get back to you soon.')];
        } catch (LocalizedException $e) {
            $response['message'] = $e->getMessage();
        } catch (\Exception $e) {
            $response['message'] = __('An unexpected error occurred. Please try again later.');
        }

        return $resultJson->setData($response);
    }

    /**
     * Validate and prepare input data
     *
     * @return array
     * @throws LocalizedException
     */
    private function validateAndPrepareData(): array
    {
        $postData = $this->getRequest()->getPostValue();
        
        if (empty($postData)) {
            throw new LocalizedException(__('Invalid form data.'));
        }

        $requiredFields = ['customer_name', 'customer_email', 'inquiry_subject', 'inquiry_message', 'product_id'];
        foreach ($requiredFields as $field) {
            if (empty($postData[$field])) {
                throw new LocalizedException(__('Please fill out all required fields.'));
            }
        }

        return $postData;
    }

    /**
     * Save product inquiry
     *
     * @param array $postData
     * @return \Sagar\ProductInquiry\Model\ProductInquiry
     */
    private function saveProductInquiry(array $postData)
    {
        $productInquiry = $this->productInquiryFactory->create();
        $productInquiry->setData([
            'name' => $postData['customer_name'],
            'email' => $postData['customer_email'],
            'subject' => $postData['inquiry_subject'],
            'message' => $postData['inquiry_message'],
            'product_id' => (int)$postData['product_id']
        ]);

        return $productInquiry->save();
    }

    /**
     * Prepare email data
     *
     * @param array $postData
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function prepareEmailData(array $postData): array
    {
        // Retrieve product name
        try {
            $product = $this->productRepository->getById((int)$postData['product_id']);
            $productName = $product->getName();
        } catch (\Exception $e) {
            $productName = __('Unknown Product')->render();
        }

        return [
            'template_identifier' => $this->helper->getConfig("productinquiry/email/template"),
            'template_options' => [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND
            ],
            'template_vars' => [
                'Name' => $postData['customer_name'],
                'Email' => $postData['customer_email'],
                'Subject' => $postData['inquiry_subject'],
                'Message' => $postData['inquiry_message'],
                'Product_Id' => $postData['product_id'],
                'Product_Name' => $productName
            ],
            'send_from' => [
                'email' => $this->helper->getConfig("trans_email/ident_general/email"),
                'name' => $this->helper->getConfig("trans_email/ident_general/name")
            ],
            'send_to' => $this->helper->getConfig("trans_email/ident_sales/email")
        ];
    }
}