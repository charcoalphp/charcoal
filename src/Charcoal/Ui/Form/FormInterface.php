<?php

namespace Charcoal\Ui\Form;

use \Charcoal\Ui\UiItemInterface;

interface FormInterface extends UiItemInterface
{
    /**
     * @param string $action
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
     * @param array $data
     * @return FormInterface Chainable
     */
    public function setFormData(array $data);

    /**
     * @param string $key
     * @param mixed  $val
     * @return FormInterface Chainable
     */
    public function addFormData($key, $val);

    /**
     * @return array
     */
    public function formData();
}
