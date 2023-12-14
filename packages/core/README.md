Charcoal Core
=============

The Core package provides abstract objects and tools for defining object data models and managing datasource connections.

## Installation

```shell
composer require charcoal/core
```

For Charcoal projects, the service provider can be registered from your configuration file:

```json
{
    "service_providers": {
        "charcoal/model/service-provider/model": {}
    }
}
```

## Overview

### Loader

TODO

### Model

TODO

### Source

`Source` provides storage support to Charcoal models.

Using a `Source` object directly:

```php
$model = ModelFactory::instance()->create('namespace/model');
$source = SourceFactory::instance()->create('database');
$source->load_item(1, $model);
```

Using a `Storable` object, which abstract away the `Source` completely.

```php
// Model implements StorableInterface with StorableTrait
$model = ModelFactory::instance()->create('namespace/model');
// This will load the Model's source from it's metadata
$model->load(1);
```

#### Available Source

Currently, only the `database` source is supported.

##### Database Source

The `DatabaseSource` source type is currently the only supported storage source.

##### TODOs

* Implements a `FileSource`, at least a basic CSV support.
* Move `CollectionLoader` to here, somehow.

### Validator

The validator namespace is obsolete and should not be used.
Its usage is currently being removed from everywhere in charcoal.

## Resources

* [Contributing](https://github.com/charcoalphp/.github/blob/main/CONTRIBUTING.md)
* [Report issues](https://github.com/charcoalphp/charcoal/issues) and
  [send pull requests](https://github.com/charcoalphp/charcoal/pulls)
  in the [main Charcoal repository](https://github.com/charcoalphp/charcoal)
