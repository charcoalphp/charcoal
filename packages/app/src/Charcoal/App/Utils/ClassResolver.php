<?php

declare(strict_types=1);

namespace Charcoal\App\Utils;

class ClassResolver
{
    private const DEFAULT_CAPITALS = [
        '-',
        '\\',
        '/',
        '.',
        '_'
    ];

    private const DEFAULT_REPLACEMENTS = [
        '-' => '',
        '/' => '\\',
        '.' => '_'
    ];

    /**
     * @var string[]
     */
    private $capitals;

    /**
     * @var string[]
     */
    private $replacements;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var string
     */
    private $suffix;

    /**
     * @param string[] $capitals
     * @param string[] $replacements
     * @param string $prefix
     * @param string $suffix
     */
    public function __construct(
        array $capitals = self::DEFAULT_CAPITALS,
        array $replacements = self::DEFAULT_REPLACEMENTS,
        string $prefix = '',
        string $suffix = ''
    ) {
        $this->capitals = $capitals;
        $this->replacements = $replacements;
        $this->prefix = $prefix;
        $this->suffix = $suffix;
    }

    /**
     * @param string $type
     * @return string
     */
    public function __invoke(string $type): string
    {
        return $this->resolve($type);
    }

    /**
     * @param string $type
     * @return string
     */
    public function resolve(string $type): string
    {
        $capitalizeNext = function (&$i) {
            $i = ucfirst($i);
        };

        $type = $this->prefix . $type . $this->suffix;
        foreach ($this->capitals as $cap) {
            $expl = explode($cap, $type);
            if ($expl === false) {
                continue;
            }
            array_walk($expl, $capitalizeNext);
            $type = implode($cap, $expl);
        }

        foreach ($this->replacements as $rep => $target) {
            $type = str_replace($rep, $target, $type);
        }

        $class = '\\' . trim($type, '\\');
        return $this->prefix . $class . $this->prefix;
    }
}
