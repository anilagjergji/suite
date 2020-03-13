<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace PyzTest\Glue\Checkout\RestApi;

use Codeception\Util\HttpCode;
use Generated\Shared\DataBuilder\AddressBuilder;
use Generated\Shared\Transfer\DummyPaymentTransfer;
use Generated\Shared\Transfer\PaymentTransfer;
use Generated\Shared\Transfer\RestCheckoutErrorTransfer;
use Generated\Shared\Transfer\RestPaymentTransfer;
use Generated\Shared\Transfer\ShipmentMethodTransfer;
use PyzTest\Glue\Checkout\CheckoutApiTester;
use PyzTest\Glue\Checkout\RestApi\Fixtures\CheckoutRestApiFixtures;
use Spryker\Glue\CheckoutRestApi\CheckoutRestApiConfig;

/**
 * Auto-generated group annotations
 *
 * @group PyzTest
 * @group Glue
 * @group Checkout
 * @group RestApi
 * @group CheckoutRestApiCest
 * Add your own group annotations below this line
 * @group EndToEnd
 */
class CheckoutRestApiCest
{
    protected const RESPONSE_CODE_CART_IS_EMPTY = '1104';
    protected const RESPONSE_DETAILS_CART_IS_EMPTY = 'Cart is empty.';

    /**
     * @var \PyzTest\Glue\Checkout\RestApi\Fixtures\CheckoutRestApiFixtures
     */
    protected $fixtures;

    /**
     * @param \PyzTest\Glue\Checkout\CheckoutApiTester $I
     *
     * @return void
     */
    public function loadFixtures(CheckoutApiTester $I): void
    {
        /** @var \PyzTest\Glue\Checkout\RestApi\Fixtures\CheckoutRestApiFixtures $fixtures */
        $fixtures = $I->loadFixtures(CheckoutRestApiFixtures::class);
        $this->fixtures = $fixtures;
    }

    /**
     * @depends loadFixtures
     *
     * @param \PyzTest\Glue\Checkout\CheckoutApiTester $I
     *
     * @return void
     */
    public function requestWithNoItemsInQuote(CheckoutApiTester $I): void
    {
        // Arrange
        $customerTransfer = $this->fixtures->getCustomerTransfer();
        $I->authorizeCustomerToGlue($customerTransfer);

        $quoteTransfer = $this->fixtures->getEmptyQuoteTransfer();
        $shippingAddressTransfer = (new AddressBuilder())->build();
        $shipmentMethodTransfer = $this->fixtures->getShipmentMethodTransfer();

        $url = $I->buildCheckoutUrl();
        $requestPayload = [
            'data' => [
                'type' => CheckoutRestApiConfig::RESOURCE_CHECKOUT,
                'attributes' => [
                    'idCart' => $quoteTransfer->getUuid(),
                    'billingAddress' => $I->getAddressRequestPayload($quoteTransfer->getBillingAddress()),
                    'shippingAddress' => $I->getAddressRequestPayload($shippingAddressTransfer),
                    'customer' => $I->getCustomerRequestPayload($customerTransfer),
                    'payments' => [
                        [
                            RestPaymentTransfer::PAYMENT_METHOD_NAME => 'invoice',
                            RestPaymentTransfer::PAYMENT_PROVIDER_NAME => 'DummyPayment',
                            RestPaymentTransfer::PAYMENT_SELECTION => 'dummyPaymentInvoice',
                            RestPaymentTransfer::DUMMY_PAYMENT_INVOICE => [
                                DummyPaymentTransfer::DATE_OF_BIRTH => '08.04.1986',
                            ],
                            PaymentTransfer::AMOUNT => 899910,
                        ],
                    ],
                    'shipment' => [
                        ShipmentMethodTransfer::ID_SHIPMENT_METHOD => $shipmentMethodTransfer->getIdShipmentMethod(),
                        [
                            ShipmentMethodTransfer::ID => $shipmentMethodTransfer->getIdShipmentMethod(),
                            ShipmentMethodTransfer::CARRIER_NAME => $shipmentMethodTransfer->getCarrierName(),
                            ShipmentMethodTransfer::NAME => $shipmentMethodTransfer->getName(),
                            ShipmentMethodTransfer::TAX_RATE => $shipmentMethodTransfer->getTaxRate(),
                        ],
                    ],
                ],
            ],
        ];

        // Act
        $I->sendPOST($url, $requestPayload);

        // Assert
        $I->seeResponseCodeIs(HttpCode::UNPROCESSABLE_ENTITY);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesOpenApiSchema();

        $errors = $I->grabDataFromResponseByJsonPath('$.errors[0]');
        $I->assertEquals($errors[RestCheckoutErrorTransfer::CODE], static::RESPONSE_CODE_CART_IS_EMPTY);
        $I->assertEquals($errors[RestCheckoutErrorTransfer::STATUS], HttpCode::UNPROCESSABLE_ENTITY);
        $I->assertEquals($errors[RestCheckoutErrorTransfer::DETAIL], static::RESPONSE_DETAILS_CART_IS_EMPTY);
    }

    /**
     * @depends loadFixtures
     *
     * @param \PyzTest\Glue\Checkout\CheckoutApiTester $I
     *
     * @return void
     */
    public function requestWithOneItemInQuoteAndInvoicePayment(CheckoutApiTester $I): void
    {
        // Arrange
        $customerTransfer = $this->fixtures->getCustomerTransfer();
        $I->authorizeCustomerToGlue($customerTransfer);

        $shipmentMethodTransfer = $this->fixtures->getShipmentMethodTransfer();
        $quoteTransfer = $I->havePersistentQuoteWithItemsAndItemLevelShipment(
            $customerTransfer,
            [$I->getQuoteItemOverrideData($this->fixtures->getProductConcreteTransfer(), $shipmentMethodTransfer, 10)]
        );
        $shippingAddressTransfer = $quoteTransfer->getItems()[0]->getShipment()->getShippingAddress();

        $url = $I->buildCheckoutUrl();
        $requestPayload = [
            'data' => [
                'type' => CheckoutRestApiConfig::RESOURCE_CHECKOUT,
                'attributes' => [
                    'idCart' => $quoteTransfer->getUuid(),
                    'billingAddress' => $I->getAddressRequestPayload($quoteTransfer->getBillingAddress()),
                    'shippingAddress' => $I->getAddressRequestPayload($shippingAddressTransfer),
                    'customer' => $I->getCustomerRequestPayload($customerTransfer),
                    'payments' => $I->getPaymentRequestPayload(),
                    'shipment' => $I->getShipmentRequestPayload($shipmentMethodTransfer->getIdShipmentMethod()),
                ],
            ],
        ];

        // Act
        $I->sendPOST($url, $requestPayload);

        // Assert
        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesOpenApiSchema();

        $I->assertCheckoutResponseResourceHasCorrectData();

        $I->amSure('The returned resource has correct self link')
            ->whenI()
            ->seeSingleResourceHasSelfLink($url);
    }

    /**
     * @depends loadFixtures
     *
     * @param \PyzTest\Glue\Checkout\CheckoutApiTester $I
     *
     * @return void
     */
    public function requestWithOneItemInQuoteAndCreditCardPayment(CheckoutApiTester $I): void
    {
        // Arrange
        $customerTransfer = $this->fixtures->getCustomerTransfer();
        $I->authorizeCustomerToGlue($customerTransfer);

        $shipmentMethodTransfer = $this->fixtures->getShipmentMethodTransfer();
        $quoteTransfer = $I->havePersistentQuoteWithItemsAndItemLevelShipment(
            $customerTransfer,
            [$I->getQuoteItemOverrideData($this->fixtures->getProductConcreteTransfer(), $shipmentMethodTransfer, 10)]
        );
        $shippingAddressTransfer = $quoteTransfer->getItems()[0]->getShipment()->getShippingAddress();

        $url = $I->buildCheckoutUrl();
        $requestPayload = [
            'data' => [
                'type' => CheckoutRestApiConfig::RESOURCE_CHECKOUT,
                'attributes' => [
                    'idCart' => $quoteTransfer->getUuid(),
                    'billingAddress' => $I->getAddressRequestPayload($quoteTransfer->getBillingAddress()),
                    'shippingAddress' => $I->getAddressRequestPayload($shippingAddressTransfer),
                    'customer' => $I->getCustomerRequestPayload($customerTransfer),
                    'payments' => $I->getPaymentRequestPayload('credit card'),
                    'shipment' => $I->getShipmentRequestPayload($shipmentMethodTransfer->getIdShipmentMethod()),
                ],
            ],
        ];

        // Act
        $I->sendPOST($url, $requestPayload);

        // Assert
        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesOpenApiSchema();

        $I->assertCheckoutResponseResourceHasCorrectData();

        $I->amSure('The returned resource has correct self link')
            ->whenI()
            ->seeSingleResourceHasSelfLink($url);
    }

    /**
     * @depends loadFixtures
     *
     * @param \PyzTest\Glue\Checkout\CheckoutApiTester $I
     *
     * @return void
     */
    public function requestWithOneItemInQuoteAndPersistedAddresses(CheckoutApiTester $I): void
    {
        // Arrange
        $customerTransfer = $this->fixtures->getCustomerTransferWithPersistedAddress();
        $I->authorizeCustomerToGlue($customerTransfer);

        $shipmentMethodTransfer = $this->fixtures->getShipmentMethodTransfer();
        $quoteTransfer = $I->havePersistentQuoteWithItemsAndItemLevelShipment(
            $customerTransfer,
            [$I->getQuoteItemOverrideData($this->fixtures->getProductConcreteTransfer(), $shipmentMethodTransfer, 10)]
        );
        $persistedAddressTransfer = $customerTransfer->getAddresses()->getAddresses()[0];

        $url = $I->buildCheckoutUrl();
        $requestPayload = [
            'data' => [
                'type' => CheckoutRestApiConfig::RESOURCE_CHECKOUT,
                'attributes' => [
                    'idCart' => $quoteTransfer->getUuid(),
                    'billingAddress' => $I->getAddressRequestPayload($persistedAddressTransfer),
                    'shippingAddress' => $I->getAddressRequestPayload($persistedAddressTransfer),
                    'customer' => $I->getCustomerRequestPayload($customerTransfer),
                    'payments' => $I->getPaymentRequestPayload(),
                    'shipment' => $I->getShipmentRequestPayload($shipmentMethodTransfer->getIdShipmentMethod()),
                ],
            ],
        ];

        // Act
        $I->sendPOST($url, $requestPayload);

        // Assert
        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesOpenApiSchema();

        $I->assertCheckoutResponseResourceHasCorrectData();

        $I->amSure('The returned resource has correct self link')
            ->whenI()
            ->seeSingleResourceHasSelfLink($url);
    }
}
