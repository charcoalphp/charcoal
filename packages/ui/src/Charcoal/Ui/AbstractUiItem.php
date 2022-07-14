<?php

namespace Charcoal\Ui;

use InvalidArgumentException;
// From PSR-3 (Logger)
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Pimple\Container;
// From 'charcoal-config'
use Charcoal\Config\AbstractEntity;
// From 'charcoal-translator'
use Charcoal\Translator\TranslatorAwareTrait;
// From 'charcoal-user'
use Charcoal\User\AuthAwareInterface;
use Charcoal\User\AuthAwareTrait;
// From 'charcoal-view'
use Charcoal\View\ViewableInterface;
use Charcoal\View\ViewableTrait;
// Intra-module ('charcoal-ui') dependencies
use Charcoal\Ui\PrioritizableTrait;
use Charcoal\Ui\UiItemInterface;
use Charcoal\Ui\UiItemTrait;

/**
 * An abstract UI Item.
 *
 * Abstract implementation of {@see \Charcoal\Ui\UiItemInterface}.
 */
abstract class AbstractUiItem extends AbstractEntity implements
    AuthAwareInterface,
    LoggerAwareInterface,
    UiItemInterface
{
    use AuthAwareTrait;
    use LoggerAwareTrait;
    use PrioritizableTrait;
    use ConditionalizableTrait;
    use TranslatorAwareTrait;
    use UiItemTrait;
    use ViewableTrait;

    /**
     * Return a new UI item.
     *
     * @param array $data The class depdendencies.
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
    protected function setDependencies(Container $container)
    {
        $this->setTranslator($container['translator']);
        $this->setAuthenticator($container['authenticator']);
        $this->setAuthorizer($container['authorizer']);
    }
}
