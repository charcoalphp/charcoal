<?php

namespace Charcoal\Tests\Translator;

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
     */
    public function getMethod($class, $name): \ReflectionMethod
    {
        return new ReflectionMethod($class, $name);
    }

    /**
     * Invoke the requested method, via the Reflection API.
     *
     * @param  mixed  $object The object that contains the method.
     * @param  string $name   The method name to invoke.
     * @param  array  $args   The parameters to be passed to the function.
     * @return mixed Returns the method result.
     */
    public function callMethod($object, $name, array $args = [])
    {
        $method = $this->getMethod($object, $name);
        if ($args === []) {
            return $method->invoke($object);
        } else {
            return $method->invokeArgs($object, $args);
        }
    }

    /**
     * Invoke the requested method with arguments, via the Reflection API.
     *
     * @param  mixed  $object  The object that contains the method.
     * @param  string $name    The method name to invoke.
     * @param  mixed  ...$args The parameters to be passed to the function.
     * @return mixed Returns the method result.
     */
    public function callMethodWith($object, $name, ...$args)
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
     */
    public function getProperty($class, $name): \ReflectionProperty
    {
        return new ReflectionProperty($class, $name);
    }

    /**
     * Gets class property value, via the Reflection API.
     *
     * @param  mixed  $object The object to access.
     * @param  string $name   The property name to fetch.
     * @return mixed
     */
    public function getPropertyValue($object, $name)
    {
        return $this->getProperty($object, $name)->getValue($object);
    }

    /**
     * Set class property value, via the Reflection API.
     *
     * @param  mixed  $object The object to access.
     * @param  string $name   The property name to affect.
     * @param  mixed  $value  The new value.
     */
    public function setPropertyValue($object, $name, $value): void
    {
        $this->getProperty($object, $name)->setValue($object, $value);
    }
}
