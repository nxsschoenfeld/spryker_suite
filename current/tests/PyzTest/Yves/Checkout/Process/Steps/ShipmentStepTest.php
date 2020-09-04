<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace PyzTest\Yves\Checkout\Process\Steps;

use Codeception\Test\Unit;
use Generated\Shared\DataBuilder\ExpenseBuilder;
use Generated\Shared\DataBuilder\ItemBuilder;
use Generated\Shared\DataBuilder\QuoteBuilder;
use Generated\Shared\DataBuilder\ShipmentBuilder;
use Generated\Shared\Transfer\ExpenseTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\ShipmentTransfer;
use Spryker\Shared\Checkout\CheckoutConfig;
use Spryker\Yves\StepEngine\Dependency\Plugin\Handler\StepHandlerPluginCollection;
use Spryker\Yves\StepEngine\Dependency\Plugin\Handler\StepHandlerPluginInterface;
use SprykerShop\Yves\CheckoutPage\CheckoutPageDependencyProvider;
use SprykerShop\Yves\CheckoutPage\Dependency\Client\CheckoutPageToCalculationClientInterface;
use SprykerShop\Yves\CheckoutPage\Dependency\Service\CheckoutPageToShipmentServiceBridge;
use SprykerShop\Yves\CheckoutPage\Dependency\Service\CheckoutPageToShipmentServiceInterface;
use SprykerShop\Yves\CheckoutPage\GiftCard\GiftCardItemsChecker;
use SprykerShop\Yves\CheckoutPage\GiftCard\GiftCardItemsCheckerInterface;
use SprykerShop\Yves\CheckoutPage\Process\Steps\PostConditionCheckerInterface;
use SprykerShop\Yves\CheckoutPage\Process\Steps\ShipmentStep;
use SprykerShop\Yves\CheckoutPage\Process\Steps\ShipmentStep\PostConditionChecker;
use SprykerTest\Shared\Testify\Helper\LocatorHelperTrait;
use Symfony\Component\HttpFoundation\Request;

/**
 * Auto-generated group annotations
 *
 * @group PyzTest
 * @group Yves
 * @group Checkout
 * @group Process
 * @group Steps
 * @group ShipmentStepTest
 * Add your own group annotations below this line
 */
class ShipmentStepTest extends Unit
{
    use LocatorHelperTrait;

    /**
     * @var \PyzTest\Yves\Checkout\CheckoutBusinessTester
     */
    public $tester;

    /**
     * @return void
     */
    public function testShipmentStepExecuteShouldTriggerPlugins()
    {
        // Arrange
        $quoteTransfer = new QuoteTransfer();

        $shipmentTransfer = new ShipmentTransfer();
        $shipmentTransfer->setShipmentSelection(CheckoutPageDependencyProvider::PLUGIN_SHIPMENT_STEP_HANDLER);
        $quoteTransfer->setShipment($shipmentTransfer);

        $shipmentPluginMock = $this->createShipmentMock();
        $shipmentPluginMock->expects($this->once())->method('addToDataClass')->willReturn($quoteTransfer);

        $shipmentStepHandler = new StepHandlerPluginCollection();
        $shipmentStepHandler->add($shipmentPluginMock, CheckoutPageDependencyProvider::PLUGIN_SHIPMENT_STEP_HANDLER);
        $shipmentStep = $this->createShipmentStep($shipmentStepHandler);

        // Act
        $shipmentStep->execute($this->createRequest(), $quoteTransfer);
    }

    /**
     * @return void
     */
    public function testShipmentStepExecuteShouldTriggerPluginsWithItemLevelShipments()
    {
        // Arrange
        $shipmentBuilder = new ShipmentBuilder([ShipmentTransfer::SHIPMENT_SELECTION => CheckoutPageDependencyProvider::PLUGIN_SHIPMENT_STEP_HANDLER]);
        $quoteTransfer = (new QuoteBuilder())
            ->withItem((new ItemBuilder())->withShipment($shipmentBuilder))
            ->build();

        $shipmentPluginMock = $this->createShipmentMock();
        $shipmentPluginMock->expects($this->once())->method('addToDataClass')->willReturn($quoteTransfer);

        $shipmentStepHandler = new StepHandlerPluginCollection();
        $shipmentStepHandler->add($shipmentPluginMock, CheckoutPageDependencyProvider::PLUGIN_SHIPMENT_STEP_HANDLER);
        $shipmentStep = $this->createShipmentStep($shipmentStepHandler);

        // Act
        $shipmentStep->execute($this->createRequest(), $quoteTransfer);
    }

    /**
     * @return void
     */
    public function testShipmentPostConditionsShouldReturnTrueWhenShipmentSet()
    {
        $quoteTransfer = new QuoteTransfer();
        $expenseTransfer = new ExpenseTransfer();
        $expenseTransfer->setType(CheckoutConfig::SHIPMENT_EXPENSE_TYPE);
        $quoteTransfer->addExpense($expenseTransfer);

        $shipmentStep = $this->createShipmentStep(new StepHandlerPluginCollection());

        $this->assertTrue($shipmentStep->postCondition($quoteTransfer));
    }

    /**
     * @return void
     */
    public function testShipmentPostConditionsShouldReturnTrueWhenShipmentSetWithItemLevelShipments()
    {
        $shipmentTransfer = (new ShipmentBuilder([
            ShipmentTransfer::SHIPMENT_SELECTION => CheckoutPageDependencyProvider::PLUGIN_SHIPMENT_STEP_HANDLER,
        ]))->build();

        $quoteTransfer = (new QuoteBuilder())
            ->withExpense((new ExpenseBuilder([ExpenseTransfer::TYPE => CheckoutConfig::SHIPMENT_EXPENSE_TYPE])))
            ->withItem((new ItemBuilder()))
            ->build();

        $quoteTransfer->getItems()[0]->setShipment($shipmentTransfer);
        $quoteTransfer->getExpenses()[0]->setShipment($shipmentTransfer);

        $shipmentStep = $this->createShipmentStep(new StepHandlerPluginCollection());

        $this->assertTrue($shipmentStep->postCondition($quoteTransfer));
    }

    /**
     * @param \Spryker\Yves\StepEngine\Dependency\Plugin\Handler\StepHandlerPluginCollection $shipmentPlugins
     *
     * @return \SprykerShop\Yves\CheckoutPage\Process\Steps\ShipmentStep
     */
    protected function createShipmentStep(StepHandlerPluginCollection $shipmentPlugins): ShipmentStep
    {
        return new ShipmentStep(
            $this->createCalculationClientMock(),
            $shipmentPlugins,
            $this->createPostConditionChecker(),
            $this->createGiftCardItemsChecker(),
            'checkout-shipment',
            'home',
            []
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    protected function createRequest()
    {
        return Request::createFromGlobals();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\SprykerShop\Yves\CheckoutPage\Dependency\Client\CheckoutPageToCalculationClientInterface
     */
    protected function createCalculationClientMock(): CheckoutPageToCalculationClientInterface
    {
        $calculationMock = $this->getMockBuilder(CheckoutPageToCalculationClientInterface::class)->getMock();
        $calculationMock->method('recalculate')->willReturnArgument(0);

        return $calculationMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\SprykerShop\Yves\CheckoutPage\Process\Steps\PostConditionCheckerInterface
     */
    protected function createPostConditionChecker(): PostConditionCheckerInterface
    {
        return $this->getMockBuilder(PostConditionChecker::class)
            ->setConstructorArgs([$this->createShipmentServiceMock(), $this->createGiftCardItemsChecker()])
            ->enableProxyingToOriginalMethods()
            ->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\SprykerShop\Yves\CheckoutPage\GiftCard\GiftCardItemsCheckerInterface
     */
    protected function createGiftCardItemsChecker(): GiftCardItemsCheckerInterface
    {
        return $this->getMockBuilder(GiftCardItemsChecker::class)
            ->enableProxyingToOriginalMethods()
            ->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Yves\StepEngine\Dependency\Plugin\Handler\StepHandlerPluginInterface
     */
    protected function createShipmentMock(): StepHandlerPluginInterface
    {
        return $this->getMockBuilder(StepHandlerPluginInterface::class)->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\SprykerShop\Yves\CheckoutPage\Dependency\Service\CheckoutPageToShipmentServiceInterface
     */
    protected function createShipmentServiceMock(): CheckoutPageToShipmentServiceInterface
    {
        return $this->getMockBuilder(CheckoutPageToShipmentServiceBridge::class)
            ->setConstructorArgs([$this->tester->getShipmentService()])
            ->enableProxyingToOriginalMethods()
            ->getMock();
    }
}
