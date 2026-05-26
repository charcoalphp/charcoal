<?php

namespace Charcoal\Attachment\Traits;

use InvalidArgumentException;
// From 'charcoal-core'
use Charcoal\Model\ModelInterface;
// From 'charcoal-attachment'
use Charcoal\Attachment\Interfaces\AttachableInterface;
use Charcoal\Attachment\Interfaces\AttachmentAwareInterface;
use Charcoal\Attachment\Interfaces\AttachmentContainerInterface;
use Charcoal\Attachment\Object\Join;
use Charcoal\Attachment\Object\Attachment;

/**
 * Provides support for attachments to objects.
 *
 * Used by objects that can have an attachment to other objects.
 * This is the glue between the {@see Join} object and the current object.
 *
 * Abstract method needs to be implemented.
 *
 * Implementation of {@see \Charcoal\Attachment\Interfaces\AttachmentAwareInterface}
 *
 * ## Required Services
 *
 * - "model/factory" — {@see \Charcoal\Model\ModelFactory}
 * - "model/collection/loader" — {@see \Charcoal\Loader\CollectionLoader}
 */
trait AttachmentAwareTrait
{
    /**
     * A store of cached attachments, by ID.
     *
     * @var Attachment[] $attachmentCache
     */
    protected static $attachmentCache = [];

    /**
     * Store a collection of node objects.
     *
     * @var Collection|Attachment[]
     */
    protected $attachments = [];

    /**
     * Retrieve the objects associated to the current object.
     *
     * @param  array|string|null $group  Filter the attachments by a group identifier.
     *                                   When an array, filter the attachments by a options list.
     * @param  string|null       $type   Filter the attachments by type.
     * @param  callable|null     $before Process each attachment before applying data.
     * @param  callable|null     $after  Process each attachment after applying data.
     * @throws InvalidArgumentException If the $group or $type is invalid.
     * @return Collection|Attachment[]
     */
    public function getAttachments(
        $group = null,
        $type = null,
        ?callable $before = null,
        ?callable $after = null
    ) {
        if (is_array($group)) {
            $options = $group;
        } else {
            if ($group !== null) {
                $this->logger->warning(
                    'AttachmentAwareTrait::getAttachments() parameters are deprecated. ' .
                    'An array of parameters should be used.',
                    [ 'package' => 'charcoal/attachment' ]
                );
            }
            $options = [
                'group'  => $group,
                'type'   => $type,
                'before' => $before,
                'after'  => $after,
            ];
        }

        $options = $this->parseAttachmentOptions($options);
        extract($options);

        if ($group !== 0 && !is_string($group)) {
            throw new InvalidArgumentException(sprintf(
                'The "group" must be a string, received %s',
                get_debug_type($group)
            ));
        }

        if ($type !== 0) {
            if (!is_string($type)) {
                throw new InvalidArgumentException(sprintf(
                    'The "type" must be a string, received %s',
                    get_debug_type($type)
                ));
            }
            $type = preg_replace('/([a-z])([A-Z])/', '$1-$2', $type);
            $type = strtolower(str_replace('\\', '/', $type));
        }

        if (isset($this->attachments[$group][$type])) {
            return $this->attachments[$group][$type];
        }

        $objType = $this->objType();
        $objId   = $this->id();

        $joinProto = $this->modelFactory()->get(Join::class);
        $joinTable = $joinProto->source()->table();

        $attProto = $this->modelFactory()->get(Attachment::class);
        $attTable = $attProto->source()->table();

        if (!$attProto->source()->tableExists() || !$joinProto->source()->tableExists()) {
            return [];
        }

        $query = sprintf('
            SELECT
                attachment.*,
                joined.attachment_id AS attachment_id,
                joined.position AS position
            FROM
                `%s` AS attachment
            LEFT JOIN
                `%s` AS joined
            ON
                joined.attachment_id = attachment.id
            WHERE
                1 = 1', $attTable, $joinTable);

        /** Disable `active` check in admin, or according to $isActive value */
        if ($isActive === true) {
            $query .= '
            AND
                attachment.active = 1';
        }

        if ($type !== '' && $type !== 0) {
            $query .= sprintf('
            AND
                attachment.type = "%s"', $type);
        }

        $query .= sprintf('
            AND
                joined.object_type = "%s"
            AND
                joined.object_id = "%s"', $objType, $objId);

        if ($group !== '' && $group !== 0) {
            $query .= sprintf('
            AND
                joined.group = "%s"', $group);
        }

        $query .= '
            ORDER BY joined.position';

        $loader = $this->collectionLoader();
        $loader->setModel($attProto);
        $loader->setDynamicTypeField('type');

        $callable = function (&$att) use ($before): void {
            if ($this instanceof AttachableInterface) {
                $att->setContainerObj($this);
            }

            $att->isPresentable(true);

            if ($att->presenter() !== null) {
                $att = $this->modelFactory()
                            ->create($att->presenter())
                            ->setData($att->flatData());
            }

            if ($before !== null) {
                call_user_func_array($before, [ &$att ]);
            }
        };
        $collection = $loader->loadFromQuery($query, $after, $callable->bindTo($this));

        $this->attachments[$group][$type] = $collection;

        return $this->attachments[$group][$type];
    }

    /**
     * Determine if the current object has any nodes.
     *
     * @return boolean Whether $this has any nodes (TRUE) or not (FALSE).
     */
    public function hasAttachments(): bool
    {
        return (bool)$this->numAttachments();
    }

    /**
     * Count the number of nodes associated to the current object.
     */
    public function numAttachments(): int
    {
        return count($this->getAttachments([
            'group' => null
        ]));
    }

    /**
     * Attach an node to the current object.
     *
     * @param  AttachableInterface|ModelInterface $attachment An attachment or object.
     * @param  string                             $group      Attachment group, defaults to contents.
     * @return boolean|self
     */
    public function addAttachment($attachment, $group = 'contents')
    {
        if (!$attachment instanceof AttachableInterface && !$attachment instanceof ModelInterface) {
            return false;
        }

        $join = $this->modelFactory()->create(Join::class);

        $objId   = $this->id();
        $objType = $this->objType();
        $attId   = $attachment->id();

        $join->setAttachmentId($attId);
        $join->setObjectId($objId);
        $join->setGroup($group);
        $join->setObjectType($objType);

        $join->save();

        return $this;
    }

    /**
     * Remove all joins linked to a specific attachment.
     *
     * @return boolean
     */
    #[\Deprecated(message: 'in favour of AttachmentAwareTrait::removeAttachmentJoins()')]
    public function removeJoins()
    {
        $this->logger->warning(
            'AttachmentAwareTrait::removeJoins() is deprecated. ' .
            'Use AttachmentAwareTrait::removeAttachmentJoins() instead.',
            [ 'package' => 'charcoal/attachment' ]
        );

        return $this->removeAttachmentJoins();
    }

    /**
     * Remove all joins linked to a specific attachment.
     */
    public function removeAttachmentJoins(): bool
    {
        $joinProto = $this->modelFactory()->get(Join::class);

        $loader = $this->collectionLoader();
        $loader
            ->setModel($joinProto)
            ->addFilter('object_type', $this->objType())
            ->addFilter('object_id', $this->id());

        $collection = $loader->load();

        foreach ($collection as $obj) {
            $obj->delete();
        }

        return true;
    }

    /**
     * Delete the objects associated to the current object.
     *
     * @param  array $options Filter the attachments by an option list.
     */
    public function deleteAttachments(array $options = []): bool
    {
        foreach ($this->getAttachments($options) as $attachment) {
            $attachment->delete();
        }

        return true;
    }

    /**
     * Available attachment obj_type related to the current object.
     * This goes throught the entire forms / form groups, starting from the
     * dashboard widgets.
     * Returns an array of object classes by group
     * [
     *    group : [
     *        'object\type',
     *        'object\type2',
     *        'object\type3'
     *    ]
     * ]
     * @return array Attachment obj_types.
     */
    public function attachmentObjTypes(): array
    {
        $defaultEditDashboard = $this->metadata()->get('admin.default_edit_dashboard');
        $dashboards = $this->metadata()->get('admin.dashboards');
        $editDashboard = $dashboards[$defaultEditDashboard];
        $widgets = $editDashboard['widgets'];

        $formIdent = '';
        foreach ($widgets as $val) {
            if ($val['type'] == 'charcoal/admin/widget/object-form') {
                $formIdent = $val['form_ident'];
            }
        }

        if (!$formIdent) {
            // No good!
            return [];
        }

        // Current form
        $form = $this->metadata()->get('admin.forms.' . $formIdent);

        // Setted form gruops
        $formGroups = $this->metadata()->get('admin.form_groups');

        // Current form groups
        $groups = $form['groups'];

        $attachmentObjects = [];
        foreach ($groups as $groupIdent => $group) {
            if (isset($formGroups[$groupIdent])) {
                $group = array_replace_recursive(
                    $formGroups[$groupIdent],
                    $group
                );
            }

            if (isset($group['attachable_objects'])) {
                $attachmentObjects[$group['group']] = [];
                foreach ($group['attachable_objects'] as $type => $content) {
                    $attachmentObjects[$group['group']][] = $type;
                }
            }
        }

        return $attachmentObjects;
    }

    /**
     * Parse a given options for loading a collection of attachments.
     *
     * @param  array $options A list of options.
     *    Option keys not present in {@see self::getDefaultAttachmentOptions() default options}
     *    are rejected.
     */
    protected function parseAttachmentOptions(array $options): array
    {
        $defaults = $this->getDefaultAttachmentOptions();

        $options = array_intersect_key($options, $defaults);
        $options = array_filter($options, [ $this, 'filterAttachmentOption' ], ARRAY_FILTER_USE_BOTH);

        return array_replace($defaults, $options);
    }

    /**
     * Parse a given options for loading a collection of attachments.
     *
     * @param  mixed  $val The option value.
     * @param  string $key The option key.
     * @return boolean Return TRUE if the value is preserved. Otherwise FALSE.
     */
    protected function filterAttachmentOption($val, $key)
    {
        if ($val === null) {
            return false;
        }
        return match ($key) {
            'isActive' => is_bool($val),
            'before', 'after' => is_callable($val),
            default => true,
        };
    }

    /**
     * Retrieve the default options for loading a collection of attachments.
     */
    protected function getDefaultAttachmentOptions(): array
    {
        return [
            'group'    => 0,
            'type'     => 0,
            'before'   => null,
            'after'    => null,
            'isActive' => true
        ];
    }



    // Abstract Methods
    // =========================================================================

    /**
     * Retrieve the object's unique ID.
     *
     * @return mixed
     */
    abstract public function id();

    /**
     * Retrieve the object model factory.
     *
     * @return \Charcoal\Factory\FactoryInterface
     */
    abstract public function modelFactory();

    /**
     * Retrieve the model collection loader.
     *
     * @return \Charcoal\Loader\CollectionLoader
     */
    abstract public function collectionLoader();
}
