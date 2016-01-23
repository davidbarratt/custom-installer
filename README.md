Composer Custom Type Installer
==============================
[![Build Status](https://travis-ci.org/davidbarratt/custom-installer.svg?branch=develop)](https://travis-ci.org/davidbarratt/custom-installer)

Adds a root-level custom [type](https://getcomposer.org/doc/04-schema.md#type) installer path to composer.json. Any custom type can be used to define a path the [type](https://getcomposer.org/doc/04-schema.md#type) should be installed in.

## Installation
Simply require this library in your composer.json file. Typically this will be added as a dependency of the custom [type](https://getcomposer.org/doc/04-schema.md#type) to ensure that the library is loaded before the library that needs it. However, this can be added to the root composer.json, as long as it goes before any library that needs it.
```json
{
    "require": {
        "davidbarratt/custom-installer": "1.0.*@dev"
    }
}
```

## Usage
The added parameter(s) are only allowed on the root to avoid conflicts between multiple libraries. This also prevents a project owner from having a directory accidentally wiped out by a library. Note: Each package will go in it’s respective folder in the order in which they are installed.

The configuration has to be added in `custom-installer` of `composer.json`'s `extra` section. It is similar to [Composer installer's installation paths](https://github.com/composer/installers#custom-install-paths).

### Pattern syntax

The key of the the configuration array is the path pattern. You can use some
replacement tokens:

- `{$name}`: The name of the package (e.g. `yaml` of `symfony/yaml`)
- `{$vendor}`: The vendor of the package (e.g. `symfony` of `symfony/yaml`)
- `{$type}`: for the composer package type (e.g. `library`, `drupal-module`)

### Package filters

The value of the configuration array has to be an array. It holds the package 
filter for the given pattern. The pattern will be applied if any filter matches.

#### Package type filter

With `type:[package-type]` you can define a pattern per package type. You can use
any custom package type and [are not limited to a predefined set](https://github.com/composer/installers#should-we-allow-dynamic-package-types-or-paths-no).

Composer specific package types `metapackage` or `composer-plugin` will never be
handled by _Custom Installer_.

Example: `type:custom-library` for package type `custom-library`

#### Package name filter

You can  specify a pattern per full package name (`[vendor]/[name]`).

_Custom Installer_ will only handle a specific package if a configuration exists
that also handles the package type in general.

### Examples

```json
{
    "extra": {
        "custom-installer": {
            "web/": ["type:drupal-core"],
            "web/sites/{$name}/": ["type:drupal-site"],
            "custom/{$type}/{$vendor}/{$name}/": ["type:random-type"],
            "vendor/{$vendor}/{$name}/": ["type:library"],
            "web/sites/all/libraries/{$name}/": [
                "type:component",
                "ckeditor/ckeditor",
                "flesler/jquery.scrollto"
            ],
            "custom-folder-for-single-package": ["myvendorname/single-package"],
        }
    }
}
```

In the example we want to make sure _CKEditor_ and _Jquery ScrollTo_ will be
placed in `web/sites/all/libraries`. Both packages are of of package type
`library`. In order to change the path of those packages, we must declare a
fallback for the package type `library`. It shall stay in the default vendor
location (as `library` is the default composer package type). If you do not do
that the custom installer cannot handle the single packages (`ckeditor` and 
`flesler/jquery.scrollto` ) due to [the way composer installer plugins work](https://github.com/composer/composer/pull/4059).
