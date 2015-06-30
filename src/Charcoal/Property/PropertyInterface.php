<?php

namespace Charcoal\Property;

/**
*
*/
interface PropertyInterface
{
    /**
    * @return string
    */
    public function type();
    
    /**
    * @param array $data
    * @return PropertyInterface Chainable
    */
    public function set_data(array $data);

    /**
    * @param string $ident
    * @return PropertyInterface Chainable
    */
    public function set_ident($ident);

    /**
    * @return string
    */
    public function ident();

    /**
    * @param mixed $val
    * @return PropertyInterface Chainable
    */
    public function set_val($val);

    /**
    * @return mixed
    */
    public function val();

    /**
    * @param string $field_ident
    * @return mixed
    */
    public function field_val($field_ident);

    /**
    * @param mixed $label
    * @return PropertyInterface Chainable
    */
    public function set_label($label);

    /**
    * @return boolean
    */
    public function label();

    /**
    * @param boolean $l10n
    * @return PropertyInterface Chainable
    */
    public function set_l10n($l10n);

    /**
    * @return boolean
    */
    public function l10n();

    /**
    * @param boolean $hidden
    * @return PropertyInterface Chainable
    */
    public function set_hidden($hidden);

    /**
    * @return boolean
    */
    public function hidden();

    /**
    * @param boolean $multiple
    * @return PropertyInterface Chainable
    */
    public function set_multiple($multiple);

    /**
    * @return boolean
    */
    public function multiple();

    /**
    * @param array $multiple_options
    * @return PropertyInterface Chainable
    */
    public function set_multiple_options($multiple_options);

    /**
    * @return array
    */
    public function multiple_options();
    
    /**
    * @param boolean $required
    * @return PropertyInterface Chainable
    */
    public function set_required($required);

    /**
    * @return boolean
    */
    public function required();

    /**
    * @param boolean $unique
    * @return PropertyInterface Chainable
    */
    public function set_unique($unique);

    /**
    * @return boolean
    */
    public function unique();

    /**
    * @param boolean $active
    * @return PropertyInterface Chainable
    */
    public function set_active($active);

    /**
    * @return boolean
    */
    public function active();

    /**
    * @return array
    */
    public function fields();

    /**
    * @return string
    */
    public function sql_type();
}
