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


