<?php declare(strict_types=1);

namespace Ananta\ImprovedCheckout\Plugin\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessor as CheckoutLayoutProcessor;

/**
 * Move Billing address to top
 */
class LayoutProcessor
{
    protected $_customerSession;

    /**
     * @var customerRepository
     */
    private $customerRepository;

    public function __construct(
        \Magento\Customer\Model\Session $session,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ){
        $this->_customerSession = $session;
        $this->customerRepository = $customerRepository;
    }
    /**
     * @param CheckoutLayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(
        CheckoutLayoutProcessor $subject,
        array $jsLayout
    ): array {
        
        $paymentLayout = $jsLayout['components']['checkout']['children']['steps']
        ['children']['billing-step']['children']['payment']['children'];        
        if (isset($paymentLayout['afterMethods']['children']['billing-address-form'])) {
            $paymentLayout['afterMethods']['children']['billing-address-form']['sortOrder'] = 9999;
            $addresses = $this->getAddresses();
            if($this->_customerSession->isLoggedIn() && $addresses) {
                $jsLayout['components']['checkout']['children']['steps']
                ['children']['shipping-step']['children']['shippingAddress']['children']['before-form']['children']['billing-address-form']
                    = $paymentLayout['afterMethods']['children']['billing-address-form'];
            } else {                
                $jsLayout['components']['checkout']['children']['steps']
                ['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['billing-address-form']
                    = $paymentLayout['afterMethods']['children']['billing-address-form'];
            }

            unset($jsLayout['components']['checkout']['children']['steps']
            ['children']['billing-step']['children']['payment']
            ['children']['afterMethods']['children']['billing-address-form']);
        }

        return $jsLayout;
    }

    public function getAddresses()
    {
        try {
            $addressData = $this->customerRepository->getById($this->_customerSession->getCustomerId());
            $count = count($addressData->getAddresses());
        } catch (\Exception $exception) {
            $count = 0;
        }
        return $count;
    }
}

