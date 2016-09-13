<?php

namespace AdBar;

/**
 * Session
 *
 * This class is a helper for setting and getting session values.
 * Sessions also use namespaces.
 */
class Session
{
    /** @var string Session namespace */
    protected $namespace = 'slim_app';

    /**
     * Constructor
     *
     * @param string|null $namespace Session namespace
     */
    public function __construct($namespace = null)
    {
        if (is_string($namespace)) {
            $this->setNamespace($namespace);
        }
        $this->validateNamespace($this->namespace);
    }

    /**
     * Set session value or multiple values as an array
     *
     * @param string|array $key   Session key or an array of keys and values
     * @param mixed|null   $value Session value or null if $key is an array
     */
    public function set($key, $value = null)
    {
        if (is_array($key)) {
            $_SESSION[$this->namespace] = array_merge($_SESSION[$this->namespace], $key);
        } else {
            $_SESSION[$this->namespace][$key] = $value;
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
    public function get($key = null, $default = null)
    {
        return $key === null
            ? $_SESSION[$this->namespace]
            : $this->has($key) ? $_SESSION[$this->namespace][$key] : $default;
    }

    /**
     * Check if session value is set
     *
     * @param  string $key Session key
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $_SESSION[$this->namespace]);
    }

    /**
     * Delete session value
     *
     * @param string $key Session key
     */
    public function delete($key)
    {
        if ($this->has($key)) {
            unset($_SESSION[$this->namespace][$key]);
        }
    }

    /**
     * Delete all session values
     */
    public function clear()
    {
        $_SESSION[$this->namespace] = [];
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
     * Set session namespace
     *
     * @param string $namespace Session namespace
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
        $this->validateNamespace($namespace);
    }

    /**
     * Get session namespace
     *
     * @return string Session namespace
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Create array for namespace if it doesn't exist
     */
    protected function validateNamespace($namespace)
    {
        if (!isset($_SESSION[$namespace])) {
            $_SESSION[$namespace] = [];
        }
    }

    /**
     * Set session value(s) to specific namespace
     *
     * @param string       $namespace Session namespace
     * @param string|array $key       Session key or an array of keys and values
     * @param mixed|null   $value     Session value or null if $key is an array
     */
    public function setTo($namespace, $key, $value = null)
    {
        if (is_array($key)) {
            $this->validateNamespace($namespace);
            $_SESSION[$namespace] = array_merge($_SESSION[$namespace], $key);
        } else {
            $_SESSION[$namespace][$key] = $value;
        }
    }

    /**
     * Get session value from specific namespace
     *
     * @param  string $namespace Session namespace
     * @param  string $key       Session key
     * @param  mixed  $default   Default value
     * @return mixed
     */
    public function getFrom($namespace, $key = null, $default = null)
    {
        $this->validateNamespace($namespace);

        return $key === null
            ? $_SESSION[$namespace]
            : $this->hasIn($namespace, $key) ? $_SESSION[$namespace][$key] : $default;
    }

    /**
     * Check if session value is set in specific namespace
     *
     * @param  string $namespace Session namespace
     * @param  string $key       Session key
     * @return bool
     */
    public function hasIn($namespace, $key)
    {
        $this->validateNamespace($namespace);

        return array_key_exists($key, $_SESSION[$namespace]);
    }

    /**
     * Delete session value from specific namespace
     *
     * @param string $namespace Session namespace
     * @param string $key       Session key
     */
    public function deleteFrom($namespace, $key)
    {
        if ($this->hasIn($namespace, $key)) {
            unset($_SESSION[$namespace][$key]);
        }
    }

    /**
     * Delete all session values from specific namespace
     *
     * @param  string $namespace Session namespace
     */
    public function clearFrom($namespace)
    {
        $_SESSION[$namespace] = [];
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
