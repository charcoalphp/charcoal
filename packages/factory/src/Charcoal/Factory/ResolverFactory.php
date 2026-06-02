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
    private string $resolverPrefix = '';

    private string $resolverSuffix = '';

    private array $resolverCapitals;

    private array $resolverReplacements;

    /**
     * @param array $data Factory arguments.
     */
    public function __construct(?array $data = null)
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
    public function setResolverPrefix($prefix): static
    {
        if (!is_string($prefix)) {
            throw new InvalidArgumentException(
                'Prefix must be a string'
            );
        }
        $this->resolverPrefix = $prefix;
        return $this;
    }

    public function resolverPrefix(): string
    {
        return $this->resolverPrefix;
    }

    /**
     * @param string $suffix The resolver suffix string.
     * @throws InvalidArgumentException If the suffix argument is not a string.
     * @return ResolverFactory Chainable
     */
    public function setResolverSuffix($suffix): static
    {
        if (!is_string($suffix)) {
            throw new InvalidArgumentException(
                'Prefix must be a string'
            );
        }
        $this->resolverSuffix = $suffix;
        return $this;
    }

    public function resolverSuffix(): string
    {
        return $this->resolverSuffix;
    }

    /**
     * @param array $capitals The array of letter to "calitalize-next" (uppercase next letter in the string).
     * @return ResolverFactory Chainable
     */
    public function setResolverCapitals(array $capitals): static
    {
        $this->resolverCapitals = $capitals;
        return $this;
    }

    public function resolverCapitals(): array
    {
        return $this->resolverCapitals;
    }

    /**
     * @param array $replacements The array (key=>value) of replacements.
     * @return ResolverFactory Chainable
     */
    public function setResolverReplacements(array $replacements): static
    {
        $this->resolverReplacements = $replacements;
        return $this;
    }

    public function resolverReplacements(): array
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
    #[\Override]
    public function resolve($type): string
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException(
                'Can not resolve class ident: type must be a string'
            );
        }

        $capitalize_next = function (&$i): void {
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
     */
    #[\Override]
    public function isResolvable($type): bool
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
