<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Persistent;

use DateTime;
use InvalidArgumentException;

class Persistent
{
    use LoggerTrait;

    /**
     * @var string Default expiration time
     */
    private $expireInterval = "+21 days";

    /**
     * @var Instance of Persistent\GatewayInterface
     */
    private $gateway;

    /**
     * @var Instance of Persistent\CookieInterface
     */
    private $cookie;

    public function __construct(GatewayInterface $gateway, CookieInterface $cookie)
    {
        $this->gateway = $gateway;
        $this->cookie = $cookie;
    }

    public function getPersistentUser()
    {
        // check for remember me cookie
        if (!$token = $this->cookie->get()) {
            // remove cookie
            $this->log("debug", "Persistent cookie with empty token");
            return ;
        }

        if (!$data = $this->gateway->findToken($token)) {
            // remove cookie
            $this->cookie->delete();
            $this->log("notice", "Token not found", ["token" => $token]);
            return ;
        }

        // user_id, state and expires are mandatory
        if (empty($data["user_id"]) || empty($data["state"]) || empty($data["expires"])) {
            $this->cookie->delete();
            $this->log("error", "Mandatory token data is missing", $data);
            return ;
        }

        // we need the state and the state should not be void
        if (TokenState::VOID == $data["state"]) {
            // remove cookie
            $this->cookie->delete();
            $this->log("info", "Token for user ID {$data["user_id"]} void");
            return ;
        }

        $userId = $data["user_id"];
        $expires = new DateTime($data["expires"]);
        if (new Datetime() > $expires) {
            $this->cookie->delete();
            $this->log("info", "Token expired", $data);
            return ;
        }

        // INVALID state is not good
        if (TokenState::INVALID == $data["state"]) {
            $this->cookie->delete();
            // void ALL active user tokens
            $this->clearPersistenceForUser($userId);
            $this->log("alert", "Token for user ID {$userId} invalid", $data);
            throw new InvalidTokenException("Invalid token. All your persistent sessions are invalidated for security reasons (possible cookie hijack)");
        }

        // invalidate the token and store a NEW token cookie with the same expiration time
        $this->gateway->changeTokenState($token, TokenState::INVALID);
        $newToken = $this->createToken();
        $this->gateway->storeToken($newToken, $userId, $expires, TokenState::VALID);
        $this->cookie->set($newToken, $expires);

        $this->log("info", "Persistent Login for user ID {$userId}");

        return $userId;
    }

    public function persistentLogin($userId)
    {
        $token = $this->createToken();
        $expires = new DateTime($this->expireInterval);

        // save data in the DB
        $this->gateway->storeToken($token, $userId, $expires, TokenState::VALID);

        // set cookie with the token
        $this->cookie->set($token, $expires);

        $this->log("debug", "Creating new persistent session for user ID {$userId}");
    }

    public function persistentLogout()
    {
        // get the token
        if ($token = $this->cookie->get()) {
            // invalidate token stored in the DB
            $this->gateway->changeTokenState($token, TokenState::INVALID);
            // remove cookie
            $this->cookie->delete();
        }
    }

    /**
     * Deactivates all unused user's tokens
     *
     * @param integer $userId
     */
    public function clearPersistenceForUser($userId)
    {
        if (!$tokens = $this->gateway->findUserTokens($userId, TokenState::VALID)) {
            return ;
        }
        foreach ($tokens as $t) {
            $this->gateway->changeTokenState($t["token"], TokenState::VOID);
        }
    }

    private function createToken()
    {
        // create new random token
        if (function_exists("random_bytes")) {
            $token = random_bytes(256 / 8);
        } else {
            $token = mt_rand() . uniqid(mt_rand(), true) . microtime(true) . mt_rand();
        }
        // SHA-512 produces 128 chars
        // SHA-256 produces 64 chars
        $token = hash("sha256", $token);
        // 86 chars
        // $token = trim(base64_encode($token), "=");

        return $token;
    }
}
