# Paypal Plugin for CakePHP 2.x

[![Build Status](https://secure.travis-ci.org/robmcvey/cakephp-paypal.png?branch=master)](https://travis-ci.org/robmcvey/cakephp-paypal)

A CakePHP plugin to interact with Paypal's "classic" and new REST APIs.

### Requirements

* CakePHP 2.x
* A PayPal Website Payments Pro account

### Installation

_[Manual]_

* Download this: [http://github.com/robmcvey/cakephp-paypal/zipball/master](http://github.com/robmcvey/cakephp-paypal/zipball/master)
* Unzip that download.
* Copy the resulting folder to `app/Plugin`
* Rename the folder you just copied to `Paypal`

_[GIT Submodule]_

In your app directory type:

```shell
git submodule add -b master git://github.com/robmcvey/cakephp-paypal.git Plugin/Paypal
git submodule init
git submodule update
```

_[GIT Clone]_

In your `Plugin` directory type:

```shell
git clone -b master git://github.com/robmcvey/cakephp-paypal.git Paypal
```

### Usage

Make sure the plugin is loaded in `app/Config/bootstrap.php`.

```php
CakePlugin::load('Paypal');
```

## PayPal Classic Methods

Create an instance of the class with your PayPal credentials. For testing purposes, ensure `sandboxMode` is set to `true`.

```php
App::uses('Paypal', 'Paypal.Lib');

$this->Paypal = new Paypal(array(
	'sandboxMode' => true,
	'nvpUsername' => '{username}',
	'nvpPassword' => '{password}',
	'nvpSignature' => '{signature}'
));
```

### SetExpressCheckout

Create an order(s) in the following format. `setExpressCheckout` will return a string URL to redirect the customer to.

```php
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
 try {
	$this->Paypal->setExpressCheckout($order);
} catch (Exception $e) {
	// $e->getMessage();
}	
```

### GetExpressCheckoutDetails

Once the customer has returned to your site (see `return` URL above) you can request their details with the token returned from the `setExpressCheckout` method.

```php
try {
	$this->Paypal->getExpressCheckoutDetails($token);
} catch (Exception $e) {
	// $e->getMessage();
}		
```

### DoExpressCheckoutPayment

Complete the transaction using the same order details. The `$token` and `$payerId` will be returned from the `setExpressCheckout` method.

This method may throw a `PaypalRedirectException` if a user's funding method (the credit card or bank account associated with their PayPal account) needs updating. The exception message will contain a URL to redirect the user to where they will be prompted to update their funding method.

```php
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

try {
	$this->Paypal->doExpressCheckoutPayment($order, $token, $payerId);	
} catch (PaypalRedirectException $e) {
	$this->redirect($e->getMessage());
} catch (Exception $e) {
	// $e->getMessage();
}	
```

### DoDirectPayment

Charge a credit card. Ensure you are using SSL and following PCI compliance guidelines.

```php
$payment = array(
	'amount' => 30.00,
	'card' => '4008 0687 0641 8697', // This is a sandbox CC
	'expiry' => array(
		'M' => '2',
		'Y' => '2016',
	),
	'cvv' => '321',
	'currency' => 'USD' // Defaults to GBP if not provided
);

try {
	$this->Paypal->doDirectPayment($payment);
} catch (Exception $e) {
	// $e->getMessage();
}	
```

### RefundTransaction

Refund a transaction. Transactions can only be refunded up to 60 days after the completion date.

```php
$refund = array(
	'transactionId' => '96L684679W100181R' 	// Original PayPal Transcation ID
	'type' => 'Partial', 					// Full, Partial, ExternalDispute, Other
	'amount' => 30.00, 						// Amount to refund, only required if Refund Type is Partial
	'note' => 'Refund because we are nice',	// Optional note to customer
	'reference' => 'abc123',  				// Optional internal reference
	'currency' => 'USD'  					// Defaults to GBP if not provided
);

try {
	$this->Paypal->refundTransaction($refund);
} catch (Exception $e) {
	// $e->getMessage();
}	
```

## PayPal REST Methods

Create an instance of the class with your PayPal credentials, including your client ID and secret key For testing purposes, ensure `sandboxMode` is set to `true`.

```php
App::uses('Paypal', 'Paypal.Lib');

$this->Paypal = new Paypal(array(
	'sandboxMode' => true,
	'nvpUsername' => '{username}',
	'nvpPassword' => '{password}',
	'nvpSignature' => '{signature}',
	'oAuthClientId' => '{client ID}',
	'oAuthSecret' => '{secret key}',
));
```

### Store card in vault

You can store a customer's card in the vault, in return for a token which can be used for future transactions.

```php
$creditCard = array(
	'payer_id' => 186,
	'type' => 'visa',
	'card' => 'xxxxxxxxxxxx8697',
	'cvv2' => 232,
	'expiry' => array(
	    'M' => '2',
        'Y' => '2018',
    ),
	'first_name' => 'Joe',
	'last_name' => 'Shopper'
);

try {
	$this->Paypal->storeCreditCard($creditCard);
} catch (Exception $e) {
	// $e->getMessage();
}	
```

### Charge a stored card

Once a card is stored in the vault, you can make a charge(s) on that card using the token issued when it was first stored. 

```php
$cardPayment = array(
	'intent' => 'sale',
	'payer' => array(
		'payment_method' => 'credit_card',
		'funding_instruments' => array(
			0 => array(
				'credit_card_token' => array(
					'credit_card_id' => 'CARD-39N7854321M2DDC2',
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
			'description' => 'This is test payment'
		)
	)
);

try {
	$this->Paypal->chargeStoredCard($cardPayment);
} catch (Exception $e) {
	// $e->getMessage();
}	
```

## PayPal Adaptive Payments

Create an instance of the class with your PayPal credentials, including your Adaptive App ID and Adaptive username. For testing purposes, ensure `sandboxMode` is set to `true`.

```php
App::uses('Paypal', 'Paypal.Lib');

$this->Paypal = new Paypal(array(
	'sandboxMode' => true,
	'nvpUsername' => '{username}',
	'nvpPassword' => '{password}',
	'nvpSignature' => '{signature}',
	'adaptiveAppID' => '{adaptive app id}',
	'adaptiveUserID' => '{adaptive user id}'
));
```

### GetVerifiedStatus

The GetVerifiedStatus API operation lets you determine whether the specified PayPal account's status is verified or unverified.

```php
try {
	$this->Paypal->getVerifiedStatus('hello@gmail.com')
} catch (Exception $e) {
	// $e->getMessage();
}	
````
