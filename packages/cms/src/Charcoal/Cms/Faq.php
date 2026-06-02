<?php

declare(strict_types=1);

namespace Charcoal\Cms;

/**
 *
 */
final class Faq extends AbstractFaq
{
    /**
     * CategorizableTrait > categoryType()
     */
    public function categoryType(): string
    {
        return FaqCategory::class;
    }
}
