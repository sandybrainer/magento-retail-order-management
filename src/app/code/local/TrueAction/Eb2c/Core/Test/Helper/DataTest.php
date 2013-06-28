<?php
/**
 * @category  TrueAction
 * @package   TrueAction_Eb2c
 * @copyright Copyright (c) 2013 True Action (http://www.trueaction.com)
 */
class TrueAction_Eb2c_Core_Test_Helper_DataTest extends EcomDev_PHPUnit_Test_Case
{
	protected $_helper;

	/**
	 * setUp method
	 */
	public function setUp()
	{
		parent::setUp();
		$this->_helper = $this->_getHelper();
	}

	/**
	 * Get Dom instantiated object.
	 *
	 * @return TrueAction_Dom_Document
	 */
	public function getDomDocument()
	{
		return new TrueAction_Dom_Document('1.0', 'UTF-8');
	}

	/**
	 * Get helper instantiated object.
	 *
	 * @return TrueAction_Eb2c_Core_Helper_Data
	 */
	protected function _getHelper()
	{
		if (!$this->_helper) {
			$this->_helper = Mage::helper('eb2ccore');
		}
		return $this->_helper;
	}

	public function providerApiCall()
	{
		$domDocument = $this->getDomDocument();
		$quantityRequestMessage = $domDocument->addElement('QuantityRequestMessage', null, 'http://api.gsicommerce.com/schema/checkout/1.0')->firstChild;
		$quantityRequestMessage->createChild(
			'QuantityRequest',
			null,
			array('lineId' => 1, 'itemId' => 'SKU-1234')
		);
		$quantityRequestMessage->createChild(
			'QuantityRequest',
			null,
			array('lineId' => 2, 'itemId' => 'SKU-4321')
		);
		return array(
			array(
				$domDocument, 'http://eb2c.edge.mage.tandev.net/GSI%20eb2c%20Web%20Service%20Schemas%20v1.0/Inventory-Service-Quantity-1.0.xsd'
			)
		);
	}

	/**
	 * testing callApi method
	 *
	 * @test
	 * @dataProvider providerApiCall
	 */
	public function testApiCall($request, $apiUri, $timeout=0, $signature='POST')
	{
		$this->assertNotEmpty(
			$this->_getHelper()->callApi($request, $apiUri, $timeout)
		);
	}

	/**
	 * Mock out the config helper.
	 */
	protected function _mockConfig()
	{
		$mock = $this->getModelMockBuilder('eb2ccore/config_registry')
			->disableOriginalConstructor()
			->setMethods(array('__get'))
			->getMock();
		$mockConfig = array(
			array('apiEnvironment', 'prod'),
			array('apiRegion', 'eu'),
			array('apiMajorVersion', '1'),
			array('apiMinorVersion', '10'),
			array('storeId', 'store-123'),
		);
		$mock->expects($this->any())
			->method('__get')
			->will($this->returnValueMap($mockConfig));
		$this->replaceByMock('model', 'eb2ccore/config_registry', $mock);
	}

	/**
	 * test generating the API URIs
	 * @test
	 */
	public function testApiUriCreation()
	{
		$this->_mockConfig();
		$helper = Mage::helper('eb2ccore');
		// simplest case - just a service and operation
		$this->assertSame(
			'https://prod-eu.gsipartners.com/v1.10/stores/store-123/address/validate.xml',
			$helper->apiUri('address', 'validate'));
		// service, operation and params
		$this->assertSame(
			'https://prod-eu.gsipartners.com/v1.10/stores/store-123/payments/creditcard/auth/VC.xml',
			$helper->apiUri('payments', 'creditcard', array('auth', 'VC')));
		// service, operation, params and type
		$this->assertSame(
			'https://prod-eu.gsipartners.com/v1.10/stores/store-123/inventory/allocations/delete.json',
			$helper->apiUri('inventory', 'allocations', array('delete'), 'json'));
	}
}
