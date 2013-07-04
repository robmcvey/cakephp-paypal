<?php
/**
 * Paypal.php
 * Created by Rob Mcvey on 2013-07-04.
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Rob Mcvey on 2013-07-04.
 * @link          www.copify.com
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * Paypal class
 */
class Paypal {
	
/**
 * Live API endpoint
 */
	public static $base_path = "https://api.paypal.com";
	
/**
 * Returns the live Paypal API endpoint
 *
 * @return void
 * @author Rob Mcvey
 **/
	public static function getEndpoint() {
		return self::$base_path;
	}
	
}
