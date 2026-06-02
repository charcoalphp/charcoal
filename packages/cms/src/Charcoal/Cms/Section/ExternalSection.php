<?php

declare(strict_types=1);

namespace Charcoal\Cms\Section;

// From 'charcoal-cms'
use Charcoal\Cms\AbstractSection;

/**
 * External section may appear in menus and breadcrumbs, but only
 *
 * Unlike all other section types, they do not provide any metadata information.
 */
class ExternalSection extends AbstractSection
{
    /**
     * @var Translation|string|null
     */
    private $externalUrl;

    /**
     * @param  mixed $url The external URL (localized).
     */
    #[\Override]
    public function setExternalUrl($url): static
    {
        $this->externalUrl = $this->translator()->translation($url);

        return $this;
    }

    /**
     * @return Translation|string|null
     */
    #[\Override]
    public function externalUrl()
    {
        return $this->externalUrl;
    }
}
