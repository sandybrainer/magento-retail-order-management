<?php
/**
 * Copyright (c) 2013-2014 eBay Enterprise, Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright   Copyright (c) 2013-2014 eBay Enterprise, Inc. (http://www.ebayenterprise.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class EbayEnterprise_Eb2cProduct_Test_Model_ExporterTest
	extends EbayEnterprise_Eb2cCore_Test_Base
{
	const FEED_TYPE = 'madeup_feed';

	// @var EbayEnterprise_Eb2cProduct_Model_Pim
	protected $_pimMock;
	// @var EbayEnterprise_Eb2cProduct_Model_Pim_Batch
	protected $_batch;
	// @var EbayEnterprise_Eb2cCore_Model_Config_Registry
	protected $_configRegistry;
	// @var array product entity id's
	protected $_entityIds = array(87, 98);
	// @var string
	protected $_cutoffDate = '2014-03-27T13:56:32+00:00';
	// @var string
	protected $_startTime = '2014-03-27T13:56:32+00:00';
	// @var array stubbed config data for a feed
	protected $_feedConfig = array(self::FEED_TYPE => array('feed config data'));

	public function setUp()
	{
		// disable the constructor to avoid having to mock the internals of the pim model.
		$this->_pimMock = $this->getModelMock('eb2cproduct/pim', array('buildFeed'));
		$this->_batch = $this->getModelMock('eb2cproduct/pim_batch');
		$this->_container = $this->getModelMock('eb2cproduct/pim_batch_container', array('getBatches'));
		$this->_priceCollector = $this->getModelMock('eb2cproduct/pim_collector_price');
		$this->_combinedCollector = $this->getModelMock('eb2cproduct/pim_collector_combined');
		$this->_configRegistry = $this->getModelMock('eb2ccore/config_registry', array('getConfigData', '__get'));
		$this->_configRegistry->expects($this->any())->method('getConfigData')
			->with($this->isType('string'))->will($this->returnValue($this->_feedConfig));
		$this->_configRegistry->expects($this->any()) ->method('__get')
			->will($this->returnValueMap(array(
				array('pimExportFeedCutoffDate', $this->_cutoffDate),
				array('exportFeedConfig', $this->_feedConfig),
			)));
		// mock the product helper
		$this->_productHelper = $this->getHelperMock('eb2cproduct/data', array('getConfigModel'));
		$this->_productHelper->expects($this->any())->method('getConfigModel')
			->will($this->returnValue($this->_configRegistry));
	}

	/**
	 * 1. Verify the 'ebayenterprise_eb2cproduct_gather_exportbatches_madeup_feed' event is triggered
	 * 		with a container instance, feed type config and cutoff date.
	 * 2. Verify the Pim model's buildFeed method is called with the batch.
	 */
	public function testRunExport()
	{
		$batches = array($this->_batch);
		$this->replaceByMock('model', 'eb2cproduct/pim_batch_container', $this->_container);
		$this->replaceByMock('model', 'eb2cproduct/pim', $this->_pimMock);
		$this->_container->expects($this->any())->method('getBatches')
			->will($this->returnValue($batches));
		// make sure pim::buildFeed gets called with a batch
		$this->_pimMock->expects($this->once())
			->method('buildFeed')
			->will($this->returnSelf());
		// run the test.
		$exporter = Mage::getModel('eb2cproduct/exporter');
		EcomDev_Utils_Reflection::setRestrictedPropertyValue($exporter, '_config', $this->_configRegistry);
		$exporter->runExport();
		// make sure our event was fired
		EcomDev_PHPUnit_Test_Case_Config::assertEventDispatched('ebayenterprise_product_export_madeup_feed');
	}

	/**
	 * Verify the Exporter will stop if an error is thrown.
	 */
	public function testRunExportBuildFeedThrowException()
	{
		$invalidXml = 'Unittest Throwing exception';
		$xmlException = new EbayEnterprise_Eb2cCore_Exception_InvalidXml($invalidXml);

		$magelogHelperMock = $this->getHelperMockBuilder('ebayenterprise_magelog/data')
			->disableOriginalConstructor()
			->setMethods(array('logInfo', 'logDebug', 'logCrit'))
			->getMock();
		$magelogHelperMock->expects($this->at(1))
			->method('logCrit')
			->with($this->isType('string'), $this->isType('array'))
			->will($this->returnSelf());

		$this->replaceByMock('model', 'eb2cproduct/pim_batch_container', $this->_container);
		$batches = array($this->_batch);
		$this->_container->expects($this->any())->method('getBatches')
			->will($this->returnValue($batches));

		$this->replaceByMock('model', 'eb2cproduct/pim', $this->_pimMock);
		$this->_pimMock->expects($this->once())->method('buildFeed')->will($this->throwException($xmlException));

		$exporter = $this->getModelMock('eb2cproduct/exporter', array('_loadConfig', '_gatherAllBatches'));
		$exporter->expects($this->once())
			->method('_gatherAllBatches')
			->will($this->returnValue($batches));

		// inject the logger mock
		EcomDev_Utils_Reflection::setRestrictedPropertyValue($exporter, '_logger', $magelogHelperMock);
		$this->assertSame($exporter, $exporter->runExport());
	}
}
