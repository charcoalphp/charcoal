<?php

namespace Charcoal\Email;

/**
 * Default Email template controller, when none is provided.
 */
class GenericEmailTemplate
{
    /**
     * @param array $data The data array (as [key=>value] pair) to set.
     * @return GenericEmailTemplate Chainable
     */
    public function setData(array $data)
    {
        foreach ($data as $prop => $val) {
            if ($val === null) {
                continue;
            }
            $func = [$this, $this->setter($prop)];
            if (is_callable($func)) {
                call_user_func($func, $val);
            } else {
                $this->{$prop} = $val;
            }
        }
        return $this;
    }

    /**
     * Allow an object to define how the key setter are called.
     *
     * @param string $key The key to get the setter from.
     * @return string The setter method name, for a given key.
     */
    protected function setter($key)
    {
        $setter = 'set_'.$key;
        return $this->camelize($setter);

    }

    /**
     * Transform a snake_case string to camelCase.
     *
     * @param string $str The snake_case string to camelize.
     * @return string The camelCase string.
     */
    private function camelize($str)
    {
        return lcfirst(implode('', array_map('ucfirst', explode('_', $str))));
    }
}
