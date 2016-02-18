<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Persistent;

/**
 * Describes token states
 */
class TokenState
{
    /** The token can be used to login. After it is used the state MUST be changed to INVALID */
    const VALID = 1;

    /** The token is already used or invalidated by user logout */
    const INVALID = 2;

    /** All valid user tokens are marked as VOID if the user changes his/her password, or there
        was an attempt to use an invalid token. */
    const VOID = 3;
}
