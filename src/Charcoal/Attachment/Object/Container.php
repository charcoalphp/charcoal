<?php

namespace Charcoal\Attachment\Object;

// From Pimple
use Pimple\Container;

// From 'charcoal-config'
use Charcoal\Config\ConfigurableInterface;

// From 'beneroch/charcoal-attachments'
use Charcoal\Attachment\Interfaces\AttachmentAwareInterface;
use Charcoal\Attachment\Interfaces\AttachmentContainerInterface;
use Charcoal\Attachment\Traits\AttachmentAwareTrait;
use Charcoal\Attachment\Traits\AttachmentContainerTrait;
use Charcoal\Attachment\Traits\ConfigurableAttachmentsTrait;

/**
 * Gallery Attachment Type
 *
 * This type allows for nesting of additional attachment types.
 */
class Container extends Attachment implements
    AttachmentAwareInterface,
    AttachmentContainerInterface,
    ConfigurableInterface
{
    use AttachmentAwareTrait;
    use AttachmentContainerTrait;
    use ConfigurableAttachmentsTrait;

    /**
     * Inject dependencies from a DI Container.
     *
     * @param  Container $container A dependencies container instance.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        if (isset($container['attachments/config'])) {
            $this->setConfig($container['attachments/config']);
        } elseif (isset($container['config']['attachments'])) {
            $this->setConfig($container['config']['attachments']);
        }
    }
}
