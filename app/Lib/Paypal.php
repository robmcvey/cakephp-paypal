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
App::uses('HttpSocket', 'Network/Http');

/**
 * Paypal class
 */
class Paypal {

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
 * Default NVPs used when calling SetExpressCheckout.
 */	
	protected $expressCheckoutDefaultNvps = array(
		'METHOD' => 'SetExpressCheckout',
		'VERSION' => '104.0',
		'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
	);

/**
 * HttpSocket utility class
 */	
	public $HttpSocket = null;
	
/**
 * Constructor. Takes API credentials, and other properties to set (e.g sandbox mode)
 *
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
 * @return void
 * @author Rob Mcvey
 **/
	public function setExpressCheckout($order, $nvps = array()) {		
		if (!$this->HttpSocket) {
			$this->HttpSocket = new HttpSocket();
		}
		$endPoint = $this->getClassicEndpoint();
		
		// Build the NVPs
		$nvps = $this->buildExpressCheckoutNvp($order);
	
		// Make a Http request for a new token
		$response = $this->HttpSocket->post($endPoint , $nvps);
		
		// Parse the results
		$parsed = $this->pasrseSetExpressCheckoutResponse($response);
		
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
 * Build the login url for an express checkout payment
 *
 * @return string 
 * @param string $token
 * @author Rob Mcvey
 **/
	public function expressCheckoutUrl($token) {
		$endpoint = $this->getPaypalLoginUri();
		return "$endpoint?cmd=_express-checkout&token=$token";
	}

/**
 * Parse the body of the reponse from setExpressCheckout
 *
 * @return void
 * @author Rob Mcvey
 **/
	public function pasrseSetExpressCheckoutResponse($response) {
		parse_str($response , $parsed);
		return $parsed;
	}

/**
 * Formats the order array to Paypal nvps
 *
 * @return void
 * @param array
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
		$nvps = $this->getExpressCheckoutDefaultNvps();
		$nvps['USER'] = $this->nvpUsername;
		$nvps['PWD'] = $this->nvpPassword;
		$nvps['SIGNATURE'] = $this->nvpSignature;
		$nvps['RETURNURL'] = $order['return'];
		$nvps['CANCELURL'] = $order['cancel'];
		$nvps['PAYMENTREQUEST_0_CURRENCYCODE'] = $order['currency'];
		$nvps['PAYMENTREQUEST_0_DESC'] = $order['description'];
		
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
				return $this->setExpressCheckoutDefaultNvps($nvps);
			}
			foreach ($order['items'] as $m => $item) {
				$nvps["L_PAYMENTREQUEST_0_NAME$m"] = $item['name'];
				$nvps["L_PAYMENTREQUEST_0_DESC$m"] = $item['description'];
				$nvps["L_PAYMENTREQUEST_0_TAXAMT$m"] = $item['tax'];
				$nvps["L_PAYMENTREQUEST_0_AMT$m"] = $item['subtotal'];
				$nvps["L_PAYMENTREQUEST_0_QTY$m"] = 1;
			}
		}
		return $this->setExpressCheckoutDefaultNvps($nvps);
	}
	
/**
 * Add/merge NVPs to the express checkout defaults
 *
 * @return void
 * @author Rob Mcvey
 **/
	public function setExpressCheckoutDefaultNvps($nvps) {
		$this->expressCheckoutDefaultNvps = array_merge(
			$this->expressCheckoutDefaultNvps , $nvps
		);
		return $this->getExpressCheckoutDefaultNvps();
	}
	
/**
 * Returns expressCheckoutDefaultNvps
 *
 * @return void
 * @author Rob Mcvey
 **/
	public function getExpressCheckoutDefaultNvps() {
		return $this->expressCheckoutDefaultNvps;
	}
	
/**
 * DoExpressCheckoutPayment
 *
 * @return void
 * @author Rob Mcvey
 **/
	public function doExpressCheckoutPayment() {

	}

/**
 * GetExpressCheckoutDetails
 *
 * @return void
 * @author Rob Mcvey
 **/
	public function getExpressCheckoutDetails() {

	}

/**
 * Returns the live Paypal REST API endpoint
 *
 * @return void
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
 * @return void
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
 * @return void
 * @author Rob Mcvey
 **/
	public function getPaypalLoginUri() {
		if ($this->sandboxMode) {
			return $this->sandboxPaypalLoginUri;
		}
		return $this->livePaypalLoginUri;
	}	
	
}
