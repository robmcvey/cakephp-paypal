<?php
/**
 * Paypal.php
 * Created by Rob Mcvey on 2013-07-04.
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice
 * 
 * @copyright Rob Mcvey on 2013-07-04.
 * @link www.copify.com
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::uses('CakeRequest', 'Network');
App::uses('HttpSocket', 'Network/Http');

/**
 * Paypal Exception classes
 */
class PaypalException extends CakeException {
}

/**
 * PaypaliRedirect Exception classes
 */
class PaypalRedirectException extends CakeException {
}

/**
 * Paypal class
 */
class Paypal {

/**
 * Target version for "Classic" Paypal API
 */
	protected $_paypalClassicApiVersion = '104.0';

/**
 * Live or sandbox
 */
	protected $_sandboxMode = true;

/**
 * API credentials - nvp username
 */
	protected $_nvpUsername = null;

/**
 * API credentials - nvp password
 */
	protected $_nvpPassword = null;

/**
 * API credentials - nvp signature
 */
	protected $_nvpSignature = null;

/**
 * API credentials - nvp token
 */
	protected $_nvpToken = null;

/**
 * API credentials - Adaptive App ID
 */
	protected $_adaptiveAppID = null;

/**
 * API credentials - Adaptive User ID
 */
	protected $_adaptiveUserID = null;

/**
 * API credentials - Application id
 */
	protected $_oAuthAppId = null;

/**
 * API credentials - oAuth client id
 */
	protected $_oAuthClientId = null;

/**
 * API credentials - oAuth secret
 */
	protected $_oAuthSecret = null;

/**
 * API credentials - oAuth access token
 */
	protected $_oAuthAccessToken = null;

/**
 * API credentials - oAuth token type
 */
	protected $_oAuthTokenType = null;

/**
 * Live endpoint for REST API
 */
	protected $_liveRestEndpoint = 'https://api.paypal.com';

/**
 * Sandbox endpoint for REST API
 */
	protected $_sandboxRestEndpoint = 'https://api.sandbox.paypal.com';

/**
 * Live endpoint for Classic API
 */
	protected $_liveClassicEndpoint = 'https://api-3t.paypal.com/nvp';

/**
 * Sandbox endpoint for Classic API
 */
	protected $_sandboxClassicEndpoint = 'https://api-3t.sandbox.paypal.com/nvp';

/**
 * Live endpoint for Adaptive Accounts API
 */
	protected $_liveAdaptiveAccountsEndpoint = 'https://svcs.paypal.com/AdaptiveAccounts/';

/**
 * Sandbox endpoint for Adaptive Accounts API
 */
	protected $_sandboxAdaptiveAccountsEndpoint = 'https://svcs.sandbox.paypal.com/AdaptiveAccounts/';

/**
 * Live endpoint for Paypal web login (used in classic paypal payments)
 */
	protected $_livePaypalLoginUri = 'https://www.paypal.com/webscr';

/**
 * Sandbox endpoint for Paypal web login (used in classic paypal payments)
 */
	protected $_sandboxPaypalLoginUri = 'https://www.sandbox.paypal.com/webscr';

/**
 * More descriptive API error messages. Error code and message.
 *
 * @var array
 */
	protected $_errorMessages = array ();

/**
 * Redirect error codes
 *
 * @var array
 */
	protected $_redirectErrors = array (
		10411,
		10412,
		10422,
		10445,
		10486
	);

/**
 * HttpSocket utility class
 */
	public $HttpSocket = null;

/**
 * CakeRequest
 */
	public $CakeRequest = null;

/**
 * Constructor. Takes API credentials, and other properties to set (e.g sandbox mode)
 *
 * @param array $config An array of properties to overide (e.g the API signature)
 * @return void
 * @author Rob Mcvey
 */
	public function __construct($config = array()) {
		if (!empty($config)) {
			foreach ($config as $property => $value) {
				$property = '_' . $property;
				if (property_exists($this, $property)) {
					$this->{$property} = $value;
				}
			}
		}

		$this->_errorMessages = array (
			10411 => __('The Express Checkout transaction has expired and the transaction needs to be restarted' ),
			10412 => __('You may have made a second call for the same payment or you may have used the same invoice ID for seperate transactions.' ),
			10422 => __('Please use a different funcing source.' ),
			10445 => __('An error occured, please retry the transaction.' ),
			10486 => __('This transaction couldn\'t be completed. Redirecting to payment gateway' ),
			10500 => __('You have not agreed to the billing agreement.' ),
			10501 => __('The billing agreement is disabled or inactive.' ),
			10502 => __('The credit card used is expired.' ),
			10505 => __('The transaction was refused because the AVS response returned the value of N, and the merchant account is not able to accept such transactions.' ),
			10507 => __('The payment gateway account is restricted.' ),
			10509 => __('You must submit an IP address of the buyer with each API call.' ),
			10511 => __('The merchant selected a value for the PaymentAction field that is not supported.' ),
			10519 => __('The credit card field was blank.' ),
			10520 => __('The total amount and item amounts do not match.' ),
			10534 => __('The credit card entered is currently restricted by the payment gateway.' ),
			10536 => __('The merchant entered an invoice ID that is already associated with a transaction by the same merchant. Attempt with a new invoice ID' ),
			10537 => __('The transaction was declined by the country filter managed by the merchant.' ),
			10538 => __('The transaction was declined by the maximum amount filter managed by the merchant.' ),
			10539 => __('The transaction was declined by the payment gateway.' ),
			10541 => __('The credit card entered is currently restricted by the payment gateway.' ),
			10544 => __('The transaction was declined by the payment gateway.' ),
			10545 => __('The transaction was declined by payment gateway because of possible fraudulent activity.' ),
			10546 => __('The transaction was declined by payment gateway because of possible fraudulent activity on the IP address.' ),
			10548 => __('The merchant account attempting the transaction is not a business account.' ),
			10549 => __('The merchant account attempting the transaction is not able to process Direct Payment transactions. ' ),
			10550 => __('Access to Direct Payment was disabled for your account.' ),
			10552 => __('The merchant account attempting the transaction does not have a confirmed email address with the payment gateway.' ),
			10553 => __('The merchant attempted a transaction where the amount exceeded the upper limit for that merchant.' ),
			10554 => __('The transaction was declined because of a merchant risk filter for AVS. Specifically, the merchant has set to decline transaction when the AVS returned a no match (AVS = N).' ),
			10555 => __('The transaction was declined because of a merchant risk filter for AVS. Specifically, the merchant has set the filter to decline transactions when the AVS returns a partial match.' ),
			10556 => __('The transaction was declined because of a merchant risk filter for AVS. Specifically, the merchant has set the filter to decline transactions when the AVS is unsupported.' ),
			10747 => __('The merchant entered an IP address that was in an invalid format. The IP address must be in a format such as 123.456.123.456.' ),
			10748 => __('The merchant\'s configuration requires a CVV to be entered, but no CVV was provided with this transaction.' ),
			10751 => __('The merchant provided an address either in the United States or Canada, but the state provided is not a valid state in either country.' ),
			10752 => __('The transaction was declined by the issuing bank, not the payment gateway. The merchant should attempt another card.' ),
			10754 => __('The transaction was declined by the payment gateway.' ),
			10760 => __('The merchant\'s country of residence is not currently supported to allow Direct Payment transactions.' ),
			10761 => __('The transaction was declined because the payment gateway is currently processing a transaction by the same buyer for the same amount. Can occur when a buyer submits multiple, identical transactions in quick succession.' ),
			10762 => __('The CVV provided is invalid. The CVV is between 3-4 digits long.' ),
			10764 => __('Please try again later. Ensure you have passed the correct CVV and address info for the buyer. If creating a recurring profile, please try again by passing a init amount of 0.' ),
			12000 => __('Transaction is not compliant due to missing or invalid 3-D Secure authentication values. Check ECI, ECI3DS, CAVV, XID fields.' ),
			12001 => __('Transaction is not compliant due to missing or invalid 3-D Secure authentication values. Check ECI, ECI3DS, CAVV, XID fields.' ),
			15001 => __('The transaction was rejected by the payment gateway because of excessive failures over a short period of time for this credit card.' ),
			15002 => __('The transaction was declined by payment gateway.' ),
			15003 => __('The transaction was declined because the merchant does not have a valid commercial entity agreement on file with the payment gateway.' ),
			15004 => __('The transaction was declined because the CVV entered does not match the credit card.' ),
			15005 => __('The transaction was declined by the issuing bank, not the payment gateway. The merchant should attempt another card.' ),
			15006 => __('The transaction was declined by the issuing bank, not the payment gateway. The merchant should attempt another card.' ),
			15007 => __('The transaction was declined by the issuing bank because of an expired credit card. The merchant should attempt another card.' )
		);
	}

/**
 * GetVerifiedStatus The GetVerifiedStatus API operation is used to determine whether a user is verified or
 * unverified.
 *
 * @param string $email Email address of the buyer
 * @return array Response array
 * @throws PaypalException
 * @author Chris Green
 */
	public function getVerifiedStatus($email) {
		if (! $this->HttpSocket) {
			$this->HttpSocket = new HttpSocket();
		}

		$headers = array (
			'X-PAYPAL-SANDBOX-EMAIL-ADDRESS' => $this->_nvpUsername,
			'X-PAYPAL-SECURITY-PASSWORD' => $this->_nvpPassword,
			'X-PAYPAL-SECURITY-SIGNATURE' => $this->_nvpSignature,
			'X-PAYPAL-APPLICATION-ID' => $this->_adaptiveAppID,
			'X-PAYPAL-REQUEST-DATA-FORMAT' => 'NV',
			'X-PAYPAL-RESPONSE-DATA-FORMAT' => 'JSON',
			'X-PAYPAL-SECURITY-USERID' => $this->_adaptiveUserID
		);

		$query = array (
			'accountIdentifier.emailAddress' => $email,
			'matchCriteria' => 'NONE',
			'requestEnvelope.errorLanguage' => 'en_GB'
		);

		$endPoint = $this->getAdaptiveAccountsEndpoint();
		$endPoint .= 'GetVerifiedStatus';

		$response = $this->HttpSocket->post($endPoint, $query, array('header' => $headers));

		$parsed = json_decode($response, true);

		if (in_array($parsed['responseEnvelope']['ack'], array('Success', 'SuccessWithWarning'))) {
			return $parsed;
		} elseif ($parsed['responseEnvelope']['ack'] == 'Failure' && isset($parsed['error'])) {
			throw new PaypalException(__d('paypal', $parsed['error'][0]['message']));
		} else {
			throw new PaypalException(__d('paypal', 'An error occured while getting the status of your account.'));
		}
	}

/**
 * Returns custom error message if there are any set for the error code passed in with the parsed response. Returns
 * the long message in the response otherwise.
 *
 * @param array $parsed Parsed response
 * @return string The error message
 * @author Chris Green
 */
	public function getErrorMessage($parsed) {
		if (array_key_exists($parsed['L_ERRORCODE0'], $this->_errorMessages)) {
			return $this->_errorMessages[$parsed['L_ERRORCODE0']];
		}
		return $parsed['L_LONGMESSAGE0'];
	}

/**
 * SetExpressCheckout The SetExpressCheckout API operation initiates an Express Checkout transaction.
 *
 * @param array $order Takes an array order (See tests for supported fields).
 * @return string Will return the full URL to redirect the user to.
 * @throws PaypalException
 * @author Rob Mcvey
 */
	public function setExpressCheckout($order) {
		try {
			$nvps = $this->buildExpressCheckoutNvp($order);

			if (! $this->HttpSocket) {
				$this->HttpSocket = new HttpSocket();
			}

			$endPoint = $this->getClassicEndpoint();
			$response = $this->HttpSocket->post($endPoint, $nvps);
			$parsed = $this->parseClassicApiResponse($response);

			if (isset($parsed['TOKEN']) && $parsed['ACK'] == "Success") {
				return $this->expressCheckoutUrl($parsed['TOKEN']);
			} elseif ($parsed['ACK'] == "Failure" && isset($parsed['L_LONGMESSAGE0'])) {
				throw new PaypalException(__d('paypal', $this->getErrorMessage($parsed)));
			} else {
				throw new PaypalException(__d('paypal', 'There was an error while connecting to Paypal'));
			}
		} catch ( SocketException $e ) {
			throw new PaypalException(__d('paypal', 'There was a problem initiating the transaction, please try again.'));
		}
	}

/**
 * GetExpressCheckoutDetails Call GetExpressCheckoutDetails to obtain customer information e.g. for customer review
 * before payment
 *
 * @param string $token The token for this purchase (from Paypal, see SetExpressCheckout)
 * @return array $parsed Returns an array containing details of the transaction/buyer
 * @throws PaypalException
 * @author Rob Mcvey
 */
	public function getExpressCheckoutDetails($token) {
		try {
			$nvps = array (
				'METHOD' => 'GetExpressCheckoutDetails',
				'VERSION' => $this->_paypalClassicApiVersion,
				'TOKEN' => $token,
				'USER' => $this->_nvpUsername,
				'PWD' => $this->_nvpPassword,
				'SIGNATURE' => $this->_nvpSignature
			);

			if (! $this->HttpSocket) {
				$this->HttpSocket = new HttpSocket();
			}

			$endPoint = $this->getClassicEndpoint();
			$response = $this->HttpSocket->post( $endPoint, $nvps);
			$parsed = $this->parseClassicApiResponse( $response);

			if (isset($parsed['TOKEN']) && $parsed['ACK'] == "Success") {
				return $parsed;
			} elseif ($parsed['ACK'] == "Failure" && isset($parsed['L_LONGMESSAGE0'])) {
				throw new PaypalException(__d('paypal', $this->getErrorMessage($parsed)));
			} else {
				throw new PaypalException(__d('paypal', 'There was an error while connecting to Paypal'));
			}
		} catch ( SocketException $e ) {
			throw new PaypalException(__d('paypal', 'There was a problem getting your details, please try again.'));
		}
	}

/**
 * DoExpressCheckoutPayment The DoExpressCheckoutPayment API operation completes an Express Checkout transaction
 *
 * @param array $order Takes an array order (See tests for supported fields).
 * @param string $token The token for this purchase (from Paypal, see SetExpressCheckout)
 * @param string $payerId The ID of the Paypal user making the purchase
 * @return array Details of the completed transaction
 * @throws PaypalException
 * @throws PaypalRedirectException
 * @author Rob Mcvey
 */
	public function doExpressCheckoutPayment($order, $token, $payerId) {
		try {
			$nvps = $this->buildExpressCheckoutNvp($order);

			$keysToAdd = array (
				'METHOD' => 'DoExpressCheckoutPayment',
				'TOKEN' => $token,
				'PAYERID' => $payerId
			);

			$finalNvps = array_merge($nvps, $keysToAdd);

			if (!$this->HttpSocket) {
				$this->HttpSocket = new HttpSocket();
			}

			$endPoint = $this->getClassicEndpoint();

			$response = $this->HttpSocket->post($endPoint, $finalNvps);

			$parsed = $this->parseClassicApiResponse($response);

			if (isset($parsed['TOKEN']) && $parsed['ACK'] == "Success") {
				return $parsed;
			} elseif ($parsed['ACK'] == "Failure" && isset($parsed['L_LONGMESSAGE0'])) {
				if (in_array($parsed['L_ERRORCODE0'], $this->_redirectErrors) && isset($parsed['TOKEN'])) {
					throw new PaypalRedirectException($this->getErrorMessage($parsed));
				}
				throw new PaypalException(__d('paypal', $this->getErrorMessage($parsed)));
			} else {
				throw new PaypalException(__d('paypal', 'There was an error completing the payment'));
			}
		} catch ( SocketException $e ) {
			throw new PaypalException(__d('paypal', 'There was a problem processing the transaction, please try again.'));
		}
	}

/**
 * DoDirectPayment The DoDirectPayment API Operation enables you to process a credit card payment.
 *
 * @param array $payment Credit card and amount details to process
 * @return void
 * @throws PaypalException
 * @author Rob Mcvey
 */
	public function doDirectPayment($payment) {
		try {
			$nvps = $this->formatDoDirectPaymentNvps($payment);

			if (! $this->HttpSocket) {
				$this->HttpSocket = new HttpSocket();
			}

			$endPoint = $this->getClassicEndpoint();
			$response = $this->HttpSocket->post($endPoint, $nvps);
			$parsed = $this->parseClassicApiResponse($response);

			if (isset($parsed['ACK']) && $parsed['ACK'] == "Success") {
				return $parsed;
			} elseif ($parsed['ACK'] == "Failure" && isset($parsed['L_LONGMESSAGE0'])) {
				throw new PaypalException(__d('paypal', $this->getErrorMessage($parsed)));
			} else {
				throw new PaypalException(__d('paypal', 'There was an error processing the card payment'));
			}
		} catch ( SocketException $e ) {
			throw new PaypalException(__d('paypal', 'There was a problem processing your card, please try again.'));
		}
	}

/**
 * RefundTransaction The RefundTransaction API Operation enables you to refund a transaction that is less than 60
 * days old.
 *
 * @param array $refund original transaction information and amount to refund
 * @return void
 * @author James Mikkelson
 * @throws PaypalException
 */
	public function refundTransaction($refund) {
		try {
			$nvps = $this->formatRefundTransactionNvps($refund );

			if (! $this->HttpSocket) {
				$this->HttpSocket = new HttpSocket();
			}

			$endPoint = $this->getClassicEndpoint();

			$response = $this->HttpSocket->post($endPoint, $nvps);

			$parsed = $this->parseClassicApiResponse($response);

			if (isset($parsed['ACK']) && $parsed['ACK'] == "Success") {
				return $parsed;
			} elseif ($parsed['ACK'] == "Failure" && isset($parsed['L_LONGMESSAGE0'])) {
				throw new PaypalException(__d('paypal', $this->getErrorMessage($parsed)));
			} else {
				throw new PaypalException(__d('paypal', 'There was an error processing the the refund'));
			}
		} catch ( SocketException $e ) {
			throw new PaypalException(__d('paypal', 'A problem occurred during the refund process, please try again.'));
		}
	}

/**
 * Takes a payment array and formats in to the minimum NVPs to complete a payment
 *
 * @param array Credit card/amount information (see tests)
 * @return array Formatted array of Paypal NVPs for DoDirectPayment
 * @throws PaypalException
 * @author Rob Mcvey
 */
	public function formatDoDirectPaymentNvps($payment) {
		if (! $this->CakeRequest) {
			$this->CakeRequest = new CakeRequest();
		}

		$ipAddress = $this->CakeRequest->clientIp();
		if (empty( $ipAddress)) {
			throw new PaypalException(__d('paypal', 'Could not detect client IP address'));
		}

		if (!isset($payment['card'])) {
			throw new PaypalException(__d('paypal', 'Not a valid credit card number'));
		}
		$payment['card'] = preg_replace("/\s/", "", $payment['card']);

		if (!isset($payment['cvv'])) {
			throw new PaypalException(__d('paypal', 'You must include the 3 digit security number'));
		}
		$payment['cvv'] = preg_replace("/\s/", "", $payment['cvv']);

		if (!isset($payment['amount'])) {
			throw new PaypalException(__d('paypal', 'Must specify an "amount" to charge'));
		}

		if (!isset($payment['expiry'])) {
			throw new PaypalException(__d('paypal', 'Must specify an expiry date'));
		}

		$dateKeys = array_keys($payment['expiry']);
		sort($dateKeys);
		if ($dateKeys != array ('M', 'Y')) {
			throw new PaypalException(__d('paypal', 'Must include a M and Y in expiry date'));
		}

		$month = $payment['expiry']['M'];
		$year = $payment['expiry']['Y'];
		$expiry = sprintf('%d%d', $month, $year);

		$currency = 'GBP';
		if (isset($payment['currency'])) {
			$currency = strtoupper($payment['currency']);
		}

		$nvps = array (
			'METHOD' => 'DoDirectPayment',
			'VERSION' => $this->_paypalClassicApiVersion,
			'USER' => $this->_nvpUsername,
			'PWD' => $this->_nvpPassword,
			'SIGNATURE' => $this->_nvpSignature,
			'IPADDRESS' => $ipAddress,
			'AMT' => $payment['amount'],
			'CURRENCYCODE' => $currency,
			'RECURRING' => 'N',
			'ACCT' => $payment['card'],
			'EXPDATE' => $expiry,
			'CVV2' => $payment['cvv'],
			'FIRSTNAME' => '',
			'LASTNAME' => '',
			'STREET' => '',
			'CITY' => '',
			'STATE' => '',
			'COUNTRYCODE' => '',
			'ZIP' => ''
		);
		return $nvps;
	}

/**
 * Formats the order array to Paypal nvps
 *
 * @param array $order Takes an array order (See tests for supported fields).
 * @return array Formatted array of Paypal NVPs for setExpressCheckout
 * @throws PaypalException
 * @author Rob Mcvey
 */
	public function buildExpressCheckoutNvp($order) {
		if (empty($order) || !is_array($order)) {
			throw new PaypalException(__d('paypal', 'You must pass a valid order array'));
		}

		if (!isset($order['return']) || !isset($order['cancel'])) {
			throw new PaypalException(__d('paypal', 'Valid "return" and "cancel" urls must be provided'));
		}

		if (!isset($order['currency'])) {
			throw new PaypalException(__d( 'paypal', 'You must provide a currency code'));
		}
		$nvps = array (
			'METHOD' => 'SetExpressCheckout',
			'VERSION' => $this->_paypalClassicApiVersion,
			'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
			'USER' => $this->_nvpUsername,
			'PWD' => $this->_nvpPassword,
			'SIGNATURE' => $this->_nvpSignature,
			'RETURNURL' => $order['return'],
			'CANCELURL' => $order['cancel'],
			'PAYMENTREQUEST_0_CURRENCYCODE' => $order['currency'],
			'PAYMENTREQUEST_0_DESC' => $order['description']
		);

		if (isset($order['custom'])) {
			$nvps['PAYMENTREQUEST_0_CUSTOM'] = $order['custom'];
		}

		if (isset($order['notifyUrl'])) {
			$nvps['PAYMENTREQUEST_0_NOTIFYURL'] = $order['notifyUrl'];
		}

		if (isset($order['items']) && is_array($order['items'])) {
			$itemsSubtotal = array_sum(Hash::extract($order, 'items.{n}.subtotal'));
			$itemsShipping = array_sum(Hash::extract($order, 'items.{n}.shipping'));
			$itemsTax = array_sum(Hash::extract($order, 'items.{n}.tax'));
			$itemsTotal = array_sum(array(
				$itemsSubtotal,
				$itemsTax,
				$itemsShipping
			));

			$nvps['PAYMENTREQUEST_0_ITEMAMT'] = $itemsSubtotal;
			$nvps['PAYMENTREQUEST_0_SHIPPINGAMT'] = $itemsShipping;
			$nvps['PAYMENTREQUEST_0_TAXAMT'] = $itemsTax;
			$nvps['PAYMENTREQUEST_0_AMT'] = $itemsTotal;

			if (count($order['items']) > 10) {
				return $nvps;
			}

			foreach ($order['items'] as $m => $item) {
				$nvps["L_PAYMENTREQUEST_0_NAME$m"] = $item['name'];
				$nvps["L_PAYMENTREQUEST_0_DESC$m"] = $item['description'];
				$nvps["L_PAYMENTREQUEST_0_TAXAMT$m"] = $item['tax'];
				$nvps["L_PAYMENTREQUEST_0_AMT$m"] = $item['subtotal'];
				$nvps["L_PAYMENTREQUEST_0_QTY$m"] = 1;
			}
		}
		return $nvps;
	}

/**
 * Takes a refund transaction array and formats in to the minimum NVPs to process a refund
 *
 * @param array original transaction details and refund amount
 * @return array Formatted array of Paypal NVPs for RefundTransaction
 * @throws PaypalException
 * @author James Mikkelson
 */
	public function formatRefundTransactionNvps($refund) {
		if (!isset($refund['transactionId'])) {
			throw new PaypalException(__d('paypal', 'Original PayPal Transaction ID is required'));
		}

		$refund['transactionId'] = preg_replace("/\s/", "", $refund['transactionId']);

		if (!isset($refund['amount'])) {
			throw new PaypalException(__d('paypal', 'Must specify an "amount" to refund'));
		}

		if (!isset($refund['type'])) {
			throw new PaypalException(__d('paypal', 'You must specify a refund type, such as Full or Partial'));
		}

		$reference = (isset($refund['reference'])) ? $refund['reference'] : '';
		$note = (isset($refund['note'])) ? $refund['note'] : false;
		$currency = (isset($refund['currency'])) ? $refund['currency'] : 'GBP';
		$source = (isset($refund['source'])) ? $refund['source'] : 'any';

		$nvps = array (
			'METHOD' => 'RefundTransaction',
			'VERSION' => $this->_paypalClassicApiVersion,
			'USER' => $this->_nvpUsername,
			'PWD' => $this->_nvpPassword,
			'SIGNATURE' => $this->_nvpSignature,
			'TRANSACTIONID' => $refund['transactionId'],
			'INVOICEID' => $reference,
			'REFUNDTYPE' => $refund['type'],
			'CURRENCYCODE' => $currency,
			'NOTE' => $note,
			'REFUNDSOURCE' => $source
		);

		if ($refund['type'] != 'Full') {
			if (!isset($refund['amount'])) {
				throw new PaypalException(__d('paypal', 'Must specify an "amount" to refund'));
			}
			$nvps['AMT'] = $refund['amount'];
		}
		return $nvps;
	}

/**
 * Returns the Paypal Classic API endpoint
 *
 * @return string
 * @author Rob Mcvey
 */
	public function getClassicEndpoint() {
		if ($this->_sandboxMode) {
			return $this->_sandboxClassicEndpoint;
		}
		return $this->_liveClassicEndpoint;
	}

/**
 * Returns Paypal Adaptive Accounts API endpoint
 *
 * @return string
 * @author Chris Green
 */
	public function getAdaptiveAccountsEndpoint() {
		if ($this->_sandboxMode) {
			return $this->_sandboxAdaptiveAccountsEndpoint;
		}
		return $this->_liveAdaptiveAccountsEndpoint;
	}

/**
 * Returns the Paypal login URL for express checkout
 *
 * @return string
 * @author Rob Mcvey
 */
	public function getPaypalLoginUri() {
		if ($this->_sandboxMode) {
			return $this->_sandboxPaypalLoginUri;
		}
		return $this->_livePaypalLoginUri;
	}

/**
 * Build the login url for an express checkout payment, user is redirected to this
 *
 * @param string $token
 * @return string
 * @author Rob Mcvey
 */
	public function expressCheckoutUrl($token) {
		$endpoint = $this->getPaypalLoginUri();
		return "$endpoint?cmd=_express-checkout&token=$token";
	}

/**
 * Parse the body of the reponse from setExpressCheckout
 *
 * @param string A URL encoded response from Paypal
 * @return array Nicely parsed array
 * @author Rob Mcvey
 */
	public function parseClassicApiResponse($response) {
		parse_str( $response, $parsed );
		return $parsed;
	}

	// TODO Split in another class from here is RestFul API Logic

/**
 * Do payment
 *
 * @param array $payment
 * @author Israel Sotomayor
 */
	public function doPayment($payment) {
		$json = $this->__buildPaymentRequest($payment);

		return $this->__doPayment($json);
	}

/**
 * Do payment with a credit card
 *
 * @param array $payment
 * @throws PaypalException
 * @author Israel Sotomayor
 */
	public function doCreditCardPayment($payment) {
		if (!isset($payment) || empty($payment) || !is_array($payment)) {
			throw new PaypalException(__d('paypal', 'Valid payment array must be provided'));
		}

		$payment['intent'] = 'sale';
		$payment['payer']['paymentMethod'] = 'credit_card';

		$json = $this->__buildPaymentRequest($payment);

		return $this->__doPayment($json);
	}

/**
 * Do delayed payment with a credit card
 *
 * @param array $payment
 * @throws PaypalException
 * @author Israel Sotomayor
 */
	public function doAuthorizeCreditCardPayment($payment) {
		if (!isset($payment) || empty($payment) || !is_array($payment)) {
			throw new PaypalException(__d('paypal', 'Valid payment array must be provided'));
		}

		$payment['intent'] = 'authorize';
		$payment['payer']['paymentMethod'] = 'credit_card';

		$json = $this->__buildPaymentRequest($payment);

		return $this->__doPayment($json);
	}

/**
 * Store customer credit card, suing the new RestFul API
 *
 * @param array $refund original transaction information and amount to refund
 * @return array store card response
 * @throws PaypalException
 * @author Israel Sotomayor Azcuna
 */
	public function storeCreditCard($creditCard) {
		$json = $this->__buildCreditCardObject( $creditCard );

		$endPoint = $this->__storeCreditCardUrl();

		return $this->__doPostRequest($endPoint, $json);
	}

/**
 * Calling Hypermedia as the Engine of Application State (HATEOAS) for credit card
 *
 * @param string $href URL of the related HATEOAS link you can use for subsequent calls
 * @param string $method The HTTP method required for the related call
 * @param string $rel Link relation that describes how this link relates to the previous call
 * @throws PaypalException
 * @author Israel Sotomayor
 */
	public function hateoasCreditCard($href, $method, $rel = null) {
		try {
			if (! $this->HttpSocket) {
				$this->HttpSocket = new HttpSocket();
			}

			if ($method === 'GET') {
				return $this->HttpSocket->get($href);
			} elseif ($method === 'DELETE') {
				return $this->HttpSocket->delete($href);
			} else {
				throw new PaypalException(__d('paypal', 'There was an error using the credit card, method not found.'));
			}
		} catch ( SocketException $e ) {
			throw new PaypalException(__d('paypal', 'A problem occurred during the using credit card, please try again.'));
		}
	}

	private function __doPayment($json) {
		$endPoint = $this->__createPaymentUrl();

		return $this->__doPostRequest($endPoint, $json);
	}

	private function __doPostRequest($endPoint, $json) {
		try {
			$token = $this->__getOAuthAccessToken();
			$this->_oAuthAccessToken = $token['access_token'];
			$this->_oAuthTokenType = $token['token_type'];

			if (! $this->HttpSocket) {
				$this->HttpSocket = new HttpSocket();
			}

			$this->HttpSocket->configAuth('Paypal.OAuth', array(
				'access_token' => $this->_oAuthAccessToken,
				'token_type' => $this->_oAuthTokenType
			));

			$response = $this->HttpSocket->post($endPoint, $json);
			$parsed = $this->__parseRestApiResponse($response);

			if (isset($parsed['state'])) {
				if($parsed['state'] == "ok" || $parsed['state'] == "approved") {
					return $parsed;
				} else {
					throw new PaypalException(__d('paypal', 'Response state do not recognized: ' . $parsed['message']));
				}
			} elseif (isset($parsed['name']) && isset($parsed['message'])) {
				// TODO Show/format the "$parsed['details']" array error field to show a more detailed error 
				throw new PaypalException(__d('paypal', $parsed['message']));
			} else {
				throw new PaypalException(__d('paypal', 'There was an error doing a payment.'));
			}
		} catch (SocketException $e) {
			throw new PaypalException(__d('paypal', 'A problem occurred during the store credit card process, please try again.'));
		}
	}

	private function __buildPaymentRequest($payment) {
		if (!isset($payment['intent'])) {
			throw new PaypalException(__d('paypal', 'Valid intent field must be provided'));
		}

		if ($payment['intent'] != 'sale' && $payment['intent'] != 'authorize') {
			throw new PaypalException(__d('paypal', 'Intent provided is not correct (must be sale or authorize'));
		}

		if (!isset($payment['payer']) || empty($payment['payer']) || !is_array($payment['payer'])) {
			throw new PaypalException(__d('paypal', 'Valid payer array must be provided'));
		}

		if (!isset($payment['transactions']) || empty($payment['transactions']) || !is_array($payment['transactions'])) {
			throw new PaypalException(__d('paypal', 'Valid transactions array must be provided'));
		}

		$payer = $this->__buildPayerObject($payment['payer']);
		$transactions = $this->__buildTransactionsObject($payment['transactions']);

		$object = array (
			'intent' => $payment['intent'],
			'payer' => $payer,
			'transactions' => $transactions
		);

		if ($payment['payer']['paymentMethod'] == 'paypal') {
			if (!isset($payment['redirectUrls']) || empty($payment['redirectUrls']) || !is_array($payment['redirectUrls'])) {
				throw new PaypalException(__d('paypal', 'Valid redirect urls array must be provided'));
			}
			$object['redirect_urls'] = $this->__buildRedirectUrlsObject($payment['redirectUrls']);
		}
		return json_encode($object);
	}

	private function __buildPayerObject($payer) {
		if (!isset( $payer['paymentMethod']) || empty($payer['paymentMethod'])) {
			throw new PaypalException(__d('paypal', 'Payment method must be provided'));
		}

		if ($payer['paymentMethod'] != 'paypal' && $payer['paymentMethod'] != 'credit_card') {
			throw new PaypalException(__d('paypal', 'Payment method provided is not correct (must be paypal or credit_card' ));
		}

		$object = array (
			'payment_method' => $payer['paymentMethod']
		);

		if (isset($payer['fundingInstruments'] )) {
			$object['funding_instruments'] = $this->__buildFundingInstrumentsObject( $payer['fundingInstruments']);
		}

		if (isset($payer['payerInfo'] )) {
			$object['payer_info'] = $this->__buildPayerInfoObject($payer['payerInfo'] );
		}
		return $object;
	}

	private function __buildFundingInstrumentsObject($fundingInstruments) {
		$object = array();
		foreach ($fundingInstruments as $fundingInstrument) {
			$object = array_merge($object, $this->__buildFundingInstrumentObject($fundingInstrument));
		};
		return array($object);
	}

	private function __buildFundingInstrumentObject($fundingInstrument) {
		if (!is_array($fundingInstrument)) {
			throw new PaypalException(__d('paypal', 'Valid funding instrument array must be provided'));
		}

		if (!isset($fundingInstrument['creditCard']) && !isset($fundingInstrument['creditCardToken'])) {
			throw new PaypalException(__d('paypal', 'Valid credit card array must be provided if credit card token is not provided'));
		}

		$object = array ();
		if (isset( $fundingInstrument['creditCard'])) {
			$object['credit_card'] = $this->__buildCreditCardObject($fundingInstrument['creditCard']);
		} else {
			$object['credit_card_token'] = $this->__buildCreditCardTokenObject($fundingInstrument['creditCardToken']);
		}
		return $object;
	}

	private function __buildPayerInfoObject($payerInfo) {
		if (is_array( $payerInfo['$payerInfo'] )) {
			throw new PaypalException( __d( 'paypal', 'Valid payer info array must be provided' ) );
		}

		$object = array (
			'email' => null
		);
		return $object;
	}

	private function __buildRedirectUrlsObject($redirectUrls) {
		// TODO Complete, check parameters recieved, build and return the array needed https://developer.paypal.com/docs/api/#redirecturls-object
		$object = array ();
		return $object;
	}

	private function __buildTransactionsObject($transactions) {
		$object = array();
		foreach ($transactions as $transaction) {
			$object = array_merge($object, $this->__buildTransactionObject($transaction));
		};
		return array($object);
	}

	private function __buildTransactionObject($transaction) {
		if (!isset($transaction['amount']) && is_array($transaction['amount'])) {
			throw new PaypalException( __d( 'paypal', 'Transaction amout must be provided'));
		}

		$object = array ();
		if (isset($transaction['amount'])) {
			$object['amount'] = $this->__buildAmountObject($transaction['amount']);
		}

		$object['description'] = (isset($transaction['description'])) ? $transaction['description'] : '';

		if (isset($transaction['itemList']) && is_array($transaction['itemList'])) {
			$object['item_list'] = $this->__buildItemListObject($transaction['itemList']);
		}

		// TODO check and add related_resources

		return $object;
	}

	private function __buildAmountObject($amount) {
		if (!isset($amount['total'])) {
			throw new PaypalException( __d( 'paypal', 'Transaction amout must be provided'));
		}

		if (!isset($amount['currency'])) {
			throw new PaypalException( __d( 'paypal', 'Transaction amout must be provided'));
		}

		$object = array(
			'total' => $amount['total'],
			'currency' => $amount['currency']
		);

		// TODO check and add details

		return $object;
	}

	private function __buildItemListObject($items) {
		$object = array();
		foreach ($items as $item) {
			array_merge($object, $this->__buildItemObject($item));
		};

		// TODO shipping_address

		return $object;
	}

	private function __buildItemObject($item) {
		if (!isset($item['quantity'])) {
			throw new PaypalException( __d( 'paypal', 'Item quantity must be provided'));
		}

		if (!isset($item['name'])) {
			throw new PaypalException( __d( 'paypal', 'Item name must be provided'));
		}

		if (!isset($item['price'])) {
			throw new PaypalException( __d( 'paypal', 'Item price must be provided'));
		}

		if (!isset($item['currency'])) {
			throw new PaypalException( __d( 'paypal', 'Item currency must be provided'));
		} else {
			// TODO check 3-letter currency code https://developer.paypal.com/docs/integration/direct/rest_api_payment_country_currency_support/
		}

		$sku = (isset($item['sku'])) ? $item['sku'] : '';

		$object = array(
			'quantity' => $item['quantity'],
			'name' => $item['name'],
			'price' => $item['price'],
			'currency' => $item['currency'],
			'sku' => $sku,
		);
		return $object;
	}

	private function __buildCreditCardTokenObject($creditCardToken) {
		if (!isset( $creditCardToken['creditCardId'])) {
			throw new PaypalException(__d('paypal', 'Valid credit card id must be provided'));
		}

		$last4 = (isset($creditCardToken['last4'])) ? $creditCardToken['last4'] : '';
		$type = (isset($creditCardToken['type'])) ? $creditCardToken['type'] : '';
		$expireYear = (isset($creditCardToken['expire_year'])) ? $creditCardToken['expire_year'] : '';
		$expireMonth = (isset($creditCardToken['expire_month'])) ? $creditCardToken['expire_month'] : '';

		$object = array(
			'credit_card_id' => $creditCardToken['creditCardId'],
			'last4' => $last4,
			'type' => $type,
			'expire_year' => $expireYear,
			'expire_month' => $expireMonth
		);
		
		if (isset($creditCardToken['payerId'])) {
			$object['payer_id'] = $creditCardToken['payerId'];
		}
		
		return $object;
	}

	private function __buildCreditCardObject($creditCard) {
		if (empty($creditCard) || !is_array($creditCard)) {
			throw new PaypalException(__d('paypal', 'You must pass a valid credit card array'));
		}

		if (! isset($creditCard['type'] )) {
			throw new PaypalException(__d('paypal', 'Valid credit card type must be provided' ));
		}

		if (! isset($creditCard['expireMonth'] ) || !isset($creditCard['expireYear'])) {
			throw new PaypalException(__d('paypal', 'Valid expire month/year card type must be provided'));
		}

		$cvv2 = (isset($creditCard['cvv2'])) ? $creditCard['cvv2'] : '';
		$firstName = (isset($creditCard['firstName'])) ? $creditCard['firstName'] : '';
		$lastName = (isset($creditCard['lastName'])) ? $creditCard['lastName'] : '';

		$object = array (
			'number' => $creditCard['number'],
			'type' => $creditCard['type'],
			'expire_month' => $creditCard['expireMonth'],
			'expire_year' => $creditCard['expireYear'],
			'cvv2' => $creditCard['cvv2'],
			'first_name' => $creditCard['firstName'],
			'last_name' => $creditCard['lastName']
		);

		if (isset($creditCard['payerId'])) {
			$object['payer_id'] = $creditCard['payerId'];
		}

		return json_encode($object);
	}

	private function __getOAuthAccessToken() {
		// TODO Check $this->_oAuthClientId $this->_oAuthSecret is not null and launch error if its
		if (! $this->HttpSocket) {
			$this->HttpSocket = new HttpSocket();
		}
		$this->HttpSocket->configAuth('Basic', $this->_oAuthClientId, $this->_oAuthSecret);

		$endPoint = $this->__oAuthTokenUrl();

		$response = $this->HttpSocket->post( $endPoint, array(
			"grant_type" => "client_credentials"
		) );

		$parsed = $this->__parseRestApiResponse($response);

		if (isset($response->code) && $response->code == 200) {
			return $parsed;
		} else {
			throw new PaypalException(__d('paypal', 'There was an error getting the oAuth credentials'));
		}
	}

	private function __oAuthTokenUrl() {
		return $this->getRestEndpoint() . '/v1/oauth2/token';
	}

	private function __storeCreditCardUrl() {
		return $this->getRestEndpoint() . '/v1/vault/credit-card';
	}

	private function __createPaymentUrl() {
		return $this->getRestEndpoint() . '/v1/payments/payment';
	}

	private function __parseRestApiResponse($response) {
		return json_decode($response->body(), true);
	}

/**
 * Returns the Paypal REST API endpoint
 *
 * @return string
 * @author Rob Mcvey
 */
	public function getRestEndpoint() {
		if ($this->_sandboxMode) {
			return $this->_sandboxRestEndpoint;
		}
		return $this->_liveRestEndpoint;
	}
}
