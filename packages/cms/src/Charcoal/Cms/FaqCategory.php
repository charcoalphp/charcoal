<?php

declare(strict_types=1);

namespace Charcoal\Cms;

// From 'charcoal-object'
use Charcoal\Object\Content;
use Charcoal\Object\CategoryInterface;
use Charcoal\Object\CategoryTrait;

/**
 * FAQ Category
 */
final class FaqCategory extends Content implements CategoryInterface
{
    use CategoryTrait;

    /**
     * CategoryTrait > itemType()
     */
    public function itemType(): string
    {
        return Faq::class;
    }

    /**
     * @return \Charcoal\Model\Collection|array
     */
    public function loadCategoryItems(): array
    {
        return [];
    }
}
