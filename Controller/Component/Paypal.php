<?php 
// 
//  Paypal.php
//  CakePHP 2.0 component for paypal website payments pro
//  PayPal Express and Direct Payments
//  Created by Rob Mcvey on 2011-12-03.
//  Copyright 2011 Rob Mcvey. All rights reserved.
// 
App::uses('HttpSocket', 'Network/Http');

class PaypalComponent extends Component {

	// Live v Sandbox mode !important
	public $sandbox_mode = true;

	// Live paypal API config
	public $config = array(
		'webscr' => 'https://www.paypal.com/webscr/',
		'endpoint' => 'https://api.paypal.com/nvp/',
		'password' => '',
		'email' => '',
		'signature' => ''
	);

	// Sandbox paypal API config
	public $sandbox_config = array(
		'webscr' => 'https://www.sandbox.paypal.com/webscr/',
		'endpoint' => 'https://api.sandbox.paypal.com/nvp/',
		'password' => '12345678',
		'email' => 'robmcv_125578377blahblah.gmail.com',
		'signature' => 'A43525523523452354SQTV.0UUxbV4NuL2'
	);

	// API version
	public $api_version = '53.0';
	
	// Return URL for express payments
	public $return_url = '';
	
	// Cancel URL for Express payments cancelled
	public $cancel_url = '';

	// Default Currency code
	public $currency_code = 'GBP';

	//The amount of the transaction For example, EUR 2.000,00 must be specified as 2000.00 or 2,000.00.	
	public $amount = 0;
	
	// Customise Express checkout with a description (api version > 53)
	public $item_name = '';
	
	// Customise Express checkout with a description (api version > 53)
	public $order_desc = '';
	
	// optional quantity
	public $quantity = 1;
	
	// The token returned from payapl and used in subsequesnt reuqest
	public $token = null;
	
	// The payers paypal ID 
	public $payer_id = null;
	
	// controller reference
	protected $controller = null;
	
	
	/**
	 * undocumented function
	 *
	 * @return void
	 * @author Rob Mcvey
	 **/
	public function initialize($controller) {
		$this->controller = $controller;		
		if($this->sandbox_mode) {
			$this->config = $this->sandbox_config;
		}
	}
	
	
	/**
	 * undocumented function
	 *
	 * @return void
	 * @author Rob Mcvey
	 **/
	public function ExpressCheckout() {
		
		// We dont have a valid amount	
		if(!isset($this->amount) || empty($this->amount) || !is_numeric($this->amount)) {
			throw new Exception(__('Invalid amount - must be numeric in the format 1234.00'));
		}
		
		// Call the SetExpressCheckout method to get a fresh token
		$token = $this->SetExpressCheckout();
		
		// We have a token, redirect to paypals web server (not the URL is different to the API endpoint)
		if($token) {
			$this->controller->redirect($this->config['webscr'].'?cmd=_express-checkout&token='.$token);
		} else {
			throw new Exception(__('The was a problem with the payment gateway'));
		}
	}
	
	
	/**
	 * To set up an Express Checkout transaction, you must invoke the SetExpressCheckout API 
	 * operation to provide sufficient information to initiate the payment flow and redirect 
	 * to PayPal if the operation was successful with the token sent back from Paypal
	 *
	 * @return void
	 * @author Rob Mcvey
	 **/
	public function SetExpressCheckout() {
			
		$set_express_checkout_nvp = array(
			'METHOD' => 'SetExpressCheckout',
			'VERSION' => $this->api_version,
			'USER' => $this->config['email'],
			'PWD' => $this->config['password'],									
			'SIGNATURE' => $this->config['signature'],
			'CURRENCYCODE' => $this->currency_code,
			'RETURNURL' => $this->return_url,
			'CANCELURL' => $this->cancel_url,
			'PAYMENTACTION' => 'ORDER',
			'PAGESTYLE' => 'Copify',
			'AMT' => $this->amount,
			'L_NAME0'=> $this->item_name,    
			'L_DESC0'=> $this->order_desc, 
			'L_AMT0'=> $this->amount, 
			'L_QTY0' => $this->quantity,													
		);	

		// HTTPSocket class		
		$HttpSocket = new HttpSocket();	

		// Post the NVPs to the relevent endpoint
		$response = $HttpSocket->post($this->config['endpoint'] , $set_express_checkout_nvp);
		
		// Parse the guff that comes back from paypal
		parse_str($response->body , $parsed);
		
		//debug($parsed);
		
		// Return the token, or throw a human readable error
		if(array_key_exists('TOKEN', $parsed) && array_key_exists('ACK', $parsed) && $parsed['ACK'] == 'Success') {
			return $parsed['TOKEN'];
		}
		elseif(array_key_exists('ACK', $parsed) && array_key_exists('L_LONGMESSAGE0', $parsed) && $parsed['ACK'] != 'Success') {
			throw new Exception($parsed['ACK'] . ' : ' . $parsed['L_LONGMESSAGE0']);
		}
		elseif(array_key_exists('ACK', $parsed) && array_key_exists('L_ERRORCODE0', $parsed) && $parsed['ACK'] != 'Success') {
			throw new Exception($parsed['ACK'] . ' : ' . $parsed['L_ERRORCODE0']);
		} 
		else {
			throw new Exception(__('There is a problem with the payment gateway. Please try again later.'));
		}
	}
	
	
	/**
	 * To obtain details about an Express Checkout transaction, you can invoke the 
	 * GetExpressCheckoutDetails API operation. 
	 * @return void
	 * @author Rob Mcvey
	 **/
	public function GetExpressCheckoutDetails() {
		$get_express_checkout_details_nvp = array(
			'METHOD' => 'GetExpressCheckoutDetails' , 
			'TOKEN' => $this->token,
			'VERSION' => $this->api_version,
			'USER' => $this->config['email'],
			'PWD' => $this->config['password'],									
			'SIGNATURE' => $this->config['signature'],
		);
		
		// HTTPSocket class		
		$HttpSocket = new HttpSocket();	

		// Post the NVPs to the relevent endpoint
		$response = $HttpSocket->post($this->config['endpoint'] , $get_express_checkout_details_nvp);

		// Parse the guff that comes back from paypal
		parse_str($response , $parsed);
		
		// Return the token, or throw a human readable error
		if(array_key_exists('TOKEN', $parsed) && array_key_exists('ACK', $parsed) && $parsed['ACK'] == 'Success') {
			return $parsed;
		}
		elseif(array_key_exists('ACK', $parsed) && array_key_exists('L_LONGMESSAGE0', $parsed) && $parsed['ACK'] != 'Success') {
			throw new Exception($parsed['ACK'] . ' : ' . $parsed['L_LONGMESSAGE0']);
		}
		elseif(array_key_exists('ACK', $parsed) && array_key_exists('L_ERRORCODE0', $parsed) && $parsed['ACK'] != 'Success') {
			throw new Exception($parsed['ACK'] . ' : ' . $parsed['L_ERRORCODE0']);
		} 
		else {
			throw new Exception(__('There is a problem with the payment gateway. Please try again later.'));
		}	
	}
	
	
	/**
	 * To complete an Express Checkout transaction, you must invoke the 
	 * DoExpressCheckoutPayment API operation. 
	 *
	 * @return void
	 * @author Rob Mcvey
	 **/
	public function DoExpressCheckoutPayment() {
		$do_express_checkout_payment = array(
			'METHOD' => 'DoExpressCheckoutPayment' ,
			'USER' => $this->config['email'],
			'PWD' => $this->config['password'],									
			'SIGNATURE' => $this->config['signature'],
			'VERSION' => $this->api_version,
			'TOKEN' => $this->token,
			'PAYERID' => $this->payer_id,	
			'PAYMENTACTION' => 'Sale',
			'CURRENCYCODE' => $this->currency_code,
			'AMT'=> $this->amount													
		);
		
		// HTTPSocket class		
		$HttpSocket = new HttpSocket();	

		// Post the NVPs to the relevent endpoint
		$response = $HttpSocket->post($this->config['endpoint'] , $do_express_checkout_payment);

		// Parse the guff that comes back from paypal
		parse_str($response , $parsed);
		
		// Return the token, or throw a human readable error
		if(array_key_exists('TOKEN', $parsed) && array_key_exists('ACK', $parsed) && $parsed['ACK'] == 'Success') {
			return $parsed;
		}
		elseif(array_key_exists('ACK', $parsed) && array_key_exists('L_LONGMESSAGE0', $parsed) && $parsed['ACK'] != 'Success') {
			throw new Exception($parsed['ACK'] . ' : ' . $parsed['L_LONGMESSAGE0']);
		}
		elseif(array_key_exists('ACK', $parsed) && array_key_exists('L_ERRORCODE0', $parsed) && $parsed['ACK'] != 'Success') {
			throw new Exception($parsed['ACK'] . ' : ' . $parsed['L_ERRORCODE0']);
		} 
		else {
			throw new Exception(__('There is a problem with the payment gateway. Please try again later.'));
		}		
	}
	
	
}