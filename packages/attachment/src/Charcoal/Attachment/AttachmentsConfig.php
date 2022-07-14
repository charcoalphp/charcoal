<?php

namespace Charcoal\Attachment;

use InvalidArgumentException;
// From 'charcoal-config'
use Charcoal\Config\AbstractConfig;

/**
 * Attachments Configset
 */
class AttachmentsConfig extends AbstractConfig
{
    /**
     * Available attachment widget structures.
     *
     * @var array
     */
    private $widgets = [];

    /**
     * Attachment type groupings.
     *
     * @var array
     */
    private $groups = [];

    /**
     * Available attachment types.
     *
     * @var array
     */
    private $attachables = [];

    /**
     * Set attachments settings in a specific order.
     *
     * @param  array $data New config values.
     * @return AttachmentsConfig Chainable
     */
    public function setData(array $data)
    {
        if (isset($data['attachables'])) {
            $this->setAttachables($data['attachables']);
        }

        if (isset($data['groups'])) {
            $this->setGroups($data['groups']);
        }

        if (isset($data['widgets'])) {
            $this->setWidgets($data['widgets']);
        }

        unset($data['attachables'], $data['groups'], $data['widgets']);

        return parent::setData($data);
    }

    /**
     * Set attachment types.
     *
     * @param  array $attachables One or more attachment types.
     * @throws InvalidArgumentException If the attachment type or structure is invalid.
     * @return AttachmentsConfig Chainable
     */
    public function setAttachables(array $attachables)
    {
        foreach ($attachables as $attType => $attStruct) {
            if (!is_array($attStruct)) {
                throw new InvalidArgumentException(sprintf(
                    'The attachment structure for "%s" must be an array',
                    $attType
                ));
            }

            if (isset($attStruct['attachment_type'])) {
                $attType = $attStruct['attachment_type'];
            } else {
                $attStruct['attachment_type'] = $attType;
            }

            if (!is_string($attType)) {
                throw new InvalidArgumentException(
                    'The attachment type must be a string'
                );
            }

            $this->attachables[$attType] = $attStruct;
        }

        return $this;
    }

    /**
     * Retrieve the available attachment types.
     *
     * @return array
     */
    public function attachables()
    {
        return $this->attachables;
    }

    /**
     * Set attachment type groups.
     *
     * @param  array $groups One or more groupings.
     * @throws InvalidArgumentException If the group identifier or structure is invalid.
     * @return AttachmentsConfig Chainable
     */
    public function setGroups(array $groups)
    {
        foreach ($groups as $groupIdent => $groupStruct) {
            if (!is_array($groupStruct)) {
                throw new InvalidArgumentException(sprintf(
                    'The attachment group "%s" must be an array of attachable objects',
                    $groupIdent
                ));
            }

            if (isset($groupStruct['ident'])) {
                $groupIdent = $groupStruct['ident'];
                unset($groupStruct['ident']);
            }

            if (!is_string($groupIdent)) {
                throw new InvalidArgumentException(
                    'The attachment group identifier must be a string'
                );
            }

            if (isset($groupStruct['attachable_objects'])) {
                $groupStruct = $groupStruct['attachable_objects'];
            } elseif (isset($groupStruct['attachables'])) {
                $groupStruct = $groupStruct['attachables'];
            }

            $this->groups[$groupIdent] = $groupStruct;
        }

        return $this;
    }

    /**
     * Retrieve the available attachment type groups.
     *
     * @return array
     */
    public function groups()
    {
        return $this->groups;
    }

    /**
     * Set attachment widget structures.
     *
     * @param  array $widgets One or more widget structures.
     * @throws InvalidArgumentException If the widget identifier or structure is invalid.
     * @return AttachmentsConfig Chainable
     */
    public function setWidgets(array $widgets)
    {
        foreach ($widgets as $widgetIdent => $widgetStruct) {
            if (!is_array($widgetStruct)) {
                throw new InvalidArgumentException(sprintf(
                    'The attachment widget "%s" must be an array of widget settings',
                    $widgetIdent
                ));
            }

            if (isset($widgetStruct['ident'])) {
                $widgetIdent = $widgetStruct['ident'];
                unset($widgetStruct['ident']);
            }

            if (!is_string($widgetIdent)) {
                throw new InvalidArgumentException(
                    'The attachment widget identifier must be a string'
                );
            }

            $this->widgets[$widgetIdent] = $widgetStruct;
        }

        return $this;
    }

    /**
     * Retrieve the available attachment widget structures.
     *
     * @return array
     */
    public function widgets()
    {
        return $this->widgets;
    }
}
