<?php

namespace Charcoal\Object;

use Charcoal\Config\AbstractConfig;

/**
 * Revision Config
 *
 * The config loaded when creating a revision for a model.
 * The config is generated from the `revisions` key in the config and can be customized per model.
 *
 * {'revisions' : {'Namespace\\Model: {...}'}} here the `...` represents the data used to create the config.
 */
class RevisionConfig extends AbstractConfig
{
    protected bool $enabled = true;
    protected string $revisionClass = ObjectRevision::class;
    protected array $properties = [];
    protected array $propertyBlacklist = [
        'created',
        'lastModified',
        'createdBy',
        'lastModifiedBy',
    ];
}
