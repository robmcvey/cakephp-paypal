<?php
// 
//  PaypalREST.php
//  cakephp-paypal
//  
//  Created by Rob Mcvey on 2014-03-10.
//  Copyright 2014 Rob McVey. All rights reserved.
// 
trait PaypalREST {
	
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
	
}
