<?php

namespace Charcoal\Factory;

use InvalidArgumentException;
use Charcoal\Factory\AbstractFactory;

/**
 * The Resolver Factory resolves the **class name** by different configurably
 * methods applied to the **type**.
 */
class ResolverFactory extends AbstractFactory
{
    /**
     * @var string $resolverPrefix
     */
    private $resolverPrefix = '';

    /**
     * @var string $resolverSuffix
     */
    private $resolverSuffix = '';

    /**
     * @var array $resolverCapitals
     */
    private $resolverCapitals;

    /**
     * @var array $resolverReplacements
     */
    private $resolverReplacements;

    /**
     * @param array $data Factory arguments.
     */
    public function __construct(array $data = null)
    {
        parent::__construct($data);

        if (!isset($data['resolver_prefix'])) {
            $data['resolver_prefix'] = '';
        }
        if (!isset($data['resolverSuffix'])) {
            $data['resolver_suffix'] = '';
        }
        if (!isset($data['resolver_capitals'])) {
            $data['resolver_capitals'] = [
                '-',
                '\\',
                '/',
                '.',
                '_'
            ];
        }
        if (!isset($data['resolver_replacements'])) {
            $data['resolver_replacements'] = [
                '-' => '',
                '/' => '\\',
                '.' => '_'
            ];
        }
        $this->setResolverPrefix($data['resolver_prefix']);
        $this->setResolverSuffix($data['resolver_suffix']);
        $this->setResolverCapitals($data['resolver_capitals']);
        $this->setResolverReplacements($data['resolver_replacements']);
    }

    /**
     * @param string $prefix The resolver prefix string.
     * @throws InvalidArgumentException If the prefix argument is not a string.
     * @return ResolverFactory Chainable
     */
    public function setResolverPrefix($prefix)
    {
        if (!is_string($prefix)) {
            throw new InvalidArgumentException(
                'Prefix must be a string'
            );
        }
        $this->resolverPrefix = $prefix;
        return $this;
    }

    /**
     * @return string
     */
    public function resolverPrefix()
    {
        return $this->resolverPrefix;
    }

    /**
     * @param string $suffix The resolver suffix string.
     * @throws InvalidArgumentException If the suffix argument is not a string.
     * @return ResolverFactory Chainable
     */
    public function setResolverSuffix($suffix)
    {
        if (!is_string($suffix)) {
            throw new InvalidArgumentException(
                'Prefix must be a string'
            );
        }
        $this->resolverSuffix = $suffix;
        return $this;
    }

    /**
     * @return string
     */
    public function resolverSuffix()
    {
        return $this->resolverSuffix;
    }

    /**
     * @param array $capitals The array of letter to "calitalize-next" (uppercase next letter in the string).
     * @return ResolverFactory Chainable
     */
    public function setResolverCapitals(array $capitals)
    {
        $this->resolverCapitals = $capitals;
        return $this;
    }

    /**
     * @return array
     */
    public function resolverCapitals()
    {
        return $this->resolverCapitals;
    }

    /**
     * @param array $replacements The array (key=>value) of replacements.
     * @return ResolverFactory Chainable
     */
    public function setResolverReplacements(array $replacements)
    {
        $this->resolverReplacements = $replacements;
        return $this;
    }

    /**
     * @return array
     */
    public function resolverReplacements()
    {
        return $this->resolverReplacements;
    }

    /**
     * Resolve the class name from the requested type.
     *
     * @param string $type The "type" of object to resolve (the object ident).
     * @throws InvalidArgumentException If the type parameter is not a string.
     * @return string The resolved class name (FQN).
     */
    public function resolve($type)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException(
                'Can not resolve class ident: type must be a string'
            );
        }

        $capitalize_next = function (&$i) {
            $i = ucfirst($i);
        };

        $capitals = $this->resolverCapitals();
        foreach ($capitals as $cap) {
            $expl = explode($cap, $type);
            array_walk($expl, $capitalize_next);
            $type = implode($cap, $expl);
        }

        $replacements = $this->resolverReplacements();
        foreach ($replacements as $rep => $target) {
            $type = str_replace($rep, $target, $type);
        }

        $class = '\\' . trim($type, '\\');

        // Add prefix + suffix, if applicable
        $class = $this->resolverPrefix() . $class . $this->resolverSuffix();

        return $class;
    }

    /**
     * @param string $type The "type" of object to resolve (the object ident).
     * @throws InvalidArgumentException If the type parameter is not a string.
     * @return boolean
     */
    public function isResolvable($type)
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
