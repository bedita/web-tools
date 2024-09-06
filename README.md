# BEdita/WebTools plugin for CakePHP web apps using BEdita API

[![Github Actions](https://github.com/bedita/web-tools/workflows/php/badge.svg)](https://github.com/bedita/web-tools/actions?query=workflow%3Aphp)
[![codecov](https://codecov.io/gh/bedita/web-tools/branch/master/graph/badge.svg)](https://codecov.io/gh/bedita/web-tools)
[![phpstan](https://img.shields.io/badge/PHPStan-level%200-brightgreen.svg)](https://phpstan.org)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bedita/web-tools/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/bedita/web-tools/?branch=master)
[![image](https://img.shields.io/packagist/v/bedita/web-tools.svg?label=stable)](https://packagist.org/packages/bedita/web-tools)
[![image](https://img.shields.io/github/license/bedita/web-tools.svg)](https://github.com/bedita/web-tools/blob/master/LICENSE.LGPL)

## Installation

First, if `vendor` directory has not been created, you have to install composer dependencies using:

```bash
composer install
```

You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

The recommended way to install composer packages is:

```bash
composer require bedita/web-tools
```

## Helpers

### WebComponents

This helper provides some methods to setup Custom Elements with some app variables in order to initialize client side JavaScript components. It aims to avoid the generation of inline JS dictionaries or variables using declarative assignments to HTML nodes. String and numeric values are added as node attributes, while objects and arrays using inline scripts.

#### Example

Create a js file in the `webroot/js` which contains the Custom Element definition:

**webroot/js/components/awesome-video.js**
```js
class AwesomeVideo extends HTMLElement {
    connectedCallback() {
        this.video = document.createElement('video');
        this.video.src = this.getAttribute('src');
        this.appendChild(this.video);
    }
}

customElements.define('awesome-video', AwesomeVideo);
```

Now you can initialize the element in a twig template:

**templates/Pages/document.twig**
```twig
{{ WebComponents.element('awesome-video', { src: attach.uri }, 'components/awesome-video') }}
```

You can also extends native tags in order to setup simple interactions with the `is` method:

**webroot/js/components/awesome-table.js**
```js
class AwesomeTable extends HTMLElement {
    connectedCallback() {
        this.addEventListener('click', (event) => {
            let th = event.target.closest('[sort-by]');
            if (th) {
                this.sortBy(th.getAttribute('sort-by'));
            }
        }):
    }

    sortBy(field) {
        // ...
    }
}

customElements.define('awesome-table', AwesomeTable, { extends: 'table' });
```

**templates/Pages/users.twig**
```twig
<table {{ WebComponents.is('awesome-table', {}, 'components/awesome-table')|raw }}>
    <thead>
        <th>Email</th>
        <th>Name</th>
    </thead>
    <tbody>
        {% for user in users %}
            <tr>
                <td>{{ user.attributes.email }}</td>
                <td>{{ user.attributes.name }}</td>
            </tr>
        {% endfor %}
    </tbody>
</table>
```

## Load assets with AssetRevisions

`AssetRevisions` with the help of an asset strategy can easily resolve the common issue
of loading built versioned assets as `js` and `css`.

Through `\BEdita\WebTools\View\Helper\HtmlHelper` you can transparently link built assets placed in a custom folder or raw assets living in `webroot/js` or `webroot/css`.

### Define which strategy to use

The best place to define which strategy your app will use is the `Application::bootstrap()`

```php
use BEdita\WebTools\Utility\AssetRevisions;
use BEdita\WebTools\Utility\Asset\Strategy\EntrypointsStrategy;

public function bootstrap(): void
{
    parent::bootstrap();

    AssetsRevisions::setStrategy(new EntrypointsStrategy());

    // you can also set the path where to find the manifest (default is webroot/build/entrypoints.json)
    // AssetsRevisions::setStrategy(
    //     new EntrypointsStrategy(['manifestPath' => '/custom/path/entrypoints.json']);
    // );
}
```

There are two assets strategies out of the box:

* `EntrypointsStrategy` based on the `entrypoints.json` file generated by [Webpack Encore](https://github.com/symfony/webpack-encore)
* `RevManifestStrategy` based on `rev-manifest.json` file generated by [gulp-rev](https://github.com/sindresorhus/gulp-rev)

Anyway you are free to define your own strategy implementing `AssetStrategyInterface`.

### Use HtmlHelper to load assets

Once a strategy is set you can link assets using `\BEdita\WebTools\View\Helper\HtmlHelper` and its methods `script()`, `css()` and `assets()`, for example:

```php
<?= $this->Html->script('app') ?>
```

The javascript `app` asset will be searched first from your asset strategy falling back to CakePHP `HtmlHelper` if strategy doesn't resolve the asset.

In this way you can continue to load assets as it was placed in common `webroot/js` or `webroot/css` and delegate to `\BEdita\WebTools\View\Helper\HtmlHelper` the task of resolve the link to them.


## Identifiers

### ApiIdentifier

`ApiIdentifier` is an [identifier](https://book.cakephp.org/authentication/2/en/identifiers.html) of [Authentication](https://github.com/cakephp/authentication) plugin that helps to identify a user through the BEdita API.

In order to use the identifier you need to install and load [Authentication](https://github.com/cakephp/authentication) plugin in the application bootstrap in `Application.php`

Install

```bash
composer require cakephp/authentication
```

then load in app

```php
public function bootstrap(): void
{
    parent::bootstrap();

    $this->addPlugin('Authentication');
}
```

then add the `AuthenticationMiddleware`

```php
public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
{
    // Various other middlewares for error handling, routing etc. added here.

    // Create an authentication middleware object
    $authentication = new AuthenticationMiddleware($this);

    // Add the middleware to the middleware queue.
    // Authentication should be added *after* RoutingMiddleware.
    // So that subdirectory information and routes are loaded.
    $middlewareQueue->add($authentication);

    return $middlewareQueue;
}
```

and take advantage of `getAuthenticationService()` hook to set up the identifier.

```php
public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
{
    $service = new AuthenticationService();

    // Load the authenticators, you want session first
    $service->loadAuthenticator('Authentication.Session');
    $service->loadAuthenticator('Authentication.Form', [
        'loginUrl' => '/users/login'
    ]);

    // Load identifiers
    $service->loadIdentifier('BEdita/WebTools.Api');

    return $service;
}
```

## Identity and Identity Helper

To use them ensure to install [Authentication](https://github.com/cakephp/authentication) plugin

```bash
composer require cakephp/authentication
```

and load plugin `$this->addPlugin('Authentication')` in `Application::bootstrap()`.

Then setup your application to use `Identity`, for example

```php
// Edit Application.php
public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
{
    $service = new AuthenticationService([
        'identityClass' => \BEdita\WebTools\Identity::class
    ]);

    // Load the authenticators, you want session first
    $service->loadAuthenticator('Authentication.Session');
    $service->loadAuthenticator('Authentication.Form');

    // Load identifiers
    $service->loadIdentifier('BEdita/WebTools.Api');

    return $service;
}
```

`Identity` exposes a handy `hasRole()` method:

```php
// in a Controller
$identity = $this->Authentication->getIdentity();
if ($identity->hasRole('admin')) {
    $this->Flash->success('Hi admin!');
}
```

`IdentityHelper` allows to delegate configured methods to `Identity`, for example in a `TwigView` template

```twig
{% if Identity.hasRole('basic') %}
    <button type="button">Upgrade to premium</button>
{% endif %}
```

## Request policy

Using the `RequestPolicy` class it is possible to setup the access to controller and actions by identity's roles
or by custom policy rules.

First of all install [Authorization](https://github.com/cakephp/authorization) plugin

```bash
composer require cakephp/authorization
```

and load plugin `$this->addPlugin('Authorization')` in `Application::bootstrap()`.

Then proceed with setup the policy in `Application` class.
Add the `AuthorizationMiddleware` and the `RequestAuthorizationMiddleware`

```php
public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue {
    // other middleware...
    // $middlewareQueue->add(new AuthenticationMiddleware($this));

    // Add authorization (after authentication if you are using that plugin too).
    $middlewareQueue->add(new AuthorizationMiddleware($this));
    $middlewareQueue->add(new RequestAuthorizationMiddleware());
}
```

and configure the policy

```php
public function getAuthorizationService(ServerRequestInterface $request): AuthorizationServiceInterface
{
    $mapResolver = new MapResolver();
    $mapResolver->map(
        ServerRequest::class,
        new RequestPolicy([
            // setup your request policy rules
            'rules' => [
                'Dashboard' => [
                    'index' => ['premium', 'basic'], // allow access to DashboardController::index() for these roles
                    'special' => 'premium', // allow access to Dashboard::special() only for 'premium' role
                    '*' => false, // fallback for other DashboardController actions. Forbidden to all
                ],
            ],
        ]);
    );

    return new AuthorizationService($mapResolver);
}
```

## OAuth2 setup

Quick steps to use the `OAuth2` tools provided.

1. Create a route to a path like `/ext/login/{provider}` to interact with the selected OAuth2 provider in `config/routes.php`. Each `{provider}` must match a provider configuration key, for instance `google` in the configuration example below, see [OAuth2 providers structure](#oauth2-providers-structure). An example here:

```php
        $builder->connect(
            '/ext/login/{provider}',
            ['controller' => 'ExternalLogin', 'action' => 'login'],
            ['_name' => 'login:oauth2']
        );
```


2. Define a controller for the above routing rule. A minimal version of the login action could include just a simple redirect like this example:

```php
    public function login(): ?Response
    {
        $result = $this->Authentication->getResult();
        if (!empty($result) && $result->isValid()) {
            $target = $this->Authentication->getLoginRedirect() ?? ['_name' => 'home'];

            return $this->redirect($target);
        }
        // Handle authentication failure below with flash messages, logs, redirects...
        // ....
    }
```

3. Setup `OAuth2Authenticator` and `OAuth2Identifier` classes in your main `Application` class and create the corresponding `OAuth2Providers` configuration. See the paragraphs below for more details.

4. Add the `OAuth2Middleware` in your middleware stack just after the `AuthenticationMiddleware` in `Application::middleware()`. like this:

```php
            ->add(new AuthenticationMiddleware($this))
            ->add(new OAuth2Middleware())
```

## OAuth2 providers

To use `OAuth2Authenticator` and `OAuth2Identifier` classes you must pass the supported OAuth2 providers configuration when loading this classes in the authentication service.
Here a brief example of how to do this in `Application::getAuthenticationService()`:

```php

    $service = new AuthenticationService();

    $path = $request->getUri()->getPath();
    if (strpos($path, '/ext/login') === 0) {
        $providers = (array)Configure::read('OAuth2Providers');
        $service->loadIdentifier('BEdita/WebTools.OAuth2', compact('providers') + [
            'autoSignup' => true,
            'signupRoles' => ['customer'],
        ]);
        $service->loadAuthenticator('BEdita/WebTools.OAuth2', compact('providers') + [
            'redirect' => ['_name' => 'login:oauth2'],
        ]);
    }

```

We are setting up the OAUth2 authenticator and identifier only when the request path matches our oauth2 login route as defined above.

It is recommended to use a configuration key like `OAuth2Providers` to store the provider information, anyway you must pass providers settings array using the `providers` key.
Other possibile configuration are:

* (`OAuth2Authenticator`) `'redirect'` - default `['_name' => 'login']`, redirect url route specified as named array
* (`OAuth2Identifier`) `autoSignup` - default `false`, set to `true` if you want an automatic signup to be performed if login fails
* (`OAuth2Identifier`) `'signupRoles'` - default `[]`, user roles to use during the signup process, used only if `autoSignup` is `true`

### OAuth2 providers structure

The providers configuration structure is in the following example.
Here a single `google` provider is defined.
Mandatory configuration keys are `class`, `setup`, `options` and `map` explained below.
Each provider key must match the `auth_provider` name defined and configured in BEdita API.

```php

    'google' => [
        // OAuth2 class name, must be a supported provider of `league/oauth2-client`
        // see https://oauth2-client.thephpleague.com/providers/league/ official or third-party
        'class' => '\League\OAuth2\Client\Provider\Google',

        // Provider class setup parameters, normally this includes `clientId` and `clientSecret` keys
        // Other parameters like 'redirectUri' will be added dynamically
        'setup' => [
            'clientId' => '####',
            'clientSecret' => '####',
        ],

        // Provider authorization options, specify the user information scope that you want to read
        // `'scope'` array will vary between providers, please read the oauth2-client documentation.
        'options' => [
            'scope' => ['https://www.googleapis.com/auth/userinfo.email'],
        ],

        // Map BEdita user fields with auth provider data path, using dot notation like 'user.id'
        // In this array keys are BEdita fields, and values are paths to extract the desired item from the provider response
        // only `'provider_username'` is mandatory, to uniquely identify the user in the provider context
        // other fields could be used during signup
        'map' => [
            'provider_username' => 'sub',
            'username' => 'email',
            'email' => 'email',
            'name' => 'given_name',
            'surname' => 'family_name',
        ],
    ],
```

For a brief OAuth2 providers reference have a look at the [OAuth2 providers configuration wiki page](https://github.com/bedita/web-tools/wiki/OAuth2-providers-configurations)
