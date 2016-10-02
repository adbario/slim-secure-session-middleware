<?php

namespace AdBar;

/**
 * Session
 *
 * This class is a helper for setting and getting session values.
 * Sessions also use namespaces.
 */
class Session extends Dot
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
            $this->namespace = $namespace;
        }
        $this->setNamespace($this->namespace);
    }

    /**
     * Set session namespace
     *
     * @param string $namespace Session namespace
     */
    public function setNamespace($namespace)
    {
        if (!isset($_SESSION[$namespace])) {
            $_SESSION[$namespace] = [];
        }
        $this->setDataAsRef($_SESSION[$namespace]);
        $this->namespace = $namespace;
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
     * Set session value or array of values to specific namespace
     *
     * @param string     $namespace Session namespace
     * @param mixed      $key       Session key or an array of keys and values
     * @param mixed|null $value     Session value to set if key is not an array
     */
    public function setTo($namespace, $key, $value = null)
    {
        $oldNamespace = $this->namespace;
        $this->setNamespace($namespace);
        $this->set($key, $value);
        $this->setNamespace($oldNamespace);
    }

    /**
     * Add session value or array of values to specific namespace
     *
     * @param string     $namespace Session namespace
     * @param mixed      $key       Session key or an array of keys and values
     * @param mixed|null $value     Session value to add if key is not an array
     */
    public function addTo($namespace, $key, $value = null)
    {
        $oldNamespace = $this->namespace;
        $this->setNamespace($namespace);
        $this->add($key, $value);
        $this->setNamespace($oldNamespace);
    }

    /**
     * Get session value from specific namespace
     *
     * @param  string     $namespace Session namespace
     * @param  mixed|null $key       Session key
     * @param  mixed|null $default   Default value
     * @return mixed
     */
    public function getFrom($namespace, $key = null, $default = null)
    {
        if (isset($_SESSION[$namespace])) {
            $oldNamespace = $this->namespace;
            $this->setNamespace($namespace);
            $result = $this->get($key, $default);
            $this->setNamespace($oldNamespace);

            return $result;
        }
        return $default;
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
        if (isset($_SESSION[$namespace])) {
            $oldNamespace = $this->namespace;
            $this->setNamespace($namespace);
            $result = $this->has($key);
            $this->setNamespace($oldNamespace);

            return $result;
        }
    }

    /**
     * Delete session key or array of keys from specific namespace
     *
     * @param string $namespace Session namespace
     * @param mixed  $key       Session key or array of keys
     */
    public function deleteFrom($namespace, $key)
    {
        if (isset($_SESSION[$namespace])) {
            $oldNamespace = $this->namespace;
            $this->setNamespace($namespace);
            $this->delete($key);
            $this->setNamespace($oldNamespace);
        }
    }

    /**
     * Delete all session values from specific namespace
     *
     * @param  string     $namespace Session namespace
     * @param  mixed|null $key       Session key or array of keys
     * @param  boolean    $format    Format option
     */
    public function clearFrom($namespace, $key = null, $format = false)
    {
        if (isset($_SESSION[$namespace]) || $format === true) {
            $oldNamespace = $this->namespace;
            $this->setNamespace($namespace);
            $this->clear($key, $format);
            $this->setNamespace($oldNamespace);
        }
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
     * Update current session id
     *
     * @param boolean $deleteOld Delete old
     */
    public static function regenerateId($deleteOld = true)
    {
        if (self::isActive()) {
            session_regenerate_id($deleteOld);
        }
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
}
