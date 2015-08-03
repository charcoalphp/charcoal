Charcoal Source
===============

`Source` provides storage support to Charcoal `Model`s.

# Usage

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

# Available Source

Currently, only the `database` source is supported.

## Database Source

The `DatabaseSource` source type is currently the only supported storage source.

# Development

This package is distributed as a namespace inside `charcoal-core`. The development process is therefore the same as `charcoal-core`'s.

## TODOs

- Implements a `FileSource`, at least a basic CSV support.
- Move `CollectionLoader` to here, somehow.
