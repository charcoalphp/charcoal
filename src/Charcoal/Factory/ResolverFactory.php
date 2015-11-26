<?php

namespace Charcoal\Factory;

use \InvalidArgumentException;

// Local namespace dependencies
use \Charcoal\Factory\AbstractFactory;

/**
*
*/
class ResolverFactory extends AbstractFactory
{
    /**
    * @var string $resolver_prefix
    */
    private $resolver_prefix = '';

    /**
    * @var string $resolver_suffix
    */
    private $resolver_suffix = '';

    /**
    * @var array $resolver_capitals
    */
    private $resolver_capitals = null;

    /**
    * @var array $resolver_replacements
    */
    private $resolver_replacements = null;

    /**
    * @param string $prefix
    * @throws InvalidArgumentException
    * @return ResolverFactory Chainable
    */
    public function set_resolver_prefix($prefix)
    {
        if (!is_string($prefix)) {
            throw new InvalidArgumentException(
                'Prefix must be a string'
            );
        }
        $this->resolver_prefix = $prefix;
        return $this;
    }

    /**
    * @return string
    */
    public function resolver_prefix()
    {
        return $this->resolver_prefix;
    }

    /**
    * @param string $suffix
    * @throws InvalidArgumentException
    * @return ResolverFactory Chainable
    */
    public function set_resolver_suffix($suffix)
    {
        if (!is_string($suffix)) {
            throw new InvalidArgumentException(
                'Prefix must be a string'
            );
        }
        $this->resolver_suffix = $suffix;
        return $this;
    }

    /**
    * @return string
    */
    public function resolver_suffix()
    {
        return $this->resolver_suffix;
    }

    /**
    * @param array $capitals
    * @return ResolverFactory Chainable
    */
    public function set_resolver_capitals(array $capitals)
    {
        $this->resolver_capitals = $capitals;
        return $this;
    }

    /**
    * @return array
    */
    public function resolver_capitals()
    {
        if ($this->resolver_capitals === null) {
            return [
                '-',
                '\\',
                '/',
                '.',
                '_'
            ];
        }
        return $this->resolver_capitals;
    }

    /**
    * @param array $replacements
    * @return ResolverFactory Chainable
    */
    public function set_resolver_replacements(array $replacements)
    {
        $this->resolver_replacements = $replacements;
        return $this;
    }

    /**
    * @return array
    */
    public function resolver_replacements()
    {
        if ($this->resolver_replacements === null) {
            return [
                '-'=>'',
                '/'=>'\\',
                '.'=>'_'
            ];
        }
        return $this->resolver_replacements;
    }

    /**
    * Resolve the class name from the requested type.
    *
    * @param string $type
    * @throws InvalidArgumentException
    * @return string
    */
    public function resolve($type)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException(
                'Can not resolve class ident: type must be a string'
            );
        }

        $capitalize_next = function(&$i) {
            $i = ucfirst($i);
        };

        $capitals = $this->resolver_capitals();
        foreach ($capitals as $cap) {
            $expl = explode($cap, $type);
            array_walk($expl, $capitalize_next);
            $type = implode($cap, $expl);
        }

        $replacements = $this->resolver_replacements();
        foreach ($replacements as $rep => $target) {
            $type = str_replace($rep, $target, $type);
        }

        $class = '\\'.trim($type, '\\');
            
        // Add prefix + suffix, if applicable
        $class = $this->resolver_prefix().$class.$this->resolver_suffix();

        return $class;
    }

    /**
    * @param string $type
    * @throws InvalidArgumentException If the type parameter is not a string
    * @return boolean
    */
    public function is_resolvable($type)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException(
                'Can not check resolvable: type must be a string'
            );
        }
        
        $class_name = $this->resolve($type);
        return class_exists($class_name);
    }
}
