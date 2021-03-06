<?php
/**
 * @package SugiPHP.Persistent
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Persistent;

/**
 * Used only for tests
 */
class MemoryGateway implements GatewayInterface
{
    /**
     * @var array Token Data Storage
     */
    private $storage;

    public function __construct(array $storage = [])
    {
        $this->storage = $storage;
    }

    public function findToken($token)
    {
        foreach ($this->storage as $arr) {
            if ($arr["token"] == $token) {
                return $arr;
            }
        }
    }

    public function findUserTokens($userId, $state)
    {
        return array_filter($this->storage, function ($arr) use ($state, $userId) {
            return (($arr["user_id"] == $userId) && ($arr["state"] == $state));
        });
    }

    public function storeToken($token, $userId, \DateTime $expires, $state)
    {
        $this->storage[] = ["token" => $token, "user_id" => $userId, "expires" => $expires->format("Y-m-d H:i:s"), "state" => $state];
    }

    public function changeTokenState($token, $state)
    {
        foreach ($this->storage as &$arr) {
            if ($arr["token"] == $token) {
                $arr["state"] = $state;
                break;
            }
        }
    }
}
