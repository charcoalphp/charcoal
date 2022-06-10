<?php

namespace Charcoal\Attachment\Object\Category;

// From 'charcoal-object'
use Charcoal\Object\Content;
use Charcoal\Object\CategoryInterface;
use Charcoal\Object\CategoryTrait;

/**
 * Attachment Category
 *
 * Based on 'charcoal/object''s category.
 */
class Generic extends Content implements CategoryInterface
{
    use CategoryTrait;

    /**
     * The name of the category.
     *
     * @var \Charcoal\Translator\Translation|string|null
     */
    private $name;

    /**
     * @param  string $name The attachment category name.
     * @return NewsCategory Chainable
     */
    public function setName($name)
    {
        $this->name = $this->translator()->translation($name);

        return $this;
    }

    /**
     * @return \Charcoal\Translator\Translation|string|null
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function loadCategoryItems()
    {
        return [];
    }
}
