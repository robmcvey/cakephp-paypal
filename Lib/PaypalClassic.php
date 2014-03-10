<?php
// 
//  PaypalClassic.php
//  cakephp-paypal
//  
//  Created by Rob Mcvey on 2014-03-10.
//  Copyright 2014 Rob McVey. All rights reserved.
// 
trait PaypalClassic {

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
 * Live endpoint for Classic API
 */
	protected $_liveClassicEndpoint = 'https://api-3t.paypal.com/nvp';

/**
 * Sandbox endpoint for Classic API
 */
	protected $_sandboxClassicEndpoint = 'https://api-3t.sandbox.paypal.com/nvp';		
	
/**
 * Live endpoint for Paypal web login (used in classic paypal payments)
 */
	protected $_livePaypalLoginUri = 'https://www.paypal.com/webscr';

/**
 * Sandbox endpoint for Paypal web login (used in classic paypal payments)
 */
	protected $_sandboxPaypalLoginUri = 'https://www.sandbox.paypal.com/webscr';
		
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
 * Live endpoint for Adaptive Accounts API
 */
	protected $_liveAdaptiveAccountsEndpoint = 'https://svcs.paypal.com/AdaptiveAccounts/';

/**
 * Sandbox endpoint for Adaptive Accounts API
 */
	protected $_sandboxAdaptiveAccountsEndpoint = 'https://svcs.sandbox.paypal.com/AdaptiveAccounts/';
	
/**
 * API credentials - Adaptive App ID
 */
	protected $_adaptiveAppID = null;

/**
 * API credentials - Adaptive User ID
 */
	protected $_adaptiveUserID = null;
	
	/**
	 * undocumented function
	 *
	 * @return void
	 * @author Rob Mcvey
	 **/
	public function __construct() {
		echo 'PaypalClassic!';
	}
	
/**
 * getPaypalClassicVersion
 *
 * @return void
 * @author Rob Mcvey
 **/
	public function getPaypalClassicVersion() {
		return $this->_paypalClassicApiVersion;
	}
	
}
