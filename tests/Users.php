<?php
require_once 'PHPUnit/Framework.php';
require_once '../lib/common.php';

class Users extends PHPUnit_Framework_TestCase {
    public function testRegister() {
        $this->assertEquals(user_register(array('passwd'=>'abc', 'passwd_re'=>'ABC')), 2);
        $this->assertEquals(user_register(array('passwd'=>'abc', 'passwd_re'=>' abc')), 2);
        $this->assertEquals(user_register(array('passwd'=>''   , 'passwd_re'=>'', 'login'=>'abc')), 5);
        $this->assertEquals(user_register(array('passwd'=>'abc', 'passwd_re'=>'abc', 'login'=>'')), 5);
    }
}
?>
