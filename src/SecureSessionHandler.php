<?php

namespace AdBar;

use SessionHandler;
use RuntimeException;

/**
 * Safe Session Handler
 *
 * This class etends PHP's native session handler and encrypts
 * session data with custom key and HTTP user agent.
 */
class SecureSessionHandler extends SessionHandler
{
    /** @var string Encryption key */
    private $key;

    /**
     * Constructor
     * @param string $key Encryption key
     */
    public function __construct($key)
    {
        if (!extension_loaded('openssl')) {
            throw new RuntimeException('OpenSSL needs to be available to encrypt session data.');
        }

        $this->key = $key;
    }

    /**
     * Read session data
     * @param  int $id Session id
     * @return string
     */
    public function read($sid)
    {
        $data = parent::read($sid);

        return ($data) ? $this->decrypt($data, $this->key) : '';
    }

    /**
     * Write session data
     * @param  int $id   Session id
     * @param  string $data Session data
     */
    public function write($sid, $data)
    {
        $data = $this->encrypt($data, $this->key);

        return parent::write($sid, $data);
    }

    /**
     * Encrypt session data
     * @param  string $data     Session data
     * @param  string $password Encryption key
     * @return string
     */
    private function encrypt($data, $key)
    {
        $salt = random_bytes(16);

        $salted = hash('sha512', $key . $salt, true);
        $key = substr($salted, 0, 32);
        $iv  = substr($salted, 32, 16);

        $encrypted_data = openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($salt . $encrypted_data);
    }

    /**
     * Decrypt session data
     * @param  string $edata    Encrypted session data
     * @param  string $password Encryption key
     * @return string
     */
    private function decrypt($data, $key)
    {
        $data = base64_decode($data);
        $salt = substr($data, 0, 16);
        $data = substr($data, 16);

        $salted = hash('sha512', $key . $salt, true);
        $key = substr($salted, 0, 32);
        $iv  = substr($salted, 32, 16);

        return openssl_decrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    }
}
