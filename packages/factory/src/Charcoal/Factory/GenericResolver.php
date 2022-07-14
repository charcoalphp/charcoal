<?php

namespace Charcoal\Factory;

use InvalidArgumentException;

/**
 * Converts the given **type** into a **class name**.
 */
class GenericResolver
{
    /**
     * @var string $prefix
     */
    private $prefix = '';

    /**
     * @var string $suffix
     */
    private $suffix = '';

    /**
     * @var array $capitals
     */
    private $capitals;

    /**
     * @var array $replacements
     */
    private $replacements;

    /**
     * @param array $data Optional class dependencies. Will use default values if none are provided.
     */
    public function __construct(array $data = null)
    {
        if (!isset($data['prefix'])) {
            $data['prefix'] = '';
        }
        if (!isset($data['suffix'])) {
            $data['suffix'] = '';
        }
        if (!isset($data['capitals'])) {
            $data['capitals'] = [
                '-',
                '\\',
                '/',
                '.',
                '_'
            ];
        }
        if (!isset($data['replacements'])) {
            $data['replacements'] = [
                '-' => '',
                '/' => '\\',
                '.' => '_'
            ];
        }
        $this->prefix = $data['prefix'];
        $this->suffix = $data['suffix'];
        $this->capitals = $data['capitals'];
        $this->replacements = $data['replacements'];
    }

    /**
     * Resolver needs to be callable
     *
     * @param string $type The "type" of object to resolve (the object ident).
     * @return string The resolved class name (FQN).
     */
    public function __invoke($type)
    {
        return $this->resolve($type);
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

        // Normalize requested type with prefix / suffix, if applicable.
        $type = $this->prefix . $type . $this->suffix;

        $capitalizeNext = function (&$i) {
            $i = ucfirst($i);
        };

        $capitals = $this->capitals;
        foreach ($capitals as $cap) {
            $expl = explode($cap, $type);
            array_walk($expl, $capitalizeNext);
            $type = implode($cap, $expl);
        }

        $replacements = $this->replacements;
        foreach ($replacements as $rep => $target) {
            $type = str_replace($rep, $target, $type);
        }

        $class = '\\' . trim($type, '\\');

        return $class;
    }
}
