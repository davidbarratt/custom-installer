Composer Custom Type Installer
==============================
Adds a root-level custom [type](https://getcomposer.org/doc/04-schema.md#type) installer path to composer.json. Any custom type can be used to define a path the [type](https://getcomposer.org/doc/04-schema.md#type) should be installed in.

## Installation
Simply require this library in your composer.json file. Typically this will be added as a dependency of the custom [type](https://getcomposer.org/doc/04-schema.md#type) to ensure that the library is loaded before the library that needs it. However, this can be added to the root composer.json, as long as it goes before any library that needs it.
```json
{
    "require": {
        "davidbarratt/custom-installer": "1.0.*@alpha"
    }
}
```

## Usage
The added parameter(s) are only allowed on the root to avoid conflicts between multiple libraries. This also prevents a project owner from having a directory accidentally wiped out by a library.

#### custom-installer (root-level)
You may use [Composer Installer](https://github.com/composer/installers) type [installation paths](https://github.com/composer/installers#custom-install-paths) with the variables `{$name}`, `{$vendor}`, and `{$type}`. Each package will go in it’s respective folder in the order in which they are installed.

```json
{
    "extra": {
        "custom-installer": {
            "drupal-core": "web/",
            "drupal-site": "web/sites/{$name}/",
            "random-type": "custom/{$type}/{$vendor}/{$name}/"
        }
    }
}
```
