# Component Handle Loader for Twig

[![Packagist](https://img.shields.io/packagist/v/gglnx/twig-component-handle-loader.svg)](https://packagist.org/packages/gglnx/twig-component-handle-loader)

[Twig](https://twig.symfony.com/) loader for loading templates by using a component handle based on [the Fractal naming convention](https://fractal.build/guide/core-concepts/naming.html#referencing-other-items).

For example:

```
├── components
│   └── small-components
|       └── button
|           └── button.twig # Will be @button
```

You can now include the button template with the `@[component-handle]` syntax:

```twig
{% include '@button' %}
```

## Requirements

* Twig >=2.14
* PHP >=7.4

## Installation

The recommended way to install this loader is via [Composer](https://getcomposer.org/):

```bash
composer require gglnx/twig-component-handle-loader
```

Then you can use this loader directly with Twig:

```php
require_once '/path/to/vendor/autoload.php';

$loader = new \Gglnx\TwigComponentHandleLoader\Loader\TwigComponentHandleLoader('../path-to-my-components');
$twig = new \Twig\Environment($loader);
```

You can also combine this loader with other loaders using [`ChainLoader`](https://twig.symfony.com/doc/3.x/api.html#twig-loader-chainloader).

## Differences between this loader and the Fractal implementation

* [Ordering and hiding](https://fractal.build/guide/core-concepts/naming.html#ordering-and-hiding) are yet not fully supported and tested.
* Using [Prefixes](https://fractal.build/guide/core-concepts/naming.html#prefixes) is not possible.
* Overriding [handle in the component configuration](https://fractal.build/guide/components/configuration-reference.html#component-properties) is not possible.

Prefixing and overriding the component handle require to read the corresponding component configuration. This could be out-of-scope for this loader and maybe better placed in a specific Twig Fractal loader.
