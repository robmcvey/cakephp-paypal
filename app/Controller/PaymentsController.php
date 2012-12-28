<?php
/**
* Example usuage of Paypal Component
*
*/
App::uses('AppController', 'Controller');

class PaymentsController extends AppController {

	// Include the Payapl component
	public $components = array('Paypal');
  

	// Example usuage
	public function index() {
		
	}


  	// Set the values and begin paypal process
  	public function express_checkout() {
		try{
			$this->Paypal->amount = 10.00;
			$this->Paypal->currencyCode = 'GBP';	
			$this->Paypal->returnUrl = Router::url(array('action' => 'get_details'), true);
			$this->Paypal->cancelUrl = Router::url($this->here, true);
			$this->Paypal->orderDesc = 'A description of the thing someone is about to buy';
			$this->Paypal->itemName = 'Swedish penis enlargement kit';
			$this->Paypal->quantity = 1;
			$this->Paypal->expressCheckout();
		} catch(Exception $e) {
			$this->Session->setFlash($e->getMessage());
			return $this->redirect('index');
		}
  	}
  

	// Use the token in the return URL to fetch details
  	public function get_details() {
		try {
    			$this->autoRender = false;

    			// Token and PayerID will be present in URL
	    		$this->Paypal->token = $this->request->query['token'];
	    		$this->Paypal->payerId = $this->request->query['PayerID'];
	    		
			// At this point, you can let the customer review their order.
			// Use the "getExpressCheckoutDetails" method to fetch details...
	    		$customer_details = $this->Paypal->getExpressCheckoutDetails();
	    		debug($customer_details);
	
	    		// Then you must call "doExpressCheckoutPayment" to complete the payment
	    		$this->Paypal->amount = 10.00;
			$this->Paypal->currencyCode = 'GBP';
	    		$response = $this->Paypal->doExpressCheckoutPayment(); 
			debug($response);

    		} catch(Exception $e) {
			$this->Session->setFlash($e->getMessage());
			return $this->redirect('index');
		}
  	}

  	
  	// Do a direct credit card payment
  	public function charge_card() {
  		try {
  			$this->autoRender = false;
	  		$this->Paypal->amount = 10.00;
			$this->Paypal->currencyCode = 'GBP';	
			$this->Paypal->creditCardNumber = '4008068706418697'; // Paypal sandbox CC
			$this->Paypal->creditCardCvv = '123';
			$this->Paypal->creditCardExpires = '012020';
			$this->Paypal->creditCardType = 'Visa';
			$result = $this->Paypal->doDirectPayment();
			debug($result);
  		} catch(Exception $e) {
			$this->Session->setFlash($e->getMessage());
			return $this->redirect('index');
		}
  	}

}
