<?php
/**
 * PaypalTest.php
 * Created by Rob Mcvey on 2013-07-04.
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Rob Mcvey on 2013-07-04.
 * @link          www.copify.com
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::uses('Paypal', 'Lib');

/**
 * PaypalTest class
 */
class PaypalTestCase extends CakeTestCase {

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
	}

/**
 * test getEndpoint
 *
 * @return void
 * @author Rob Mcvey
 **/
	public function testGetEndpoint() {
		$this->assertEqual("https://api.paypal.com" , Paypal::getEndpoint());
	}
	
}
	