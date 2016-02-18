<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Persistent;

use DateTime;

interface GatewayInterface
{
    /**
     * Finds a token previously stored in the DB
     *
     * @param string $token
     *
     * @return mixed Returns FALSE if the token is not found
     * ["user_id", "expires", "state"]
     */
    public function findToken($token);

    /**
     * Finds tokens for a particular user and a particular state
     *
     * @param integer $userId
     * @param integer $state
     */
    public function findUserTokens($userId, $state);

    /**
     * Stores a token in the DB
     *
     * @param string $token
     * @param integer $userId
     * @param DateTime $expires
     */
    public function storeToken($token, $userId, \DateTime $expires);

    /**
     * Change a token state.
     *
     * @param string $token
     * @param integer $state
     */
    public function changeTokenState($token, $state);
}
