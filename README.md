# BEdita/WebTools plugin for CakePHP web apps using BEdita 4 API

## Installation

You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

The recommended way to install composer packages is:

```bash
composer require bedita/web-tools
```

## Helpers

### WebComponents

This helper provides some methods to setup Custom Elements with some app variables in order to initialize client side JavaScript components. It aims to avoid the generation of inline JS dictionaries or variables using declarative assignments to HTML nodes.

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
{{ WebComponent.element('awesome-video', { src: attach.uri }, 'components/awesome-video') }}
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
