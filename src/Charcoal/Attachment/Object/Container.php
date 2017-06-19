<?php

namespace Charcoal\Attachment\Object;

// From Pimple
use Pimple\Container as PimpleContainer;

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
    use AttachmentAwareTrait {
        AttachmentAwareTrait::attachments as getAttachments;
    }
    use AttachmentContainerTrait;
    use ConfigurableAttachmentsTrait;

    /**
     * Inject dependencies from a DI Container.
     *
     * @param  PimpleContainer $container A dependencies container instance.
     * @return void
     */
    public function setDependencies(PimpleContainer $container)
    {
        parent::setDependencies($container);

        if (isset($container['attachments/config'])) {
            $this->setConfig($container['attachments/config']);
        } elseif (isset($container['config']['attachments'])) {
            $this->setConfig($container['config']['attachments']);
        }
    }

    /**
     * Retrieve the objects associated to the current object.
     *
     * @param  string|null   $group  Filter the attachments by a group identifier.
     * @param  string|null   $type   Filter the attachments by type.
     * @param  callable|null $before Process each attachment before applying data.
     * @param  callable|null $after  Process each attachment after applying data.
     * @throws InvalidArgumentException If the $group or $type is invalid.
     * @return Collection|Attachment[]
     */
    public function attachments(
        $group = null,
        $type = null,
        callable $before = null,
        callable $after = null
    ) {
        $attachableObjects = $this->attachableObjects();
        $attachments       = $this->getAttachments($group, $type, $before, $after);

        foreach ($attachments as $attachment) {
            if (isset($attachableObjects[$attachment->objType()])) {
                $attachment->attachmentType = $attachableObjects[$attachment->objType()];
            } else {
                $attachment->attachmentType = [];
            }

            yield $attachment;
        }
    }
}
