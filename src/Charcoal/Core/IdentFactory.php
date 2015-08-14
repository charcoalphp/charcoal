<?php

namespace Charcoal\Core;

class IdentFactory extends AbstractFactory
{
    /**
    * Generate the class name from the requested type.
    *
    * By default,
    *
    * @see self::prepare_classname()
    */
    public function classname($type)
    {
        // Change "foo-bar" to "fooBar"
        $expl = explode('-', $type);
        array_walk(
            $expl,
            function(&$i) {
                $i = ucfirst($i);
            }
        );
        $type = implode('', $expl);

        // Change "/foo/bar" to "\Foo\Bar"
        $class = str_replace('/', '\\', $type);
        $expl  = explode('\\', $class);
        array_walk(
            $expl,
            function(&$i) {
                $i = ucfirst($i);
            }
        );

        $class = '\\'.trim(implode('\\', $expl), '\\');
        $class = $this->prepare_classname($class);
        return $class;
    }

    /**
    * {@inheritdoc}
    */
    public function validate($type)
    {
        $class_name = $this->classname($type);
        return class_exists($class_name);
    }

    /**
    *
    *
    * @param string
    * @return string
    */
    public function prepare_classname($class)
    {
        return $class;
    }
}
