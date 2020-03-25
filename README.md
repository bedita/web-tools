# BEdita/WebTools plugin for CakePHP web apps using BEdita 4 API

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
