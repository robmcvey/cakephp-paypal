<?php
/**
 * Paypal.php
 * Created by Rob Mcvey on 2013-07-04.
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Rob Mcvey on 2013-07-04.
 * @link          www.copify.com
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::uses('CakeRequest', 'Network');
App::uses('HttpSocket', 'Network/Http');

/**
 * Paypal class
 */
class Paypal {

/**
 * Target version for "Classic" Paypal API
 */
	protected $paypalClassicApiVersion = '104.0';

/**
 * Live or sandbox
 */
	protected $sandboxMode = true;
	
/**
 * API credentials - nvp username
 */
	protected $nvpUsername = null;
	
/**
 * API credentials - nvp password
 */
	protected $nvpPassword = null;

/**
 * API credentials - nvp signature
 */
	protected $nvpSignature = null;	
	
/**
 * API credentials - nvp token
 */
	protected $nvpToken = null;	
	
/**
 * API credentials - Application id
 */
	protected $oAuthAppId = null;

/**
 * API credentials - oAuth client id
 */
	protected $oAuthClientId = null;
	
/**
 * API credentials - oAuth secret
 */
	protected $oAuthSecret = null;	
	
/**
 * API credentials - oAuth access token
 */
	protected $oAuthAccessToken = null;			
	
/**
 * Live endpoint for REST API
 */
	protected $liveRestEndpoint = 'https://api.paypal.com';

/**
 * Sandbox endpoint for REST API
 */
	protected $sandboxRestEndpoint = 'https://api.sandbox.paypal.com';
	
/**
 * Live endpoint for Classic API
 */
	protected $liveClassicEndpoint = 'https://api-3t.paypal.com/nvp';

/**
 * Sandbox endpoint for Classic API
 */
	protected $sandboxClassicEndpoint = 'https://api-3t.sandbox.paypal.com/nvp';
	
/**
 * Live endpoint for Paypal web login (used in classic paypal payments)
 */
	protected $livePaypalLoginUri = 'https://www.paypal.com/webscr';	
	
/**
 * Sandbox endpoint for Paypal web login (used in classic paypal payments)
 */
	protected $sandboxPaypalLoginUri = 'https://www.sandbox.paypal.com/webscr';	

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
 **/
	public function __construct($config = array()) {
		if (!empty($config)) {
			foreach ($config as $property => $value) {
				if (property_exists($this, $property)) {
					$this->{$property} = $value;
				}
			}
		}
	}
	
/**
 * SetExpressCheckout
 * The SetExpressCheckout API operation initiates an Express Checkout transaction.
 *
 * @param array $order Takes an array order (See tests for supported fields).
 * @return string Will return the full URL to redirect the user to.
 * @author Rob Mcvey
 **/
	public function setExpressCheckout($order) {		
		// Build the NVPs
		$nvps = $this->buildExpressCheckoutNvp($order);
	
		// HttpSocket
		if (!$this->HttpSocket) {
			$this->HttpSocket = new HttpSocket();
		}
		// Classic API endpoint
		$endPoint = $this->getClassicEndpoint();
	
		// Make a Http request for a new token
		$response = $this->HttpSocket->post($endPoint , $nvps);
		
		// Parse the results
		$parsed = $this->parseClassicApiResponse($response);
		
		// Handle the resposne
		if (isset($parsed['TOKEN']) && $parsed['ACK'] == "Success")  {
			return $this->expressCheckoutUrl($parsed['TOKEN']);
		}
		else if ($parsed['ACK'] == "Failure" && isset($parsed['L_SHORTMESSAGE0']))  {
			throw new Exception($parsed['L_SHORTMESSAGE0']);
		}
		else {
			throw new Exception(__d('paypal' , 'There was an error while connecting to Paypal'));
		}
	}
	
/**
 * GetExpressCheckoutDetails
 * Call GetExpressCheckoutDetails to obtain customer information
 * e.g. for customer review before payment
 *
 * @param string $token The token for this purchase (from Paypal, see SetExpressCheckout)
 * @return array $parsed Returns an array containing details of the transaction/buyer
 * @author Rob Mcvey
 **/
	public function getExpressCheckoutDetails($token) {
		// Build the NVPs (Named value pairs)	
		$nvps = array(
			'METHOD' => 'GetExpressCheckoutDetails' , 
			'VERSION' => $this->paypalClassicApiVersion,
			'TOKEN' => $token,
			'USER' => $this->nvpUsername,
			'PWD' => $this->nvpPassword,
			'SIGNATURE' => $this->nvpSignature,
		);
		// HttpSocket
		if (!$this->HttpSocket) {
			$this->HttpSocket = new HttpSocket();
		}
		// Classic API endpoint
		$endPoint = $this->getClassicEndpoint();

		// Make a Http request for a new token
		$response = $this->HttpSocket->post($endPoint , $nvps);
		
		// Parse the results
		$parsed = $this->parseClassicApiResponse($response);
		
		// Handle the resposne
		if (isset($parsed['TOKEN']) && $parsed['ACK'] == "Success")  {
			return $parsed;
		}
		else if ($parsed['ACK'] == "Failure" && isset($parsed['L_SHORTMESSAGE0']))  {
			throw new Exception($parsed['L_SHORTMESSAGE0']);
		}
		else {
			throw new Exception(__d('paypal' , 'There was an error while connecting to Paypal'));
		}
	}
	
/**
 * DoExpressCheckoutPayment
 * The DoExpressCheckoutPayment API operation completes an Express Checkout transaction
 *
 * @param array $order Takes an array order (See tests for supported fields).
 * @param string $token The token for this purchase (from Paypal, see SetExpressCheckout)
 * @param string $payerId The ID of the Paypal user making the purchase
 * @return array Details of the completed transaction
 * @author Rob Mcvey
 **/
	public function doExpressCheckoutPayment($order, $token , $payerId) {
		// Build the NVPs
		$nvps = $this->buildExpressCheckoutNvp($order);
		
		// When we call DoExpressCheckoutPayment, there are 3 NVPs that are different;
		$keysToAdd = array(
			'METHOD' => 'DoExpressCheckoutPayment',
			'TOKEN' => $token,
			'PAYERID' => $payerId,
		);
		
		// Add/overite, we now habe our final NVPs
		$finalNvps = array_merge($nvps, $keysToAdd);
		
		// HttpSocket
		if (!$this->HttpSocket) {
			$this->HttpSocket = new HttpSocket();
		}
		// Classic API endpoint
		$endPoint = $this->getClassicEndpoint();

		// Make a Http request for a new token
		$response = $this->HttpSocket->post($endPoint , $finalNvps);
		
		// Parse the results
		$parsed = $this->parseClassicApiResponse($response);
		
		// Handle the resposne
		if (isset($parsed['TOKEN']) && $parsed['ACK'] == "Success")  {
			return $parsed;
		}
		else if ($parsed['ACK'] == "Failure" && isset($parsed['L_SHORTMESSAGE0']))  {
			throw new Exception($parsed['L_SHORTMESSAGE0']);
		}
		else {
			throw new Exception(__d('paypal' , 'There was an error completing the payment'));
		}
	}	
	
/**
 * DoDirectPayment
 * The DoDirectPayment API Operation enables you to process a credit card payment.
 *
 * @param array $payment Credit card and amount details to process
 * @return void
 * @author Rob Mcvey
 **/
	public function doDirectPayment($payment) {
		$nvps = $this->formatDoDirectPaymentNvps($payment);
		
		// HttpSocket
		if (!$this->HttpSocket) {
			$this->HttpSocket = new HttpSocket();
		}
		// Classic API endpoint
		$endPoint = $this->getClassicEndpoint();
		
		// Make a Http request for a new token
		$response = $this->HttpSocket->post($endPoint , $nvps);
		
		// Parse the results
		$parsed = $this->parseClassicApiResponse($response);
		
		// Handle the resposne
		if (isset($parsed['ACK']) && $parsed['ACK'] == "Success")  {
			return $parsed;
		}
		else if ($parsed['ACK'] == "Failure" && isset($parsed['L_SHORTMESSAGE0']))  {
			throw new Exception($parsed['L_SHORTMESSAGE0']);
		}
		else {
			throw new Exception(__d('paypal' , 'There was an error processing the card payment'));
		}
	}	
	
/**
 * Takes a payment array anf formats in to the minimum NVPs to complete a payment
 *
 * @param array Credit card/amount information (see tests)
 * @return array Formatted array of Paypal NVPs for DoDirectPayment
 * @author Rob Mcvey
 **/
	public function formatDoDirectPaymentNvps($payment) {
		// IP Address
		if (!$this->CakeRequest) {
			$this->CakeRequest = new CakeRequest();
		}
		$ipAddress = $this->CakeRequest->clientIp();
		if (empty($ipAddress)) {
			throw new Exception(__d('paypal' , 'Could not detect client IP address'));
		}
		
		// Credit card number
		if (!isset($payment['card'])) {
			throw new Exception(__d('paypal' , 'Not a valid credit card number'));
		}
		$payment['card'] = preg_replace("/\s/" , "" , $payment['card']);
		
		// Credit card number
		if (!isset($payment['cvv'])) {
			throw new Exception(__d('paypal' , 'You must include the 3 digit security number'));
		}
		$payment['cvv'] = preg_replace("/\s/" , "" , $payment['cvv']);
		
		// Amount
		if (!isset($payment['amount'])) {
			throw new Exception(__d('paypal' , 'Must specify an "amount" to charge'));
		}
		
		// Expiry
		if (!isset($payment['expiry'])) {
			throw new Exception(__d('paypal' , 'Must specify an expiry date'));
		}
		$dateKeys = array_keys($payment['expiry']);
		sort($dateKeys); // Sort alphabetcially
		if ($dateKeys != array('M' , 'Y')) {
			throw new Exception(__d('paypal' , 'Must include a M and Y in expiry date'));
		}
		$month = $payment['expiry']['M'];
		$year = $payment['expiry']['Y'];
		$expiry = sprintf('%d%d' , $month, $year);
		
		$nvps = array(
			'METHOD' => 'DoDirectPayment',
			'VERSION' => $this->paypalClassicApiVersion,
			'USER' => $this->nvpUsername,
			'PWD' => $this->nvpPassword,
			'SIGNATURE' => $this->nvpSignature,
			'IPADDRESS' => $ipAddress, 		// Required
			'AMT' => $payment['amount'], 	// The total cost of the transaction
			'CURRENCYCODE' => 'GBP',		// A 3-character currency code
			'RECURRING' => 'N',				// Recurring flag
			'ACCT' => $payment['card'],		// Numeric characters only with no spaces
			'EXPDATE' => $expiry,			// MMYYYY
			'CVV2' => $payment['cvv'],		// xxx
			'FIRSTNAME' => '',				// Required
			'LASTNAME' => '', 				// Required
			'STREET' => '', 				// Required
			'CITY' => '', 					// Required
			'STATE' => '', 					// Required
			'COUNTRYCODE' => '',			// Required 2 single-byte characters
			'ZIP' => '', 					// Required
		);
		return $nvps;
	}

/**
 * Formats the order array to Paypal nvps
 *
 * @param array $order Takes an array order (See tests for supported fields).
 * @return array Formatted array of Paypal NVPs for setExpressCheckout
 * @author Rob Mcvey
 **/
	public function buildExpressCheckoutNvp($order) {
		if (empty($order) || !is_array($order)) {
			throw new Exception(__d('paypal' , 'You must pass a valid order array'));
		}
		if (!isset($order['return']) || !isset($order['cancel'])) {
			throw new Exception(__d('paypal' , 'Valid "return" and "cancel" urls must be provided'));
		}
		if (!isset($order['currency']))  {
			throw new Exception(__d('paypal' , 'You must provide a currency code'));
		}
		$nvps = array(
			'METHOD' => 'SetExpressCheckout',
			'VERSION' => $this->paypalClassicApiVersion,
			'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
			'USER' => $this->nvpUsername,
			'PWD' => $this->nvpPassword,
			'SIGNATURE' => $this->nvpSignature,
			'RETURNURL' => $order['return'],
			'CANCELURL' => $order['cancel'],
			'PAYMENTREQUEST_0_CURRENCYCODE' => $order['currency'],
			'PAYMENTREQUEST_0_DESC' => $order['description'],
		);		
		
		// Custom field?
		if (isset($order['custom'])) {
			$nvps['PAYMENTREQUEST_0_CUSTOM'] = $order['custom'];
		}
		
		// Add up each item and calculate totals
		if (isset($order['items']) && is_array($order['items'])) {
			$items_subtotal = array_sum(Hash::extract($order , 'items.{n}.subtotal'));
			$items_shipping = array_sum(Hash::extract($order , 'items.{n}.shipping'));
			$items_tax = array_sum(Hash::extract($order , 'items.{n}.tax'));
			$items_total = array_sum(array($items_subtotal , $items_tax, $items_shipping));
			$nvps['PAYMENTREQUEST_0_ITEMAMT'] = $items_subtotal;
			$nvps['PAYMENTREQUEST_0_SHIPPINGAMT'] = $items_shipping;
			$nvps['PAYMENTREQUEST_0_TAXAMT'] = $items_tax;
			$nvps['PAYMENTREQUEST_0_AMT'] = $items_total;
			// Paypal only supports 10 items in express checkout
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
 * Returns the Paypal REST API endpoint
 *
 * @return string
 * @author Rob Mcvey
 **/
	public function getRestEndpoint() {
		if ($this->sandboxMode) {
			return $this->sandboxRestEndpoint;
		}
		return $this->liveRestEndpoint;
	}
	
/**
 * Returns the Paypal Classic API endpoint
 *
 * @return string
 * @author Rob Mcvey
 **/
	public function getClassicEndpoint() {
		if ($this->sandboxMode) {
			return $this->sandboxClassicEndpoint;
		}
		return $this->liveClassicEndpoint;
	}
	
/**
 * Returns the Paypal login URL for express checkout
 *
 * @return string
 * @author Rob Mcvey
 **/
	public function getPaypalLoginUri() {
		if ($this->sandboxMode) {
			return $this->sandboxPaypalLoginUri;
		}
		return $this->livePaypalLoginUri;
	}	

/**
 * Build the login url for an express checkout payment, user is redirected to this
 *
 * @param string $token 
 * @return string 
 * @author Rob Mcvey
 **/
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
 **/
	public function parseClassicApiResponse($response) {
		parse_str($response , $parsed);
		return $parsed;
	}	
	
}
