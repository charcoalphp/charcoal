<?php

declare(strict_types=1);

namespace Charcoal\Tests\Cms\Mock;

// From 'charcoal-app'
use Charcoal\App\Template\AbstractTemplate;

// From 'charcoal-cms'
use Charcoal\Cms\NewsInterface;

/**
 * News Template Controller
 */
class NewsTemplate extends AbstractTemplate
{
    private ?\Charcoal\Cms\NewsInterface $news = null;

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
}
