<?php

namespace Charcoal\Object;

use Charcoal\Event\AbstractEventListener;
use Charcoal\Model\ModelInterface;
use Pimple\Container;

/**
 * Event Listener: Generates a revision for the event's related model.
 */
class GenerateRevisionListener extends AbstractEventListener
{
    protected RevisionsManager $revisionManager;

    public function __invoke(object $event)
    {
        /** @var ModelInterface $model */
        $model = $event->getObject();

        $this->revisionManager->setModel($model)->generateRevision();
    }

    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->revisionManager = $container->get('revisions/manager');
    }
}
