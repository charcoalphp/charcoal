<?php

namespace Charcoal\Property;

// From 'charcoal-property'
use Charcoal\Property\StringProperty;

/**
 * HTML Property.
 *
 * The html property is a specialized string property.
 */
class HtmlProperty extends StringProperty
{

    /**
     * The available filesystems (used in TinyMCE's elFinder media manager).
     *
     * @var string
     */
    private $filesystem = '';

    /**
     * @return string
     */
    public function type()
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
     * @return self
     */
    public function setFilesystem($filesystem)
    {
        $this->filesystem = $filesystem;

        return $this;
    }

    /**
     * Unlike strings' default upper limit of 255, HTML has no default max length (0).
     *
     * @see StringProperty::defaultMaxLength()
     * @return integer
     */
    public function defaultMaxLength()
    {
        return 0;
    }

    /**
     * Unlike the parent's String Property, HTML property obviously always allow HTML.
     *
     * @see StringProperty::allowHtml()
     * @return boolean
     */
    public function getAllowHtml()
    {
        return true;
    }

    /**
     * Get the SQL type (Storage format).
     *
     * @return string The SQL type
     */
    public function sqlType()
    {
        return 'TEXT';
    }
}
