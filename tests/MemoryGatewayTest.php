<?php
/**
 * @package SugiPHP.Persistent
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Persistent\Tests;

use SugiPHP\Persistent\MemoryGateway;
use SugiPHP\Persistent\GatewayInterface;
use SugiPHP\Persistent\UserState;
use DateTime;

class MemoryGatewayTest extends \PHPUnit_Framework_TestCase
{
    public function testMemoryGatewayImplementsGatewayInterface()
    {
        $gateway = new MemoryGateway();
        $this->assertTrue($gateway instanceof GatewayInterface);
    }

    public function testFindTokenReturnsNullIfTokenNotFound()
    {
        $gateway = new MemoryGateway();
        $this->assertNull($gateway->findToken("123"));
    }

    public function testFindTokenReturnsTokenDataIfTokenIsFound()
    {
        $gateway = new MemoryGateway([["token" => "123", "somedata" => "foo"]]);
        $data = $gateway->findToken("123");
        $this->assertNotEmpty($data);
        $this->assertEquals("foo", $data["somedata"]);
    }

    public function testStoreToken()
    {
        $token = "abc";
        $date = new DateTime();
        $userId = 222;
        $state = 1;
        $gateway = new MemoryGateway();
        $gateway->storeToken($token, $userId, $date, $state);
        $data = $gateway->findToken($token);
        $this->assertNotEmpty($data);
    }

    public function testStoreTokenSavesUserAndExpiresDate()
    {
        $token = "abc";
        $date = new DateTime("+2 weeks");
        $userId = 222;
        $state = 1;
        $gateway = new MemoryGateway();
        $gateway->storeToken($token, $userId, $date, $state);
        $data = $gateway->findToken($token);
        $this->assertEquals($token, $data["token"]);
        $this->assertEquals($userId, $data["user_id"]);
        $this->assertEquals($date, new DateTime($data["expires"]));
        $this->assertEquals($state, $data["state"]);
    }

    public function testChangeTokenState()
    {
        $token = "abc";
        $date = new DateTime("+2 weeks");
        $userId = 222;
        $gateway = new MemoryGateway();
        $state = 1;
        $newState = 2;
        $gateway->storeToken($token, $userId, $date, $state);
        $gateway->changeTokenState($token, $newState);
        $data = $gateway->findToken($token);
        $this->assertEquals($newState, $data["state"]);
    }
}
