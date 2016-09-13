# Slim Secure Session Middleware
Secure session middleware for [Slim 3 framework](http://www.slimframework.com/).
- longer and harder to guess session id's
- set session cookie path, domain and secure values automatically
- extend session lifetime after each user activity
- encrypt session data
- session namespaces
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
            'encryption_key' => null,
            
            // Session namespace
            'namespace'      => 'slim_app'
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
This class uses namespaces for sessions, so basically session variables are always inside an array and array's key is the namespace. If you insert key 'user' with value 'John' into namespace 'users', superglobal $_SESSION looks like this:
    
    Array
    (
        [users] => Array
            (
                [user] => John
            )
    )

#### Basic usage    
Session helper class is injected to application container and can be used as an object, i.e. like this:

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
    
    // Magic method
    $session->user = 'John';
    
    // Set value to specific namespace
    $session->setTo('my_namespace', 'user', 'John');

Set multiple values as an array:
    
    $user = ['firstname' => 'John', 'lastname' => 'Smith'];
    $session->set($user);
    
    // Or just simply
    $session->set([
        'firstname' => 'John',
        'lastname'  => 'Smith
    ]);
    
    // Set values to specific namespace
    $user = ['firstname' => 'John', 'lastname' => 'Smith'];
    $session->setTo('my_namespace', $user);

#### Get value
    
    $session->get('user');
    
    // Get default value if key doesn't exist
    $session->get('user', 'some default user');
    
    // Magic method
    $session->user;
    
    // Get value from specific namespace
    $session->getFrom('my_namespace', 'user');

#### Check if value exists

    if ($session->has('user')) {
        // Do something...
    }
    
    // Magic method
    if (isset($session->user)) {
        // Do something...
    }
    
    // Check if value exists in specific namespace
    if ($session->hasIn('my_namespace', 'user')) {
        // Do something...
    }

#### Delete value
    
    $session->delete('user');
    
    // Magic method
    unset($session->user);
    
    // Delete value from specific namespace
    $session->deleteFrom('my_namespace', 'user');

#### Clear all session values

    $session->clear();
    
    // Clear specific namespace
    $session->clearFrom('my_namespace');

#### Destroy session completely
    
    $session->destroy();
    
    // Static methof
    AdBar\Session::destroy();

#### Regenerate session id

    $session->regenerateId();
    
    // Static method
    AdBar\Session::regenerateId();

#### Set active namespace

    $session->setNamespace('another_namespace');

#### Get active namespace

    $namespace = $session->getNamespace();
