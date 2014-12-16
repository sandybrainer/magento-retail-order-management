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

class EbayEnterprise_Address_Test_Helper_DataTest extends EcomDev_PHPUnit_Test_Case
{
	protected $_addressParts = array(
		'line1'            => "123 Don't you wish you lived here too at this place I like to call Main Street",
		'line1_trimmed'    => "123 Don't you wish you lived here too at this place I like to call Mai",
		'line2'            => '12345678901234567890123456789012345678901234567890123456789012345678901234567890',
		'line2_trimmed'    => '1234567890123456789012345678901234567890123456789012345678901234567890',
		'line3'            => '12345678901234567890123456789012345678901234567890123456789012345678901234567890',
		'line3_trimmed'    => '1234567890123456789012345678901234567890123456789012345678901234567890',
		'line4'            => '12345678901234567890123456789012345678901234567890123456789012345678901234567890',
		'line4_trimmed'    => '1234567890123456789012345678901234567890123456789012345678901234567890',
		'city'             => '1234567890123456789012345678901234567890',
		'city_trimmed'     => '12345678901234567890123456789012345',
		'region_id'        => '51',
		'region_code'      => 'PA',
		'country_id'       => 'US',
		'postcode'         => '12345678901234567890',
		'postcode_trimmed' => '123456789012345'
	);

	/**
	 * Generate a reusable Mage_Customer_Model_Address object
	 */
	protected function _generateAddressObject($streetLines=4)
	{
		$address = Mage::getModel('customer/address');
		$street = array();
		for ($i = 1; $i <= $streetLines; $i++) {
			$street[] = $this->_addressParts['line' . $i];
		}
		$address->setStreet($street);
		$address->setCity($this->_addressParts['city']);
		$address->setRegionId($this->_addressParts['region_id']);
		$address->setCountryId($this->_addressParts['country_id']);
		$address->setPostcode($this->_addressParts['postcode']);
		return $address;
	}

	/**
	 * Create a DOMDocument containing a PhysicalAddressType
	 */
	protected function _generatePhysicalAddressElement($streetLines=4)
	{
		$dom = Mage::helper('eb2ccore')->getNewDomDocument();
		$rootNs = Mage::helper('ebayenterprise_address')->getConfigModel()->apiNamespace;
		$root = $dom->appendChild($dom->createElement('address', null, $rootNs));
		for ($i = 1; $i <= $streetLines; $i++) {
			$root->addChild('Line' . $i, $this->_addressParts['line' . $i]);
		}
		$root->addChild('City', $this->_addressParts['city'])
			->addChild('MainDivision', $this->_addressParts['region_code'])
			->addChild('CountryCode', $this->_addressParts['country_id'])
			->addChild('PostalCode', $this->_addressParts['postcode']);
		return $root;
	}

	/**
	 * Test the generic method for getting the text contents from a set of XML
	 * based on a given XPath expression using element as XPath context.
	 */
	public function testTextValueFromXmlPathOnElement()
	{
		$doc = Mage::helper('eb2ccore')->getNewDomDocument();
		$doc->addElement('root');
		$root = $doc->documentElement;
		$root->createChild('foo')->addChild('bar', 'one')->addChild('baz', 'two');
		$root->createChild('color', 'red');

		$multiValues = Mage::helper('ebayenterprise_address')->getTextValueByXPath('foo/*', $root);
		$singleValue = Mage::helper('ebayenterprise_address')->getTextValueByXPath('color', $root);
		$nullValue = Mage::helper('ebayenterprise_address')->getTextValueByXPath('nope_not_here', $root);
		$this->assertSame($multiValues, array('one', 'two'));
		$this->assertSame($singleValue, 'red');
		$this->assertNull($nullValue);
	}

	/**
	 * Test the generic method for getting text contents from a set of XML
	 * based on a given XPath expression using document as XPath context.
	 */
	public function testTextValueFromXmlPathOnDocument()
	{
		$doc = Mage::helper('eb2ccore')->getNewDomDocument();
		$doc->addElement('root');
		$root = $doc->documentElement;
		$root->createChild('foo')->addChild('bar', 'one')->addChild('baz', 'two');
		$root->createChild('color', 'red');

		$multiValuesFromDoc = Mage::helper('ebayenterprise_address')->getTextValueByXPath('root/foo/*', $doc);
		$singleValueFromDoc = Mage::helper('ebayenterprise_address')->getTextValueByXPath('root/color', $doc);
		$nullValueFromDoc = Mage::helper('ebayenterprise_address')->getTextValueByXPath('root/nope_not_here', $doc);
		$this->assertSame($multiValuesFromDoc, array('one', 'two'));
		$this->assertSame($singleValueFromDoc, 'red');
		$this->assertNull($nullValueFromDoc);
	}

	/**
	 * Test getting the address lines from PhysicalAddressType XML
	 */
	public function testAddressStreetLines()
	{
		$expectedParts = $this->_addressParts;
		$street = Mage::helper('ebayenterprise_address')
			->physicalAddressStreet($this->_generatePhysicalAddressElement());
		$this->assertEquals($street, array($expectedParts['line1'], $expectedParts['line2'], $expectedParts['line3'], $expectedParts['line4']));
		$street = Mage::helper('ebayenterprise_address')
			->physicalAddressStreet($this->_generatePhysicalAddressElement(3));
		$this->assertEquals($street, array($expectedParts['line1'], $expectedParts['line2'], $expectedParts['line3']));
		$street = Mage::helper('ebayenterprise_address')
			->physicalAddressStreet($this->_generatePhysicalAddressElement(2));
		$this->assertEquals($street, array($expectedParts['line1'], $expectedParts['line2']));
		$street = Mage::helper('ebayenterprise_address')
			->physicalAddressStreet($this->_generatePhysicalAddressElement(1));
		$this->assertEquals($street, $expectedParts['line1']);
	}

	/**
	 * Test getting the city from PhysicalAddressType XML
	 */
	public function testAddressCity()
	{
		$city = Mage::helper('ebayenterprise_address')
			->physicalAddressCity($this->_generatePhysicalAddressElement());
		$this->assertSame($city, $this->_addressParts['city']);
	}

	/**
	 * Test getting a Magento region id from PhysicalAddressType XML
	 */
	public function testAddressRegion()
	{
		$region = Mage::helper('ebayenterprise_address')
			->physicalAddressRegionId($this->_generatePhysicalAddressElement());
		$this->assertSame($region, 51); // 'PA' maps to region id '51' in Magento addreses
	}

	/**
	 * Test getting the country id from PhysicalAddressType XML
	 */
	public function testAddressContry()
	{
		$country = Mage::helper('ebayenterprise_address')
			->physicalAddressCountryId($this->_generatePhysicalAddressElement());
		$this->assertSame($country, 'US');
	}

	/**
	 * Test getting the postcode from PhysicalAddressType XML
	 */
	public function testAddressPostcode()
	{
		$postcode = Mage::helper('ebayenterprise_address')
			->physicalAddressPostcode($this->_generatePhysicalAddressElement());
		$this->assertSame($postcode, $this->_addressParts['postcode']);
	}

	/**
	 * Test the conversion of an address object to XML
	 */
	public function testAddressToXml()
	{
		$dom = Mage::helper('eb2ccore')->getNewDomDocument();
		$address = $this->_generateAddressObject();
		$addressFragment = Mage::helper('ebayenterprise_address')
			->addressToPhysicalAddressXml($address, $dom, 'test-ns');
		$fragmentNodes = $addressFragment->childNodes;
		$this->assertEquals($fragmentNodes->item(0)->nodeName, 'Line1');
		$this->assertEquals($fragmentNodes->item(0)->textContent, $this->_addressParts['line1_trimmed']);
		$this->assertEquals($fragmentNodes->item(1)->nodeName, 'Line2');
		$this->assertEquals($fragmentNodes->item(1)->textContent, $this->_addressParts['line2_trimmed']);
		$this->assertEquals($fragmentNodes->item(2)->nodeName, 'Line3');
		$this->assertEquals($fragmentNodes->item(2)->textContent, $this->_addressParts['line3_trimmed']);
		$this->assertEquals($fragmentNodes->item(3)->nodeName, 'Line4');
		$this->assertEquals($fragmentNodes->item(3)->textContent, $this->_addressParts['line4_trimmed']);
		$this->assertEquals($fragmentNodes->item(4)->nodeName, 'City');
		$this->assertEquals($fragmentNodes->item(4)->textContent, $this->_addressParts['city_trimmed']);
		$this->assertEquals($fragmentNodes->item(5)->nodeName, 'MainDivision');
		$this->assertEquals($fragmentNodes->item(5)->textContent, $this->_addressParts['region_code']);
		$this->assertEquals($fragmentNodes->item(6)->nodeName, 'CountryCode');
		$this->assertEquals($fragmentNodes->item(6)->textContent, $this->_addressParts['country_id']);
		$this->assertEquals($fragmentNodes->item(7)->nodeName, 'PostalCode');
		$this->assertEquals($fragmentNodes->item(7)->textContent, $this->_addressParts['postcode_trimmed']);
	}

	/**
	 * Test converting a DOM element representing an address to a proper Mage_Customer_Model_Address object
	 */
	public function testXmlToAddress()
	{
		$address = Mage::helper('ebayenterprise_address')
			->physicalAddressXmlToAddress($this->_generatePhysicalAddressElement());
		$this->assertInstanceOf('Mage_Customer_Model_Address', $address);
		$this->assertSame($address->getStreet1(), $this->_addressParts['line1']);
		$this->assertSame($address->getStreet2(), $this->_addressParts['line2']);
		$this->assertSame($address->getStreet3(), $this->_addressParts['line3']);
		$this->assertSame($address->getStreet4(), $this->_addressParts['line4']);
		$this->assertSame($address->getCity(), $this->_addressParts['city']);
		$this->assertSame($address->getRegionCode(), $this->_addressParts['region_code']);
		$this->assertSame($address->getCountry(), $this->_addressParts['country_id']);
		$this->assertSame($address->getPostcode(), $this->_addressParts['postcode']);
	}
}