<?php

namespace AdBar;

/**
 * Session
 *
 * This class is a helper for setting and getting session values.
 */
class Session
{
    /**
     * Set session value or multiple values as an array
     *
     * @param string|array $key   Session key or an array of keys and values
     * @param mixed|null   $value Session value or null if $key is an array
     */
    public static function set($key, $value = null)
    {
        if (is_array($key)) {
            $_SESSION = array_merge($_SESSION, $key);
        } else {
            $_SESSION[$key] = $value;
        }
    }

    /**
     * Get session value, default value if session key doesn't exist
     * or all session values if $key is null
     *
     * @param  string $key     Session key
     * @param  mixed  $default Default value
     * @return mixed
     */
    public static function get($key = null, $default = null)
    {
        return $key === null
            ? $_SESSION
            : self::has($key) ? $_SESSION[$key] : $default;
    }

    /**
     * Check if session value is set
     *
     * @param  string $key Session key
     * @return bool
     */
    public static function has($key)
    {
        return array_key_exists($key, $_SESSION);
    }

    /**
     * Delete session value
     *
     * @param string $key Session key
     */
    public static function delete($key)
    {
        if (self::has($key)) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Delete all session values
     */
    public static function clear()
    {
        $_SESSION = [];
    }

    /**
     * Check if session is active
     *
     * @return bool
     */
    public static function isActive()
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    /**
     * Destroy session
     */
    public static function destroy()
    {
        if (self::isActive()) {
            $_SESSION = [];
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
            session_destroy();
        }
    }

    /**
     * Update current session id
     */
    public static function regenerateId()
    {
        if (self::isActive()) {
            session_regenerate_id(true);
        }
    }

    /**
     * Magic methods
     */
    public function __set($key, $value = null)
    {
        $this->set($key, $value);
    }
    public function __get($key)
    {
        return $this->get($key);
    }
    public function __isset($key)
    {
        return $this->has($key);
    }
    public function __unset($key)
    {
        $this->delete($key);
    }
}
