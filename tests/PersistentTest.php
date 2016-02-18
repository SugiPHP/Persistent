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
use SugiPHP\Persistent\Tests\MemoryGateway as Gateway;

class PersistentTest extends \PHPUnit_Framework_TestCase
{
    const DEMODATA = [
        ["token" => "123", "user_id" => 1, "state" => TokenState::INVALID],
        ["token" => "abc", "user_id" => 1, "state" => TokenState::VALID],
        ["token" => "qwe", "user_id" => 2, "state" => TokenState::VOID],
    ];

    private $cookie;
    private $gateway;
    private $service;

    public function setUp()
    {
        $data = self::DEMODATA;

        $this->gateway = new Gateway($data);
        $this->cookie = new Cookie();
        $this->service = new Service($this->gateway, $this->cookie);
    }

    public function testCreate()
    {
        $this->assertTrue($this->service instanceof Service);
    }

    public function testGetPersistentUserReturnsNullIfNoCookieFound()
    {
        $this->assertNull($this->service->getPersistentUser());
    }
}
