<?php
/**
 * PaypalTest.php
 * Created by Rob Mcvey on 2013-07-04.
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Rob Mcvey on 2013-07-04.
 * @link          www.copify.com
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::uses('Paypal', 'Lib');

/**
 * PaypalTest class
 */
class PaypalTestCase extends CakeTestCase {

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		unset($this->Paypal);
	}
	
/**
 * test setExpressCheckout
 *
 * @return void
 * @author Rob Mcvey
 **/
	public function testSetExpressCheckout() {
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar'
		));
		$order = array(
			'description' => 'Your purchase with Acme clothes store',
			'currency' => 'GBP',
			'return' => 'https://www.my-amazing-clothes-store.com/review-paypal.php',
			'cancel' => 'https://www.my-amazing-clothes-store.com/checkout.php',
			'custom' => 'bingbong',
			'items' => array(
				0 => array(
					'name' => 'Blue shoes',
					'description' => 'A pair of really great blue shoes',
					'tax' => 2.00,
					'shipping' => 0.00,
					'subtotal' => 8.00,
				),
				1 => array(
					'name' => 'Red trousers',
					'description' => 'Tight pair of red pants, look good with a hat.',
					'tax' => 2.00,
					'shipping' => 2.00,
					'subtotal' => 6.00
				),
			)
		);
		$expectedNvps = array(
			'METHOD' => 'SetExpressCheckout',
			'VERSION' => '104.0',
			'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
			'RETURNURL' => 'https://www.my-amazing-clothes-store.com/review-paypal.php',
			'CANCELURL' => 'https://www.my-amazing-clothes-store.com/checkout.php',
			'USER' => 'foo',
			'PWD' => 'bar',
			'SIGNATURE' => 'foobar',
			'PAYMENTREQUEST_0_CURRENCYCODE' => 'GBP',
			'PAYMENTREQUEST_0_ITEMAMT' => 14.00,
			'PAYMENTREQUEST_0_SHIPPINGAMT' => 2.00,
			'PAYMENTREQUEST_0_TAXAMT' => 4.00,
			'PAYMENTREQUEST_0_AMT' => 20.00,
			'PAYMENTREQUEST_0_DESC' => 'Your purchase with Acme clothes store',
			'PAYMENTREQUEST_0_CUSTOM' => 'bingbong',
			'L_PAYMENTREQUEST_0_NAME0' => 'Blue shoes',
			'L_PAYMENTREQUEST_0_DESC0' => 'A pair of really great blue shoes',
			'L_PAYMENTREQUEST_0_TAXAMT0' => 2.00,
			'L_PAYMENTREQUEST_0_AMT0' => 8.00,
			'L_PAYMENTREQUEST_0_QTY0' => 1,
			'L_PAYMENTREQUEST_0_NAME1' => 'Red trousers',
			'L_PAYMENTREQUEST_0_DESC1' => 'Tight pair of red pants, look good with a hat.',
			'L_PAYMENTREQUEST_0_TAXAMT1' => 2.00,
			'L_PAYMENTREQUEST_0_AMT1' => 6.00,
			'L_PAYMENTREQUEST_0_QTY1' => 1,
		);
		$expectedEndpoint = 'https://api-3t.sandbox.paypal.com/nvp';
		$mockResponse = 'TOKEN=EC%2d5PY500325X986371J&TIMESTAMP=2013%2d07%2d04T13%3a37%3a53Z&CORRELATIONID=845286d6c4caa&ACK=Success&VERSION=104%2e0&BUILD=6680107';
		$this->Paypal->HttpSocket = $this->getMock('HttpSocket');
		$this->Paypal->HttpSocket->expects($this->once())
			->method('post')
			->with($this->equalTo($expectedEndpoint) , $this->equalTo($expectedNvps))
			->will($this->returnValue($mockResponse));	
		$result = $this->Paypal->setExpressCheckout($order);
		$expected = 'https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=EC-5PY500325X986371J';
		$this->assertEqual($expected , $result);
	}

/**
 * test pasrseSetExpressCheckoutResponse
 *
 * @return void
 * @author Rob Mcvey
 **/
	public function testPasrseSetExpressCheckoutResponse() {
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar'
		));
		
		// Success
		$response = 'TOKEN=EC%2d5PY500325X986371J&TIMESTAMP=2013%2d07%2d04T13%3a37%3a53Z&CORRELATIONID=845286d6c4caa&ACK=Success&VERSION=104%2e0&BUILD=6680107';
		$result = $this->Paypal->pasrseSetExpressCheckoutResponse($response);
		$expected = array(
			'TOKEN' => 'EC-5PY500325X986371J',
			'TIMESTAMP' => '2013-07-04T13:37:53Z',
			'CORRELATIONID' => '845286d6c4caa',
			'ACK' => 'Success',
			'VERSION' => '104.0',
			'BUILD' => '6680107'
		);
		$this->assertEqual($expected , $result);
			
		// Error
		$response = 'TIMESTAMP=2013%2d07%2d04T13%3a27%3a02Z&CORRELATIONID=b0d9a91ac8d1b&ACK=Failure&VERSION=104%2e0&BUILD=6680107&L_ERRORCODE0=10002&L_SHORTMESSAGE0=Authentication%2fAuthorization%20Failed&L_LONGMESSAGE0=You%20do%20not%20have%20permissions%20to%20make%20this%20API%20call&L_SEVERITYCODE0=Error';
		$result = $this->Paypal->pasrseSetExpressCheckoutResponse($response);
		$expected = array(
			'L_SEVERITYCODE0' => 'EC-5PY500325X986371J',
			'TIMESTAMP' => '2013-07-04T13:27:02Z',
			'CORRELATIONID' => 'b0d9a91ac8d1b',
			'ACK' => 'Failure',
			'VERSION' => '104.0',
			'BUILD' => '6680107',
			'L_ERRORCODE0' => '10002',
			'L_SHORTMESSAGE0' => 'Authentication/Authorization Failed',
			'L_LONGMESSAGE0' => 'You do not have permissions to make this API call',
			'L_SEVERITYCODE0' => 'Error',
		);
		$this->assertEqual($expected , $result);
	}

/**
 * testGetEndpoints
 *
 * @return void
 * @author Rob Mcvey
 **/
	public function testGetEndpoints() {
		// Live
		$this->Paypal = new Paypal(array('sandboxMode' => false));
		$this->assertEqual("https://api.paypal.com" , $this->Paypal->getRestEndpoint());
		$this->assertEqual("https://api-3t.paypal.com/nvp" , $this->Paypal->getClassicEndpoint());
		$this->assertEqual("https://www.paypal.com/webscr" , $this->Paypal->getPaypalLoginUri());
		// Sandbox
		$this->Paypal = new Paypal(array('sandboxMode' => true));
		$this->assertEqual("https://api.sandbox.paypal.com" , $this->Paypal->getRestEndpoint());
		$this->assertEqual("https://api-3t.sandbox.paypal.com/nvp" , $this->Paypal->getClassicEndpoint());
		$this->assertEqual("https://www.sandbox.paypal.com/webscr" , $this->Paypal->getPaypalLoginUri());
	}

/**
 * test buildExpressCheckoutNvp
 *
 * @return void
 * @author Rob Mcvey
 **/
	public function testBuildExpressCheckoutNvp() {
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar'
		));
		$order = array(
			'description' => 'Your purchase with Acme clothes store',
			'currency' => 'GBP',
			'return' => 'https://www.my-amazing-clothes-store.com/review-paypal.php',
			'cancel' => 'https://www.my-amazing-clothes-store.com/checkout.php',
			'custom' => 'bingbong',
			'items' => array(
				0 => array(
					'name' => 'Blue shoes',
					'description' => 'A pair of really great blue shoes',
					'tax' => 2.00,
					'shipping' => 0.00,
					'subtotal' => 8.00,
				),
				1 => array(
					'name' => 'Red trousers',
					'description' => 'Tight pair of red pants, look good with a hat.',
					'tax' => 2.00,
					'shipping' => 2.00,
					'subtotal' => 6.00
				),
			)
		);
		$expected = array(
			'METHOD' => 'SetExpressCheckout',
			'VERSION' => '104.0',
			'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
			'USER' => 'foo',
			'PWD' => 'bar',
			'SIGNATURE' => 'foobar',
			'RETURNURL' => 'https://www.my-amazing-clothes-store.com/review-paypal.php',
			'CANCELURL' => 'https://www.my-amazing-clothes-store.com/checkout.php',
			'PAYMENTREQUEST_0_CURRENCYCODE' => 'GBP',
			'PAYMENTREQUEST_0_ITEMAMT' => 14.00,
			'PAYMENTREQUEST_0_SHIPPINGAMT' => 2.00,
			'PAYMENTREQUEST_0_TAXAMT' => 4.00,
			'PAYMENTREQUEST_0_AMT' => 20.00,
			'PAYMENTREQUEST_0_DESC' => 'Your purchase with Acme clothes store',
			'PAYMENTREQUEST_0_CUSTOM' => 'bingbong',
			'L_PAYMENTREQUEST_0_NAME0' => 'Blue shoes',
			'L_PAYMENTREQUEST_0_DESC0' => 'A pair of really great blue shoes',
			'L_PAYMENTREQUEST_0_TAXAMT0' => 2.00,
			'L_PAYMENTREQUEST_0_AMT0' => 8.00,
			'L_PAYMENTREQUEST_0_QTY0' => 1,
			'L_PAYMENTREQUEST_0_NAME1' => 'Red trousers',
			'L_PAYMENTREQUEST_0_DESC1' => 'Tight pair of red pants, look good with a hat.',
			'L_PAYMENTREQUEST_0_TAXAMT1' => 2.00,
			'L_PAYMENTREQUEST_0_AMT1' => 6.00,
			'L_PAYMENTREQUEST_0_QTY1' => 1,
		);
		$result = $this->Paypal->buildExpressCheckoutNvp($order);
		$this->assertEqual($expected , $result);
	}
	
/**
 * test buildExpressCheckoutNvp
 *
 * @return void
 * @author Rob Mcvey
 **/
	public function testBuildExpressCheckoutNvpLimit() {
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar'
		));
		$item = array(
			'name' => 'Blue shoes',
			'description' => 'A pair of really great blue shoes',
			'tax' => 2.00,
			'shipping' => 0.00,
			'subtotal' => 8.00,
		);
		$items = array();
		$n = 0;
		while ($n++ < 15) {
			$items[] = $item;
		}
		$order = array(
			'description' => 'Your purchase with Acme clothes store',
			'currency' => 'GBP',
			'return' => 'https://www.my-amazing-clothes-store.com/review-paypal.php',
			'cancel' => 'https://www.my-amazing-clothes-store.com/checkout.php',
			'custom' => 'bingbong',
			'items' => $items
		);
		$expected = array(
			'METHOD' => 'SetExpressCheckout',
			'VERSION' => '104.0',
			'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
			'USER' => 'foo',
			'PWD' => 'bar',
			'SIGNATURE' => 'foobar',
			'RETURNURL' => 'https://www.my-amazing-clothes-store.com/review-paypal.php',
			'CANCELURL' => 'https://www.my-amazing-clothes-store.com/checkout.php',
			'PAYMENTREQUEST_0_CURRENCYCODE' => 'GBP',
			'PAYMENTREQUEST_0_ITEMAMT' => 120.00,
			'PAYMENTREQUEST_0_SHIPPINGAMT' => 0.00,
			'PAYMENTREQUEST_0_TAXAMT' => 30.00,
			'PAYMENTREQUEST_0_AMT' => 150.00,
			'PAYMENTREQUEST_0_DESC' => 'Your purchase with Acme clothes store',
			'PAYMENTREQUEST_0_CUSTOM' => 'bingbong',
		);
		$result = $this->Paypal->buildExpressCheckoutNvp($order);
		$this->assertEqual($expected , $result);
	}	
	
/**
 * buildExpressCheckoutNvp exceptions
 *
 * @return void
 * @author Rob Mcvey
 **/
	public function testBuildExpressCheckoutNvpExceptions() {
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar'
		));
		
		// Empty array
		$order = array();
		$expected = 'You must pass a valid order array';
		try {
			$this->Paypal->buildExpressCheckoutNvp($order);
			$this->fail("Did not throw Exception with $expected");
		} catch(Exception $e) {
			$this->assertEqual($expected , $e->getMessage());
		}
		
		// Missing return/cancel urls
		$order = array('description' => 'foo');
		$expected = 'Valid "return" and "cancel" urls must be provided';
		try {
			$this->Paypal->buildExpressCheckoutNvp($order);
			$this->fail("Did not throw Exception with $expected");
		} catch(Exception $e) {
			$this->assertEqual($expected , $e->getMessage());
		}
		
		$order = array(
			'description' => 'foo',
			'return' => 'https://www.my-amazing-clothes-store.com/review-paypal.php',
			'cancel' => 'https://www.my-amazing-clothes-store.com/checkout.php',
		);
		$expected = 'You must provide a currency code';
		try {
			$this->Paypal->buildExpressCheckoutNvp($order);
			$this->fail("Did not throw Exception with $expected");
		} catch(Exception $e) {
			$this->assertEqual($expected , $e->getMessage());
		}
	}
	
/**
 * test setExpressCheckoutDefaultNvps
 *
 * @return void
 * @author Rob Mcvey
 **/
	public function testSetExpressCheckoutDefaultNvps() {
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar'
		));
		$nvps = array(
			'RETURNURL' => 'https://www.my-amazing-clothes-store.com/review-paypal.php',
			'CANCELURL' => 'https://www.my-amazing-clothes-store.com/checkout.php',
			'PAYMENTREQUEST_0_CUSTOM' => 'bingbong'
		);
		$this->Paypal->setExpressCheckoutDefaultNvps($nvps);
		$expected = array(
			'METHOD' => 'SetExpressCheckout',
			'VERSION' => '104.0',
			'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
			'RETURNURL' => 'https://www.my-amazing-clothes-store.com/review-paypal.php',
			'CANCELURL' => 'https://www.my-amazing-clothes-store.com/checkout.php',
			'PAYMENTREQUEST_0_CUSTOM' => 'bingbong'
		);
		$result = $this->Paypal->getExpressCheckoutDefaultNvps();
		$this->assertEqual($expected , $result);
	}
		
}
