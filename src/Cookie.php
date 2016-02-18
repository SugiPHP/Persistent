<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Persistent;

use DateTime;

class Cookie implements CookieInterface
{
    /**
     * @var string
     */
    protected $cookieName;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $domain;

    /**
     * @var boolean
     */
    protected $secure;

    /**
     * @var boolean
     */
    protected $httpOnly;

    public function __construct($name = "REMEMBERME", $path = "/", $domain = "", $secure = false, $httpOnly = true)
    {
        $this->setCookieName($name);
        $this->path = $path;
        $this->domain = $domain;
        $this->secure = $secure;
        $this->httpOnly = $httpOnly;
    }

    public function set($token, DateTime $expires)
    {
        setcookie($this->cookieName, $token, $expires->format("U"), $this->path, $this->domain, $this->secure, $this->httpOnly);
    }

    public function get()
    {
        return isset($_COOKIE[$this->cookieName]) ? $_COOKIE[$this->cookieName] : null;
    }

    public function delete()
    {
        $this->set("", new DateTime("1970"));
    }

    protected function setCookieName($name)
    {
        $name = trim($name);
        if (empty($name)) {
            throw new InvalidArgumentException("Cookie name cannot be empty");
        }
        $this->cookieName = $name;

        return $this;
    }
}
