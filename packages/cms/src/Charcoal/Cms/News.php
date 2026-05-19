<?php

declare(strict_types=1);

namespace Charcoal\Cms;

/**
 * CMS News
 */
final class News extends AbstractNews
{
    /**
     * CategorizableTrait > categoryType()
     */
    public function categoryType(): string
    {
        return NewsCategory::class;
    }
}
