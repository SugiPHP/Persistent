<?php
/**
 * @package SugiPHP.Auth2
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Persistent;

use PDO;
use DateTime;

/*
 * PDO based Persistent Gateway
 *
 * DB Schema:

CREATE TABLE IF NOT EXISTS persistent
(
    token      VARCHAR(250) NOT NULL PRIMARY KEY,
    user_id    INTEGER NOT NULL,
    expires    TIMESTAMP NOT NULL,
    state      INTEGER NOT NULL
);

 */
class PDOGateway implements GatewayInterface
{
    /**
     * PDO handler
     */
    protected $db;

    protected $table = "persistent";
    protected $fields = "token, user_id, expires, state";

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * @see PersistentGatewayInterface::findToken()
     *
     * Finds a token previously stored in the DB
     *
     * @param string $token
     *
     * @return mixed Returns FALSE if the token is not found
     * ["user_id", "expires", "state"]
     */
    public function findToken($token)
    {
        $sql = "SELECT {$this->fields} FROM {$this->table} WHERE token = :token";
        $sth = $this->db->prepare($sql);
        $sth->bindValue("token", $token);
        $sth->execute();

        return $sth->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @see PersistentGatewayInterface::storeToken()
     *
     * Stores a token in the DB
     *
     * @param string $token
     * @param integer $userId
     * @param DateTime $expires
     */
    public function storeToken($token, $userId, \DateTime $expires)
    {
        $sql = "INSERT INTO {$this->table} (token, user_id, expires, state) VALUES (:token, :user_id, :expires, :state)";
        $sth = $this->db->prepare($sql);
        $sth->bindValue("token", $token);
        $sth->bindValue("user_id", (int) $userId, PDO::PARAM_INT);
        $sth->bindValue("expires", $expires->format("Y-m-d H:i:s"));
        $sth->bindValue("state", TokenState::VALID, PDO::PARAM_INT);
        $sth->execute();
    }

    /**
     * @see PersistentGatewayInterface::changeTokenState()
     *
     * Change a token state.
     *
     * @param string $token
     * @param integer $state
     */
    public function changeTokenState($token, $state)
    {
        $sql = "UPDATE {$this->table} SET state = :state, expires = expires WHERE token = :token";
        $sth = $this->db->prepare($sql);
        $sth->bindValue("state", (int) $state, PDO::PARAM_INT);
        $sth->bindValue("token", $token);
        $sth->execute();
    }

    /**
     * @see PersistentGatewayInterface::findUserTokens()
     *
     * Finds tokens for a particular user and a particular state
     *
     * @param integer $userId
     * @param integer $state
     */
    public function findUserTokens($userId, $state)
    {
        $sql = "SELECT {$this->fields} FROM {$this->table} WHERE user_id = :user_id AND state = :state";
        $sth = $this->db->prepare($sql);
        $sth->bindValue("state", (int) $state, PDO::PARAM_INT);
        $sth->bindValue("user_id", (int) $userId, PDO::PARAM_INT);
        $sth->execute();

        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }
}
