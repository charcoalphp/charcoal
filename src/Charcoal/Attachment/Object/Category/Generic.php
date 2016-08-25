<?php

namespace Charcoal\Attachment\Object\Category;

// Module `charcoal-base` dependencies
use \Charcoal\Object\Content;
use \Charcoal\Object\CategoryInterface;
use \Charcoal\Object\CategoryTrait;

// Module `charcoal-translation` dependencies
use \Charcoal\Translation\TranslationString;

/**
 * Saint-Constant News Category, based on charcoal-object's category.
 */
class Generic extends Content implements CategoryInterface
{
    use CategoryTrait;

    /**
     * @var TranslationString $name
     */
    private $name;

    /**
     * @param mixed $name The news category name (localized).
     * @return NewsCategory Chainable
     */
    public function setName($name)
    {
        $this->name = new TranslationString($name);
        return $this;
    }

    /**
     * @return TranslationString
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
