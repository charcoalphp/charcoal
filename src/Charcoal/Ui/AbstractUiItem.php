<?php

namespace Charcoal\Ui;

use \InvalidArgumentException;

// From PSR-3 (Logger)
use \Psr\Log\LoggerAwareInterface;
use \Psr\Log\LoggerAwareTrait;
use \Psr\Log\NullLogger;

use \Pimple\Container;

// From 'charcoal-config'
use \Charcoal\Config\AbstractEntity;

// From 'charcoal-core'
use \Charcoal\Translation\TranslationString;

// From 'charcoal-view'
use \Charcoal\View\ViewableInterface;
use \Charcoal\View\ViewableTrait;

// Intra-module ('charcoal-ui') dependencies
use \Charcoal\Ui\UiItemInterface;
use \Charcoal\Ui\UiItemTrait;

/**
 * An abstract UI Item.
 *
 * Abstract implementation of {@see \Charcoal\Ui\UiItemInterface}.
 */
abstract class AbstractUiItem extends AbstractEntity implements
    LoggerAwareInterface,
    UiItemInterface
{
    use LoggerAwareTrait;
    use ViewableTrait;
    use UiItemTrait;

    /**
     * A UI item is active by default.
     *
     * @var boolean
     */
    private $active = true;

    /**
     * Return a new UI item.
     *
     * @param array|\ArrayAccess $data The class depdendencies.
     */
    public function __construct(array $data = null)
    {
        if (!isset($data['logger'])) {
            $data['logger'] = new NullLogger();
        }
        $this->setLogger($data['logger']);

        if (isset($data['container'])) {
            $this->setDependencies($data['container']);
        }
    }

    /**
     * Inject dependencies from a DI Container.
     *
     * @param  Container $container A dependencies container instance.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        // This method is a stub. Reimplement in children template classes.
    }

    /**
     * Activates/deactivates the UI item.
     *
     * @param boolean $active Activate (TRUE) or deactivate (FALSE) the UI item.
     * @return AbstractUiItem Chainable
     */
    public function setActive($active)
    {
        $this->active = !!$active;

        return $this;
    }

    /**
     * Determine if the UI item is active.
     *
     * @return boolean
     */
    public function active()
    {
        return $this->active;
    }
}
