<?php

declare(strict_types=1);

namespace Charcoal\Property;

// From 'charcoal-property'
use Charcoal\Property\TextProperty;

/**
 * HTML Property.
 *
 * The html property is a specialized string property.
 */
class HtmlProperty extends TextProperty
{
    public const DEFAULT_LONG = true;

    /**
     * @var boolean
     */
    protected $long = self::DEFAULT_LONG;

    /**
     * The available filesystems (used in TinyMCE's elFinder media manager).
     *
     * @var string
     */
    private $filesystem = '';

    #[\Override]
    public function type(): string
    {
        return 'html';
    }

    /**
     * @return string
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * @param string $filesystem The file system.
     */
    public function setFilesystem($filesystem): static
    {
        $this->filesystem = $filesystem;

        return $this;
    }

    /**
     * Unlike strings' default upper limit of 255, HTML has no default max length (0).
     *
     * @see StringProperty::defaultMaxLength()
     */
    #[\Override]
    public function defaultMaxLength(): int
    {
        return 0;
    }

    /**
     * Unlike the parent's String Property, HTML property obviously always allow HTML.
     *
     * @see StringProperty::allowHtml()
     */
    #[\Override]
    public function getAllowHtml(): bool
    {
        return true;
    }
}
