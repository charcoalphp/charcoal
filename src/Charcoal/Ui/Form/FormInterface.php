<?php

namespace Charcoal\Ui\Form;

/**
 * Form Interface
 */
interface FormInterface
{
    /**
     * @param string $action The form action, typically a URL.
     * @return FormInterface Chainable
     */
    public function setAction($action);

    /**
     * @return string
     */
    public function action();

    /**
     * @param string $method Either "post" or "get".
     * @return FormInterface Chainable
     */
    public function setMethod($method);

    /**
     * @return string Either "post" or "get"
     */
    public function method();

    /**
     * @param array $data The (pre-populated) form data.
     * @return FormInterface Chainable
     */
    public function setFormData(array $data);

    /**
     * @param string $key The form data key, or property identifier.
     * @param mixed  $val The form data value, for a given key.
     * @return FormInterface Chainable
     */
    public function addFormData($key, $val);

    /**
     * @return array
     */
    public function formData();
}
