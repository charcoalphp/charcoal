Charcoal Core
=============

# Table of Contet


# The Charcoal Model
- `Model\Model`
- `Model\Metadata`
- `Model\Property`
- `Model\Object`


## Usage example
``` php

$metadata = new \Charcoal\Model\Metadata();
$metadata->set_data([
	'properties'=>[
		'id'=>[
			'type'=>'id'
		],
		'test'=>[
			'type'=>'string',
			'min_length'=>3
		]
	]
]);

$model = new \Charcoal\Model\Model();
$model->set_metadata($metadata);
$model->set_data([
	'id'=>3,
	'test'=>'Hello World!'
]);

$validations = $model->validate();
if($validations->is_valid()) {
	// Yay!
}

$id_property = $model->property('id');
echo $id_property->view('input_base');

```

# Core Services
- `Service\Loader`
  - `Service\Loader\Model`
  - `Service\Loader\Metadata`
  - `Service\Loader\Object`
- `Service\Validator`
  - `Service\Validator\Model`


# Authors
- Mathieu Ducharme <mat@locomotive.ca>

# TODOs
- _2015-02-24:_ Rename ruleset.xml to phpcs.xml