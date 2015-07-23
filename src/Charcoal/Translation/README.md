Charcoal Translation
====================

This namespace (part of the `charcoal-core` package) provides a comprehensive solution for handling localisation, internationalization, and translation for a multi-lingual Charcoal project.

# Usage

Setting the default language / setting the current language:
```php
use \Charcoal\Translation\TranslationConfig;

$translation_config = TranslationConfig::instance();

// Set the list of available languages
$translation_config->set_available_langs([
    'en' => [],
    'fr' => []
]);

// Set the default language to "English" ("en")
$translation_config->set_default_lang('en');

// Set the current language to French
$translation_config->set_lang('fr');
```

Loading a (translated) string from the catalog:
```php
use \Charcoal\Translation\Catalog;

$catalog = Catalog::instance();
$catalog->add_resource('myproject.csv');
echo $catalog->tr('my string');

// Add a custom string..
$catalog->add_translation('custom string', [
    'en'=>'Custom string',
    'fr'=>'Chaîne aléatoire'
]);
ech $catalog->tr('custom string');
```

Using the `TranslationString` object directly:
```php
// Let's assume the default language has been set to 'en'...

use \Charcoal\Translation\TranslationString;

$str = new TranslationString([
    'fr'=>'foo',
    'en'=>'bar'
]);

// All the following examples output "bar"
echo $str;
echo $str->en();
echo $str->val('en');

// All the following examples output "foo"
echo $str->fr();
echo $str->set_lang('fr')->val();
echo $str->val('fr');
//
```

## Usage in Mustache templates

By default, all call to the `{{#_t}}` mustache helper in Charcoal Templates will try to translate a string using the default Charcoal Catalog.

For example:
- _Assuming the "my string" has been added to the main catalog, as in the previous example_

# Dependencies

The `Charcoal\Translation` package is currently distributed as part of the `charcoal-core` module. Intra-dependencies (dependencides on other part of the `charcoal-core` module) are:

- `Charcoal\Config` for the `TranslationConfig`

There are no other external dependencies.
