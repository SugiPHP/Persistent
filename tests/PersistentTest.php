<?php
/**
 * @package SugiPHP.Persistent
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Persistent\Tests;

include __DIR__."/MemoryGateway.php";

use SugiPHP\Persistent\CookieInterface;
use SugiPHP\Persistent\Cookie;
use SugiPHP\Persistent\TokenState;
use SugiPHP\Persistent\Persistent as Service;
use SugiPHP\Persistent\GatewayInterface;
use SugiPHP\Persistent\InvalidTokenException;
use SugiPHP\Persistent\Tests\MemoryGateway;

class PersistentTest extends \PHPUnit_Framework_TestCase
{
    private $cookie;
    private $storage;
    private $service;

    public function setUp()
    {
        $this->storage = $this->getMock("SugiPHP\Persistent\Tests\MemoryGateway");
        $this->cookie = $this->getMock("SugiPHP\Persistent\Cookie");
        $this->service = new Service($this->storage, $this->cookie);
    }

    public function testCreate()
    {
        $this->assertTrue($this->service instanceof Service);
    }

    public function testGetPersistentUserReturnsNullIfNoCookieFound()
    {
        $this->assertNull($this->service->getPersistentUser());
    }

    public function testGetPersistentUserReturnsNullIfNoTokenFoundInTheStorage()
    {
        $this->cookie->expects($this->once())->method("get")->will($this->returnValue('wrongtoken'));
        $this->assertNull($this->service->getPersistentUser());
    }

    public function testGetPersistentUserReturnsNullIfExpireIsMissingFromTheStorageData()
    {
        $token = "123";
        $userId = 1;
        $date = date("Y-m-d H:i:s", strtotime("+1 week"));
        $state = TokenState::VALID;
        $this->storage->expects($this->once())
            ->method("findToken")
            ->will($this->returnValue(["token" => $token, "user_id" => $userId, "state" => $state]));
        $this->cookie->expects($this->once())->method("get")->will($this->returnValue($token));
        $this->cookie->expects($this->once())->method("delete");
        $this->assertNull($this->service->getPersistentUser());
    }

    public function testGetPersistentUserReturnsNullIfUserIdIsMissingFromTheStorageData()
    {
        $token = "123";
        $userId = 1;
        $date = date("Y-m-d H:i:s", strtotime("+1 week"));
        $state = TokenState::VALID;
        $this->storage->expects($this->once())
            ->method("findToken")
            ->will($this->returnValue(["token" => $token, "state" => $state, "expires" => $date]));
        $this->cookie->expects($this->once())->method("get")->will($this->returnValue($token));
        $this->cookie->expects($this->once())->method("delete");
        $this->assertNull($this->service->getPersistentUser());
    }

    public function testGetPersistentUserReturnsNullIfStateIsMissingFromTheStorageData()
    {
        $token = "123";
        $userId = 1;
        $date = date("Y-m-d H:i:s", strtotime("+1 week"));
        $state = TokenState::VALID;
        $this->storage->expects($this->once())
            ->method("findToken")
            ->will($this->returnValue(["token" => $token, "user_id" => $userId, "expires" => $date]));
        $this->cookie->expects($this->once())->method("get")->will($this->returnValue($token));
        $this->cookie->expects($this->once())->method("delete");
        $this->assertNull($this->service->getPersistentUser());
    }

    public function testGetPersistentUserReturnsNullIfStateIsVoid()
    {
        $token = "123";
        $userId = 1;
        $date = date("Y-m-d H:i:s", strtotime("+1 week"));
        $state = TokenState::VOID;
        $this->storage->expects($this->once())
            ->method("findToken")
            ->will($this->returnValue(["token" => $token, "user_id" => $userId, "state" => $state, "expires" => $date]));
        $this->cookie->expects($this->once())->method("get")->will($this->returnValue($token));
        $this->cookie->expects($this->once())->method("delete");
        $this->assertNull($this->service->getPersistentUser());
    }

    public function testGetPersistentUserReturnsNullIfExpiresIsInThePast()
    {
        $token = "123";
        $userId = 1;
        $date = date("Y-m-d H:i:s", strtotime("-1 week"));
        $state = TokenState::VALID;
        $this->storage->expects($this->once())
            ->method("findToken")
            ->will($this->returnValue(["token" => $token, "user_id" => $userId, "state" => $state, "expires" => $date]));
        $this->cookie->expects($this->once())->method("get")->will($this->returnValue($token));
        $this->cookie->expects($this->once())->method("delete");
        $this->assertNull($this->service->getPersistentUser());
    }

    /**
     * @expectedException SugiPHP\Persistent\InvalidTokenException
     */
    public function testGetPersistentUserThrowsExceptionIfTokenStateIsInvalid()
    {
        $token = "123";
        $userId = 1;
        $date = date("Y-m-d H:i:s", strtotime("+1 week"));
        $state = TokenState::INVALID;
        $this->storage->expects($this->once())->method("findToken")
            ->will($this->returnValue(["token" => $token, "user_id" => $userId, "state" => $state, "expires" => $date]));

        $this->storage->expects($this->once())->method("findUserTokens");

        $this->cookie->expects($this->once())->method("get")->will($this->returnValue($token));
        $this->cookie->expects($this->once())->method("delete");

        // $this->expectException(InvalidTokenException::class);

        $this->assertNull($this->service->getPersistentUser());
    }

    public function testGetPersistentUserReturnsUserId()
    {
        $token = "123";
        $userId = 1;
        $date = date("Y-m-d H:i:s", strtotime("+1 week"));
        $state = TokenState::VALID;
        $this->storage->expects($this->once())
            ->method("findToken")
            ->will($this->returnValue(["token" => $token, "user_id" => $userId, "state" => $state, "expires" => $date]));

        $this->storage->expects($this->once())->method("changeTokenState")->with($token, TokenState::INVALID);
        $this->storage->expects($this->once())->method("storeToken");
        $this->cookie->expects($this->once())->method("get")->will($this->returnValue($token));
        $this->cookie->expects($this->once())->method("set");

        $this->assertEquals($userId, $this->service->getPersistentUser());
    }
}
