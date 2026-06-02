<?php

declare(strict_types=1);

namespace Charcoal\Tests\Cms\Mock;

// From 'charcoal-app'
use Charcoal\App\Template\AbstractTemplate;

// From 'charcoal-cms'
use Charcoal\Cms\SectionInterface;

/**
 * Home Template Controller
 */
class HomeTemplate extends AbstractTemplate
{
    private ?\Charcoal\Cms\SectionInterface $section = null;

    /**
     * @return SectionInterface
     */
    public function section(): ?\Charcoal\Cms\SectionInterface
    {
        return $this->section;
    }

    /**
     * @param  SectionInterface $section The current section.
     */
    public function setSection(SectionInterface $section): static
    {
        $this->section = $section;

        return $this;
    }
}
