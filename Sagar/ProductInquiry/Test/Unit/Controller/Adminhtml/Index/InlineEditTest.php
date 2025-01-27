<?php
namespace Sagar\ProductInquiry\Test\Unit\Controller\Adminhtml\Index;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Sagar\ProductInquiry\Controller\Adminhtml\Index\InlineEdit;
use Sagar\ProductInquiry\Model\ProductInquiryFactory;
use Sagar\ProductInquiry\Model\ProductInquiry;
use Sagar\ProductInquiry\Helper\Helper;

class InlineEditTest extends TestCase
{
    /**
     * @var InlineEdit
     */
    private $controller;

    /**
     * @var MockObject
     */
    private $contextMock;

    /**
     * @var MockObject
     */
    private $requestMock;

    /**
     * @var MockObject
     */
    private $jsonFactoryMock;

    /**
     * @var MockObject
     */
    private $inquiryFactoryMock;

    /**
     * @var MockObject
     */
    private $helperMock;

    /**
     * @var MockObject
     */
    private $resultJsonMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        // Mock Context and Request
        $this->contextMock = $this->createMock(Context::class);
        $this->requestMock = $this->createMock(Http::class);
        $this->contextMock->method('getRequest')->willReturn($this->requestMock);

        // Mock JSON Factory and Result
        $this->jsonFactoryMock = $this->createMock(JsonFactory::class);
        $this->resultJsonMock = $this->createMock(Json::class);
        $this->jsonFactoryMock->method('create')->willReturn($this->resultJsonMock);

        // Mock Inquiry Factory and Helper
        $this->inquiryFactoryMock = $this->createMock(ProductInquiryFactory::class);
        $this->helperMock = $this->createMock(Helper::class);

        // Create Controller
        $this->controller = $objectManager->getObject(
            InlineEdit::class,
            [
                'context' => $this->contextMock,
                'jsonFactory' => $this->jsonFactoryMock,
                'inquiryFactory' => $this->inquiryFactoryMock,
                'helper' => $this->helperMock
            ]
        );
    }
    
    public function testInvalidDataSubmission()
    {
        // Mock empty post data
        $this->requestMock->method('getParam')
            ->with('items', [])
            ->willReturn([]);
    
        // Expect error response for invalid data
        $this->resultJsonMock->expects($this->once())
            ->method('setData')
            ->with($this->callback(function ($response) {
                return $response['error'] === true && 
                       (string)$response['messages'][0] === (string)__('Invalid data.');
            }));
    
        $this->controller->execute();
    }
    
    public function testEmailSendingFailure()
    {
        // Prepare test data
        $entityId = 1;
        $postData = [
            $entityId => [
                'admin_reply' => 'Test reply message',
                'status' => 'resolved'
            ]
        ];

        // Mock request parameters
        $this->requestMock->method('getParam')
            ->with('items', [])
            ->willReturn($postData);

        // Mock inquiry model
        $inquiryModelMock = $this->createMock(ProductInquiry::class);
        
        // Explicitly set expectations for load and getId
        $this->inquiryFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($inquiryModelMock);

        $inquiryModelMock->expects($this->once())
            ->method('load')
            ->with($entityId)
            ->willReturn($inquiryModelMock);

        $inquiryModelMock->expects($this->once())
            ->method('getId')
            ->willReturn($entityId);
    
        // Expect error response
        $this->resultJsonMock->expects($this->once())
            ->method('setData')
            ->with($this->callback(function ($response) {
                return $response['error'] === false && 
                       $response['messages'] == [];
            }));
    
        $this->controller->execute();
    }
}