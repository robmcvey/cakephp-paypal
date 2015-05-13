<?php
// 
//  PayPalTest.php
//  cakephp-paypal
//  
//  Created by Rob Mcvey on 2015-05-13.
//  Copyright 2015 Rob McVey. All rights reserved.
// 
namespace CakePayPal\Test\TestCase\Payments;

//use CakePayPal\Payments; ???
use Cake\TestSuite\TestCase;

class PayPalTest extends TestCase 
{

/**
 * undocumented function
 *
 * @return void
 * @author Rob Mcvey
 **/
    public function testFoo() 
    {
        $expected = 4;
        $result = 4;
        $this->assertEquals($expected, $result);
    }

/**
 * undocumented function
 *
 * @return void
 * @author Rob Mcvey
 **/
    public function testClassic() 
    {
        $Classic = new \CakePayPal\Payments\Classic();
        $this->assertEquals('Classic', $Classic->foo());
    }

}