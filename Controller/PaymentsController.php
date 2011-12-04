<?php
/**
* Example usuage of Paypal Component
*
*/
App::uses('AppController', 'Controller');

class PaymentsController extends AppController {

	// Include the Payapl component
	public $components = array('Paypal');
  
  	// Set the values and begin paypal process
  	public function express_checkout() {
		$this->Paypal->amount = 10.00;
		$this->Paypal->currencyCode = 'GBP';	
		$this->Paypal->returnUrl = Router::url(array('action' => 'get_details'), true);
		$this->Paypal->cancelUrl = Router::url($this->here, true);
		$this->Paypal->orderDesc = 'A description of the thing someone is about to buy';
		$this->Paypal->itemName = 'Swedish penis enlargement kit';
		$this->Paypal->quantity = 1;
		$this->Paypal->expressCheckout();
  	}
  
	// Use the token in the return URL to fetch details
  	public function get_details() {
    		$this->Paypal->token = $this->request->query['token'];
    		$this->Paypal->payerId = $this->request->query['PayerID'];
		$customer_details = $this->Paypal->getExpressCheckoutDetails();
    		debug($customer_details);
  	}
  
  	// Complete the payment, pass back the token and payerId
  	public function complete_express_checkout($token,$payerId) {
    		$this->Paypal->amount = 10.00;
		$this->Paypal->currencyCode = 'GBP';
    		$this->Paypal->token = $token;
		$this->Paypal->payerId = $payerId;
		$response = $this->Paypal->doExpressCheckoutPayment(); 
    		debug($response);
  	}

}