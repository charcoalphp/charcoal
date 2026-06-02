<?php

declare(strict_types=1);

namespace Charcoal\Tests\Cms\Mock;

// From 'charcoal-cms'
use Charcoal\Cms\AbstractSection;

/**
 *
 */
class WebPage extends AbstractSection
{
    /**
     * Insert object in storage.
     */
    #[\Override]
    public function preSave(): bool
    {
        $this->generateDefaultMetaTags();

        return parent::preSave();
    }

    /**
     * Update object in storage.
     *
     * @param  array $properties Optional. The list of properties to update.
     */
    #[\Override]
    public function preUpdate(?array $properties = null): bool
    {
        $this->generateDefaultMetaTags();

        return parent::preUpdate($properties);
    }
}
