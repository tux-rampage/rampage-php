<?php
/**
 * LICENSE: $license_text$
 *
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2013 LUKA netconsult GmbH (www.luka.de)
 * @license   $license$
 */

namespace rampage\auth;

use RuntimeException;
use InvalidArgumentException;

class PasswordStrategy implements PasswordStrategyInterface
{
    const PASSWORD_BCRYPT = 1;
    const PASSWORD_DEFAULT = self::PASSWORD_BCRYPT;

    /**
     * @var int
     */
    protected $algorythm = self::PASSWORD_DEFAULT;

    /**
     * @var int
     */
    protected $cost = null;

    /**
     * @var string
     */
    protected $salt = null;

    /**
     * @param int $cost The crypt expensiveness (rounds)
     * @param string $salt The salt to use when crypting passwords
     * @param int $algorythm The algorythm to use
     */
    public function __construct($cost = null, $salt = null, $algorythm = null)
    {
        if ($algorythm) {
            $this->algorythm = $algorythm;
        }

        $this->cost = $cost;
        $this->salt = $salt;
    }

    /**
     * @return array
     */
    protected function getPasswordOptions()
    {
        $options = array(
            'cost' => (int)$this->cost,
            'salt' => $this->salt
        );

        $options = array_filter($options);
        return $options;
    }

    /**
     * Hash the password using the specified algorithm
     *
     * @param string $password The password to hash
     * @param int $algo The algorithm to use (Defined by PASSWORD_* constants)
     * @param array $options The options for the algorithm to use
     *
     * @return string|false The hashed password, or false on error.
     */
    protected function passwordHash($password, $algo, array $options = array())
    {
        if (!function_exists('crypt')) {
            throw new RuntimeException('Crypt must be loaded for password_hash to function');
        }

        if (!is_string($password)) {
            throw new InvalidArgumentException('Password must be a string');
        }

        if (!is_int($algo)) {
            throw new InvalidArgumentException(__METHOD__ . '() expects parameter 2 to be long, ' . gettype($algo) . ' given');
        }

        switch ($algo) {
            case self::PASSWORD_BCRYPT:
                // Note that this is a C constant, but not exposed to PHP, so we don't define it here.
                $cost = 10;
                if (isset($options['cost'])) {
                    $cost = (int)$options['cost'];
                    if ($cost < 4 || $cost > 31) {
                        throw new InvalidArgumentException(sprintf('Invalid bcrypt cost parameter specified: %d', $cost));
                    }
                }

                // The length of salt to generate
                $rawSaltLen = 16;
                // The length required in the final serialization
                $requiredSaltLen = 22;
                $hashFormat = sprintf("$2y$%02d$", $cost);
                break;

            default:
                throw new InvalidArgumentException(sprintf('Unknown password hashing algorithm: %s', $algo));
        }

        if (isset($options['salt'])) {
            switch (gettype($options['salt'])) {
                case 'NULL':
                case 'boolean':
                case 'integer':
                case 'double':
                case 'string':
                    $salt = (string)$options['salt'];
                    break;

                case 'object':
                    if (method_exists($options['salt'], '__tostring')) {
                        $salt = (string) $options['salt'];
                        break;
                    }

                case 'array':
                case 'resource':
                default:
                    throw new InvalidArgumentException('Non-string salt parameter supplied');
            }

            if (strlen($salt) < $requiredSaltLen) {
                throw new InvalidArgumentException(sprintf("Provided salt is too short: %d expecting %d", strlen($salt), $requiredSaltLen));
            } elseif (0 == preg_match('#^[a-zA-Z0-9./]+$#D', $salt)) {
                $salt = str_replace('+', '.', base64_encode($salt));
            }
        } else {
            $buffer = '';
            $buffer_valid = false;

            if (function_exists('mcrypt_create_iv') && !defined('PHALANGER')) {
                $buffer = mcrypt_create_iv($rawSaltLen, MCRYPT_DEV_URANDOM);
                if ($buffer) {
                    $buffer_valid = true;
                }
            }

            if (!$buffer_valid && function_exists('openssl_random_pseudo_bytes')) {
                $buffer = openssl_random_pseudo_bytes($rawSaltLen);
                if ($buffer) {
                    $buffer_valid = true;
                }
            }

            if (!$buffer_valid && is_readable('/dev/urandom')) {
                $f = fopen('/dev/urandom', 'r');
                $read = strlen($buffer);
                while ($read < $rawSaltLen) {
                    $buffer .= fread($f, $rawSaltLen - $read);
                    $read = strlen($buffer);
                }
                fclose($f);
                if ($read >= $rawSaltLen) {
                    $buffer_valid = true;
                }
            }

            if (!$buffer_valid || strlen($buffer) < $rawSaltLen) {
                $bl = strlen($buffer);
                for ($i = 0; $i < $rawSaltLen; $i++) {
                    if ($i < $bl) {
                        $buffer[$i] = $buffer[$i] ^ chr(mt_rand(0, 255));
                    } else {
                        $buffer .= chr(mt_rand(0, 255));
                    }
                }
            }

            $salt = str_replace('+', '.', base64_encode($buffer));
        }

        $salt = substr($salt, 0, $requiredSaltLen);
        $hash = $hashFormat . $salt;
        $ret = crypt($password, $hash);

        if (!is_string($ret) || strlen($ret) <= 13) {
            return false;
        }

        return $ret;
    }

    /**
     * Get information about the password hash. Returns an array of the information
     * that was used to generate the password hash.
     *
     * array(
     *     'algo' => 1,
     *         'algoName' => 'bcrypt',
     *         'options' => array(
     *         'cost' => 10,
     *     ),
     * )
     *
     * @param string $hash The password hash to extract info from
     * @return array The array of information about the hash.
     */
    private function passwordGetInfo($hash)
    {
        $return = array(
            'algo' => 0,
            'algoName' => 'unknown',
            'options' => array(),
        );

        if (substr($hash, 0, 4) == '$2y$' && strlen($hash) == 60) {
            $return['algo'] = PASSWORD_BCRYPT;
            $return['algoName'] = 'bcrypt';
            list($cost) = sscanf($hash, "$2y$%d$");
            $return['options']['cost'] = $cost;
        }

        return $return;
    }

    /**
     * Determine if the password hash needs to be rehashed according to the options provided
     *
     * If the answer is true, after validating the password using password_verify, rehash it.
     *
     * @param string $hash The hash to test
     * @param int $algo The algorithm used for new password hashes
     * @param array $options The options array passed to password_hash
     * @return boolean True if the password needs to be rehashed.
     */
    protected function passwordNeedsRehash($hash, $algo, array $options = array())
    {
        $info = $this->passwordGetInfo($hash);

        if ($info['algo'] != $algo) {
            return true;
        }

        switch ($algo) {
            case self::PASSWORD_BCRYPT:
                $cost = isset($options['cost']) ? $options['cost'] : 10;
                if ($cost != $info['options']['cost']) {
                    return true;
                }
                break;
        }

        return false;
    }

    /**
     * Verify a password against a hash using a timing attack resistant approach
     *
     * @param string $password The password to verify
     * @param string $hash The hash to verify against
     *
     * @return boolean If the password matches the hash
     */
    protected function passwordVerify($password, $hash)
    {
        // PHP 5.5 compatible implementation
        if (!function_exists('crypt')) {
            throw new RuntimeException("Crypt must be loaded for password_verify to function");
        }

        $ret = crypt($password, $hash);
        if (!is_string($ret) || strlen($ret) != strlen($hash) || strlen($ret) <= 13) {
            return false;
        }

        $status = 0;

        for ($i = 0; $i < strlen($ret); $i++) {
            $status |= (ord($ret[$i]) ^ ord($hash[$i]));
        }

        return ($status === 0);
    }

	/**
     * {@inheritdoc}
     * @see \rampage\auth\PasswordStrategyInterface::createPasswordHash()
     */
    public function createPasswordHash($password)
    {
        return $this->passwordHash($password, $this->algorythm, $this->getPasswordOptions());
    }

	/**
     * {@inheritdoc}
     * @see \rampage\auth\PasswordStrategyInterface::isRehashRequired()
     */
    public function isRehashRequired($hash)
    {
        return $this->passwordNeedsRehash($hash, $this->algorythm, $this->getPasswordOptions());
    }

	/**
     * {@inheritdoc}
     * @see \rampage\auth\PasswordStrategyInterface::verify()
     */
    public function verify($password, $passwordHash)
    {
        return $this->passwordVerify($password, $passwordHash);
    }
}
