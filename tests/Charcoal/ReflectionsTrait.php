<?php

namespace Charcoal\Tests;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Utilities for testing non-public methods and properties.
 */
trait ReflectionsTrait
{
    /**
     * Gets a {@see ReflectionMethod} for a class method.
     *
     * The method will be made accessible in the process.
     *
     * @param  mixed  $class The class name or object that contains the method.
     * @param  string $name  The method name to reflect.
     * @return ReflectionMethod
     */
    final public function getMethod($class, $name)
    {
        $reflected = new ReflectionMethod($class, $name);
        $reflected->setAccessible(true);
        return $reflected;
    }

    /**
     * Invoke the requested method, via the Reflection API.
     *
     * @param  object $object The object that contains the method.
     * @param  string $name   The method name to invoke.
     * @return mixed Returns the method result.
     */
    final public function callMethod($object, $name)
    {
        return $this->getMethod($object, $name)->invoke($object);
    }

    /**
     * Invoke the requested method with arguments, via the Reflection API.
     *
     * @param  object $object  The object that contains the method.
     * @param  string $name    The method name to invoke.
     * @param  mixed  ...$args The parameters to be passed to the function.
     * @return mixed Returns the method result.
     */
    final public function callMethodWith($object, $name, ...$args)
    {
        return $this->getMethod($object, $name)->invoke($object, ...$args);
    }

    /**
     * Gets a {@see ReflectionProperty} for a class property.
     *
     * The property will be made accessible in the process.
     *
     * @param  mixed  $class The class name or object that contains the property.
     * @param  string $name  The property name to reflect.
     * @return ReflectionProperty
     */
    final public function getProperty($class, $name)
    {
        $reflected = new ReflectionProperty($class, $name);
        $reflected->setAccessible(true);
        return $reflected;
    }

    /**
     * Gets class property value, via the Reflection API.
     *
     * @param  object $object The object to access.
     * @param  string $name   The property name to fetch.
     * @return mixed
     */
    final public function getPropertyValue($object, $name)
    {
        return $this->getProperty($object, $name)->getValue($object);
    }

    /**
     * Set class property value, via the Reflection API.
     *
     * @param  object $object The object to access.
     * @param  string $name   The property name to affect.
     * @param  mixed  $value  The new value.
     * @return void
     */
    final public function setPropertyValue($object, $name, $value)
    {
        $this->getProperty($object, $name)->setValue($object, $value);
    }
}
