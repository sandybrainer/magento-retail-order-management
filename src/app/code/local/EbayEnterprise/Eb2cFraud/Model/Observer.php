<?php
class EbayEnterprise_Eb2cFraud_Model_Observer
{
	/**
	 * Handler called before order save.
	 * Updates quote with 41st Parameter anti-fraud JS.
	 */
	public function captureOrderContext($observer)
	{
		$timestamp = new DateTime();
		$http = Mage::helper('eb2cfraud/http');
		$hlpr = Mage::helper('eb2cfraud');
		$sess = Mage::getSingleton('customer/session');
		$rqst = $observer->getEvent()->getRequest();
		$observer->getEvent()->getQuote()->addData(array(
			'eb2c_fraud_char_set'        => $http->getHttpAcceptCharset(),
			'eb2c_fraud_content_types'   => $http->getHttpAccept(),
			'eb2c_fraud_encoding'        => $http->getHttpAcceptEncoding(),
			'eb2c_fraud_host_name'       => $http->getRemoteHost(),
			'eb2c_fraud_referrer'        => $sess->getOrderSource(),
			'eb2c_fraud_user_agent'      => $http->getHttpUserAgent(),
			'eb2c_fraud_language'        => $http->getHttpAcceptLanguage(),
			'eb2c_fraud_ip_address'      => $http->getRemoteAddr(),
			'eb2c_fraud_session_id'      => $sess->getEncryptedSessionId(),
			'eb2c_fraud_javascript_data' => $hlpr->getJavaScriptFraudData($rqst),
		))->save();
		Mage::getSingleton('checkout/session')->addData(array(
			'eb2c_fraud_cookies'         => Mage::getSingleton('core/cookie')->get(),
			'eb2c_fraud_connection'      => $http->getHttpConnection(),
			'eb2c_fraud_session_info'    => $hlpr->getSessionInfo(),
			'eb2c_fraud_timestamp'       => $timestamp->format($hlpr::XML_DATETIME_FORMAT),
		));
	}

	/**
	 * call captureOrderContext when creating an order in the backend.
	 */
	public function captureAdminOrderContext($observer)
	{
		// the request field in the event data is just an array and doesn't
		// have the action name, so we specially get the full request object instead.
		$request = $this->_getRequest();
		if ($request->getActionName() === 'save') {
			$this->captureOrderContext(new Varien_Event_Observer(
				array('event' => new Varien_Event(array(
				'quote' => $observer->getEvent()->getOrderCreateModel()->getQuote(),
				'request' => $request,
				)))
			));
		}
	}

	/**
	 * get the request object in a way that can be stubbed in tests.
	 * @return Mage_Core_Controller_Request_Http
	 * @codeCoverageIgnore
	 */
	protected function _getRequest()
	{
		return Mage::app()->getRequest();
	}
}
