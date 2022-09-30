<?php

namespace Charcoal\Object;

use Charcoal\Event\AbstractEventListener;
use Charcoal\Model\ModelInterface;
use Pimple\Container;

/**
 * Listener
 */
class GenerateRevisionListener extends AbstractEventListener
{
    protected RevisionsManager $revisionService;

    public function __invoke(object $event)
    {
        /** @var ModelInterface $model */
        $model = $event->getObject();

        $this->revisionService->setModel($model)->generateRevision();
    }

    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->revisionService = $container->get('revision/service');
    }
}
