<?php
namespace Sagar\ProductInquiry\Test\Unit\Controller\Index;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\App\Request\Http;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Sagar\ProductInquiry\Controller\Index\Submit;
use Sagar\ProductInquiry\Model\ProductInquiryFactory;
use Sagar\ProductInquiry\Model\ProductInquiry;
use Sagar\ProductInquiry\Helper\Helper;

class SubmitTest extends TestCase
{
    /**
     * @var Submit
     */
    private $controller;

    /**
     * @var MockObject
     */
    private $jsonFactoryMock;

    /**
     * @var MockObject
     */
    private $productInquiryFactoryMock;

    /**
     * @var MockObject
     */
    private $helperMock;

    /**
     * @var MockObject
     */
    private $requestMock;

    /**
     * @var MockObject
     */
    private $contextMock;

    /**
     * @var MockObject
     */
    private $resultJsonMock;

    /**
     * @var MockObject
     */
    private $productRepositoryMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $messageManagerMock = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);

        $this->contextMock = $this->createMock(\Magento\Framework\App\Action\Context::class);
        $this->contextMock->method('getMessageManager')->willReturn($messageManagerMock);

        $this->requestMock = $this->createMock(Http::class);
        $this->contextMock->method('getRequest')->willReturn($this->requestMock);
        
        $this->jsonFactoryMock = $this->createMock(JsonFactory::class);
        $this->resultJsonMock = $this->createMock(Json::class);
        $this->jsonFactoryMock->method('create')->willReturn($this->resultJsonMock);

        $this->productInquiryFactoryMock = $this->createMock(ProductInquiryFactory::class);
        $this->helperMock = $this->createMock(Helper::class);
        $this->productRepositoryMock = $this->createMock(ProductRepositoryInterface::class);

        $this->controller = $objectManager->getObject(
            Submit::class,
            [
                'context' => $this->contextMock,
                'jsonFactory' => $this->jsonFactoryMock,
                'productInquiryFactory' => $this->productInquiryFactoryMock,
                'helper' => $this->helperMock,
                'productRepository' => $this->productRepositoryMock
            ]
        );
    }

    public function testSuccessfulSubmissionWithEmailNotification()
    {
        // Mock post data
        $postData = [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'inquiry_subject' => 'Product Question',
            'inquiry_message' => 'I want to know more about this product',
            'product_id' => 1,
        ];

        // Setup request mock
        $this->requestMock->method('getPostValue')->willReturn($postData);

        // Mock product
        $productMock = $this->createMock(ProductInterface::class);
        $productMock->method('getName')->willReturn('Test Product');
        $this->productRepositoryMock->method('getById')->willReturn($productMock);

        // Mock product inquiry model
        $productInquiryMock = $this->createMock(ProductInquiry::class);
        $this->productInquiryFactoryMock->method('create')->willReturn($productInquiryMock);
        $productInquiryMock->expects($this->once())->method('save');

        // Mock helper email configuration
        $this->helperMock->method('getConfig')
            ->willReturnMap([
                ['productinquiry/email/template', 'product_inquiry_template'],
                ['trans_email/ident_general/email', 'general@example.com'],
                ['trans_email/ident_general/name', 'General Contact'],
                ["trans_email/ident_sales/email", "sagarparikh301297@gmail.com"]
            ]);

        // Expect email to be sent
        $this->helperMock->expects($this->once())
            ->method('sendEmail')
            ->with($this->callback(function ($data) use ($postData) {
                $this->assertEquals('product_inquiry_template', $data['template_identifier']);
                $this->assertEquals($postData['customer_name'], $data['template_vars']['Name']);
                $this->assertEquals($data['send_to'], $data['send_to']);
                $this->assertEquals($data['template_vars']['Product_Name'], $data['template_vars']['Product_Name']);
                return true;
            }));

        // Expect successful JSON response
        $this->resultJsonMock->expects($this->once())
            ->method('setData')
            ->with($this->callback(function ($response) {
                return $response['success'] === true 
                && (string)$response['message'] === (string)__('Thank you for your inquiry. We will get back to you soon.');
            }));

        $this->controller->execute();
    }

    public function testSubmissionWithMissingFields()
    {
        // Mock incomplete post data
        $postData = [
            'customer_name' => '',
            'customer_email' => 'john@example.com'
        ];

        $this->requestMock->method('getPostValue')->willReturn($postData);

        // Expect error response
        $this->resultJsonMock->expects($this->once())
            ->method('setData')
            ->with($this->callback(function ($response) {
                return $response['success'] === false 
                && (string)$response['message'] === (string)__('Please fill out all required fields.');
            }));

        $this->controller->execute();
    }
    
}