<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Persistent;

use DateTime;

interface CookieInterface
{
    public function set($token, DateTime $expires);

    public function get();

    public function delete();
}
