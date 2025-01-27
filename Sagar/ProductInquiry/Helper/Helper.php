<?php

namespace Sagar\ProductInquiry\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ProductFactory;
use Psr\Log\LoggerInterface;

class Helper extends AbstractHelper
{
    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param Context $context
     * @param ProductFactory $productFactory
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        ProductFactory $productFactory,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->productFactory = $productFactory;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Get system configuration value
     *
     * @param string $configPath
     * @return mixed
     */
    public function getConfig(string $configPath)
    {
        return $this->scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if the module is enabled
     *
     * @return bool
     */
    public function isModuleEnabled(): bool
    {
        return (bool)$this->getConfig('productinquiry/general/enable');
    }

    /**
     * Send email with the given data
     *
     * @param array $data
     * @return bool
     * @throws MailException
     */
    public function sendEmail(array $data): bool
    {
        try {
            // Set store ID for template options
            $data['template_options']['store'] = $this->storeManager->getStore()->getId();

            // Build and send the email
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($data['template_identifier'])
                ->setTemplateOptions($data['template_options'])
                ->setTemplateVars($data['template_vars'])
                ->setFrom($data['send_from'])
                ->addTo($data['send_to'])
                ->getTransport();

            $transport->sendMessage();

            // Log success message if logging is enabled
            if ($this->isLogEnabled()) {
                $this->logger->info('Email Data::' . print_r($data, true));
            }

            return true;
        } catch (MailException $e) {
            // Log error if logging is enabled
            if ($this->isLogEnabled()) {
                $this->logger->error('Error sending email: ' . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * Check if email logging is enabled
     *
     * @return bool
     */
    public function isLogEnabled(): bool
    {
        return (bool)$this->getConfig('productinquiry/email/log_enable');
    }
}
