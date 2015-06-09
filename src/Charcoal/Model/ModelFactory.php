<?php

namespace Charcoal\Model;

use \Exception as Exception;

use \Charcoal\Core\AbstractFactory as AbstractFactory;
use \Charcoal\Model\ModelInterface as ModelInterface;

class ModelFactory extends AbstractFactory
{
    /**
    * @param string $type
    * @throws Exception
    * @return ModelInterface
    */
    public function get($type)
    {
        $class_name = $this->ident_to_classname($type);
        if (class_exists($class_name)) {
            $obj = new $class_name();
            if (!($obj instanceof ModelInterface)) {
                throw new Exception('Invalid model (2): '.$type.' (not an action)');
            }
            return $obj;
        } else {
            throw new Exception('Invalid model: '.$type);
        }
    }
}
