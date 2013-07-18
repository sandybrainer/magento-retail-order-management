<?php
/**
 * tests the tax calculation class.
 */
class TrueAction_Eb2c_Tax_Test_Model_Overrides_CalculationTest extends EcomDev_PHPUnit_Test_Case
{
	public function setUp()
	{
        parent::setUp();
        $_SESSION = array();
        $_baseUrl = Mage::getStoreConfig('web/unsecure/base_url');
        $this->app()->getRequest()->setBaseUrl($_baseUrl);

		$this->addressMock = $this->getModelMock('sales/quote_address');
		$this->addressMock->expects($this->any())
			->method('getId')
			->will($this->returnValue(1));

		$taxQuoteMethods = array('getEffectiveRate', 'getCalculatedTax', 'getTaxableAmount');
		$taxQuote  = $this->getModelMock('eb2ctax/response_quote', $taxQuoteMethods);
		$taxQuote->expects($this->any())
			->method('getEffectiveRate')
			->will($this->returnValue(.5));
		$taxQuote->expects($this->any())
			->method('getCalculatedTax')
			->will($this->returnValue(0.38));
		$taxQuote->expects($this->any())
			->method('getTaxableAmount')
			->will($this->returnValue(10));

		$taxQuote2  = $this->getModelMock('eb2ctax/response_quote', $taxQuoteMethods);
		$taxQuote2->expects($this->any())
			->method('getEffectiveRate')
			->will($this->returnValue(0.01));
		$taxQuote2->expects($this->any())
			->method('getCalculatedTax')
			->will($this->returnValue(10.60));
		$taxQuote2->expects($this->any())
			->method('getTaxableAmount')
			->will($this->returnValue(5));
		$taxQuotes = array($taxQuote, $taxQuote2);

		$this->orderItem = $this->getModelMock(
			'eb2ctax/response_orderitem',
			array('getTaxQuotes', 'getMerchandiseAmount')
		);
		$this->orderItem->expects($this->any())
			->method('getTaxQuotes')
			->will($this->returnValue($taxQuotes));
		$orderItems = array($this->orderItem);
		$response = $this->getModelMock('eb2ctax/response', array('getResponseForItem'));
		$response->expects($this->any())
			->method('getResponseForItem')
			->will($this->returnValue($this->orderItem));
		$this->response = $response;
		$item = $this->getModelMock('sales/quote_item', array('getSku'));
		$item->expects($this->any())
			->method('getSku')
			->will($this->returnValue('somesku'));
		$this->item = $item;
	}

	/**
	 * @test
	 */
	public function testCalcTaxForItem()
	{
		$calc = Mage::getSingleton('tax/calculation');
		$calc->setTaxResponse($this->response);
		$value = $calc->getTaxForItem($this->item, $this->addressMock);
		$this->assertSame(10.98, $value);
	}

	/**
	 * @test
	 */
	public function testCalcTaxForItemAmount()
	{
		$calc = Mage::getSingleton('tax/calculation');
		$calc->setTaxResponse($this->response);
		$value = $calc->getTaxForItemAmount(1, $this->item, $this->addressMock, true);
		$this->assertSame(0.51, $value);
		$value = $calc->getTaxForItemAmount(1.1, $this->item, $this->addressMock, false);
		$this->assertSame(0.561, $value);
	}

	/**
	 * @test
	 */
	public function testSessionStore()
	{
		$calc = Mage::getModel('tax/calculation');
		$calc->setTaxResponse($this->response);
		$calc2 = Mage::getModel('tax/calculation');
		$this->assertNotNull($calc2->getTaxResponse());
		$this->assertSame($calc->getTaxResponse(), $calc2->getTaxResponse());
	}

	/**
	 * @test
	 */
	public function testGetTaxRequest()
	{
		$calc = Mage::getModel('tax/calculation');
		$request = $calc->getTaxRequest();
		$this->assertNotNull($request);
	}

	/**
	 * @test
	 */
	public function testGetTaxableForItem()
	{
		$this->markTestIncomplete('this is erroring out.')
		$calc = Mage::getModel('tax/calculation');
		$this->orderItem->expects($this->any())
			->method('getMerchandiseAmount')
			->will($this->returnValue(50));
		$calc->setTaxResponse($this->response);
		$amount = $calc->getTaxableForItem($this->item, $this->addressMock);
		$this->assertSame(15, $amount);
	}

	/**
	 * @test
	 */
	public function testGetTaxableForItem2()
	{
		$this->markTestIncomplete('this test is failing due to changes');
		$calc = Mage::getModel('tax/calculation');
		$this->orderItem->expects($this->any())
			->method('getMerchandiseAmount')
			->will($this->returnValue(7));
		$calc->setTaxResponse($this->response);
		$amount = $calc->getTaxableForItem($this->item, $this->addressMock);
		$this->assertSame(7, $amount);
	}

	/**
	 * @test
	 * @loadExpectation
	 */
	public function testGetAppliedRates()
	{
		$calc = Mage::getModel('tax/calculation');
		$calc->setTaxResponse($this->response);
		$itemSelector = new Varien_Object(
			array('item' => $this->item, 'address' => $this->addressMock)
		);
		$a = $calc->getAppliedRates($itemSelector);
		$this->assertNotEmpty($a);
		foreach ($a as $key => $group) {
			$this->assertNotEmpty($group);
			foreach ($group['rates'] as $index => $rate) {
				$expected = $this->expected('1-' . $index);
				$this->assertSame((float)$expected->getPercent(), $rate['percent']);
				$this->assertSame((float)$expected->getAmount(), $rate['amount']);
			}
		}
	}

	protected function _mockTaxQuote($percent, $tax, $rateKey = '', $taxable = 0)
	{
		$taxQuoteMethods = array('getRateKey', 'getEffectiveRate', 'getCalculatedTax', 'getTaxableAmount');
		$taxQuote  = $this->getModelMock('eb2ctax/response_quote', $taxQuoteMethods);
		$taxQuote->expects($this->any())
			->method('getEffectiveRate')
			->will($this->returnValue($percent));
		$taxQuote->expects($this->any())
			->method('getCalculatedTax')
			->will($this->returnValue($tax));
		$taxQuote->expects($this->any())
			->method('getTaxableAmount')
			->will($this->returnValue($taxable));
		$taxQuote->expects($this->any())
			->method('getRateKey')
			->will($this->returnValue($rateKey));
		return $taxQuote;
	}
}
