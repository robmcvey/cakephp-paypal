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
App::uses('Paypal', 'Paypal.Lib');

/**
 * PaypalTest class
 */
class PaypalTestCase extends CakeTestCase {

/**
 * Paypal instance
 */
	public $Paypal;

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
 * Tests that getVerifiedStatus throws an exception when there is an error is the response.
 *
 * @author Chris Green
 * @expectedException PaypalException
 * @expectedExceptionMessage Invalid request parameter: emailAddress
 **/
    public function testGetVerifiedStatusException() {
		// Setup Paypal sandbox
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar'
		));

        // Expected endpoint for POST
        $expectedEndpoint = 'https://svcs.sandbox.paypal.com/AdaptiveAccounts/GetVerifiedStatus';

        // Expected query for POST
        $expectedQuery = array(
            'accountIdentifier.emailAddress' => 'stuff',
            'matchCriteria' => 'NONE',
            'requestEnvelope.errorLanguage' => 'en_GB'
        );

        // Expected headers for POST
        $expectedHeaders = array(
            'X-PAYPAL-SANDBOX-EMAIL-ADDRESS' => 'Platform.sdk.seller@gmail.com',
            'X-PAYPAL-SECURITY-PASSWORD' => 'WX4WTU3S8MY44S7F',
            'X-PAYPAL-SECURITY-SIGNATURE' => 'AFcWxV21C7fd0v3bYYYRCpSSRl31A7yDhhsPUU2XhtMoZXsWHFxu-RWy',
            'X-PAYPAL-APPLICATION-ID' => 'APP-80W284485P519543T',
            'X-PAYPAL-REQUEST-DATA-FORMAT' => 'NV',
            'X-PAYPAL-RESPONSE-DATA-FORMAT' => 'JSON',
            'X-PAYPAL-SECURITY-USERID' => 'jb-us-seller_api1.paypal.com'
        );

        $mockResponse = json_encode(array(
            'responseEnvelope' => array(
                'timestamp' => '2013-10-23T04:45:31.479-07:00',
                'ack' => 'Failure',
                'correlationId' => 'bc86add924ec9',
                'build' => '8095610'
            ),
            'error' => array(
                (int) 0 => array(
                    'errorId' => '580022',
                    'domain' => 'PLATFORM',
                    'subdomain' => 'Application',
                    'severity' => 'Error',
                    'category' => 'Application',
                    'message' => 'Invalid request parameter: emailAddress',
                    'parameter' => array(
                        (int) 0 => 'emailAddress'
                    )
                )
            )
        ));

		// Mock request
		$this->Paypal->HttpSocket = $this->getMock('HttpSocket');
		$this->Paypal->HttpSocket->expects($this->once())
			->method('post')
			->with($this->equalTo($expectedEndpoint) , $this->equalTo($expectedQuery))
			->will($this->returnValue($mockResponse));

        $result = $this->Paypal->getVerifiedStatus('stuff');

    }

/**
 * Tests that getVerifiedStatus returns the correct response when a verified email is used
 *
 * @author Chris Green
 **/
    public function testGetVerifiedStatus() {

		// Setup Paypal sandbox
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar'
		));

        // Expected endpoint for POST
        $expectedEndpoint = 'https://svcs.sandbox.paypal.com/AdaptiveAccounts/GetVerifiedStatus';

        // Expected query for POST
        $expectedQuery = array(
            'accountIdentifier.emailAddress' => 'robm24_1322925502_per@gmail.com',
            'matchCriteria' => 'NONE',
            'requestEnvelope.errorLanguage' => 'en_GB'
        );

        // Expected headers for POST
        $expectedHeaders = array(
            'X-PAYPAL-SANDBOX-EMAIL-ADDRESS' => 'Platform.sdk.seller@gmail.com',
            'X-PAYPAL-SECURITY-PASSWORD' => 'WX4WTU3S8MY44S7F',
            'X-PAYPAL-SECURITY-SIGNATURE' => 'AFcWxV21C7fd0v3bYYYRCpSSRl31A7yDhhsPUU2XhtMoZXsWHFxu-RWy',
            'X-PAYPAL-APPLICATION-ID' => 'APP-80W284485P519543T',
            'X-PAYPAL-REQUEST-DATA-FORMAT' => 'NV',
            'X-PAYPAL-RESPONSE-DATA-FORMAT' => 'JSON',
            'X-PAYPAL-SECURITY-USERID' => 'jb-us-seller_api1.paypal.com'
        );

        $mockResponse = json_encode(array(
            'responseEnvelope' => array(
                'timestamp' => '2013-10-23T04:19:07.312-07:00',
                'ack' => 'Success',
                'correlationId' => '44765682a1d78',
                'build' => '8095610'
            ),
            'accountStatus' => 'VERIFIED',
            'countryCode' => 'GB',
            'userInfo' => array(
                'emailAddress' => 'robm24_1322925502_per@gmail.com',
                'accountType' => 'PERSONAL',
                'accountId' => 'GSEK8P4ARYMYS',
                'name' => array(
                    'salutation' => '',
                    'firstName' => 'Test',
                    'middleName' => '',
                    'lastName' => 'User',
                    'suffix' => ''
                ),
                'businessName' => ''
            )
        ));

        // Expected result
        $expected = array(
            'responseEnvelope' => array(
                'timestamp' => '2013-10-23T04:19:07.312-07:00',
                'ack' => 'Success',
                'correlationId' => '44765682a1d78',
                'build' => '8095610'
            ),
            'accountStatus' => 'VERIFIED',
            'countryCode' => 'GB',
            'userInfo' => array(
                'emailAddress' => 'robm24_1322925502_per@gmail.com',
                'accountType' => 'PERSONAL',
                'accountId' => 'GSEK8P4ARYMYS',
                'name' => array(
                    'salutation' => '',
                    'firstName' => 'Test',
                    'middleName' => '',
                    'lastName' => 'User',
                    'suffix' => ''
                ),
                'businessName' => ''
            )
        );

		// Mock request
		$this->Paypal->HttpSocket = $this->getMock('HttpSocket');
		$this->Paypal->HttpSocket->expects($this->once())
			->method('post')
			->with($this->equalTo($expectedEndpoint) , $this->equalTo($expectedQuery))
			->will($this->returnValue($mockResponse));

        $result = $this->Paypal->getVerifiedStatus('robm24_1322925502_per@gmail.com');

        $this->assertEqual($result, $expected);
    }

/**
 * Tests that a PaypalException is thrown when error 10501 is returned and correct redirect url is passed as exception message.
 *
 * @author Chris Green
 * @expectedException PaypalException
 * @expectedExceptionMessage The billing agreement is disabled or inactive.
 */
	public function testDoExpressCheckoutPaymentException() {
		// Setup Paypal sandbox
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar'
		));

		// Create order
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
		$token = 'EC-482053995J417352W';
		$payerId = 'GSEK8P4ARYMYS';
		$expectedEndpoint = 'https://api-3t.sandbox.paypal.com/nvp';
		// Response to return
		$mockResponse = 'TOKEN=EC%2d482053995J417352W&TIMESTAMP=2013%2d07%2d05T11%3a41%3a07Z&CORRELATIONID=a4821326a65c6&ACK=Failure&VERSION=104%2e0&BUILD=6680107&L_ERRORCODE0=10501&L_SHORTMESSAGE0=The%20billing%20agreement%20is%20disabled%20or%20inactive.&L_LONGMESSAGE0=The%20billing%20agreement%20is%20disabled%20or%20inactive.';
		$expectedNvps = array(
			'METHOD' => 'DoExpressCheckoutPayment',
			'VERSION' => '104.0',
			'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
			'USER' => 'foo',
			'PWD' => 'bar',
			'SIGNATURE' => 'foobar',
			'RETURNURL' => 'https://www.my-amazing-clothes-store.com/review-paypal.php',
			'CANCELURL' => 'https://www.my-amazing-clothes-store.com/checkout.php',
			'PAYMENTREQUEST_0_CURRENCYCODE' => 'GBP',
			'PAYMENTREQUEST_0_DESC' => 'Your purchase with Acme clothes store',
			'PAYMENTREQUEST_0_CUSTOM' => 'bingbong',
			'PAYMENTREQUEST_0_ITEMAMT' => (float) 14,
			'PAYMENTREQUEST_0_SHIPPINGAMT' => (float) 2,
			'PAYMENTREQUEST_0_TAXAMT' => (float) 4,
			'PAYMENTREQUEST_0_AMT' => (float) 20,
			'L_PAYMENTREQUEST_0_NAME0' => 'Blue shoes',
			'L_PAYMENTREQUEST_0_DESC0' => 'A pair of really great blue shoes',
			'L_PAYMENTREQUEST_0_TAXAMT0' => (float) 2,
			'L_PAYMENTREQUEST_0_AMT0' => (float) 8,
			'L_PAYMENTREQUEST_0_QTY0' => (int) 1,
			'L_PAYMENTREQUEST_0_NAME1' => 'Red trousers',
			'L_PAYMENTREQUEST_0_DESC1' => 'Tight pair of red pants, look good with a hat.',
			'L_PAYMENTREQUEST_0_TAXAMT1' => (float) 2,
			'L_PAYMENTREQUEST_0_AMT1' => (float) 6,
			'L_PAYMENTREQUEST_0_QTY1' => (int) 1,
			'TOKEN' => $token,
			'PAYERID' => $payerId
		);
		// Mock request
		$this->Paypal->HttpSocket = $this->getMock('HttpSocket');
		$this->Paypal->HttpSocket->expects($this->once())
			->method('post')
			->with($this->equalTo($expectedEndpoint) , $this->equalTo($expectedNvps))
			->will($this->returnValue($mockResponse));

		// Do the payment
		$result = $this->Paypal->doExpressCheckoutPayment($order , $token , $payerId);
	}

/**
 * Tests that DoDirectPayment catches SocketExceptions and throws a more meaningful PaypalException
 *
 * @author Chris Green
 * @expectedException PaypalException
 * @expectedExceptionMessage There was a problem processing your card, please try again.
 */
    public function testDoDirectPaymentCatchSocket() {
 		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar'
		));
		$payment = array(
			'amount' => 30.00,
			'card' => '4008 0687 0641 8697', // This is a sandbox CC
			'expiry' => array(
				'M' => '2',
				'Y' => '2016',
			),
			'cvv' => '321',
		);
		// Mock the CakeRequest class
		$this->Paypal->CakeRequest = $this->getMock('CakeRequest');
		$this->Paypal->CakeRequest->expects($this->once())
			->method('clientIp')
			->will($this->returnValue("217.114.52.94"));

		// Mock the HttpSocket class
		$expectedEndpoint = 'https://api-3t.sandbox.paypal.com/nvp';
		$mockResponse = 'TIMESTAMP=2013%2d07%2d05T13%3a52%3a48Z&CORRELATIONID=5d7677126e0b4&ACK=Success&VERSION=104%2e0&BUILD=6680107&AMT=30%2e00&CURRENCYCODE=GBP&AVSCODE=X&CVV2MATCH=M&TRANSACTIONID=0XW09448VG556664J';
		$expectedNvps = array(
			'METHOD' => 'DoDirectPayment',
			'VERSION' => '104.0',
			'USER' => 'foo',
			'PWD' => 'bar',
			'SIGNATURE' => 'foobar',
			'IPADDRESS' => '217.114.52.94',
			'AMT' => 30.00,
			'CURRENCYCODE' => 'GBP',
			'RECURRING' => 'N',
			'ACCT' => '4008068706418697', // This is a sandbox CC
			'EXPDATE' => '022016',
			'CVV2' => '321',
			'FIRSTNAME' => '',
			'LASTNAME' => '',
			'STREET' => '',
			'CITY' => '',
			'STATE' => '',
			'COUNTRYCODE' => '',
			'ZIP' => ''
		);
		$this->Paypal->HttpSocket = $this->getMock('HttpSocket');
		$this->Paypal->HttpSocket->expects($this->once())
			->method('post')
			->with($this->equalTo($expectedEndpoint) , $this->equalTo($expectedNvps))
			->will($this->throwException(new SocketException('A socket exception')));
		$result = $this->Paypal->doDirectPayment($payment);
    }

/**
 * Tests that DoExpressCheckoutPayment catches SocketExceptions and throws a more meaningful PaypalException
 *
 * @author Chris Green
 * @expectedException PaypalException
 * @expectedExceptionMessage There was a problem processing the transaction, please try again.
 */
    public function testDoExpressCheckoutPaymentCatchSocket() {
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
		$token = 'EC-482053995J417352W';
		$payerId = 'GSEK8P4ARYMYS';
		$expectedEndpoint = 'https://api-3t.sandbox.paypal.com/nvp';
		$mockResponse = 'TOKEN=EC%2d88C117816V738422U&SUCCESSPAGEREDIRECTREQUESTED=false&TIMESTAMP=2013%2d07%2d05T11%3a41%3a07Z&CORRELATIONID=a4821326a65c6&ACK=Success&VERSION=104%2e0&BUILD=6680107&INSURANCEOPTIONSELECTED=false&SHIPPINGOPTIONISDEFAULT=false&PAYMENTINFO_0_TRANSACTIONID=5V948789BD843205H&PAYMENTINFO_0_TRANSACTIONTYPE=cart&PAYMENTINFO_0_PAYMENTTYPE=instant&PAYMENTINFO_0_ORDERTIME=2013%2d07%2d05T11%3a41%3a06Z&PAYMENTINFO_0_AMT=20%2e00&PAYMENTINFO_0_FEEAMT=0%2e88&PAYMENTINFO_0_TAXAMT=4%2e00&PAYMENTINFO_0_CURRENCYCODE=GBP&PAYMENTINFO_0_PAYMENTSTATUS=Completed&PAYMENTINFO_0_PENDINGREASON=None&PAYMENTINFO_0_REASONCODE=None&PAYMENTINFO_0_PROTECTIONELIGIBILITY=Eligible&PAYMENTINFO_0_PROTECTIONELIGIBILITYTYPE=ItemNotReceivedEligible%2cUnauthorizedPaymentEligible&PAYMENTINFO_0_SECUREMERCHANTACCOUNTID=AD4VU2GRDM9EU&PAYMENTINFO_0_ERRORCODE=0&PAYMENTINFO_0_ACK=Success';
		$expectedNvps = array(
			'METHOD' => 'DoExpressCheckoutPayment',
			'VERSION' => '104.0',
			'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
			'USER' => 'foo',
			'PWD' => 'bar',
			'SIGNATURE' => 'foobar',
			'RETURNURL' => 'https://www.my-amazing-clothes-store.com/review-paypal.php',
			'CANCELURL' => 'https://www.my-amazing-clothes-store.com/checkout.php',
			'PAYMENTREQUEST_0_CURRENCYCODE' => 'GBP',
			'PAYMENTREQUEST_0_DESC' => 'Your purchase with Acme clothes store',
			'PAYMENTREQUEST_0_CUSTOM' => 'bingbong',
			'PAYMENTREQUEST_0_ITEMAMT' => (float) 14,
			'PAYMENTREQUEST_0_SHIPPINGAMT' => (float) 2,
			'PAYMENTREQUEST_0_TAXAMT' => (float) 4,
			'PAYMENTREQUEST_0_AMT' => (float) 20,
			'L_PAYMENTREQUEST_0_NAME0' => 'Blue shoes',
			'L_PAYMENTREQUEST_0_DESC0' => 'A pair of really great blue shoes',
			'L_PAYMENTREQUEST_0_TAXAMT0' => (float) 2,
			'L_PAYMENTREQUEST_0_AMT0' => (float) 8,
			'L_PAYMENTREQUEST_0_QTY0' => (int) 1,
			'L_PAYMENTREQUEST_0_NAME1' => 'Red trousers',
			'L_PAYMENTREQUEST_0_DESC1' => 'Tight pair of red pants, look good with a hat.',
			'L_PAYMENTREQUEST_0_TAXAMT1' => (float) 2,
			'L_PAYMENTREQUEST_0_AMT1' => (float) 6,
			'L_PAYMENTREQUEST_0_QTY1' => (int) 1,
			'TOKEN' => $token,
			'PAYERID' => $payerId
		);
		$this->Paypal->HttpSocket = $this->getMock('HttpSocket');
		$this->Paypal->HttpSocket->expects($this->once())
			->method('post')
			->with($this->equalTo($expectedEndpoint) , $this->equalTo($expectedNvps))
			->will($this->throwException(new SocketException('A socket exception')));
		$result = $this->Paypal->doExpressCheckoutPayment($order , $token , $payerId);

    }

/**
 * Tests that GetExpressCheckoutDetails catches SocketExceptions and throws a more meaningful PaypalException
 *
 * @author Chris Green
 * @expectedException PaypalException
 * @expectedExceptionMessage There was a problem getting your details, please try again.
 */
    public function testGetExpressCheckoutDetailsCatchSocket() {
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar'
		));
		$token = 'EC-482053995J417352W';
		$expectedNvps = array(
			'METHOD' => 'GetExpressCheckoutDetails' ,
			'VERSION' => '104.0',
			'TOKEN' => $token,
			'USER' => 'foo',
			'PWD' => 'bar',
			'SIGNATURE' => 'foobar',
		);
		$expectedEndpoint = 'https://api-3t.sandbox.paypal.com/nvp';
		$mockResponse = 'TOKEN=EC%2d482053995J417352W&CHECKOUTSTATUS=PaymentActionNotInitiated&TIMESTAMP=2013%2d07%2d05T11%3a16%3a32Z&CORRELATIONID=d817ecf2ba811&ACK=Success&VERSION=104%2e0&BUILD=6680107&EMAIL=robm24_1322925502_per%40gmail%2ecom&PAYERID=GSEK8P4ARYMYS&PAYERSTATUS=verified&FIRSTNAME=Test&LASTNAME=User&COUNTRYCODE=GB&SHIPTONAME=Test%20User&SHIPTOSTREET=1%20Main%20Terrace&SHIPTOCITY=Wolverhampton&SHIPTOSTATE=West%20Midlands&SHIPTOZIP=W12%204LQ&SHIPTOCOUNTRYCODE=GB&SHIPTOCOUNTRYNAME=United%20Kingdom&ADDRESSSTATUS=Confirmed&CURRENCYCODE=GBP&AMT=20%2e00&ITEMAMT=14%2e00&SHIPPINGAMT=2%2e00&HANDLINGAMT=0%2e00&TAXAMT=4%2e00&CUSTOM=bingbong&DESC=Your%20purchase%20with%20Acme%20clothes%20store&INSURANCEAMT=0%2e00&SHIPDISCAMT=0%2e00&L_NAME0=Blue%20shoes&L_NAME1=Red%20trousers&L_QTY0=1&L_QTY1=1&L_TAXAMT0=2%2e00&L_TAXAMT1=2%2e00&L_AMT0=8%2e00&L_AMT1=6%2e00&L_DESC0=A%20pair%20of%20really%20great%20blue%20shoes&L_DESC1=Tight%20pair%20of%20red%20pants%2c%20look%20good%20with%20a%20hat%2e&L_ITEMWEIGHTVALUE0=%20%20%200%2e00000&L_ITEMWEIGHTVALUE1=%20%20%200%2e00000&L_ITEMLENGTHVALUE0=%20%20%200%2e00000&L_ITEMLENGTHVALUE1=%20%20%200%2e00000&L_ITEMWIDTHVALUE0=%20%20%200%2e00000&L_ITEMWIDTHVALUE1=%20%20%200%2e00000&L_ITEMHEIGHTVALUE0=%20%20%200%2e00000&L_ITEMHEIGHTVALUE1=%20%20%200%2e00000&PAYMENTREQUEST_0_CURRENCYCODE=GBP&PAYMENTREQUEST_0_AMT=20%2e00&PAYMENTREQUEST_0_ITEMAMT=14%2e00&PAYMENTREQUEST_0_SHIPPINGAMT=2%2e00&PAYMENTREQUEST_0_HANDLINGAMT=0%2e00&PAYMENTREQUEST_0_TAXAMT=4%2e00&PAYMENTREQUEST_0_CUSTOM=bingbong&PAYMENTREQUEST_0_DESC=Your%20purchase%20with%20Acme%20clothes%20store&PAYMENTREQUEST_0_INSURANCEAMT=0%2e00&PAYMENTREQUEST_0_SHIPDISCAMT=0%2e00&PAYMENTREQUEST_0_INSURANCEOPTIONOFFERED=false&PAYMENTREQUEST_0_SHIPTONAME=Test%20User&PAYMENTREQUEST_0_SHIPTOSTREET=1%20Main%20Terrace&PAYMENTREQUEST_0_SHIPTOCITY=Wolverhampton&PAYMENTREQUEST_0_SHIPTOSTATE=West%20Midlands&PAYMENTREQUEST_0_SHIPTOZIP=W12%204LQ&PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE=GB&PAYMENTREQUEST_0_SHIPTOCOUNTRYNAME=United%20Kingdom&PAYMENTREQUEST_0_ADDRESSSTATUS=Confirmed&PAYMENTREQUEST_0_ADDRESSNORMALIZATIONSTATUS=None&L_PAYMENTREQUEST_0_NAME0=Blue%20shoes&L_PAYMENTREQUEST_0_NAME1=Red%20trousers&L_PAYMENTREQUEST_0_QTY0=1&L_PAYMENTREQUEST_0_QTY1=1&L_PAYMENTREQUEST_0_TAXAMT0=2%2e00&L_PAYMENTREQUEST_0_TAXAMT1=2%2e00&L_PAYMENTREQUEST_0_AMT0=8%2e00&L_PAYMENTREQUEST_0_AMT1=6%2e00&L_PAYMENTREQUEST_0_DESC0=A%20pair%20of%20really%20great%20blue%20shoes&L_PAYMENTREQUEST_0_DESC1=Tight%20pair%20of%20red%20pants%2c%20look%20good%20with%20a%20hat%2e&L_PAYMENTREQUEST_0_ITEMWEIGHTVALUE0=%20%20%200%2e00000&L_PAYMENTREQUEST_0_ITEMWEIGHTVALUE1=%20%20%200%2e00000&L_PAYMENTREQUEST_0_ITEMLENGTHVALUE0=%20%20%200%2e00000&L_PAYMENTREQUEST_0_ITEMLENGTHVALUE1=%20%20%200%2e00000&L_PAYMENTREQUEST_0_ITEMWIDTHVALUE0=%20%20%200%2e00000&L_PAYMENTREQUEST_0_ITEMWIDTHVALUE1=%20%20%200%2e00000&L_PAYMENTREQUEST_0_ITEMHEIGHTVALUE0=%20%20%200%2e00000&L_PAYMENTREQUEST_0_ITEMHEIGHTVALUE1=%20%20%200%2e00000&PAYMENTREQUESTINFO_0_ERRORCODE=0';
		$this->Paypal->HttpSocket = $this->getMock('HttpSocket');
		$this->Paypal->HttpSocket->expects($this->once())
			->method('post')
			->with($this->equalTo($expectedEndpoint) , $this->equalTo($expectedNvps))
			->will($this->throwException(new SocketException('A socket exception')));
		$result = $this->Paypal->getExpressCheckoutDetails($token);

    }

/**
 * Tests that SetExpressCheckout catches SocketExceptions and throws a more meaningful PaypalException
 *
 * @author Chris Green
 * @expectedException PaypalException
 * @expectedExceptionMessage There was a problem initiating the transaction, please try again.
 */
    public function testSetExpressCheckoutCatchSocket() {
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
			->will($this->throwException(new SocketException('A socket exception')));
		$result = $this->Paypal->setExpressCheckout($order);

    }

/**
 * Tests that a PaypalRedirectException is thrown when error 10486 is returned and correct redirect url is passed as exception message.
 *
 * @author Chris Green
 * @expectedException PaypalRedirectException
 * @expectedExceptionMessage https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=EC-482053995J417352W
 */
	public function testDoExpressCheckoutPaymentRedirectExcpetion() {
		// Setup Paypal sandbox
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar'
		));
		// Create order
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
		$token = 'EC-482053995J417352W';
		$payerId = 'GSEK8P4ARYMYS';
		$expectedEndpoint = 'https://api-3t.sandbox.paypal.com/nvp';
		// Response to return
		$mockResponse = 'TOKEN=EC%2d482053995J417352W&TIMESTAMP=2013%2d07%2d05T11%3a41%3a07Z&CORRELATIONID=a4821326a65c6&ACK=Failure&VERSION=104%2e0&BUILD=6680107&L_ERRORCODE0=10486&L_SHORTMESSAGE0=This%20transaction%20couldn%27t%20be%20completed.&L_LONGMESSAGE0=This%20transaction%20couldn%27t%20be%20completed.%20Please%20redirect%20your%20customer%20to%20PayPal.';
		$expectedNvps = array(
			'METHOD' => 'DoExpressCheckoutPayment',
			'VERSION' => '104.0',
			'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
			'USER' => 'foo',
			'PWD' => 'bar',
			'SIGNATURE' => 'foobar',
			'RETURNURL' => 'https://www.my-amazing-clothes-store.com/review-paypal.php',
			'CANCELURL' => 'https://www.my-amazing-clothes-store.com/checkout.php',
			'PAYMENTREQUEST_0_CURRENCYCODE' => 'GBP',
			'PAYMENTREQUEST_0_DESC' => 'Your purchase with Acme clothes store',
			'PAYMENTREQUEST_0_CUSTOM' => 'bingbong',
			'PAYMENTREQUEST_0_ITEMAMT' => (float) 14,
			'PAYMENTREQUEST_0_SHIPPINGAMT' => (float) 2,
			'PAYMENTREQUEST_0_TAXAMT' => (float) 4,
			'PAYMENTREQUEST_0_AMT' => (float) 20,
			'L_PAYMENTREQUEST_0_NAME0' => 'Blue shoes',
			'L_PAYMENTREQUEST_0_DESC0' => 'A pair of really great blue shoes',
			'L_PAYMENTREQUEST_0_TAXAMT0' => (float) 2,
			'L_PAYMENTREQUEST_0_AMT0' => (float) 8,
			'L_PAYMENTREQUEST_0_QTY0' => (int) 1,
			'L_PAYMENTREQUEST_0_NAME1' => 'Red trousers',
			'L_PAYMENTREQUEST_0_DESC1' => 'Tight pair of red pants, look good with a hat.',
			'L_PAYMENTREQUEST_0_TAXAMT1' => (float) 2,
			'L_PAYMENTREQUEST_0_AMT1' => (float) 6,
			'L_PAYMENTREQUEST_0_QTY1' => (int) 1,
			'TOKEN' => $token,
			'PAYERID' => $payerId
		);
		// Mock request
		$this->Paypal->HttpSocket = $this->getMock('HttpSocket');
		$this->Paypal->HttpSocket->expects($this->once())
			->method('post')
			->with($this->equalTo($expectedEndpoint) , $this->equalTo($expectedNvps))
			->will($this->returnValue($mockResponse));

		// Do the payment
		$result = $this->Paypal->doExpressCheckoutPayment($order , $token , $payerId);
	}

/**
 * Tests that the getErrorMessage method returns a custom error message when one has been set.
 *
 * @author Chris Green
 */
	public function testGetErrorMessage() {
		// Setup PayPal
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar'
		));
		// The parsed response
		$parsed = array(
			'TIMESTAMP' => '2013-09-11T20:39:58Z',
			'CORRELATIONID' => '8bca5788f3894',
			'ACK' => 'Failure',
			'VERSION' => '53.0',
			'BUILD' => '7645184',
			'L_ERRORCODE0' => '15005',
			'L_SHORTMESSAGE0' => 'Processor Decline',
			'L_LONGMESSAGE0' => 'This transaction cannot be processed.',
			'L_SEVERITYCODE0' =>'Error',
			'L_ERRORPARAMID0' => 'ProcessorResponse',
			'L_ERRORPARAMVALUE0' => '0051',
			'AMT' => '40.00',
			'CURRENCYCODE' => 'USD',
			'AVSCODE' => 'G',
			'CVV2MATCH' => 'N'
		);
		$result = $this->Paypal->getErrorMessage($parsed);
		// Is it the custom message
		$this->assertEqual($result, 'The transaction was declined by the issuing bank, not the payment gateway. The merchant should attempt another card.');
	}

/**
 * Tests that the getErrorMessage method returns the long error message in the response array when a custom message hasn't been set
 *
 * @author Chris Green
 */
	public function testGetErrorMessageNone() {
		// Setup Paypal
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar'
		));
		// The parsed response
		$parsed = array(
			'TIMESTAMP' => '2013-09-11T20:39:58Z',
			'CORRELATIONID' => '8bca5788f3894',
			'ACK' => 'Failure',
			'VERSION' => '53.0',
			'BUILD' => '7645184',
			'L_ERRORCODE0' => '10426',
			'L_SHORTMESSAGE0' => 'Transaction refused because of an invalid argument. See additional error messages for details.',
			'L_LONGMESSAGE0' => 'Item total is invalid.',
			'L_SEVERITYCODE0' =>'Error',
			'L_ERRORPARAMID0' => 'ProcessorResponse',
			'L_ERRORPARAMVALUE0' => '0051',
			'AMT' => '40.00',
			'CURRENCYCODE' => 'USD',
			'AVSCODE' => 'G',
			'CVV2MATCH' => 'N'
		);
		$result = $this->Paypal->getErrorMessage($parsed);
		// Is it L_LONGMESSAGE0
		$this->assertEqual($result, 'Item total is invalid.');
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
 * test getExpressCheckoutDetails
 *
 * @return void
 * @author Rob Mcvey
 **/
	public function testGetExpressCheckoutDetails() {
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar'
		));
		$token = 'EC-482053995J417352W';
		$expectedNvps = array(
			'METHOD' => 'GetExpressCheckoutDetails' ,
			'VERSION' => '104.0',
			'TOKEN' => $token,
			'USER' => 'foo',
			'PWD' => 'bar',
			'SIGNATURE' => 'foobar',
		);
		$expectedEndpoint = 'https://api-3t.sandbox.paypal.com/nvp';
		$mockResponse = 'TOKEN=EC%2d482053995J417352W&CHECKOUTSTATUS=PaymentActionNotInitiated&TIMESTAMP=2013%2d07%2d05T11%3a16%3a32Z&CORRELATIONID=d817ecf2ba811&ACK=Success&VERSION=104%2e0&BUILD=6680107&EMAIL=robm24_1322925502_per%40gmail%2ecom&PAYERID=GSEK8P4ARYMYS&PAYERSTATUS=verified&FIRSTNAME=Test&LASTNAME=User&COUNTRYCODE=GB&SHIPTONAME=Test%20User&SHIPTOSTREET=1%20Main%20Terrace&SHIPTOCITY=Wolverhampton&SHIPTOSTATE=West%20Midlands&SHIPTOZIP=W12%204LQ&SHIPTOCOUNTRYCODE=GB&SHIPTOCOUNTRYNAME=United%20Kingdom&ADDRESSSTATUS=Confirmed&CURRENCYCODE=GBP&AMT=20%2e00&ITEMAMT=14%2e00&SHIPPINGAMT=2%2e00&HANDLINGAMT=0%2e00&TAXAMT=4%2e00&CUSTOM=bingbong&DESC=Your%20purchase%20with%20Acme%20clothes%20store&INSURANCEAMT=0%2e00&SHIPDISCAMT=0%2e00&L_NAME0=Blue%20shoes&L_NAME1=Red%20trousers&L_QTY0=1&L_QTY1=1&L_TAXAMT0=2%2e00&L_TAXAMT1=2%2e00&L_AMT0=8%2e00&L_AMT1=6%2e00&L_DESC0=A%20pair%20of%20really%20great%20blue%20shoes&L_DESC1=Tight%20pair%20of%20red%20pants%2c%20look%20good%20with%20a%20hat%2e&L_ITEMWEIGHTVALUE0=%20%20%200%2e00000&L_ITEMWEIGHTVALUE1=%20%20%200%2e00000&L_ITEMLENGTHVALUE0=%20%20%200%2e00000&L_ITEMLENGTHVALUE1=%20%20%200%2e00000&L_ITEMWIDTHVALUE0=%20%20%200%2e00000&L_ITEMWIDTHVALUE1=%20%20%200%2e00000&L_ITEMHEIGHTVALUE0=%20%20%200%2e00000&L_ITEMHEIGHTVALUE1=%20%20%200%2e00000&PAYMENTREQUEST_0_CURRENCYCODE=GBP&PAYMENTREQUEST_0_AMT=20%2e00&PAYMENTREQUEST_0_ITEMAMT=14%2e00&PAYMENTREQUEST_0_SHIPPINGAMT=2%2e00&PAYMENTREQUEST_0_HANDLINGAMT=0%2e00&PAYMENTREQUEST_0_TAXAMT=4%2e00&PAYMENTREQUEST_0_CUSTOM=bingbong&PAYMENTREQUEST_0_DESC=Your%20purchase%20with%20Acme%20clothes%20store&PAYMENTREQUEST_0_INSURANCEAMT=0%2e00&PAYMENTREQUEST_0_SHIPDISCAMT=0%2e00&PAYMENTREQUEST_0_INSURANCEOPTIONOFFERED=false&PAYMENTREQUEST_0_SHIPTONAME=Test%20User&PAYMENTREQUEST_0_SHIPTOSTREET=1%20Main%20Terrace&PAYMENTREQUEST_0_SHIPTOCITY=Wolverhampton&PAYMENTREQUEST_0_SHIPTOSTATE=West%20Midlands&PAYMENTREQUEST_0_SHIPTOZIP=W12%204LQ&PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE=GB&PAYMENTREQUEST_0_SHIPTOCOUNTRYNAME=United%20Kingdom&PAYMENTREQUEST_0_ADDRESSSTATUS=Confirmed&PAYMENTREQUEST_0_ADDRESSNORMALIZATIONSTATUS=None&L_PAYMENTREQUEST_0_NAME0=Blue%20shoes&L_PAYMENTREQUEST_0_NAME1=Red%20trousers&L_PAYMENTREQUEST_0_QTY0=1&L_PAYMENTREQUEST_0_QTY1=1&L_PAYMENTREQUEST_0_TAXAMT0=2%2e00&L_PAYMENTREQUEST_0_TAXAMT1=2%2e00&L_PAYMENTREQUEST_0_AMT0=8%2e00&L_PAYMENTREQUEST_0_AMT1=6%2e00&L_PAYMENTREQUEST_0_DESC0=A%20pair%20of%20really%20great%20blue%20shoes&L_PAYMENTREQUEST_0_DESC1=Tight%20pair%20of%20red%20pants%2c%20look%20good%20with%20a%20hat%2e&L_PAYMENTREQUEST_0_ITEMWEIGHTVALUE0=%20%20%200%2e00000&L_PAYMENTREQUEST_0_ITEMWEIGHTVALUE1=%20%20%200%2e00000&L_PAYMENTREQUEST_0_ITEMLENGTHVALUE0=%20%20%200%2e00000&L_PAYMENTREQUEST_0_ITEMLENGTHVALUE1=%20%20%200%2e00000&L_PAYMENTREQUEST_0_ITEMWIDTHVALUE0=%20%20%200%2e00000&L_PAYMENTREQUEST_0_ITEMWIDTHVALUE1=%20%20%200%2e00000&L_PAYMENTREQUEST_0_ITEMHEIGHTVALUE0=%20%20%200%2e00000&L_PAYMENTREQUEST_0_ITEMHEIGHTVALUE1=%20%20%200%2e00000&PAYMENTREQUESTINFO_0_ERRORCODE=0';
		$this->Paypal->HttpSocket = $this->getMock('HttpSocket');
		$this->Paypal->HttpSocket->expects($this->once())
			->method('post')
			->with($this->equalTo($expectedEndpoint) , $this->equalTo($expectedNvps))
			->will($this->returnValue($mockResponse));
		$expected = array(
			'TOKEN' => 'EC-482053995J417352W',
			'CHECKOUTSTATUS' => 'PaymentActionNotInitiated',
			'TIMESTAMP' => '2013-07-05T11:16:32Z',
			'CORRELATIONID' => 'd817ecf2ba811',
			'ACK' => 'Success',
			'VERSION' => '104.0',
			'BUILD' => '6680107',
			'EMAIL' => 'robm24_1322925502_per@gmail.com',
			'PAYERID' => 'GSEK8P4ARYMYS',
			'PAYERSTATUS' => 'verified',
			'FIRSTNAME' => 'Test',
			'LASTNAME' => 'User',
			'COUNTRYCODE' => 'GB',
			'SHIPTONAME' => 'Test User',
			'SHIPTOSTREET' => '1 Main Terrace',
			'SHIPTOCITY' => 'Wolverhampton',
			'SHIPTOSTATE' => 'West Midlands',
			'SHIPTOZIP' => 'W12 4LQ',
			'SHIPTOCOUNTRYCODE' => 'GB',
			'SHIPTOCOUNTRYNAME' => 'United Kingdom',
			'ADDRESSSTATUS' => 'Confirmed',
			'CURRENCYCODE' => 'GBP',
			'AMT' => '20.00',
			'ITEMAMT' => '14.00',
			'SHIPPINGAMT' => '2.00',
			'HANDLINGAMT' => '0.00',
			'TAXAMT' => '4.00',
			'CUSTOM' => 'bingbong',
			'DESC' => 'Your purchase with Acme clothes store',
			'INSURANCEAMT' => '0.00',
			'SHIPDISCAMT' => '0.00',
			'L_NAME0' => 'Blue shoes',
			'L_NAME1' => 'Red trousers',
			'L_QTY0' => '1',
			'L_QTY1' => '1',
			'L_TAXAMT0' => '2.00',
			'L_TAXAMT1' => '2.00',
			'L_AMT0' => '8.00',
			'L_AMT1' => '6.00',
			'L_DESC0' => 'A pair of really great blue shoes',
			'L_DESC1' => 'Tight pair of red pants, look good with a hat.',
			'L_ITEMWEIGHTVALUE0' => '   0.00000',
			'L_ITEMWEIGHTVALUE1' => '   0.00000',
			'L_ITEMLENGTHVALUE0' => '   0.00000',
			'L_ITEMLENGTHVALUE1' => '   0.00000',
			'L_ITEMWIDTHVALUE0' => '   0.00000',
			'L_ITEMWIDTHVALUE1' => '   0.00000',
			'L_ITEMHEIGHTVALUE0' => '   0.00000',
			'L_ITEMHEIGHTVALUE1' => '   0.00000',
			'PAYMENTREQUEST_0_CURRENCYCODE' => 'GBP',
			'PAYMENTREQUEST_0_AMT' => '20.00',
			'PAYMENTREQUEST_0_ITEMAMT' => '14.00',
			'PAYMENTREQUEST_0_SHIPPINGAMT' => '2.00',
			'PAYMENTREQUEST_0_HANDLINGAMT' => '0.00',
			'PAYMENTREQUEST_0_TAXAMT' => '4.00',
			'PAYMENTREQUEST_0_CUSTOM' => 'bingbong',
			'PAYMENTREQUEST_0_DESC' => 'Your purchase with Acme clothes store',
			'PAYMENTREQUEST_0_INSURANCEAMT' => '0.00',
			'PAYMENTREQUEST_0_SHIPDISCAMT' => '0.00',
			'PAYMENTREQUEST_0_INSURANCEOPTIONOFFERED' => 'false',
			'PAYMENTREQUEST_0_SHIPTONAME' => 'Test User',
			'PAYMENTREQUEST_0_SHIPTOSTREET' => '1 Main Terrace',
			'PAYMENTREQUEST_0_SHIPTOCITY' => 'Wolverhampton',
			'PAYMENTREQUEST_0_SHIPTOSTATE' => 'West Midlands',
			'PAYMENTREQUEST_0_SHIPTOZIP' => 'W12 4LQ',
			'PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE' => 'GB',
			'PAYMENTREQUEST_0_SHIPTOCOUNTRYNAME' => 'United Kingdom',
			'PAYMENTREQUEST_0_ADDRESSSTATUS' => 'Confirmed',
			'PAYMENTREQUEST_0_ADDRESSNORMALIZATIONSTATUS' => 'None',
			'L_PAYMENTREQUEST_0_NAME0' => 'Blue shoes',
			'L_PAYMENTREQUEST_0_NAME1' => 'Red trousers',
			'L_PAYMENTREQUEST_0_QTY0' => '1',
			'L_PAYMENTREQUEST_0_QTY1' => '1',
			'L_PAYMENTREQUEST_0_TAXAMT0' => '2.00',
			'L_PAYMENTREQUEST_0_TAXAMT1' => '2.00',
			'L_PAYMENTREQUEST_0_AMT0' => '8.00',
			'L_PAYMENTREQUEST_0_AMT1' => '6.00',
			'L_PAYMENTREQUEST_0_DESC0' => 'A pair of really great blue shoes',
			'L_PAYMENTREQUEST_0_DESC1' => 'Tight pair of red pants, look good with a hat.',
			'L_PAYMENTREQUEST_0_ITEMWEIGHTVALUE0' => '   0.00000',
			'L_PAYMENTREQUEST_0_ITEMWEIGHTVALUE1' => '   0.00000',
			'L_PAYMENTREQUEST_0_ITEMLENGTHVALUE0' => '   0.00000',
			'L_PAYMENTREQUEST_0_ITEMLENGTHVALUE1' => '   0.00000',
			'L_PAYMENTREQUEST_0_ITEMWIDTHVALUE0' => '   0.00000',
			'L_PAYMENTREQUEST_0_ITEMWIDTHVALUE1' => '   0.00000',
			'L_PAYMENTREQUEST_0_ITEMHEIGHTVALUE0' => '   0.00000',
			'L_PAYMENTREQUEST_0_ITEMHEIGHTVALUE1' => '   0.00000',
			'PAYMENTREQUESTINFO_0_ERRORCODE' => '0'
		);
		$result = $this->Paypal->getExpressCheckoutDetails($token);
		$this->assertEqual($expected , $result);
	}

/**
 * test doExpressCheckoutPayment
 *
 * @return void
 * @author Rob Mcvey
 **/
	public function testDoExpressCheckoutPayment() {
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
		$token = 'EC-482053995J417352W';
		$payerId = 'GSEK8P4ARYMYS';
		$expectedEndpoint = 'https://api-3t.sandbox.paypal.com/nvp';
		$mockResponse = 'TOKEN=EC%2d88C117816V738422U&SUCCESSPAGEREDIRECTREQUESTED=false&TIMESTAMP=2013%2d07%2d05T11%3a41%3a07Z&CORRELATIONID=a4821326a65c6&ACK=Success&VERSION=104%2e0&BUILD=6680107&INSURANCEOPTIONSELECTED=false&SHIPPINGOPTIONISDEFAULT=false&PAYMENTINFO_0_TRANSACTIONID=5V948789BD843205H&PAYMENTINFO_0_TRANSACTIONTYPE=cart&PAYMENTINFO_0_PAYMENTTYPE=instant&PAYMENTINFO_0_ORDERTIME=2013%2d07%2d05T11%3a41%3a06Z&PAYMENTINFO_0_AMT=20%2e00&PAYMENTINFO_0_FEEAMT=0%2e88&PAYMENTINFO_0_TAXAMT=4%2e00&PAYMENTINFO_0_CURRENCYCODE=GBP&PAYMENTINFO_0_PAYMENTSTATUS=Completed&PAYMENTINFO_0_PENDINGREASON=None&PAYMENTINFO_0_REASONCODE=None&PAYMENTINFO_0_PROTECTIONELIGIBILITY=Eligible&PAYMENTINFO_0_PROTECTIONELIGIBILITYTYPE=ItemNotReceivedEligible%2cUnauthorizedPaymentEligible&PAYMENTINFO_0_SECUREMERCHANTACCOUNTID=AD4VU2GRDM9EU&PAYMENTINFO_0_ERRORCODE=0&PAYMENTINFO_0_ACK=Success';
		$expectedNvps = array(
			'METHOD' => 'DoExpressCheckoutPayment',
			'VERSION' => '104.0',
			'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
			'USER' => 'foo',
			'PWD' => 'bar',
			'SIGNATURE' => 'foobar',
			'RETURNURL' => 'https://www.my-amazing-clothes-store.com/review-paypal.php',
			'CANCELURL' => 'https://www.my-amazing-clothes-store.com/checkout.php',
			'PAYMENTREQUEST_0_CURRENCYCODE' => 'GBP',
			'PAYMENTREQUEST_0_DESC' => 'Your purchase with Acme clothes store',
			'PAYMENTREQUEST_0_CUSTOM' => 'bingbong',
			'PAYMENTREQUEST_0_ITEMAMT' => (float) 14,
			'PAYMENTREQUEST_0_SHIPPINGAMT' => (float) 2,
			'PAYMENTREQUEST_0_TAXAMT' => (float) 4,
			'PAYMENTREQUEST_0_AMT' => (float) 20,
			'L_PAYMENTREQUEST_0_NAME0' => 'Blue shoes',
			'L_PAYMENTREQUEST_0_DESC0' => 'A pair of really great blue shoes',
			'L_PAYMENTREQUEST_0_TAXAMT0' => (float) 2,
			'L_PAYMENTREQUEST_0_AMT0' => (float) 8,
			'L_PAYMENTREQUEST_0_QTY0' => (int) 1,
			'L_PAYMENTREQUEST_0_NAME1' => 'Red trousers',
			'L_PAYMENTREQUEST_0_DESC1' => 'Tight pair of red pants, look good with a hat.',
			'L_PAYMENTREQUEST_0_TAXAMT1' => (float) 2,
			'L_PAYMENTREQUEST_0_AMT1' => (float) 6,
			'L_PAYMENTREQUEST_0_QTY1' => (int) 1,
			'TOKEN' => $token,
			'PAYERID' => $payerId
		);
		$this->Paypal->HttpSocket = $this->getMock('HttpSocket');
		$this->Paypal->HttpSocket->expects($this->once())
			->method('post')
			->with($this->equalTo($expectedEndpoint) , $this->equalTo($expectedNvps))
			->will($this->returnValue($mockResponse));
		$expected = array(
			'TOKEN' => $token,
			'SUCCESSPAGEREDIRECTREQUESTED' => 'false',
			'TIMESTAMP' => '2013-07-05T11:41:07Z',
			'CORRELATIONID' => 'a4821326a65c6',
			'ACK' => 'Success',
			'VERSION' => '104.0',
			'BUILD' => '6680107',
			'INSURANCEOPTIONSELECTED' => 'false',
			'SHIPPINGOPTIONISDEFAULT' => 'false',
			'PAYMENTINFO_0_TRANSACTIONID' => '5V948789BD843205H',
			'PAYMENTINFO_0_TRANSACTIONTYPE' => 'cart',
			'PAYMENTINFO_0_PAYMENTTYPE' => 'instant',
			'PAYMENTINFO_0_ORDERTIME' => '2013-07-05T11:41:06Z',
			'PAYMENTINFO_0_AMT' => '20.00',
			'PAYMENTINFO_0_FEEAMT' => '0.88',
			'PAYMENTINFO_0_TAXAMT' => '4.00',
			'PAYMENTINFO_0_CURRENCYCODE' => 'GBP',
			'PAYMENTINFO_0_PAYMENTSTATUS' => 'Completed',
			'PAYMENTINFO_0_PENDINGREASON' => 'None',
			'PAYMENTINFO_0_REASONCODE' => 'None',
			'PAYMENTINFO_0_PROTECTIONELIGIBILITY' => 'Eligible',
			'PAYMENTINFO_0_PROTECTIONELIGIBILITYTYPE' => 'ItemNotReceivedEligible,UnauthorizedPaymentEligible',
			'PAYMENTINFO_0_SECUREMERCHANTACCOUNTID' => 'AD4VU2GRDM9EU',
			'PAYMENTINFO_0_ERRORCODE' => '0',
			'PAYMENTINFO_0_ACK' => 'Success'
		);
		$result = $this->Paypal->doExpressCheckoutPayment($order , $token , $payerId);
	}

/**
 * test parseClassicApiResponse
 *
 * @return void
 * @author Rob Mcvey
 **/
	public function testParseClassicApiResponse() {
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar'
		));

		// Success
		$response = 'TOKEN=EC%2d5PY500325X986371J&TIMESTAMP=2013%2d07%2d04T13%3a37%3a53Z&CORRELATIONID=845286d6c4caa&ACK=Success&VERSION=104%2e0&BUILD=6680107';
		$result = $this->Paypal->parseClassicApiResponse($response);
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
		$result = $this->Paypal->parseClassicApiResponse($response);
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
 * test getRestEndpoint
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
		$this->assertEqual("https://svcs.paypal.com/AdaptiveAccounts/" , $this->Paypal->getAdaptiveAccountsEndpoint());
		// Sandbox
		$this->Paypal = new Paypal(array('sandboxMode' => true));
		$this->assertEqual("https://api.sandbox.paypal.com" , $this->Paypal->getRestEndpoint());
		$this->assertEqual("https://api-3t.sandbox.paypal.com/nvp" , $this->Paypal->getClassicEndpoint());
		$this->assertEqual("https://www.sandbox.paypal.com/webscr" , $this->Paypal->getPaypalLoginUri());
		$this->assertEqual("https://svcs.sandbox.paypal.com/AdaptiveAccounts/" , $this->Paypal->getAdaptiveAccountsEndpoint());
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
 * test buildExpressCheckoutNvp exceptions
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
 * test doDirectPayment
 *
 * @return void
 * @author Rob Mcvey
 **/
	public function testDoDirectPayment() {
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar'
		));
		$payment = array(
			'amount' => 30.00,
			'card' => '4008 0687 0641 8697', // This is a sandbox CC
			'expiry' => array(
				'M' => '2',
				'Y' => '2016',
			),
			'cvv' => '321',
		);
		// Mock the CakeRequest class
		$this->Paypal->CakeRequest = $this->getMock('CakeRequest');
		$this->Paypal->CakeRequest->expects($this->once())
			->method('clientIp')
			->will($this->returnValue("217.114.52.94"));

		// Mock the HttpSocket class
		$expectedEndpoint = 'https://api-3t.sandbox.paypal.com/nvp';
		$mockResponse = 'TIMESTAMP=2013%2d07%2d05T13%3a52%3a48Z&CORRELATIONID=5d7677126e0b4&ACK=Success&VERSION=104%2e0&BUILD=6680107&AMT=30%2e00&CURRENCYCODE=GBP&AVSCODE=X&CVV2MATCH=M&TRANSACTIONID=0XW09448VG556664J';
		$expectedNvps = array(
			'METHOD' => 'DoDirectPayment',
			'VERSION' => '104.0',
			'USER' => 'foo',
			'PWD' => 'bar',
			'SIGNATURE' => 'foobar',
			'IPADDRESS' => '217.114.52.94',
			'AMT' => 30.00,
			'CURRENCYCODE' => 'GBP',
			'RECURRING' => 'N',
			'ACCT' => '4008068706418697', // This is a sandbox CC
			'EXPDATE' => '022016',
			'CVV2' => '321',
			'FIRSTNAME' => '',
			'LASTNAME' => '',
			'STREET' => '',
			'CITY' => '',
			'STATE' => '',
			'COUNTRYCODE' => '',
			'ZIP' => ''
		);
		$this->Paypal->HttpSocket = $this->getMock('HttpSocket');
		$this->Paypal->HttpSocket->expects($this->once())
			->method('post')
			->with($this->equalTo($expectedEndpoint) , $this->equalTo($expectedNvps))
			->will($this->returnValue($mockResponse));

		$result = $this->Paypal->doDirectPayment($payment);
		$expected = array(
			'TIMESTAMP' => '2013-07-05T13:52:48Z',
			'CORRELATIONID' => '5d7677126e0b4',
			'ACK' => 'Success',
			'VERSION' => '104.0',
			'BUILD' => '6680107',
			'AMT' => '30.00',
			'CURRENCYCODE' => 'GBP',
			'AVSCODE' => 'X',
			'CVV2MATCH' => 'M',
			'TRANSACTIONID' => '0XW09448VG556664J'
		);
		$this->assertEqual($expected , $result);
	}

/**
 * test formatDoDirectPaymentNvps
 *
 * @return void
 * @author Rob Mcvey
 **/
	public function testFormatDoDirectPaymentNvps() {
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar'
		));
		$payment = array(
			'amount' => 30.00,
			'currency' => 'GBP',
			'card' => '4008 0687 0641 8697', // This is a sandbox CC
			'expiry' => array(
				'M' => '2',
				'Y' => '2016',
			),
			'cvv' => '321',
		);
		$this->Paypal->CakeRequest = $this->getMock('CakeRequest');
		$this->Paypal->CakeRequest->expects($this->once())
			->method('clientIp')
			->will($this->returnValue("217.114.52.94"));
		$result = $this->Paypal->formatDoDirectPaymentNvps($payment);
		$expected = array(
			'METHOD' => 'DoDirectPayment',
			'VERSION' => '104.0',
			'USER' => 'foo',
			'PWD' => 'bar',
			'SIGNATURE' => 'foobar',
			'IPADDRESS' => '217.114.52.94',
			'AMT' => 30.00,
			'CURRENCYCODE' => 'GBP',
			'RECURRING' => 'N',
			'ACCT' => '4008068706418697', // This is a sandbox CC
			'EXPDATE' => '022016',
			'CVV2' => '321',
			'FIRSTNAME' => '',
			'LASTNAME' => '',
			'STREET' => '',
			'CITY' => '',
			'STATE' => '',
			'COUNTRYCODE' => '',
			'ZIP' => ''
		);
		$this->assertEqual($expected , $result);
	}
	
/**
 * testFormatRefundTransactionNvpsMissingTransId
 *
 * @return void
 * @author Rob Mcvey
 * @expectedException PaypalException
 * @expectedExceptionMessage Original PayPal Transaction ID is required
 **/
	public function testFormatRefundTransactionNvpsMissingTransId() {
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar'
		));
		$refund = array();
		$this->Paypal->formatRefundTransactionNvps($refund);
	}

/**
 * testFormatRefundTransactionNvpsMissingAmt
 *
 * @return void
 * @author Rob Mcvey
 * @expectedException PaypalException
 * @expectedExceptionMessage Must specify an "amount" to refund
 **/
	public function testFormatRefundTransactionNvpsMissingAmt() {
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar'
		));
		$refund = array(
			'transactionId' => 'XYZ'
		);
		$this->Paypal->formatRefundTransactionNvps($refund);
	}

/**
 * testFormatRefundTransactionNvpsMissingType
 *
 * @return void
 * @author Rob Mcvey
 * @expectedException PaypalException
 * @expectedExceptionMessage You must specify a refund type, such as Full or Partial
 **/
	public function testFormatRefundTransactionNvpsMissingType() {
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar'
		));
		$refund = array(
			'transactionId' => 'XYZ',
			'amount' => '40.00'
		);
		$this->Paypal->formatRefundTransactionNvps($refund);
	}

/**
 * testFormatRefundTransactionNvps
 *
 * @return void
 * @author Rob Mcvey
 **/
	public function testFormatRefundTransactionNvps() {
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar'
		));
		$refund = array(
			'transactionId' => 'XYZ',
			'amount' => '40.00',
			'type' => 'Full'
		);
		$nvps = $this->Paypal->formatRefundTransactionNvps($refund);
		$expected = array(
			'METHOD' => 'RefundTransaction',
			'VERSION' => '104.0',
			'USER' => 'foo',
			'PWD' => 'bar',
			'SIGNATURE' => 'foobar',
			'TRANSACTIONID' => 'XYZ',
			'INVOICEID' => '',
			'REFUNDTYPE' => 'Full',
			'CURRENCYCODE' => 'GBP',
			'NOTE' => false,
			'REFUNDSOURCE' => 'any'
		);
		$this->assertEqual($expected, $nvps);
	}

/**
 * testFormatRefundTransactionNvpsWithOptions
 *
 * @return void
 * @author Rob Mcvey
 **/
	public function testFormatRefundTransactionNvpsWithOptions() {
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar'
		));
		$refund = array(
			'transactionId' => 'XYZ 123',
			'amount' => '40.00',
			'type' => 'Partial',
			'source' => 'tomato',
			'currency' => 'AUD',
			'note' => 'Here is your refund do try not to spend it all at once',
			'reference' => '345SGS24afsdfsFASD',
		);
		$nvps = $this->Paypal->formatRefundTransactionNvps($refund);
		$expected = array(
			'METHOD' => 'RefundTransaction',
			'VERSION' => '104.0',
			'USER' => 'foo',
			'PWD' => 'bar',
			'SIGNATURE' => 'foobar',
			'TRANSACTIONID' => 'XYZ123',
			'INVOICEID' => '345SGS24afsdfsFASD',
			'REFUNDTYPE' => 'Partial',
			'CURRENCYCODE' => 'AUD',
			'NOTE' => 'Here is your refund do try not to spend it all at once',
			'REFUNDSOURCE' => 'tomato',
			'AMT' => '40.00'
		);
		$this->assertEqual($expected, $nvps);
	}

/**
 * testRefundTransactionRequest
 *
 * @return void
 * @author Rob Mcvey
 **/
	public function testRefundTransactionRequest() {
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar'
		));
		$refund = array(
			'transactionId' => '0D4480860X580710H',
			'amount' => '60.00',
			'type' => 'Full',
			'source' => 'tomato',
			'currency' => 'GBP',
			'note' => 'Here is your refund do try not to spend it all at once',
			'reference' => '4324',
		);
		$expectedNvps = array(
			'METHOD' => 'RefundTransaction',
			'VERSION' => '104.0',
			'USER' => 'foo',
			'PWD' => 'bar',
			'SIGNATURE' => 'foobar',
			'TRANSACTIONID' => '0D4480860X580710H',
			'INVOICEID' => '4324',
			'REFUNDTYPE' => 'Full',
			'CURRENCYCODE' => 'GBP',
			'NOTE' => 'Here is your refund do try not to spend it all at once',
			'REFUNDSOURCE' => 'tomato',
		);
		$expectedEndpoint = 'https://api-3t.sandbox.paypal.com/nvp';
			$mockResponse = 'REFUNDTRANSACTIONID=17U98664L1321363R&FEEREFUNDAMT=2%2e04&GROSSREFUNDAMT=60%2e00&NETREFUNDAMT=57%2e96&CURRENCYCODE=GBP&TOTALREFUNDEDAMOUNT=60%2e00&TIMESTAMP=2014%2d02%2d17T16%3a31%3a14Z&CORRELATIONID=e46d51aadf37&ACK=Success&VERSION=104%2e0&BUILD=9720069&REFUNDSTATUS=Instant&PENDINGREASON=None';
			$this->Paypal->HttpSocket = $this->getMock('HttpSocket');
			$this->Paypal->HttpSocket->expects($this->once())
				->method('post')
				->with($this->equalTo($expectedEndpoint) , $this->equalTo($expectedNvps))
				->will($this->returnValue($mockResponse));
		$result = $this->Paypal->refundTransaction($refund);
		$expectedParsedResponse = array(
			'REFUNDTRANSACTIONID' => '17U98664L1321363R',
			'FEEREFUNDAMT' => '2.04',
			'GROSSREFUNDAMT' => '60.00',
			'NETREFUNDAMT' => '57.96',
			'CURRENCYCODE' => 'GBP',
			'TOTALREFUNDEDAMOUNT' => '60.00',
			'TIMESTAMP' => '2014-02-17T16:31:14Z',
			'CORRELATIONID' => 'e46d51aadf37',
			'ACK' => 'Success',
			'VERSION' => '104.0',
			'BUILD' => '9720069',
			'REFUNDSTATUS' => 'Instant',
			'PENDINGREASON' => 'None'
		);
		$this->assertEqual($expectedParsedResponse, $result);
	}		

/**
 * testRefundTransactionRequestPartial
 *
 * @return void
 * @author Rob Mcvey
 **/
	public function testRefundTransactionRequestPartial() {
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar'
		));
		$refund = array(
			'transactionId' => '3L696641B2868664L',
			'amount' => '10.00',
			'type' => 'Partial',
			'source' => 'tomato',
			'currency' => 'GBP',
			'note' => 'Here is your refund do try not to spend it all at once',
			'reference' => '4323',
		);
		$expectedNvps = array(
			'METHOD' => 'RefundTransaction',
			'VERSION' => '104.0',
			'USER' => 'foo',
			'PWD' => 'bar',
			'SIGNATURE' => 'foobar',
			'TRANSACTIONID' => '3L696641B2868664L',
			'INVOICEID' => '4323',
			'REFUNDTYPE' => 'Partial',
			'CURRENCYCODE' => 'GBP',
			'NOTE' => 'Here is your refund do try not to spend it all at once',
			'REFUNDSOURCE' => 'tomato',
			'AMT' => '10.00'
		);
		$expectedEndpoint = 'https://api-3t.sandbox.paypal.com/nvp';
		$mockResponse = 'REFUNDTRANSACTIONID=7A85962474587184P&FEEREFUNDAMT=0%2e34&GROSSREFUNDAMT=10%2e00&NETREFUNDAMT=9%2e66&CURRENCYCODE=GBP&TOTALREFUNDEDAMOUNT=10%2e00&TIMESTAMP=2014%2d02%2d17T16%3a20%3a54Z&CORRELATIONID=dec2e2442c751&ACK=Success&VERSION=104%2e0&BUILD=9720069&REFUNDSTATUS=Instant&PENDINGREASON=None';
		$this->Paypal->HttpSocket = $this->getMock('HttpSocket');
		$this->Paypal->HttpSocket->expects($this->once())
			->method('post')
			->with($this->equalTo($expectedEndpoint) , $this->equalTo($expectedNvps))
			->will($this->returnValue($mockResponse));
		$result = $this->Paypal->refundTransaction($refund);
		$expectedParsedResponse = array(
			'REFUNDTRANSACTIONID' => '7A85962474587184P',
			'FEEREFUNDAMT' => '0.34',
			'GROSSREFUNDAMT' => '10.00',
			'NETREFUNDAMT' => '9.66',
			'CURRENCYCODE' => 'GBP',
			'TOTALREFUNDEDAMOUNT' => '10.00',
			'TIMESTAMP' => '2014-02-17T16:20:54Z',
			'CORRELATIONID' => 'dec2e2442c751',
			'ACK' => 'Success',
			'VERSION' => '104.0',
			'BUILD' => '9720069',
			'REFUNDSTATUS' => 'Instant',
			'PENDINGREASON' => 'None'
		);
		$this->assertEqual($expectedParsedResponse, $result);
	}
	
/**
 * testValidateCC
 *
 * @return void
 * @author Rob Mcvey
 **/
	public function testValidateCC() {
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar',
			'oAuthClientId' => 'ASPu1-BDs2U35hVQkITi2WzX98cbNahkSeoxsuvfmrXNTz-gA5EGslxk0fAFU',
			'oAuthSecret' => 'QO6RKRBxI2co80qlfPHYQ14G-U9Smbz4H8fUIY9Mf846URRNdYNNXQ2wghxC',
		));
		$this->assertFalse($this->Paypal->validateCC('hello'));
		$this->assertFalse($this->Paypal->validateCC('2341324'));
		$this->assertTrue($this->Paypal->validateCC('4008 0687 0641 8697 '));
	}
	
/**
 * testStoreCreditCard
 *
 * @return void
 * @author Rob Mcvey
 **/
	public function testStoreCreditCard() {
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar',
			'oAuthClientId' => 'ASPu1-BDs2U35hVQkITi2WzX98cbNahkSeoxsuvfmrXNTz-gA5EGslxk0fAFU',
			'oAuthSecret' => 'QO6RKRBxI2co80qlfPHYQ14G-U9Smbz4H8fUIY9Mf846URRNdYNNXQ2wghxC',
		));
		// Mock the HttpSocket classs
		$this->Paypal->HttpSocket = $this->getMock('HttpSocket');
		// Mock the HttpSocket configAuth method
		$this->Paypal->HttpSocket->expects($this->once())
			->method('configAuth')
			->with('Basic', 'ASPu1-BDs2U35hVQkITi2WzX98cbNahkSeoxsuvfmrXNTz-gA5EGslxk0fAFU', 'QO6RKRBxI2co80qlfPHYQ14G-U9Smbz4H8fUIY9Mf846URRNdYNNXQ2wghxC');
		// Mock the HttpSocket post method
		$expectedTokenResponse = json_encode(array(
			'scope' => 'openid https://api.paypal.com/v1/payments/.* https://api.paypal.com/v1/developer/.* https://api.paypal.com/v1/vault/credit-card/.* https://api.paypal.com/v1/vault/credit-card',
			'access_token' => 'OfQ0Q3pYoy0MSHY4d2ZL8HcKPBE0ZDn1qCe.mkrDTkE',
			'token_type' => 'Bearer',
			'app_id' => 'APP-80W284485P519543T',
			'expires_in' => 28800
		));
		
		$HttpSocketResponse = new stdClass();
		$HttpSocketResponse->body = $expectedTokenResponse;
		$HttpSocketResponse->code = 200;
		
		$this->Paypal->HttpSocket->expects($this->once())
			->method('post')
			->with('https://api.sandbox.paypal.com/v1/oauth2/token', array(
				"grant_type" => "client_credentials"
			))
			->will($this->returnValue($HttpSocketResponse));
			
		// Mock the HttpSocket reset method
		$this->Paypal->HttpSocket->expects($this->once())
			->method('reset');
		// Mock the HttpSocket request method
		$mockResponse = '{"id":"CARD-5LH05598KW1155459KMYXT7I","state":"ok","payer_id":"186","type":"visa","number":"xxxxxxxxxxxx8697","expire_month":"2","expire_year":"2018","first_name":"Joe","last_name":"Shopper","valid_until":"2017-03-24T00:00:00Z","create_time":"2014-03-25T12:43:41Z","update_time":"2014-03-25T12:43:41Z","links":[{"href":"https://api.sandbox.paypal.com/v1/vault/credit-card/CARD-5LH05598KW1155459KMYXT7I","rel":"self","method":"GET"},{"href":"https://api.sandbox.paypal.com/v1/vault/credit-card/CARD-5LH05598KW1155459KMYXT7I","rel":"delete","method":"DELETE"},{"href":"https://api.sandbox.paypal.com/v1/vault/credit-card/CARD-5LH05598KW1155459KMYXT7I","rel":"patch","method":"PATCH"}]}';
		
		$HttpSocketResponse = new stdClass();
		$HttpSocketResponse->body = $mockResponse;
		$HttpSocketResponse->code = 200;
		
		$mockRequest = array(
			'method' => 'POST',
			'header' => array(
				'Content-Type' => 'application/json',
				'Authorization' => 'Bearer OfQ0Q3pYoy0MSHY4d2ZL8HcKPBE0ZDn1qCe.mkrDTkE'
			),
			'uri' => 'https://api.sandbox.paypal.com/v1/vault/credit-card',
			'body' => '{"number":"4008068706418697","cvv2":232,"type":"visa","expire_month":"2","expire_year":"2018","payer_id":186,"first_name":"Joe","last_name":"Shopper"}'
		);
		$this->Paypal->HttpSocket->expects($this->once())
			->method('request')
			->with($this->equalTo($mockRequest))
			->will($this->returnValue($HttpSocketResponse));
		// Call the storeCreditCard method
		$creditCard = array(
			'payer_id' => 186,
			'type' => 'visa',
			'card' => '4008 0687 0641 8697 ',
			'cvv2' => 232,
			'expiry' => array(
			    'M' => '2',
		        'Y' => '2018',
		    ),
			'first_name' => 'Joe',
			'last_name' => 'Shopper'
		);
		$result = $this->Paypal->storeCreditCard($creditCard);
		// Our expected response
		$expectedResult = array(
			'id' => 'CARD-5LH05598KW1155459KMYXT7I',
			'state' => 'ok',
			'payer_id' => '186',
			'type' => 'visa',
			'number' => 'xxxxxxxxxxxx8697',
			'expire_month' => '2',
			'expire_year' => '2018',
			'first_name' => 'Joe',
			'last_name' => 'Shopper',
			'valid_until' => '2017-03-24T00:00:00Z',
			'create_time' => '2014-03-25T12:43:41Z',
			'update_time' => '2014-03-25T12:43:41Z',
			'links' => array(
				0 => array(
					'href' => 'https://api.sandbox.paypal.com/v1/vault/credit-card/CARD-5LH05598KW1155459KMYXT7I',
					'rel' => 'self',
					'method' => 'GET'
				),
				1 => array(
					'href' => 'https://api.sandbox.paypal.com/v1/vault/credit-card/CARD-5LH05598KW1155459KMYXT7I',
					'rel' => 'delete',
					'method' => 'DELETE'
				),
				2 => array(
					'href' => 'https://api.sandbox.paypal.com/v1/vault/credit-card/CARD-5LH05598KW1155459KMYXT7I',
					'rel' => 'patch',
					'method' => 'PATCH'
				)
			)
		);
		$this->assertEqual($expectedResult, $result);
	}

/**
 * testStoreCreditCardErrorResponse
 *
 * @return void
 * @author Rob Mcvey
 * @expectedException PaypalException
 * @expectedExceptionMessage Invalid request - see details
 **/
	public function testStoreCreditCardErrorResponse() {
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar',
			'oAuthClientId' => 'ASPu1-BDs2U35hVQkITi2WzX98cbNahkSeoxsuvfmrXNTz-gA5EGslxk0fAFU',
			'oAuthSecret' => 'QO6RKRBxI2co80qlfPHYQ14G-U9Smbz4H8fUIY9Mf846URRNdYNNXQ2wghxC',
		));
		// Mock the HttpSocket classs
		$this->Paypal->HttpSocket = $this->getMock('HttpSocket');
		// Mock the HttpSocket configAuth method
		$this->Paypal->HttpSocket->expects($this->once())
			->method('configAuth')
			->with('Basic', 'ASPu1-BDs2U35hVQkITi2WzX98cbNahkSeoxsuvfmrXNTz-gA5EGslxk0fAFU', 'QO6RKRBxI2co80qlfPHYQ14G-U9Smbz4H8fUIY9Mf846URRNdYNNXQ2wghxC');
		// Mock the HttpSocket post method
		$expectedTokenResponse = json_encode(array(
			'scope' => 'openid https://api.paypal.com/v1/payments/.* https://api.paypal.com/v1/developer/.* https://api.paypal.com/v1/vault/credit-card/.* https://api.paypal.com/v1/vault/credit-card',
			'access_token' => 'OfQ0Q3pYoy0MSHY4d2ZL8HcKPBE0ZDn1qCe.mkrDTkE',
			'token_type' => 'Bearer',
			'app_id' => 'APP-80W284485P519543T',
			'expires_in' => 28800
		));
		// Mock response object
		$HttpSocketResponse = new stdClass();
		$HttpSocketResponse->body = $expectedTokenResponse;
		$HttpSocketResponse->code = 200;
		// Mock the post request
		$this->Paypal->HttpSocket->expects($this->once())
			->method('post')
			->with('https://api.sandbox.paypal.com/v1/oauth2/token', array(
				"grant_type" => "client_credentials"
			))
			->will($this->returnValue($HttpSocketResponse));	
		// Mock the HttpSocket reset method
		$this->Paypal->HttpSocket->expects($this->once())
			->method('reset');
		// Mock the HttpSocket request method
		$mockResponse = '{"id":"CARD-5LH05598KW1155459KMYXT7I","state":"ok","payer_id":"186","type":"visa","number":"xxxxxxxxxxxx8697","expire_month":"2","expire_year":"2018","first_name":"Joe","last_name":"Shopper","valid_until":"2017-03-24T00:00:00Z","create_time":"2014-03-25T12:43:41Z","update_time":"2014-03-25T12:43:41Z","links":[{"href":"https://api.sandbox.paypal.com/v1/vault/credit-card/CARD-5LH05598KW1155459KMYXT7I","rel":"self","method":"GET"},{"href":"https://api.sandbox.paypal.com/v1/vault/credit-card/CARD-5LH05598KW1155459KMYXT7I","rel":"delete","method":"DELETE"},{"href":"https://api.sandbox.paypal.com/v1/vault/credit-card/CARD-5LH05598KW1155459KMYXT7I","rel":"patch","method":"PATCH"}]}';
		// Our mock response (error)
		$errorResponse = json_encode(array(
			'name' => 'VALIDATION_ERROR',
			'details' => array(
				0 => array(
					'field' => 'type',
					'issue' => 'Value is invalid (must be visa, mastercard, amex, discover, or maestro)'
				)
			),
			'message' => 'Invalid request - see details',
			'information_link' => 'https://developer.paypal.com/docs/api/#VALIDATION_ERROR',
			'debug_id' => 'a697c8e0f566f'
		));
		// Mock the response object again, this time 401 bad request code
		$HttpSocketResponse = new stdClass();
		$HttpSocketResponse->body = $errorResponse;
		$HttpSocketResponse->code = 401;
		// The request we expect to be sent
		$mockRequest = array(
			'method' => 'POST',
			'header' => array(
				'Content-Type' => 'application/json',
				'Authorization' => 'Bearer OfQ0Q3pYoy0MSHY4d2ZL8HcKPBE0ZDn1qCe.mkrDTkE'
			),
			'uri' => 'https://api.sandbox.paypal.com/v1/vault/credit-card',
			'body' => '{"number":"4008068706418697","cvv2":232,"type":"visa","expire_month":"2","expire_year":"2018","payer_id":186,"first_name":"Joe","last_name":"Shopper"}'
		);
		$this->Paypal->HttpSocket->expects($this->once())
			->method('request')
			->with($this->equalTo($mockRequest))
			->will($this->returnValue($HttpSocketResponse));
		// Call the storeCreditCard method
		$creditCard = array(
			'payer_id' => 186,
			'type' => 'visa',
			'card' => '4008 0687 0641 8697 ',
			'cvv2' => 232,
			'expiry' => array(
			    'M' => '2',
		        'Y' => '2018',
		    ),
			'first_name' => 'Joe',
			'last_name' => 'Shopper'
		);
		$result = $this->Paypal->storeCreditCard($creditCard);
	}	

/**
 * testStoreFormatCreditCardArgs
 *
 * @return void
 * @author Rob Mcvey
 **/
	public function testStoreFormatCreditCardArgs() {
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar'
		));
		$creditCard = array(
			'payer_id' => 186,
			'type' => 'visa',
			'card' => '4008 0687 0641 8697 ',
			'cvv2' => 232,
			'expiry' => array(
			    'M' => '2',
		        'Y' => '2018',
		    ),
			'first_name' => 'Joe',
			'last_name' => 'Shopper'
		);
		$expected = array(
			"payer_id" => 186,
			"type" => "visa",
			"number" => "4008068706418697",
			'cvv2' => 232,
			"expire_month" => "2",
			"expire_year" => "2018",
			"first_name" => "Joe",
			"last_name" => "Shopper"
		);
		$result = $this->Paypal->formatStoreCreditCardArgs($creditCard);
		$this->assertEqual($expected, $result);
	}
	
/**
 * testStoreFormatCreditCardArgsMissingCard
 *
 * @return void
 * @author Rob Mcvey
 * @expectedException PaypalException
 * @expectedExceptionMessage Invalid card number
 **/
	public function testStoreFormatCreditCardArgsMissingCard() {
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar'
		));
		$creditCard = array(
			'payer_id' => 186,
			'type' => 'visa',
			'card' => '4008',
			'cvv2' => 232,
			'expiry' => array(
			    'M' => '2',
		        'Y' => '2018',
		    ),
			'first_name' => 'Joe',
			'last_name' => 'Shopper'
		);
		$result = $this->Paypal->formatStoreCreditCardArgs($creditCard);
	}

/**
 * testStoreFormatCreditCardArgsMissingCVV2
 *
 * @return void
 * @author Rob Mcvey
 * @expectedException PaypalException
 * @expectedExceptionMessage Invalid CVV2 number
 **/
	public function testStoreFormatCreditCardArgsMissingCVV2() {
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar'
		));
		$creditCard = array(
			'payer_id' => 186,
			'type' => 'visa',
			'card' => '4008 0687 0641 8697 ',
			'expiry' => array(
			    'M' => '2',
		        'Y' => '2018',
		    ),
			'first_name' => 'Joe',
			'last_name' => 'Shopper'
		);
		$result = $this->Paypal->formatStoreCreditCardArgs($creditCard);
	}

/**
 * testStoreFormatCreditCardArgsInvalidDate
 *
 * @return void
 * @author Rob Mcvey
 * @expectedException PaypalException
 * @expectedExceptionMessage Invalid expiry date
 **/
	public function testStoreFormatCreditCardArgsInvalidDate() {
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar'
		));
		$creditCard = array(
			'payer_id' => 186,
			'type' => 'visa',
			'card' => '4008 0687 0641 8697 ',
			'cvv2' => 232,
			'expiry' => array(
			    'M' => '2',
		        'Y' => '2001',
		    ),
			'first_name' => 'Joe',
			'last_name' => 'Shopper'
		);
		$result = $this->Paypal->formatStoreCreditCardArgs($creditCard);
	}
	
/**
 * testStoreFormatCreditCardArgsInvalidType
 *
 * @return void
 * @author Rob Mcvey
 * @expectedException PaypalException
 * @expectedExceptionMessage Invalid card type
 **/
	public function testStoreFormatCreditCardArgsInvalidType() {
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar'
		));
		$creditCard = array(
			'payer_id' => 186,
			'type' => 'snarf',
			'card' => '4008 0687 0641 8697 ',
			'cvv2' => 232,
			'expiry' => array(
			    'M' => '2',
		        'Y' => '2016',
		    ),
			'first_name' => 'Joe',
			'last_name' => 'Shopper'
		);
		$result = $this->Paypal->formatStoreCreditCardArgs($creditCard);
	}
	
/**
 * testStoreCreditCardUrl
 *
 * @return void
 * @author Rob Mcvey
 **/
	public function testStoreCreditCardUrl() {
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar'
		));
		$endpoint = $this->Paypal->storeCreditCardUrl();
		$this->assertEqual('https://api.sandbox.paypal.com/v1/vault/credit-card', $endpoint);
	}
	
/**
 * testGetOAuthAccessToken
 *
 * @return void
 * @author Rob Mcvey
 **/
	public function testGetOAuthAccessToken() {
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar',
			'oAuthClientId' => '1ROu-BDs2U35hVZkITi2WzX98cbNahkSeoxsuvfmrXNTz-gA5EGslxk0fAFU',
			'oAuthSecret' => 'FO6RKRBsI2co80qlfPHYQ14G-U9Smbz4H8fUIY9Mf846URRNdYNNXQ2wghxC',
		));
		// Mock the HttpSocket classs
		$this->Paypal->HttpSocket = $this->getMock('HttpSocket');
		// Mock the HttpSocket configAuth method
		$this->Paypal->HttpSocket->expects($this->once())
			->method('configAuth')
			->with('Basic', '1ROu-BDs2U35hVZkITi2WzX98cbNahkSeoxsuvfmrXNTz-gA5EGslxk0fAFU', 'FO6RKRBsI2co80qlfPHYQ14G-U9Smbz4H8fUIY9Mf846URRNdYNNXQ2wghxC');
		
		$HttpSocketResponse = new stdClass();
		$HttpSocketResponse->body = '{"scope":"openid https:\/\/api.paypal.com\/v1\/payments\/.* https:\/\/api.paypal.com\/v1\/developer\/.* https:\/\/api.paypal.com\/v1\/vault\/credit-card\/.* https:\/\/api.paypal.com\/v1\/vault\/credit-card","access_token":"V0jzza4UReFGZh3pf-APN3MwhvnATeG0kaQScgzIH3A","token_type":"Bearer","app_id":"APP-80W284485P519543T","expires_in":28800}';
		$HttpSocketResponse->code = 200;
		
		// Mock the HttpSocket post method
		$this->Paypal->HttpSocket->expects($this->once())
			->method('post')
			->with('https://api.sandbox.paypal.com/v1/oauth2/token', array(
				"grant_type" => "client_credentials"
			))
			->will($this->returnValue($HttpSocketResponse));
			
		$result = $this->Paypal->getOAuthAccessToken();
		// Expected response
		$expected = array(
			'scope' => 'openid https://api.paypal.com/v1/payments/.* https://api.paypal.com/v1/developer/.* https://api.paypal.com/v1/vault/credit-card/.* https://api.paypal.com/v1/vault/credit-card',
			'access_token' => 'V0jzza4UReFGZh3pf-APN3MwhvnATeG0kaQScgzIH3A',
			'token_type' => 'Bearer',
			'app_id' => 'APP-80W284485P519543T',
			'expires_in' => 28800
		);
		$this->assertEqual($expected, $result);
	}
	
/**
 * testGetOAuthAccessTokenHandlesError
 *
 * @return void
 * @author Rob Mcvey
 * @expectedException PaypalException
 * @expectedExceptionMessage There was an problem communicating with the payment gateway
 **/
	public function testGetOAuthAccessTokenHandlesError() {
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar',
			'oAuthClientId' => '1ROu-BDs2U35hVZkITi2WzX98cbNahkSeoxsuvfmrXNTz-gA5EGslxk0fAFU',
			'oAuthSecret' => 'FO6RKRBsI2co80qlfPHYQ14G-U9Smbz4H8fUIY9Mf846URRNdYNNXQ2wghxC',
		));
		// Mock the HttpSocket classs
		$this->Paypal->HttpSocket = $this->getMock('HttpSocket');
		// Mock the HttpSocket configAuth method
		$this->Paypal->HttpSocket->expects($this->once())
			->method('configAuth')
			->with('Basic', '1ROu-BDs2U35hVZkITi2WzX98cbNahkSeoxsuvfmrXNTz-gA5EGslxk0fAFU', 'FO6RKRBsI2co80qlfPHYQ14G-U9Smbz4H8fUIY9Mf846URRNdYNNXQ2wghxC');
		// Mock the response object
		$HttpSocketResponse = new stdClass();
		$HttpSocketResponse->body = '{"scope":"openid https:\/\/api.paypal.com\/v1\/payments\/.* https:\/\/api.paypal.com\/v1\/developer\/.* https:\/\/api.paypal.com\/v1\/vault\/credit-card\/.* https:\/\/api.paypal.com\/v1\/vault\/credit-card","access_token":"V0jzza4UReFGZh3pf-APN3MwhvnATeG0kaQScgzIH3A","token_type":"Bearer","app_id":"APP-80W284485P519543T","expires_in":28800}';
		$HttpSocketResponse->code = 400;
		// Mock the HttpSocket post method
		$this->Paypal->HttpSocket->expects($this->once())
			->method('post')
			->with('https://api.sandbox.paypal.com/v1/oauth2/token', array(
				"grant_type" => "client_credentials"
			))
			->will($this->returnValue($HttpSocketResponse));
		// Get a token
		$result = $this->Paypal->getOAuthAccessToken();
		// Expected response
		$expected = array(
			'scope' => 'openid https://api.paypal.com/v1/payments/.* https://api.paypal.com/v1/developer/.* https://api.paypal.com/v1/vault/credit-card/.* https://api.paypal.com/v1/vault/credit-card',
			'access_token' => 'V0jzza4UReFGZh3pf-APN3MwhvnATeG0kaQScgzIH3A',
			'token_type' => 'Bearer',
			'app_id' => 'APP-80W284485P519543T',
			'expires_in' => 28800
		);
		$this->assertEqual($expected, $result);
	}	

/**
 * testGetOAuthAccessTokenMissingCredentials
 *
 * @return void
 * @author Rob Mcvey
 * @expectedException PaypalException
 * @expectedExceptionMessage Missing client id/secret
 **/
	public function testGetOAuthAccessTokenMissingCredentials() {
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar',
			'oAuthSecret' => 'FO6RKRBsI2co80qlfPHYQ14G-U9Smbz4H8fUIY9Mf846URRNdYNNXQ2wghxC',
		));
		// Mock the HttpSocket classs
		$this->Paypal->HttpSocket = $this->getMock('HttpSocket');
		// Mock the HttpSocket configAuth method
		$this->Paypal->HttpSocket->expects($this->never())
			->method('configAuth');
		$this->Paypal->getOAuthAccessToken();
	}	

/**
 * testOAuthTokenUrl
 *
 * @return void
 * @author Rob Mcvey
 **/
	public function testOAuthTokenUrl() {
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar'
		));
		$endpoint = $this->Paypal->oAuthTokenUrl();
		$this->assertEqual('https://api.sandbox.paypal.com/v1/oauth2/token', $endpoint);
	}

/**
 * testChargeStoredCardUrl
 *
 * @return void
 * @author Rob Mcvey
 **/
	public function testChargeStoredCardUrl() {
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar'
		));
		$endpoint = $this->Paypal->chargeStoredCardUrl();
		$this->assertEqual('https://api.sandbox.paypal.com/v1/payments/payment', $endpoint);
	}

/**
 * testChargeStoredCardUrlLive
 *
 * @return void
 * @author Rob Mcvey
 **/
	public function testChargeStoredCardUrlLive() {
		$this->Paypal = new Paypal(array(
			'sandboxMode' => false,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar'
		));
		$endpoint = $this->Paypal->chargeStoredCardUrl();
		$this->assertEqual('https://api.paypal.com/v1/payments/payment', $endpoint);
	}	
	
/**
 * testChargeStoredCard
 *
 * @return void
 * @author Rob Mcvey
 **/
	public function testChargeStoredCard() {
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar',
			'oAuthClientId' => 'ASPu1-BDs2U35hVZkITi2WzX98cbNahkSeoxsuvfmrXNTz-gA5EGslxk0fAFU',
			'oAuthSecret' => 'EO6RKHYQ14G-U9Smbz4H8fUIY9Mf846URRNdYNNXQ2wghxC',
		));
		
		$expectedTokenResponse = array(
			'scope' => 'openid https://api.paypal.com/v1/payments/.* https://api.paypal.com/v1/vault/credit-card/.* https://api.paypal.com/v1/vault/credit-card',
			'access_token' => 'Zr-NlxdL6u1kQNUNnG7SYUjsqFIk2OCI7wxGStzPEO8',
			'token_type' => 'Bearer',
			'app_id' => 'APP-7KR335967N254033H',
			'expires_in' => 28800
		);
		
		// Mock the HttpSocket classs
		$this->Paypal->HttpSocket = $this->getMock('HttpSocket');
		
		// Mock the HttpSocket configAuth method
		$this->Paypal->HttpSocket->expects($this->once())
			->method('configAuth')
			->with('Basic', 'ASPu1-BDs2U35hVZkITi2WzX98cbNahkSeoxsuvfmrXNTz-gA5EGslxk0fAFU', 'EO6RKHYQ14G-U9Smbz4H8fUIY9Mf846URRNdYNNXQ2wghxC');
		
		// Mock the HttpSocket post method
		$expectedTokenResponse = json_encode($expectedTokenResponse);
		
		$HttpSocketResponse = new stdClass();
		$HttpSocketResponse->body = $expectedTokenResponse;
		$HttpSocketResponse->code = 200;
		
		$this->Paypal->HttpSocket->expects($this->once())
			->method('post')
			->with('https://api.sandbox.paypal.com/v1/oauth2/token', array(
				"grant_type" => "client_credentials"
			))
			->will($this->returnValue($HttpSocketResponse));
		
		// Mock the HttpSocket reset method
		$this->Paypal->HttpSocket->expects($this->once())
			->method('reset');
			
		// Expected JSON to be posted to paypal
		$expectedJSON = '{"intent":"sale","redirect_urls":{"return_url":"http:\/\/copify.com\/return","cancel_url":"http:\/\/copify.com\/cancel"},"payer":{"payment_method":"credit_card","funding_instruments":[{"credit_card_token":{"credit_card_id":"CARD-39N78604CK9041431KM2DDC2","payer_id":"186"}}]},"transactions":[{"amount":{"total":"0.60","currency":"GBP","details":{"subtotal":"0.50","tax":"0.10","shipping":"0.00"}},"description":"This is test payment to copify"}]}';	

		// Our transaction, we'll build this from the users data
		$cardPayment = array(
			'intent' => 'sale',
			"redirect_urls" => array(
				"return_url" => "http://copify.com/return",
				"cancel_url" => "http://copify.com/cancel"
			),
			'payer' => array(
				'payment_method' => 'credit_card',
				'funding_instruments' => array(
					0 => array(
						'credit_card_token' => array(
							'credit_card_id' => 'CARD-39N78604CK9041431KM2DDC2',
							'payer_id' => '186'
						)
					)
				)
			),
			'transactions' => array(
				0 => array(
					'amount' => array(
						'total' => '0.60',
						'currency' => 'GBP',
						"details" => array(
							"subtotal" => "0.50",
							"tax" => "0.10",
							"shipping" => "0.00"
				        )
					),
					'description' => 'This is test payment to copify'
				)
			)
		);
		
		// The request we expect to send to paypal
		$mockRequest = array(
			'method' => 'POST',
			'header' => array(
				'Content-Type' => 'application/json',
				'Authorization' => 'Bearer Zr-NlxdL6u1kQNUNnG7SYUjsqFIk2OCI7wxGStzPEO8'
			),
			'uri' => 'https://api.sandbox.paypal.com/v1/payments/payment',
			'body' => $expectedJSON
		);

		// What we expect to het back from paypal (json encoded)
		$expectedResponse = array(
			'id' => 'PAY-8MD93335G6649645MKM2DDUY',
			'create_time' => '2014-03-27T14:12:35Z',
			'update_time' => '2014-03-27T14:12:38Z',
			'state' => 'approved',
			'intent' => 'sale',
			'payer' => array(
				'payment_method' => 'credit_card',
				'funding_instruments' => array(
					0 => array(
						'credit_card_token' => array(
							'credit_card_id' => 'CARD-39N78604CK9041431KM2DDC2',
							'payer_id' => '186',
							'last4' => '0515',
							'type' => 'visa',
							'expire_month' => '8',
							'expire_year' => '2016'
						)
					)
				)
			),
			'transactions' => array(
				0 => array(
					'amount' => array(
						'total' => '0.60',
						'currency' => 'GBP',
						'details' => array(
							'subtotal' => '0.50',
							'tax' => '0.10'
						)
					),
					'description' => 'This is test payment to copify',
					'related_resources' => array(
						0 => array(
							'sale' => array(
								'id' => '0XP264374T4869302',
								'create_time' => '2014-03-27T14:12:35Z',
								'update_time' => '2014-03-27T14:12:38Z',
								'state' => 'completed',
								'amount' => array(
									'total' => '0.60',
									'currency' => 'GBP'
								),
								'parent_payment' => 'PAY-8MD93335G6649645MKM2DDUY',
								'links' => array(
									0 => array(
										'href' => 'https://api.paypal.com/v1/payments/sale/0XP264374T4869302',
										'rel' => 'self',
										'method' => 'GET'
									),
									1 => array(
										'href' => 'https://api.paypal.com/v1/payments/sale/0XP264374T4869302/refund',
										'rel' => 'refund',
										'method' => 'POST'
									),
									2 => array(
										'href' => 'https://api.paypal.com/v1/payments/payment/PAY-8MD93335G6649645MKM2DDUY',
										'rel' => 'parent_payment',
										'method' => 'GET'
									)
								)
							)
						)
					)
				)
			),
			'links' => array(
				0 => array(
					'href' => 'https://api.paypal.com/v1/payments/payment/PAY-8MD93335G6649645MKM2DDUY',
					'rel' => 'self',
					'method' => 'GET'
				)
			)
		);
		
		$HttpSocketResponse = new stdClass();
		$HttpSocketResponse->body = json_encode($expectedResponse);
		$HttpSocketResponse->code = 200;
		
		$this->Paypal->HttpSocket->expects($this->once())
			->method('request')
			->with($this->equalTo($mockRequest))
			->will($this->returnValue($HttpSocketResponse));
		
		$result = $this->Paypal->chargeStoredCard($cardPayment);
		
		$this->assertEqual($expectedResponse, $result);
	}
	
/**
 * testChargeStoredCardHandleError
 *
 * @return void
 * @author Rob Mcvey
 * @expectedException PaypalException
 * @expectedExceptionMessage An internal service error has occurred
 **/
	public function testChargeStoredCardHandleError() {
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar',
			'oAuthClientId' => 'ASPu1-BDs2U35hVZkITi2WzX98cbNahkSeoxsuvfmrXNTz-gA5EGslxk0fAFU',
			'oAuthSecret' => 'EO6RKHYQ14G-U9Smbz4H8fUIY9Mf846URRNdYNNXQ2wghxC',
		));
		
		$expectedTokenResponse = array(
			'scope' => 'openid https://api.paypal.com/v1/payments/.* https://api.paypal.com/v1/vault/credit-card/.* https://api.paypal.com/v1/vault/credit-card',
			'access_token' => 'Zr-NlxdL6u1kQNUNnG7SYUjsqFIk2OCI7wxGStzPEO8',
			'token_type' => 'Bearer',
			'app_id' => 'APP-7KR335967N254033H',
			'expires_in' => 28800
		);
		
		// Mock the HttpSocket classs
		$this->Paypal->HttpSocket = $this->getMock('HttpSocket');
		
		// Mock the HttpSocket configAuth method
		$this->Paypal->HttpSocket->expects($this->once())
			->method('configAuth')
			->with('Basic', 'ASPu1-BDs2U35hVZkITi2WzX98cbNahkSeoxsuvfmrXNTz-gA5EGslxk0fAFU', 'EO6RKHYQ14G-U9Smbz4H8fUIY9Mf846URRNdYNNXQ2wghxC');
		
		// Mock the HttpSocket post method
		$expectedTokenResponse = json_encode($expectedTokenResponse);
		
		$HttpSocketResponse = new stdClass();
		$HttpSocketResponse->body = $expectedTokenResponse;
		$HttpSocketResponse->code = 200;
		
		$this->Paypal->HttpSocket->expects($this->once())
			->method('post')
			->with('https://api.sandbox.paypal.com/v1/oauth2/token', array(
				"grant_type" => "client_credentials"
			))
			->will($this->returnValue($HttpSocketResponse));
		
		// Mock the HttpSocket reset method
		$this->Paypal->HttpSocket->expects($this->once())
			->method('reset');
			
		// Expected JSON to be posted to paypal
		$expectedJSON = '{"intent":"sale","redirect_urls":{"return_url":"http:\/\/copify.com\/return","cancel_url":"http:\/\/copify.com\/cancel"},"payer":{"payment_method":"credit_card","funding_instruments":[{"credit_card_token":{"credit_card_id":"CARD-39N78604CK9041431KM2DDC2","payer_id":"186"}}]},"transactions":[{"amount":{"total":"0.60","currency":"GBP","details":{"subtotal":"0.50","tax":"0.10","shipping":"0.00"}},"description":"This is test payment to copify"}]}';	

		// Our transaction, we'll build this from the users data
		$cardPayment = array(
			'intent' => 'sale',
			"redirect_urls" => array(
				"return_url" => "http://copify.com/return",
				"cancel_url" => "http://copify.com/cancel"
			),
			'payer' => array(
				'payment_method' => 'credit_card',
				'funding_instruments' => array(
					0 => array(
						'credit_card_token' => array(
							'credit_card_id' => 'CARD-39N78604CK9041431KM2DDC2',
							'payer_id' => '186'
						)
					)
				)
			),
			'transactions' => array(
				0 => array(
					'amount' => array(
						'total' => '0.60',
						'currency' => 'GBP',
						"details" => array(
							"subtotal" => "0.50",
							"tax" => "0.10",
							"shipping" => "0.00"
				        )
					),
					'description' => 'This is test payment to copify'
				)
			)
		);
		
		// The request we expect to send to paypal
		$mockRequest = array(
			'method' => 'POST',
			'header' => array(
				'Content-Type' => 'application/json',
				'Authorization' => 'Bearer Zr-NlxdL6u1kQNUNnG7SYUjsqFIk2OCI7wxGStzPEO8'
			),
			'uri' => 'https://api.sandbox.paypal.com/v1/payments/payment',
			'body' => $expectedJSON
		);
		
		$response = array(
			'name' => 'INTERNAL_SERVICE_ERROR',
			'message' => 'An internal service error has occurred',
			'information_link' => 'https://developer.paypal.com/webapps/developer/docs/api/#INTERNAL_SERVICE_ERROR',
			'debug_id' => '1fe3ea968af59'
		);
		
		$HttpSocketResponse = new stdClass();
		$HttpSocketResponse->body = json_encode($response);
		$HttpSocketResponse->code = 500;
		
		$this->Paypal->HttpSocket->expects($this->once())
			->method('request')
			->with($this->equalTo($mockRequest))
			->will($this->returnValue($HttpSocketResponse));
		
		$result = $this->Paypal->chargeStoredCard($cardPayment);
	}
	
/**
 * testChargeStoredCardHandleErrorNoMessage
 *
 * @return void
 * @author Rob Mcvey
 * @expectedException PaypalException
 * @expectedExceptionMessage There was an problem communicating with the payment gateway
 **/
	public function testChargeStoredCardHandleErrorNoMessage() {
		$this->Paypal = new Paypal(array(
			'sandboxMode' => true,
			'nvpUsername' => 'foo',
			'nvpPassword' => 'bar',
			'nvpSignature' => 'foobar',
			'oAuthClientId' => '1ROu-BDs2U35hVZkITi2WzX98cbNahkSeoxsuvfmrXNTz-gA5EGslxk0fAFU',
			'oAuthSecret' => 'EO6RKHYQ14G-U9Smbz4H8fUIY9Mf846URRNdYNNXQ2wghxC',
		));

		$expectedTokenResponse = array(
			'scope' => 'openid https://api.paypal.com/v1/payments/.* https://api.paypal.com/v1/vault/credit-card/.* https://api.paypal.com/v1/vault/credit-card',
			'access_token' => 'Zr-NlxdL6u1kQNUNnG7SYUjsqFIk2OCI7wxGStzPEO8',
			'token_type' => 'Bearer',
			'app_id' => 'APP-7KR335967N254033H',
			'expires_in' => 28800
		);

		// Mock the HttpSocket classs
		$this->Paypal->HttpSocket = $this->getMock('HttpSocket');

		// Mock the HttpSocket configAuth method
		$this->Paypal->HttpSocket->expects($this->once())
			->method('configAuth')
			->with('Basic', '1ROu-BDs2U35hVZkITi2WzX98cbNahkSeoxsuvfmrXNTz-gA5EGslxk0fAFU', 'EO6RKHYQ14G-U9Smbz4H8fUIY9Mf846URRNdYNNXQ2wghxC');

		// Mock the HttpSocket post method
		$expectedTokenResponse = json_encode($expectedTokenResponse);

		$HttpSocketResponse = new stdClass();
		$HttpSocketResponse->body = $expectedTokenResponse;
		$HttpSocketResponse->code = 200;

		$this->Paypal->HttpSocket->expects($this->once())
			->method('post')
			->with('https://api.sandbox.paypal.com/v1/oauth2/token', array(
				"grant_type" => "client_credentials"
			))
			->will($this->returnValue($HttpSocketResponse));

		// Mock the HttpSocket reset method
		$this->Paypal->HttpSocket->expects($this->once())
			->method('reset');

		// Expected JSON to be posted to paypal
		$expectedJSON = '{"intent":"sale","redirect_urls":{"return_url":"http:\/\/copify.com\/return","cancel_url":"http:\/\/copify.com\/cancel"},"payer":{"payment_method":"credit_card","funding_instruments":[{"credit_card_token":{"credit_card_id":"CARD-39N78604CK9041431KM2DDC2","payer_id":"186"}}]},"transactions":[{"amount":{"total":"0.60","currency":"GBP","details":{"subtotal":"0.50","tax":"0.10","shipping":"0.00"}},"description":"This is test payment to copify"}]}';	

		// Our transaction, we'll build this from the users data
		$cardPayment = array(
			'intent' => 'sale',
			"redirect_urls" => array(
				"return_url" => "http://copify.com/return",
				"cancel_url" => "http://copify.com/cancel"
			),
			'payer' => array(
				'payment_method' => 'credit_card',
				'funding_instruments' => array(
					0 => array(
						'credit_card_token' => array(
							'credit_card_id' => 'CARD-39N78604CK9041431KM2DDC2',
							'payer_id' => '186'
						)
					)
				)
			),
			'transactions' => array(
				0 => array(
					'amount' => array(
						'total' => '0.60',
						'currency' => 'GBP',
						"details" => array(
							"subtotal" => "0.50",
							"tax" => "0.10",
							"shipping" => "0.00"
				        )
					),
					'description' => 'This is test payment to copify'
				)
			)
		);

		// The request we expect to send to paypal
		$mockRequest = array(
			'method' => 'POST',
			'header' => array(
				'Content-Type' => 'application/json',
				'Authorization' => 'Bearer Zr-NlxdL6u1kQNUNnG7SYUjsqFIk2OCI7wxGStzPEO8'
			),
			'uri' => 'https://api.sandbox.paypal.com/v1/payments/payment',
			'body' => $expectedJSON
		);

		$response = array(
			'name' => 'NOT_FOUND',
			//'message' => 'An internal service error has occurred',
			'information_link' => 'https://developer.paypal.com/webapps/developer/docs/api/#INTERNAL_SERVICE_ERROR',
			'debug_id' => '1fe3ea968af59'
		);

		$HttpSocketResponse = new stdClass();
		$HttpSocketResponse->body = json_encode($response);
		$HttpSocketResponse->code = 500;

		$this->Paypal->HttpSocket->expects($this->once())
			->method('request')
			->with($this->equalTo($mockRequest))
			->will($this->returnValue($HttpSocketResponse));

		$result = $this->Paypal->chargeStoredCard($cardPayment);
	}
	
}
