<?php

namespace Charcoal\Model\Validator;

use Charcoal\Model\Validator as Validator;
use Charcoal\Model\Property as Property;

class PropertyValidator extends Validator
{
	public function validate_model(Property $model)
	{
		$model->validate($this);

		return $this;
	}
}