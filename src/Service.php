<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Persistent;

class Service
{
    /**
     * @var PDO
     */
    private $db;

    /**
     * @var Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var GatewayInterface
     */
    private $gateway;

    /**
     * @var CookieInterface
     */
    private $cookie;

    /**
     * @var Persistent
     */
    private $service;

    public function __construct()
    {
        $this->args = func_get_args();

        $this->db = $this->findImplementation("\\PDO");
        $this->logger = $this->findImplementation("\\Psr\\Log\\LoggerInterface");
        $this->gateway = $this->findImplementation("GatewayInterface");
        $this->cookie = $this->findImplementation("CookieInterface");
    }

    public function getPersistentUser()
    {
        return $this->getService()->getPersistentUser();
    }

    public function persistentLogin($userId)
    {
        return $this->getService()->persistentLogin($userId);
    }

    public function persistentLogout()
    {
        return $this->getService()->persistentLogout();
    }

    public function clearPersistenceForUser($userId)
    {
        return $this->getService()->clearPersistenceForUser($userId);
    }

    public function getService()
    {
        if (!$this->service) {
            $this->service = new Persistent($this->getGateway(), $this->getCookie());
            if ($this->logger) {
                $this->service->setLogger($this->logger);
            }
        }

        return $this->service;
    }

    public function getGateway()
    {
        if (!$this->gateway) {
            $this->gateway = new Gateway($this->db);
        }

        return $this->gateway;
    }

    public function getCookie()
    {
        if (!$this->cookie) {
            $this->cookie = new Cookie();
        }

        return $this->cookie;
    }

    private function findImplementation($interface)
    {
        if (strpos($interface, "\\") !== 0) {
            $interface = __NAMESPACE__."\\".$interface;
        }
        foreach ($this->args as $class) {
            if ($class instanceof $interface) {
                return $class;
            }
        }
    }
}
