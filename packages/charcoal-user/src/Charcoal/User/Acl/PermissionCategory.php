<?php

namespace Charcoal\User\Acl;

// From 'charcoal-object'
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
     * @var \Charcoal\Translator\Translation|null
     */
    private $name;

    /**
     * @param mixed $name The news category name (localized).
     * @return self
     */
    public function setName($name)
    {
        $this->name = $this->translator()->translation($name);
        return $this;
    }

    /**
     * @return \Charcoal\Translator\Translation|null
     */
    public function getName()
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
