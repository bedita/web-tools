# BEdita/WebTools plugin for CakePHP web apps using BEdita 4 API

![Github Actions](https://github.com/bedita/dev-tools/workflows/php/badge.svg)
[![codecov](https://codecov.io/gh/bedita/web-tools/branch/master/graph/badge.svg)](https://codecov.io/gh/bedita/web-tools)
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

## Identifiers

### ApiIdentifier

`ApiIdentifier` is an [identifier](https://book.cakephp.org/authentication/2/en/identifiers.html) of [Authentication](https://github.com/cakephp/authentication) plugin that helps to identify a user through the BEdita API.

In order to use the identifier you need to load [Authentication](https://github.com/cakephp/authentication) plugin in the application bootstrap in `Application.php`

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
