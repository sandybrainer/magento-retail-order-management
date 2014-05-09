<?php
class EbayEnterprise_Eb2cCore_Test_Model_SequenceTest extends EcomDev_PHPUnit_Test_Case
{
	protected $_sequence;

	/**
	 * setUp method
	 */
	public function setUp()
	{
		parent::setUp();
		$this->_sequence = Mage::getModel('eb2ccore/sequence');
	}

	public function providerBuildSequence()
	{
		return array(
			array(glob(__DIR__ . '/SequenceTest/fixtures/*.xml'))
		);
	}

	/**
	 * test buildSequence method
	 *
	 * @test
	 * @dataProvider providerBuildSequence
	 */
	public function testBuildSequence($feeds)
	{
		$this->assertSame(3, count($this->_sequence->buildSequence($feeds)));
	}
}