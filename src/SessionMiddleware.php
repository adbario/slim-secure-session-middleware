<?php

namespace AdBar;

use RuntimeException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Session Middleware
 *
 * This middleware class starts a secure session and encrypts it if encryption key is set.
 * Session cookie path, domain and secure values are configured automatically by default.
 */
class SessionMiddleware
{
    /** @var array Default settings */
    protected $settings = [

        // Session cookie settings
        'name'           => 'slim_session',
        'lifetime'       => 24,
        'path'           => '/',
        'domain'         => null,
        'secure'         => false,
        'httponly'       => true,

        // Set session cookie path, domain and secure automatically
        'cookie_autoset' => true,

        // Path where session files are stored, PHP's default path will be used if set null
        'save_path'      => null,

        // Session cache limiter
        'cache_limiter'  => 'nocache',

        // Extend session lifetime after each user activity
        'autorefresh'    => false,

        // Encrypt session data if string is string is set
        'encryption_key' => null,

        // Session namespace
        'namespace'      => 'slim_app'
    ];

    /**
     * Constructor
     *
     * @param array $settings Session settings
     */
    public function __construct(array $settings = [])
    {
        $this->settings = array_merge($this->settings, $settings);
    }

    /**
     * Invoke middleware
     *
     * @param  Request  $request  PSR7 request
     * @param  Response $response PSR7 response
     * @param  callable $next     Next middleware
     * @return ResponseInterface
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        // Get settings from request
        if ($this->settings['cookie_autoset'] === true) {
            $this->settings['path']   = $request->getUri()->getBasePath() . '/';
            $this->settings['domain'] = $request->getUri()->getHost();
            $this->settings['secure'] = $request->getUri()->getScheme() === 'https' ? true : false;
        }

        // Start session
        $this->start($request);

        // Next middleware
        return $next($request, $response);
    }

    /**
     * Configure and start session
     *
     * @param Request $request PSR7 request
     */
    protected function start(Request $request)
    {
        $settings = $this->settings;

        // Enable strict mode
        ini_set('session.use_strict_mode', 1);

        // Use cookies and only cookies to store session id
        ini_set('session.use_cookies', 1);
        ini_set('session.use_only_cookies', 1);
        
        // Disable inserting session id into links automatically
        ini_set('session.use_trans_sid', 0);

        if (is_string($settings['lifetime'])) {
            // if lifetime is string, convert it to seconds
            $settings['lifetime'] = strtotime($settings['lifetime']) - time();
        } else {
            // if lifetime is minutes, convert it to seconds
            $settings['lifetime'] *= 60;
        }

        // Set number of seconds after which data will be seen as garbage
        if ($settings['lifetime'] > 0) {
            ini_set('session.gc_maxlifetime', $settings['lifetime']);
        }

        // Set path where session cookies are saved
        if (is_string($settings['save_path'])) {
            if (!is_writable($settings['save_path'])) {
                throw new RuntimeException('Session save path is not writable.');
            }
            ini_set('session.save_path', $settings['save_path']);
        }

        // Set session id strength
        if (version_compare(PHP_VERSION, '7.1', '<')) {
            // PHP version < 7.1
            ini_set('session.entropy_file', '/dev/urandom');
            ini_set('session.entropy_length', 128);
            ini_set('session.hash_function', 'sha512');
        } else {
            // PHP version >= 7.1
            ini_set('session.sid_length', 128);
        }

        // Set session cache limiter
        session_cache_limiter($settings['cache_limiter']);

        // Set session cookie name
        session_name($settings['name']);

        // Set session cookie parameters
        session_set_cookie_params(
            $settings['lifetime'],
            $settings['path'],
            $settings['domain'],
            $settings['secure'],
            $settings['httponly']
        );

        // Set session encryption
        if (is_string($settings['encryption_key'])) {
            // Add HTTP user agent to encryption key to strengthen encryption
            $settings['encryption_key'] .= md5($request->getHeaderLine('HTTP_USER_AGENT'));

            $handler = new \AdBar\SecureSessionHandler($settings['encryption_key']);
            session_set_save_handler($handler, true);
        }

        // Start session
        session_start();

        // Extend session lifetime
        if ($settings['autorefresh'] === true && isset($_COOKIE[$settings['name']])) {
            setcookie(
                $settings['name'],
                $_COOKIE[$settings['name']],
                time() + $settings['lifetime'],
                $settings['path'],
                $settings['domain'],
                $settings['secure'],
                $settings['httponly']
            );
        }
    }
}
