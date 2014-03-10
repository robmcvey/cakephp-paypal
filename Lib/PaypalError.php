<?php
// 
//  PaypalError.php
//  cakephp-paypal
//  
//  Created by Rob Mcvey on 2014-03-10.
//  Copyright 2014 Rob McVey. All rights reserved.
// 
trait PaypalError {
	
/**
 * __construct
 *
 * @return void
 * @author Rob Mcvey
 **/
	protected function getLocalizedErrorMessages() {
		return array (
			10411 => __d('PaypalError', 'The Express Checkout transaction has expired and the transaction needs to be restarted'),
			10412 => __d('PaypalError', 'You may have made a second call for the same payment or you may have used the same invoice ID for seperate transactions.'),
			10422 => __d('PaypalError', 'Please use a different funcing source.'),
			10445 => __d('PaypalError', 'An error occured, please retry the transaction.'),
			10486 => __d('PaypalError', 'This transaction couldn\'t be completed. Redirecting to payment gateway'),
			10500 => __d('PaypalError', 'You have not agreed to the billing agreement.'),
			10501 => __d('PaypalError', 'The billing agreement is disabled or inactive.'),
			10502 => __d('PaypalError', 'The credit card used is expired.'),
			10505 => __d('PaypalError', 'The transaction was refused because the AVS response returned the value of N, and the merchant account is not able to accept such transactions.'),
			10507 => __d('PaypalError', 'The payment gateway account is restricted.'),
			10509 => __d('PaypalError', 'You must submit an IP address of the buyer with each API call.'),
			10511 => __d('PaypalError', 'The merchant selected a value for the PaymentAction field that is not supported.'),
			10519 => __d('PaypalError', 'The credit card field was blank.'),
			10520 => __d('PaypalError', 'The total amount and item amounts do not match.'),
			10534 => __d('PaypalError', 'The credit card entered is currently restricted by the payment gateway.'),
			10536 => __d('PaypalError', 'The merchant entered an invoice ID that is already associated with a transaction by the same merchant. Attempt with a new invoice ID'),
			10537 => __d('PaypalError', 'The transaction was declined by the country filter managed by the merchant.'),
			10538 => __d('PaypalError', 'The transaction was declined by the maximum amount filter managed by the merchant.'),
			10539 => __d('PaypalError', 'The transaction was declined by the payment gateway.'),
			10541 => __d('PaypalError', 'The credit card entered is currently restricted by the payment gateway.'),
			10544 => __d('PaypalError', 'The transaction was declined by the payment gateway.'),
			10545 => __d('PaypalError', 'The transaction was declined by payment gateway because of possible fraudulent activity.'),
			10546 => __d('PaypalError', 'The transaction was declined by payment gateway because of possible fraudulent activity on the IP address.'),
			10548 => __d('PaypalError', 'The merchant account attempting the transaction is not a business account.'),
			10549 => __d('PaypalError', 'The merchant account attempting the transaction is not able to process Direct Payment transactions. '),
			10550 => __d('PaypalError', 'Access to Direct Payment was disabled for your account.'),
			10552 => __d('PaypalError', 'The merchant account attempting the transaction does not have a confirmed email address with the payment gateway.'),
			10553 => __d('PaypalError', 'The merchant attempted a transaction where the amount exceeded the upper limit for that merchant.'),
			10554 => __d('PaypalError', 'The transaction was declined because of a merchant risk filter for AVS. Specifically, the merchant has set to decline transaction when the AVS returned a no match (AVS = N).'),
			10555 => __d('PaypalError', 'The transaction was declined because of a merchant risk filter for AVS. Specifically, the merchant has set the filter to decline transactions when the AVS returns a partial match.'),
			10556 => __d('PaypalError', 'The transaction was declined because of a merchant risk filter for AVS. Specifically, the merchant has set the filter to decline transactions when the AVS is unsupported.'),
			10747 => __d('PaypalError', 'The merchant entered an IP address that was in an invalid format. The IP address must be in a format such as 123.456.123.456.'),
			10748 => __d('PaypalError', 'The merchant\'s configuration requires a CVV to be entered, but no CVV was provided with this transaction.'),
			10751 => __d('PaypalError', 'The merchant provided an address either in the United States or Canada, but the state provided is not a valid state in either country.'),
			10752 => __d('PaypalError', 'The transaction was declined by the issuing bank, not the payment gateway. The merchant should attempt another card.'),
			10754 => __d('PaypalError', 'The transaction was declined by the payment gateway.'),
			10760 => __d('PaypalError', 'The merchant\'s country of residence is not currently supported to allow Direct Payment transactions.'),
			10761 => __d('PaypalError', 'The transaction was declined because the payment gateway is currently processing a transaction by the same buyer for the same amount. Can occur when a buyer submits multiple, identical transactions in quick succession.'),
			10762 => __d('PaypalError', 'The CVV provided is invalid. The CVV is between 3-4 digits long.'),
			10764 => __d('PaypalError', 'Please try again later. Ensure you have passed the correct CVV and address info for the buyer. If creating a recurring profile, please try again by passing a init amount of 0.'),
			12000 => __d('PaypalError', 'Transaction is not compliant due to missing or invalid 3-D Secure authentication values. Check ECI, ECI3DS, CAVV, XID fields.'),
			12001 => __d('PaypalError', 'Transaction is not compliant due to missing or invalid 3-D Secure authentication values. Check ECI, ECI3DS, CAVV, XID fields.'),
			15001 => __d('PaypalError', 'The transaction was rejected by the payment gateway because of excessive failures over a short period of time for this credit card.'),
			15002 => __d('PaypalError', 'The transaction was declined by payment gateway.'),
			15003 => __d('PaypalError', 'The transaction was declined because the merchant does not have a valid commercial entity agreement on file with the payment gateway.'),
			15004 => __d('PaypalError', 'The transaction was declined because the CVV entered does not match the credit card.'),
			15005 => __d('PaypalError', 'The transaction was declined by the issuing bank, not the payment gateway. The merchant should attempt another card.'),
			15006 => __d('PaypalError', 'The transaction was declined by the issuing bank, not the payment gateway. The merchant should attempt another card.'),
			15007 => __d('PaypalError', 'The transaction was declined by the issuing bank because of an expired credit card. The merchant should attempt another card.')
		);
	}
	
/**
 * Returns the localized version of a PayPal error code
 *
 * @return void
 * @author Rob Mcvey
 **/
	public function codeToLongMessage($code) {
		$localizedErrorMessages = $this->getLocalizedErrorMessages();
		if (array_key_exists($code, $localizedErrorMessages)) {
			return $localizedErrorMessages[$code];
		}
		return false;
	}
	
}
