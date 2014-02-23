# Paypal Plugin for CakePHP 2.x

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

You can either load them one by one or all of them in a single call:

```
CakePlugin::loadAll(); 		// Loads all plugins at once
CakePlugin::load('Paypal'); 	// Loads a single plugin named Paypal
```

Create an instance of the class with your PayPal credentials. For testing purposes, ensure `sandboxMode` is set to `true`.

```php
App::uses('Paypal', 'Paypal.Lib');

$this->Paypal = new Paypal(array(
	'sandboxMode' => true,
	'nvpUsername' => '{username}',
	'nvpPassword' => '{password}',
	'nvpSignature' => '{signature}',
	'oAuthClientId' => '{client_id'}, // RestFul API credentials
	'oAuthSecret' => {'secret'})); // RestFul API credentials
));
```

## SetExpressCheckout

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

$this->Paypal->setExpressCheckout($order);
```

## GetExpressCheckoutDetails

Once the customer has returned to your site (see `return` URL above) you can request their details with the token returned from the `SetExpressCheckout` method.

```php
$this->Paypal->getExpressCheckoutDetails($token);
```

## DoExpressCheckoutPayment

Complete the transaction using the same order details. The `$token` and `$payerId` will be returned from the `SetExpressCheckout` method.

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

$this->Paypal->doExpressCheckoutPayment($order, $token, $payerId);
```

## DoDirectPayment

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

$this->Paypal->doDirectPayment($payment);
```

## RefundTransaction

Refund a transction. Transactions can only be refunded up to 60 days after the completion date.

```php
$refund = array(
	'transactionId' => '96L684679W100181R' // Original PayPal Transcation ID
	'type' => 'Partial', // Full, Partial, ExternalDispute, Other
	'amount' => 30.00, // Amount to refund, only required if Refund Type is Partial, ExternalDispute, Other
	'note' => 'Refund because we are nice',  // Optional note to customer
	'reference' => 'abc123',  // Optional internal reference
	'currency' => 'USD'  // Defaults to GBP if not provided
);

$this->Paypal->refundTransaction($refund);
```

## StoreCreditCard

Store customer credit card, using the new RestFull API.

```php
$creditCard = array(
	'number' => '8762187612312' // Credit card number. Numeric characters only with no spaces or punctuation.
	'type' => 'Visa' // Credit card type. Valid types are: visa, mastercard, discover, amex
	'expireMonth' => '02',
	'expireYear' => '2016'
	'payerId' => '123', // A unique identifier that you can assign and track when storing a credit card or using a stored credit card.
	'cvv2' => '312',
	'firstName' => 'firstName',
	'lastName' => 'lastName'
);

$this->Paypal->storeCreditCard($creditCard);
```
