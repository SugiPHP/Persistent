<?php
/**
 * @package SugiPHP.Persistent
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Persistent\Tests;

use SugiPHP\Persistent\CookieInterface;
use SugiPHP\Persistent\Cookie;
use SugiPHP\Persistent\TokenState;
use SugiPHP\Persistent\Persistent as Service;
use SugiPHP\Persistent\GatewayInterface;
use SugiPHP\Persistent\InvalidTokenException;
use SugiPHP\Persistent\MemoryGateway;

class PersistentLoginTest extends \PHPUnit_Framework_TestCase
{
    private $cookie;
    private $storage;
    private $service;

    public function setUp()
    {
        $this->storage = new MemoryGateway();
        $this->cookie = $this->getMock("SugiPHP\Persistent\Cookie");
        $this->service = new Service($this->storage, $this->cookie);
    }

    public function testPersistentLoginSavesData()
    {
        $userId = 1;
        $state = TokenState::VALID;

        $data = $this->storage->findUserTokens($userId, $state);
        $this->assertCount(0, $data);

        $this->service->persistentLogin($userId);
        $data = $this->storage->findUserTokens($userId, $state);
        $this->assertCount(1, $data);
    }

    public function testPersistentSetsCookie()
    {
        $userId = 1;
        $state = TokenState::VALID;

        // Cookie is set
        $this->cookie->expects($this->once())->method("set");

        $this->service->persistentLogin($userId);
    }

    public function testPersistentLoginSavesTokenUserExpirationTimeAndState()
    {
        $userId = 333;
        $state = TokenState::VALID;

        $this->service->persistentLogin($userId);
        $data = $this->storage->findUserTokens($userId, $state);
        $this->assertNotEmpty($data[0]["token"]);
        $this->assertNotEmpty($data[0]["expires"]);
        $this->assertEquals($state, $data[0]["state"]);
        $this->assertEquals($userId, $data[0]["user_id"]);
    }
}
