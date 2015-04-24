<?php
/**
 * Paypal.php
 * Created by Rob Mcvey on 2013-07-04.
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice
 * @version 	  1.0.2
 * @copyright     Rob Mcvey on 2013-07-04.
 * @link          www.copify.com
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::uses('CakeRequest', 'Network');
App::uses('Validation', 'Utility');
App::uses('HttpSocket', 'Network/Http');

/**
 * Paypal Exception classes
 */
class PaypalException extends CakeException {}

/**
 * PaypaliRedirect Exception classes
 */
class PaypalRedirectException extends CakeException {}

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
 * API credentials - Adaptive App ID
 */
    protected $adaptiveAppID = null;

/**
 * API credentials - Adaptive User ID
 */
    protected $adaptiveUserID = null;

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
 * Live endpoint for Adaptive Accounts API
 */
    protected $liveAdaptiveAccountsEndpoint = 'https://svcs.paypal.com/AdaptiveAccounts/';

/**
 * Sandbox endpoint for Adaptive Accounts API
 */
    protected $sandboxAdaptiveAccountsEndpoint = 'https://svcs.sandbox.paypal.com/AdaptiveAccounts/';

/**
 * Live endpoint for Paypal web login (used in classic paypal payments)
 */
	protected $livePaypalLoginUri = 'https://www.paypal.com/webscr';

/**
 * Sandbox endpoint for Paypal web login (used in classic paypal payments)
 */
	protected $sandboxPaypalLoginUri = 'https://www.sandbox.paypal.com/webscr';

/**
 * More descriptive API error messages. Error code and message.
 *
 * @var array
 */
	protected $errorMessages = array();

/**
 * Redirect error codes
 *
 * @var array
 */
	protected $redirectErrors = array(10411, 10412, 10422, 10445, 10486);

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
		// Sets errorMessages instance var with localization
		$this->errorMessages = array(
			10411 => __('The Express Checkout transaction has expired and the transaction needs to be restarted'),
			10412 => __('You may have made a second call for the same payment or you may have used the same invoice ID for seperate transactions.'),
			10422 => __('Please use a different funcing source.'),
			10445 => __('An error occured, please retry the transaction.'),
			10486 => __('This transaction couldn\'t be completed. Redirecting to payment gateway'),
			10500 => __('You have not agreed to the billing agreement.'),
			10501 => __('The billing agreement is disabled or inactive.'),
			10502 => __('The credit card used is expired.'),
			10505 => __('The transaction was refused because the AVS response returned the value of N, and the merchant account is not able to accept such transactions.'),
			10507 => __('The payment gateway account is restricted.'),
			10509 => __('You must submit an IP address of the buyer with each API call.'),
			10511 => __('The merchant selected a value for the PaymentAction field that is not supported.'),
			10519 => __('The credit card field was blank.'),
			10520 => __('The total amount and item amounts do not match.'),
			10534 => __('The credit card entered is currently restricted by the payment gateway.'),
			10536 => __('The merchant entered an invoice ID that is already associated with a transaction by the same merchant. Attempt with a new invoice ID'),
			10537 => __('The transaction was declined by the country filter managed by the merchant.'),
			10538 => __('The transaction was declined by the maximum amount filter managed by the merchant.'),
			10539 => __('The transaction was declined by the payment gateway.'),
			10541 => __('The credit card entered is currently restricted by the payment gateway.'),
			10544 => __('The transaction was declined by the payment gateway.'),
			10545 => __('The transaction was declined by payment gateway because of possible fraudulent activity.'),
			10546 => __('The transaction was declined by payment gateway because of possible fraudulent activity on the IP address.'),
			10548 => __('The merchant account attempting the transaction is not a business account.'),
			10549 => __('The merchant account attempting the transaction is not able to process Direct Payment transactions. '),
			10550 => __('Access to Direct Payment was disabled for your account.'),
			10552 => __('The merchant account attempting the transaction does not have a confirmed email address with the payment gateway.'),
			10553 => __('The merchant attempted a transaction where the amount exceeded the upper limit for that merchant.'),
			10554 => __('The transaction was declined because of a merchant risk filter for AVS. Specifically, the merchant has set to decline transaction when the AVS returned a no match (AVS = N).'),
			10555 => __('The transaction was declined because of a merchant risk filter for AVS. Specifically, the merchant has set the filter to decline transactions when the AVS returns a partial match.'),
			10556 => __('The transaction was declined because of a merchant risk filter for AVS. Specifically, the merchant has set the filter to decline transactions when the AVS is unsupported.'),
			10747 => __('The merchant entered an IP address that was in an invalid format. The IP address must be in a format such as 123.456.123.456.'),
			10748 => __('The merchant\'s configuration requires a CVV to be entered, but no CVV was provided with this transaction.'),
			10751 => __('The merchant provided an address either in the United States or Canada, but the state provided is not a valid state in either country.'),
			10752 => __('The transaction was declined by the issuing bank, not the payment gateway. The merchant should attempt another card.'),
			10754 => __('The transaction was declined by the payment gateway.'),
			10760 => __('The merchant\'s country of residence is not currently supported to allow Direct Payment transactions.'),
			10761 => __('The transaction was declined because the payment gateway is currently processing a transaction by the same buyer for the same amount. Can occur when a buyer submits multiple, identical transactions in quick succession.'),
			10762 => __('The CVV provided is invalid. The CVV is between 3-4 digits long.'),
			10764 => __('Please try again later. Ensure you have passed the correct CVV and address info for the buyer. If creating a recurring profile, please try again by passing a init amount of 0.'),
			12000 => __('Transaction is not compliant due to missing or invalid 3-D Secure authentication values. Check ECI, ECI3DS, CAVV, XID fields.'),
			12001 => __('Transaction is not compliant due to missing or invalid 3-D Secure authentication values. Check ECI, ECI3DS, CAVV, XID fields.'),
			15001 => __('The transaction was rejected by the payment gateway because of excessive failures over a short period of time for this credit card.'),
			15002 => __('The transaction was declined by payment gateway.'),
			15003 => __('The transaction was declined because the merchant does not have a valid commercial entity agreement on file with the payment gateway.'),
			15004 => __('The transaction was declined because the CVV entered does not match the credit card.'),
			15005 => __('The transaction was declined by the issuing bank, not the payment gateway. The merchant should attempt another card.'),
			15006 => __('The transaction was declined by the issuing bank, not the payment gateway. The merchant should attempt another card.'),
			15007 => __('The transaction was declined by the issuing bank because of an expired credit card. The merchant should attempt another card.'),
		);
	}

/**
 * GetVerifiedStatus
 * The GetVerifiedStatus API operation is used to determine whether a user is verified or unverified.
 *
 * @author Chris Green
 * @param string $email Email address of the buyer
 * @return array Response array
 **/
    public function getVerifiedStatus($email) {
        if (!$this->HttpSocket) {
            $this->HttpSocket = new HttpSocket();
        }
        $headers = array(
            'X-PAYPAL-SANDBOX-EMAIL-ADDRESS' => $this->nvpUsername,
            'X-PAYPAL-SECURITY-PASSWORD' => $this->nvpPassword,
            'X-PAYPAL-SECURITY-SIGNATURE' => $this->nvpSignature,
            'X-PAYPAL-APPLICATION-ID' => $this->adaptiveAppID,
            'X-PAYPAL-REQUEST-DATA-FORMAT' => 'NV',
            'X-PAYPAL-RESPONSE-DATA-FORMAT' => 'JSON',
            'X-PAYPAL-SECURITY-USERID' => $this->adaptiveUserID,
        );
        $query = array(
            'accountIdentifier.emailAddress' => $email,
            'matchCriteria' => 'NONE',
            'requestEnvelope.errorLanguage' => 'en_GB'
        );
        $endPoint = $this->getAdaptiveAccountsEndpoint();
        $endPoint .= 'GetVerifiedStatus';
        $response = $this->HttpSocket->post($endPoint, $query, array('header' => $headers));
        $parsed = json_decode($response, true);
        if(in_array($parsed['responseEnvelope']['ack'], array('Success', 'SuccessWithWarning'))) {
            return $parsed;
        } else if ($parsed['responseEnvelope']['ack'] == 'Failure' && isset($parsed['error'])) {
            throw new PaypalException(__d('paypal', $parsed['error'][0]['message']));
        } else {
            throw new PaypalException(__d('paypal', 'An error occured while getting the status of your account.'));
        }
    }

/**
 * Returns custom error message if there are any set for the error code passed in with the parsed response.
 * Returns the long message in the response otherwise.
 *
 * @author Chris Green
 * @param  array $parsed  Parsed response
 * @return string         The error message
 */
	public function getErrorMessage($parsed) {
		if (array_key_exists($parsed['L_ERRORCODE0'], $this->errorMessages)) {
			return $this->errorMessages[$parsed['L_ERRORCODE0']];
		}
		return $parsed['L_LONGMESSAGE0'];
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
        try {
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
            if (isset($parsed['TOKEN']) && isset($parsed['ACK']) && in_array($parsed['ACK'], array('Success', 'SuccessWithWarning')))  {
                return $this->expressCheckoutUrl($parsed['TOKEN']);
            }
            else if ($parsed['ACK'] == "Failure" && isset($parsed['L_LONGMESSAGE0']))  {
                throw new PaypalException($this->getErrorMessage($parsed));
            }
            else {
                throw new PaypalException(__d('paypal' , 'There was an error while connecting to Paypal'));
            }
        } catch (SocketException $e) {
            throw new PaypalException(__d('paypal', 'There was a problem initiating the transaction, please try again.'));
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
        try {
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
            if (isset($parsed['TOKEN']) && isset($parsed['ACK']) && in_array($parsed['ACK'], array('Success', 'SuccessWithWarning')))  {
                return $parsed;
            }
            else if ($parsed['ACK'] == "Failure" && isset($parsed['L_LONGMESSAGE0']))  {
                throw new PaypalException($this->getErrorMessage($parsed));
            }
            else {
                throw new PaypalException(__d('paypal' , 'There was an error while connecting to Paypal'));
            }
        } catch (SocketException $e) {
            throw new PaypalException(__d('paypal', 'There was a problem getting your details, please try again.'));
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
		try {
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
			$response = $this->HttpSocket->post($endPoint, $finalNvps);

			// Parse the results
			$parsed = $this->parseClassicApiResponse($response);

			// Handle the resposne
			if (isset($parsed['TOKEN']) && isset($parsed['ACK']) && in_array($parsed['ACK'], array('Success', 'SuccessWithWarning')))  {
				return $parsed;
			}
			else if ($parsed['ACK'] == "Failure" && isset($parsed['L_LONGMESSAGE0']))  {
				if (in_array($parsed['L_ERRORCODE0'], $this->redirectErrors)) {
					// We can catch an exception that requires a redirect back to paypal
					throw new PaypalRedirectException($this->expressCheckoutUrl($token));
				}
				throw new PaypalException($this->getErrorMessage($parsed));
			}
			else {
				throw new PaypalException(__d('paypal', 'There was an error completing the payment'));
			}
		} catch (SocketException $e) {
			throw new PaypalException(__d('paypal', 'There was a problem processing the transaction, please try again.'));
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
		try {
			// Build NVPs
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
			if (isset($parsed['ACK']) && isset($parsed['ACK']) && in_array($parsed['ACK'], array('Success', 'SuccessWithWarning')))  {
				return $parsed;
			}
			else if ($parsed['ACK'] == "Failure" && isset($parsed['L_LONGMESSAGE0']))  {
				throw new PaypalException($this->getErrorMessage($parsed));
			}
			else {
				throw new PaypalException(__d('paypal', 'There was an error processing the card payment'));
			}
		} catch (SocketException $e) {
			throw new PaypalException(__d('paypal', 'There was a problem processing your card, please try again.'));
		}
	}

/**
 * DoCapture
 * The DoCapture API Operation enables you to charge a previously authorized payment
 *
 * @param array $payment Transaction Id and amount details to process
 * @return void
 * @author Michael Houghton
 **/
	public function doCapture($payment) {
		try {
			// Build NVPs
			$nvps = $this->formatDoCaptureNvps($payment);

			// HttpSocket
			if (!$this->HttpSocket) {
				$this->HttpSocket = new HttpSocket();
			}
			// Classic API endpoint
			$endPoint = $this->getClassicEndpoint();
			// Make a Http request for a new token
			$response = $this->HttpSocket->post($endPoint, $nvps);

			// Parse the results
			$parsed = $this->parseClassicApiResponse($response);

			// Handle the resposne
			if (isset($parsed['ACK']) && isset($parsed['ACK']) && in_array($parsed['ACK'], array('Success', 'SuccessWithWarning')))  {
				return $parsed;
			}
			else if ($parsed['ACK'] == "Failure" && isset($parsed['L_LONGMESSAGE0']))  {
				throw new PaypalException($this->getErrorMessage($parsed));
			}
			else {
				throw new PaypalException(__d('paypal', 'There was an error processing the payment'));
			}
		} catch (SocketException $e) {
			throw new PaypalException(__d('paypal', 'There was a problem processing the payment, please try again.'));
		}
	}

/**
 * Formats the DoCatpure array to Paypal nvps
 *
 * @param array $order Takes an array order (See tests for supported fields).
 * @return array Formatted array of Paypal NVPs for DoCapture
 * @author Michael Houghton
 **/
	public function formatDoCaptureNvps($order) {
		if (empty($order) || !is_array($order)) {
			throw new PaypalException(__d('paypal', 'You must pass a valid order array'));
		}
		if (!isset($order['authorization_id'])) {
			throw new PaypalException(__d('paypal', 'authorization_id must be passed.'));
		}
		if (!isset($order['amount'])) {
			throw new PaypalException(__d('paypal', 'You must pass an amount must be passed.'));
		}
		if (!isset($order['currency']))  {
			throw new PaypalException(__d('paypal', 'You must provide a currency code'));
		}

		if (empty($order['complete'])) {
			$order['complete'] = 'NotComplete';
		} else {
			$order['complete'] = 'Complete';
		}

		return array(
			'METHOD' => 'DoCapture',
			'VERSION' => $this->paypalClassicApiVersion,
			'USER' => $this->nvpUsername,
			'PWD' => $this->nvpPassword,
			'SIGNATURE' => $this->nvpSignature,
			'AUTHORIZATIONID' => $order['authorization_id'],
			'AMT' => $order['amount'],
			'CURRENCYCODE' => $order['currency'],
			'COMPLETETYPE' => $order['complete'],
		);
	}

/**
 * DoVoid
 * The DoVoid API Operation enables you to void a previously authorized payment
 *
 * @param array $payment Transaction Id and amount details to process
 * @return void
 * @author Michael Houghton
 **/
	public function doVoid($payment) {
		try {
			// Build NVPs
			$nvps = $this->formatDoVoidNvps($payment);

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
			if (isset($parsed['ACK']) && isset($parsed['ACK']) && in_array($parsed['ACK'], array('Success', 'SuccessWithWarning')))  {
				return $parsed;
			}
			else if ($parsed['ACK'] == "Failure" && isset($parsed['L_LONGMESSAGE0']))  {
				throw new PaypalException($this->getErrorMessage($parsed));
			}
			else {
				throw new PaypalException(__d('paypal' , 'There was an error voiding the payment'));
			}
		} catch (SocketException $e) {
			throw new PaypalException(__d('paypal', 'There was a problem voiding the payment, please try again.'));
		}
	}

/**
 * Formats the DoVoid array to Paypal nvps
 *
 * @param array $order Takes an array order (See tests for supported fields).
 * @return array Formatted array of Paypal NVPs for DoCapture
 * @author Michael Houghton
 **/
	public function formatDoVoidNvps($order) {
		if (empty($order) || !is_array($order)) {
			throw new PaypalException(__d('paypal' , 'You must pass a valid order array'));
		}
		if (!isset($order['authorization_id'])) {
			throw new PaypalException(__d('paypal' , 'authorization_id must be passed.'));
		}

		$nvps = array(
			'METHOD' => 'DoVoid',
			'VERSION' => $this->paypalClassicApiVersion,
			'USER' => $this->nvpUsername,
			'PWD' => $this->nvpPassword,
			'SIGNATURE' => $this->nvpSignature,
			'AUTHORIZATIONID' => $order['authorization_id']
		);

		if (!empty($order['note'])) {
			$nvps['NOTE'] = $order['note'];
		}

		if (!empty($order['message_id'])) {
			$nvps['MSGSUBID'] = $order['message_id'];
		}

		return $nvps;
	}

/**
 * RefundTransaction
 * The RefundTransaction API Operation enables you to refund a transaction that is less than 60 days old.
 *
 * @param array $refund original transaction information and amount to refund
 * @return void
 * @author James Mikkelson
**/
	public function refundTransaction($refund) {
		try {
			// Build NVPs
			$nvps = $this->formatRefundTransactionNvps($refund);
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
			if (isset($parsed['ACK']) && $parsed['ACK'] == "Success") {
				return $parsed;
			}
			elseif ($parsed['ACK'] == "Failure" && isset($parsed['L_LONGMESSAGE0'])) {
				throw new PaypalException($this->getErrorMessage($parsed));
			}
			else {
				throw new PaypalException(__d('paypal' , 'There was an error processing the the refund'));
			}
		} catch (SocketException $e) {
			throw new PaypalException(__d('paypal', 'A problem occurred during the refund process, please try again.'));
		}
	}

/**
 * Store and use a customer credit card
 *
 * @return void
 * @author Rob Mcvey
 * @link https://developer.paypal.com/docs/integration/direct/store-a-credit-card/#store-a-credit-card
 **/
	public function storeCreditCard($creditCard) {
		// HttpSocket
		if (!$this->HttpSocket) {
			$this->HttpSocket = new HttpSocket();
		}
		// Get access token
		$token = $this->getOAuthAccessToken();
		$authHeader = sprintf('%s %s', $token['token_type'], $token['access_token']);
		// Reset the HttpSocket as it's already been setup (with headers) by getOAuthAccessToken
		$this->HttpSocket->reset();
		// Get store card endpoint
		$endPoint = $this->storeCreditCardUrl();
		// JSON encode our card array
		$json = json_encode($this->formatStoreCreditCardArgs($creditCard));
		// Add oAuth headers and content type headers
		$request = array(
			'method' => 'POST',
			'header' => array(
				'Content-Type' => 'application/json',
				'Authorization' => $authHeader,
			),
			'uri' => $endPoint,
			'body' => $json,
		);
		// Attempt to make REST request
		$response = $this->HttpSocket->request($request);
		// Decode the JSON
		$responseArray = json_decode($response->body, true);
		if ($response->code == 200) {
			// Return an array
			return $responseArray;
		} else {
			$message = (isset($responseArray['message'])) ? $responseArray['message'] : __d('paypal', 'There was an problem communicating with the payment gateway') ;
			throw new PaypalException($message);
		}
	}

/**
 * Charge a stored card
 *
 * @return void
 * @author Rob Mcvey
 * @link https://developer.paypal.com/docs/integration/direct/store-a-credit-card/#use-a-stored-credit-card
 **/
	public function chargeStoredCard($transaction) {
		// HttpSocket
		if (!$this->HttpSocket) {
			$this->HttpSocket = new HttpSocket();
		}
		// Get access token
		$token = $this->getOAuthAccessToken();
		$authHeader = sprintf('%s %s', $token['token_type'], $token['access_token']);
		// Reset the HttpSocket as it's already been setup (with headers) by getOAuthAccessToken
		$this->HttpSocket->reset();
		// Get charge card endpoint
		$endPoint = $this->chargeStoredCardUrl();
		// JSON encode our card array
		$json = json_encode($transaction);
		// Add oAuth headers and content type headers
		$request = array(
			'method' => 'POST',
			'header' => array(
				'Content-Type' => 'application/json',
				'Authorization' => $authHeader,
			),
			'uri' => $endPoint,
			'body' => $json,
		);
		// Attempt to make REST request
		$response = $this->HttpSocket->request($request);
		// Decode the JSON
		$responseArray = json_decode($response->body, true);
		if ($response->code == 200) {
			// Return an array
			return $responseArray;
		} else {
			$message = (isset($responseArray['message'])) ? $responseArray['message'] : __d('paypal', 'There was an problem communicating with the payment gateway') ;
			throw new PaypalException($message);
		}
	}

/**
 * Get an access token
 *
 * @return void
 * @author Rob Mcvey
 * @link https://developer.paypal.com/docs/integration/direct/make-your-first-call/
 **/
	public function getOAuthAccessToken() {
		// HttpSocket may be mocked
		if (!$this->HttpSocket) {
			$this->HttpSocket = new HttpSocket();
		}
		// Do we have both an ID and secret?
		if (!$this->oAuthClientId || !$this->oAuthSecret) {
			throw new PaypalException(__d('paypal', 'Missing client id/secret'));
		}
		// Set the auth as basic
		$this->HttpSocket->configAuth('Basic', $this->oAuthClientId, $this->oAuthSecret);
		// Get the token endpoint
		$endPoint = $this->oAuthTokenUrl();
		// Make the request
		$response = $this->HttpSocket->post($endPoint, array(
			"grant_type" => "client_credentials"
		));
		// Decode the JSON
		$responseArray = json_decode($response->body, true);
		if ($response->code == 200) {
			// Return an array
			return $responseArray;
		} else {
			$message = (isset($responseArray['message'])) ? $responseArray['message'] : __d('paypal', 'There was an problem communicating with the payment gateway') ;
			throw new PaypalException($message);
		}
	}

/**
 * Takes an array containing info of a single card, and formats as per storeCreditCard
 * e.g.
 *  $creditCard = array(
 *  	'payer_id' => 186,
 *  	'type' => 'Visa',
 *  	'card' => '4008 0687 0641 8697 ',
 *  	'expiry' => array(
 *  	    'M' => '2',
 *          'Y' => '2018',
 *      ),
 *  	'first_name' => 'Joe',
 *  	'last_name' => 'Shopper'
 *  );
 *
 * @return void
 * @author Rob Mcvey
 * @link https://developer.paypal.com/docs/integration/direct/store-a-credit-card/#store-a-credit-card
 **/
	public function formatStoreCreditCardArgs($creditCard) {
		// Expiry
		if (!isset($creditCard['expiry'])) {
			throw new PaypalException(__d('paypal' , 'Must specify an expiry date'));
		}
		$dateKeys = array_keys($creditCard['expiry']);
		sort($dateKeys); // Sort alphabetcially
		if ($dateKeys != array('M' , 'Y')) {
			throw new PaypalException(__d('paypal' , 'Must include a M and Y in expiry date'));
		}
		$month = $creditCard['expiry']['M'];
		$year = $creditCard['expiry']['Y'];
		// Check date not in past
		$expiresTime = mktime(0, 0, 0, $month, 1, $year);
		$nowTime = mktime(0, 0, 0, date('n'), 1, date('Y'));
		if ($expiresTime < $nowTime) {
			throw new PaypalException(__d('paypal' , 'Invalid expiry date'));
		}
		// Strip white space
		$number = preg_replace("/\s/" , "" , $creditCard['card']);
		// Check card
		if (!$this->validateCC($creditCard['card'])) {
			throw new PaypalException(__d('paypal' , 'Invalid card number'));
		}
		// CVV2
		if (!isset($creditCard['cvv2']) || empty($creditCard['cvv2'])) {
			throw new PaypalException(__d('paypal' , 'Invalid CVV2 number'));
		}
		// Type
		$type = trim(strtolower($creditCard['type']));
		// Mastercard is not CamelCase
		if (!in_array($type, array('visa', 'mastercard', 'amex', 'discover', 'Maestro'))) {
			throw new PaypalException(__d('paypal' , 'Invalid card type'));
		}
		// Build our array as per Paypal docs
		$object = array (
			'number' => $number,
			'cvv2' => $creditCard['cvv2'],
			'type' => $type,
			'expire_month' => $month,
			'expire_year' => $year,
			'payer_id' => $creditCard['payer_id'],
			'first_name' => $creditCard['first_name'],
			'last_name' => $creditCard['last_name'],
			//'billing_address' => $creditCard['billing_address'],
		);
		return $object;
	}

/**
 * Validates a credit card number
 * Note: We use this becuase when storing a card, paypal doen not validate!!!
 *
 * @return void
 * @author Rob Mcvey
 **/
	public function validateCC($cc) {
		return Validation::cc($cc);
	}

/**
 * Takes a payment array and formats in to the minimum NVPs to complete a payment
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
			throw new PaypalException(__d('paypal' , 'Could not detect client IP address'));
		}
		// Credit card number
		if (!isset($payment['card'])) {
			throw new PaypalException(__d('paypal' , 'Not a valid credit card number'));
		}
		$payment['card'] = preg_replace("/\s/" , "" , $payment['card']);
		// Credit card number
		if (!isset($payment['cvv'])) {
			throw new PaypalException(__d('paypal' , 'You must include the 3 digit security number'));
		}
		$payment['cvv'] = preg_replace("/\s/" , "" , $payment['cvv']);
		// Amount
		if (!isset($payment['amount'])) {
			throw new PaypalException(__d('paypal' , 'Must specify an "amount" to charge'));
		}
		// Expiry
		if (!isset($payment['expiry'])) {
			throw new PaypalException(__d('paypal' , 'Must specify an expiry date'));
		}
		$dateKeys = array_keys($payment['expiry']);
		sort($dateKeys); // Sort alphabetcially
		if ($dateKeys != array('M' , 'Y')) {
			throw new PaypalException(__d('paypal' , 'Must include a M and Y in expiry date'));
		}
		$month = $payment['expiry']['M'];
		$year = $payment['expiry']['Y'];
		$expiry = sprintf('%d%d' , $month, $year);
		// Build NVps
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
			throw new PaypalException(__d('paypal' , 'You must pass a valid order array'));
		}
		if (!isset($order['return']) || !isset($order['cancel'])) {
			throw new PaypalException(__d('paypal' , 'Valid "return" and "cancel" urls must be provided'));
		}
		if (!isset($order['currency']))  {
			throw new PaypalException(__d('paypal' , 'You must provide a currency code'));
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
			// Cart totals
			$nvps['PAYMENTREQUEST_0_ITEMAMT'] = 0;
			$individualShipping = $nvps['PAYMENTREQUEST_0_SHIPPINGAMT'] = 0;
			$nvps['PAYMENTREQUEST_0_TAXAMT'] = 0;
			$nvps['PAYMENTREQUEST_0_AMT'] = 0;
			// Build each item
			foreach ($order['items'] as $m => $item) {
				// Name and subtotal are required for each item
				$nvps["L_PAYMENTREQUEST_0_NAME$m"] = $item['name'];
				// Description an Tax are optional
                if (array_key_exists("description", $item)) {
					$nvps["L_PAYMENTREQUEST_0_DESC$m"] = $item['description'];
				}
				// Qty is optional however it effects our order totals
				$quantity = $nvps["L_PAYMENTREQUEST_0_QTY$m"] = 1;
				if (array_key_exists("qty", $item) && $item['qty'] > 1 && is_numeric($item['qty'])) {
					$quantity = $nvps["L_PAYMENTREQUEST_0_QTY$m"] = (int) floor($item['qty']);
				}
				// Item subtotal
				$nvps["L_PAYMENTREQUEST_0_AMT$m"] = $item['subtotal'];
				// Shipping
				if (array_key_exists("shipping", $item)) {
					//$nvps['PAYMENTREQUEST_0_SHIPPINGAMT'] += ($item['shipping'] * $quantity);
					$individualShipping += ($item['shipping'] * $quantity);
				}
				// Tax
				if (array_key_exists("tax", $item)) {
					$nvps["L_PAYMENTREQUEST_0_TAXAMT$m"] = $item['tax'];
					$nvps['PAYMENTREQUEST_0_AMT'] += ($item['tax'] * $quantity);
					$nvps['PAYMENTREQUEST_0_TAXAMT'] += ($item['tax'] * $quantity);
				}
				// Cart totals
				$nvps['PAYMENTREQUEST_0_ITEMAMT'] += ($nvps["L_PAYMENTREQUEST_0_AMT$m"] * $quantity);
				$nvps['PAYMENTREQUEST_0_AMT'] += ($nvps["L_PAYMENTREQUEST_0_AMT$m"] * $quantity);
			}
		}
		// Custom/combined shipping for all items
		if (isset($order['shipping']) && $order['shipping'] > 1 && is_numeric($order['shipping'])) {
			$nvps['PAYMENTREQUEST_0_SHIPPINGAMT'] = $order['shipping'];
			$nvps['PAYMENTREQUEST_0_AMT'] += $nvps['PAYMENTREQUEST_0_SHIPPINGAMT'];
		} else if (isset($individualShipping)) {
			$nvps['PAYMENTREQUEST_0_SHIPPINGAMT'] = $individualShipping;
			$nvps['PAYMENTREQUEST_0_AMT'] += $individualShipping;
		}
		return $nvps;
	}

/**
 * Takes a refund transaction array and formats in to the minimum NVPs to process a refund
 *
 * @param array original transaction details and refund amount
 * @return array Formatted array of Paypal NVPs for RefundTransaction
 * @author James Mikkelson
**/
	public function formatRefundTransactionNvps($refund) {
		// PayPal Transcation ID
		if (!isset($refund['transactionId'])) {
			throw new PaypalException(__d('paypal' , 'Original PayPal Transaction ID is required'));
		}
		$refund['transactionId'] = preg_replace("/\s/" , "" , $refund['transactionId']);
		// Amount to refund
		if (!isset($refund['amount'])) {
			throw new PaypalException(__d('paypal' , 'Must specify an "amount" to refund'));
		}
		// Type of refund
		if (!isset($refund['type'])) {
			throw new PaypalException(__d('paypal' , 'You must specify a refund type, such as Full or Partial'));
		}
		// Set reference
		$reference = (isset($refund['reference'])) ? $refund['reference'] : '';
		// Set note
		$note = (isset($refund['note'])) ? $refund['note'] : false;
		// Currency
		$currency = (isset($refund['currency'])) ? $refund['currency'] : 'GBP';
		// Source
		$source = (isset($refund['source'])) ? $refund['source'] : 'any';
		// Build our NVPs for the request
		$nvps = array(
			'METHOD' => 'RefundTransaction',
			'VERSION' => $this->paypalClassicApiVersion,
			'USER' => $this->nvpUsername,
			'PWD' => $this->nvpPassword,
			'SIGNATURE' => $this->nvpSignature,
			'TRANSACTIONID' => $refund['transactionId'],	// The orginal PayPal Transaction ID
			'INVOICEID' => $reference,						// Your own reference or invoice number
			'REFUNDTYPE' => $refund['type'],				// Full, Partial, ExternalDispute, Other
			'CURRENCYCODE' => $currency, 					// Only required for partial refunds or refunds greater than 100%
			'NOTE' => $note,								// Up to 255 characters of information displayed to customer
			'REFUNDSOURCE' => $source,						// Any, default, instant, eCheck
		);
		// Refund amount, only set if REFUNDTYPE is Partial
		if ($refund['type'] == 'Partial') {
			$nvps['AMT'] = $refund['amount'];
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
 * oAuthTokenUrl
 *
 * @return void
 * @author Rob Mcvey
 **/
	public function oAuthTokenUrl() {
		return $this->getRestEndpoint() . '/v1/oauth2/token';
	}

/**
 * chargeStoredCardUrl
 *
 * @return void
 * @author Rob Mcvey
 **/
	public function chargeStoredCardUrl() {
		return $this->getRestEndpoint() . '/v1/payments/payment';
	}

/**
 * storeCreditCardUrl
 *
 * @return void
 * @author Rob Mcvey
 **/
	public function storeCreditCardUrl() {
		return $this->getRestEndpoint() . '/v1/vault/credit-card';
	}

/**
 * Returns Paypal Adaptive Accounts API endpoint
 *
 * @author Chris Green
 * @return string
 **/
	public function getAdaptiveAccountsEndpoint() {
		if ($this->sandboxMode) {
			return $this->sandboxAdaptiveAccountsEndpoint;
		}
		return $this->liveAdaptiveAccountsEndpoint;
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
