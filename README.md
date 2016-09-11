# Slim Secure Session Middleware
Secure session middleware for [Slim 3 framework](http://www.slimframework.com/).
- longer and harder to guess session id's
- set session cookie path, domain and secure values automatically
- extend session lifetime after each user activity
- encrypt session data
- helper class to set and get session values easily

**If you're on shared host and use sessions for storing sensitive data, it's a good idea to store session files in your custom location and encrypt them.**

## Installation
    composer require adbario/slim-secure-session-middleware

## Configuration
Default settings:

    $settings = [
        'session' => [
            // Session cookie settings
            'name'           => 'slim_session',
            'lifetime'       => 1440,
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
            'encryption_key' => null
        ]
    ];

## Usage

### Middleware
Add middleware:

    $app->add(new \AdBar\SessionMiddleware(
        $app->getContainer(),
        $settings['session']
    ));

### Sessions
Sessions can be used by
- session helper class as an object, injected in application container
- session helper class statically
- reqular $_SESSION superglobal

#### Basic usage    
Session helper class can be used as an object, i.e. like this:

    $app->get('/', function (Request $request, Response $response) {
        $this->session->set('user', 'John');
    })->setName('home);

Or anywhere in your code:

    $session = new \AdBar\Session;
    $session->set('user', 'John');

#### Setting values
Set single value:

    $session->set('user', 'John');
    
    // Magic method
    $session->user = 'John';

Set multiple values as an array:
    
    $user = ['firstname' => 'John', 'lastname' => 'Smith'];
    $session->set($user);
    
    // Or just simply
    $session->set([
        'firstname' => 'John',
        'lastname'  => 'Smith
    ]);

#### Get value
    
    $session->get('user');
    
    // Set default value if key doesn't exist
    $session->get('user', 'some default user');
    
    // Magic method
    $session->user;

#### Check if value exists

    if ($session->has('user')) {
        // Do something...
    }
    
    // Magic method
    if (isset($session->user)) {
        // Do something...
    }

#### Delete value
    
    $session->delete('user');
    
    // Magic method
    unset($session->user);

#### Clear all session values

    $session->clear();

#### Destroy session completely
    
    $session->destroy();

#### Regenerate session id

    $session->regenerateId();

### Static methods

All methods can be used statically as well:

    use AdBar\Session;
    
    Session::set('name', 'John');
    Session::get('name');
