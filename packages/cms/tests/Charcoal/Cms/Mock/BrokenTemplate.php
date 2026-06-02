<?php

declare(strict_types=1);

namespace Charcoal\Tests\Cms\Mock;

// From 'charcoal-app'
use Charcoal\App\Template\AbstractTemplate;

// From 'charcoal-cms'
use Charcoal\Cms\EventInterface;
use Charcoal\Cms\NewsInterface;
use Charcoal\Cms\SectionInterface;

/**
 * Broken Template Controller
 */
class BrokenTemplate extends AbstractTemplate
{
    private ?\Charcoal\Cms\EventInterface $event = null;

    private ?\Charcoal\Cms\NewsInterface $news = null;

    private ?\Charcoal\Cms\SectionInterface $section = null;

    /**
     * @return EventInterface
     */
    public function event(): ?\Charcoal\Cms\EventInterface
    {
        return $this->event;
    }

    /**
     * @param  EventInterface $event The current event.
     */
    public function setEvent(EventInterface $event): static
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return NewsInterface
     */
    public function news(): ?\Charcoal\Cms\NewsInterface
    {
        return $this->news;
    }

    /**
     * @param  NewsInterface $news The current news.
     */
    public function setNews(NewsInterface $news): static
    {
        $this->news = $news;

        return $this;
    }

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
