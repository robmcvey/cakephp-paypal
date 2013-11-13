# Paypal Utility for CakePHP 2.3.x

A utility class to interact with Paypal's "classic" and new REST APIs.

### Requirements

* CakePHP 2.3.x
* A PayPal Website Payments Pro account

### Usuage

Create an instance of the class with your PayPal credentials. For testing purposes, ensure `sandboxMode` is set to `true`.

```php
$this->Paypal = new Paypal(array(
	'sandboxMode' => true,
	'nvpUsername' => '{username}',
	'nvpPassword' => '{password}',
	'nvpSignature' => '{signature}'
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
);

$this->Paypal->doDirectPayment($payment);
```