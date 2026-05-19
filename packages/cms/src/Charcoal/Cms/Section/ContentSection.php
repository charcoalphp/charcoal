<?php

declare(strict_types=1);

namespace Charcoal\Cms\Section;

// From 'charcoal-cms'
use Charcoal\Cms\AbstractSection;
use Charcoal\Cms\Mixin\ContentSectionInterface;

/**
 * Content section
 */
class ContentSection extends AbstractSection
{
    #[\Override]
    public function sectionType(): string
    {
        return AbstractSection::TYPE_CONTENT;
    }
}
