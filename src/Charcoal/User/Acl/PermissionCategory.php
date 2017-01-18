<?php

namespace Charcoal\User\Acl;

use Charcoal\Translation\TranslationString;

use Charcoal\Object\Content;
use Charcoal\Object\CategoryInterface;
use Charcoal\Object\CategoryTrait;

/**
 * User permission category
 */
class PermissionCategory extends Content implements CategoryInterface
{
    use CategoryTrait;

    /**
     * @var TranslationString|null
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
     * @return TranslationString|null
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
