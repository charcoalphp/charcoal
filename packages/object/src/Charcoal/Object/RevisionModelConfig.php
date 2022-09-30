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
class RevisionModelConfig extends AbstractConfig
{
    protected bool $enabled = true;
    protected string $revisionClass = ObjectRevision::class;
    // TODO implement the limit feature.
    protected ?int $limitPerModel = null;
    // Limits the revision process to only these properties
    protected array $properties = [];
    // Exclude properties from the revision process.
    protected array $excludedProperties = [];
    // Include properties from the revision process. By default, all properties are included, so this can be used to
    // include a property that was excluded by a parent.
    protected array $includedProperties = [];

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @return string[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    public function hasProperties(): bool
    {
        return !!count($this->properties);
    }

    /**
     * @return string[]
     */
    public function getExcludedProperties(): array
    {
        return $this->excludedProperties;
    }

    public function hasExcludedProperties(): bool
    {
        return !!count($this->excludedProperties);
    }

    /**
     * @return string[]
     */
    public function getIncludedProperties(): array
    {
        return $this->includedProperties;
    }

    public function hasIncludedProperties(): bool
    {
        return (bool)$this->includedProperties;
    }

    /**
     * @return class-string<ObjectRevisionInterface>
     */
    public function getRevisionClass(): string
    {
        return $this->revisionClass;
    }
}
