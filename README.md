# Slim Secure Session Middleware
Secure session middleware for [Slim 3 framework](http://www.slimframework.com/).
- longer and more secure session id's
- session data encryption
- set session cookie path, domain and secure values automatically
- extend session lifetime after each user activity
- helper class to handle session values easily with namespaces and dot notation ([Dot - PHP dot notation array access](https://github.com/adbario/php-dot-notation))

**If you're on shared host and use sessions for storing sensitive data, it's a good idea to store session files in your custom location and encrypt them.**

## Installation

Via composer:

    composer require adbario/slim-secure-session-middleware

## Configuration
Create an array of session settings in you application settings. **Please note that session lifetime is defined in minutes, not in seconds.** Default settings for session:

    $settings = [
        'session' => [
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
    
            // Encrypt session data if string is set
            'encryption_key' => null,
            
            // Session namespace
            'namespace'      => 'slim_app'
        ]
    ];

## Usage

### Application
When creating application, inject all settings:
    
    $app = new \Slim\App(['settings' => $settings]);

### Middleware
Add middleware with session settings:

    $app->add(new \AdBar\SessionMiddleware($settings['session']));

### Sessions
This package comes with session helper class, but if you wish to use only PHP session superglobal, then you can skip the rest of this guide and just enjoy coding! Session security configuration is done within middleware, so by using native superglobal your session is still secure (and encrypted if you set up encryption key in settings).

Session helper class uses namespaces for sessions, so basically session variables are always inside an array and array's key is the namespace. If you insert key 'user' with value 'John' into namespace 'users', superglobal $_SESSION looks like this:
    
    Array
    (
        [users] => Array
            (
                [user] => John
            )
    )

If you don't need session namespaces in your application, then you can just ignore all namespace related parts on this guide and set namespace name in settings to null (it will default to namespace "slim_app").

#### Basic usage

You can inject session helper class in application container:

    $container['session'] = function ($container) {
        return new \AdBar\Session(
            $container->get('settings')['session']['namespace']
        );
    };

If session helper class is injected in application container and can be used as an object, i.e. like this:

    $app->get('/', function (Request $request, Response $response) {
        // Namespace is now picked up from settings
        $this->session->set('user', 'John');
    })->setName('home);

Or anywhere in your code:

    $session = new \AdBar\Session('my_namespace');
    // Namespace is now 'my_namespace'
    $session->set('user', 'John');

If you don't use session class from container and don't inject namespace when creating session object, then namespace is always 'slim_app':

    $session = new \AdBar\Session;
    // Namespace is now 'slim_app'
    $session->set('user', 'John');

#### Setting values
Set single value:

    $session->set('user', 'John');
    
    // Array style
    $session['user'] = 'John';
    
    // Magic method
    $session->user = 'John';
    
    // Set value to specific namespace
    $session->setTo('my_namespace', 'user', 'John');

    // Dot notation can be used with all methods except magic method:
    $session->set('user.firstname', 'John');
    $session['user.firstname'] = 'John';
    $session->setTo('my_namespace', 'user.firstname', 'John');

Set multiple values at once:
    
    $user = ['firstname' => 'John', 'lastname' => 'Smith'];
    $session->set($user);
    
    // Or just simply
    $session->set([
        'firstname' => 'John',
        'lastname'  => 'Smith
    ]);
    
    // Set values to specific namespace
    $session->setTo('my_namespace', $user);
    
    // Dot notation
    $session->set([
        'user.firstname' => 'John',
        'user.lastname'  => 'Smith'
    ]);

#### Get value
    
    echo $session->get('user');
    
    // Get default value if key doesn't exist
    echo $session->get('user', 'some default user');
    
    // Array style
    echo $session['user'];
    
    // Magic method
    echo $session->user;
    
    // From specific namespace
    echo $session->getFrom('my_namespace', 'user');
    
    // Dot notation
    echo $session->get('user.firstname');

#### Add value

    $session->add('users', 'Mary');
    
    // Dot notation
    $session->add('home.kids', 'Jerry');
    
Multiple value at once:
    
    $session->add([
        'users' => 'Sue',
        'cars'  => 'Toyota'
    ]);
    
    // Dot notation
    $session->add([
        'users'     => ['Katie', 'Ben'],
        'home.kids' => ['Carl', 'Tom']
    ]);

#### Check if value exists

    if ($session->has('user')) {
        // Do something...
    }
    
    // Array style
    if (isset($session['user'])) {
        // Do something...
    }
    
    // Magic method
    if (isset($session->user)) {
        // Do something...
    }
    
    // In specific namespace
    if ($session->hasIn('my_namespace', 'user')) {
        // Do something...
    }
    
    // Dot notation
    if ($session->has('user.firstname')) {
        // Do something...
    }

#### Delete value
    
    $session->delete('user');
    
    // Array style
    unset($session['user']);
    
    // Magic method
    unset($session->user);
    
    // From specific namespace
    $session->deleteFrom('my_namespace', 'user');
    
    // Dot notation
    $session->delete('user.firstname');

Multiple values at once:

    $session->delete(['user', 'home']);
    
    // From specific namespace
    $session->deleteFrom('my_namespace', ['user', 'home'']);
    
    // Dot notation
    $session->delete(['user', 'home.kids']);

#### Clear values

Clear all values:

    $session->clear();
    
    // From specific namespace
    $session->clearFrom('my_namespace');

Clear all values within specific session key:

    $session->clear('user');
    
    // From specific namespace
    $session->clearFrom('my_namespace', 'user');
    
    // Dot notation
    $session->clear('home.kids');

Multiple values at once:

    $session->clear(['user', 'home']);
    
    // From specific namespace
    $session->clearFrom('my_namespace', ['user', 'home']);
    
    // Dot notation
    $session->clear(['user', 'home.kids']);

Format option (if given key doesn't exist, create an empty array on it):

    $session->clear('my_cars', true);
    
    // Dot notation
    $session->clear('user.cars', true);

#### Destroy session completely
    
    $session->destroy();
    
    // Static method
    \AdBar\Session::destroy();

#### Regenerate session id

    $session->regenerateId();
    
    // Static method
    \AdBar\Session::regenerateId();

#### Change namespace

    $session->setNamespace('another_namespace');

#### Get current namespace

    $namespace = $session->getNamespace();
