<?php

declare(strict_types=1);

namespace Charcoal\Admin\Template;

// From 'charcoal-admin'
use Charcoal\Admin\AdminTemplate;

/**
 * Admin Help template
 */
class HelpTemplate extends AdminTemplate
{
    /**
     * Help is available to all users, no login required.
     */
    #[\Override]
    protected function authRequired(): bool
    {
        return false;
    }

    /**
     * Retrieve the title of the page.
     *
     * @return \Charcoal\Translator\Translation|string|null
     */
    #[\Override]
    public function title()
    {
        if ($this->title === null) {
            $this->setTitle($this->translator()->translation('Support'));
        }

        return $this->title;
    }
}
