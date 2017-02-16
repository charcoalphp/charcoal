<?php

namespace Charcoal\Property;

// Local namespace dependencies
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
     * Unlike strings' default upper limit of 255, HTML has no default max length (0).
     *
     * @return integer
     */
    public function defaultMaxLength()
    {
        return 0;
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

    /**
     * @return string
     */
    public function filesystem()
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
}
