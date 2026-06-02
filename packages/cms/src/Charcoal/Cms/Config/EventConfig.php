<?php

declare(strict_types=1);

namespace Charcoal\Cms\Config;

// From 'charcoal-config'
use Charcoal\Config\AbstractConfig;

/**
 * Event Config
 */
class EventConfig extends AbstractConfig
{
    /**
     * @var integer
     */
    private $numPerPage;

    /**
     * @var string
     */
    private $entryCycle;

    /**
     * @var string
     */
    private $lifespan;

    /**
     * @var string
     */
    private $objType;

    /**
     * @var string
     */
    private $category;

    /**
     * @var string
     */
    private $configFeatIdent;

    private ?array $thumbnail = null;

    /**
     * l10n
     * @var string
     */
    private $parentSectionSlug;

    /**
     * @return integer Number of items per page.
     */
    public function numPerPage()
    {
        return $this->numPerPage;
    }

    /**
     * @return boolean Entry cycle.
     */
    public function entryCycle()
    {
        return $this->entryCycle;
    }

    /**
     * Valid DateTime string
     * @return string Event expiry.
     */
    public function lifespan()
    {
        return $this->lifespan;
    }

    /**
     * @return string Event Object type.
     */
    public function objType()
    {
        return $this->objType;
    }

    /**
     * @return string Category object.
     */
    public function category()
    {
        return $this->category;
    }

    /**
     * @return string Config property.
     */
    public function configFeatIdent()
    {
        return $this->configFeatIdent;
    }

    /**
     * @return array Thumbnail generation values.
     */
    public function thumbnail(): ?array
    {
        return $this->thumbnail;
    }

    /**
     * @return string Event parent section slug.
     */
    public function parentSectionSlug()
    {
        return $this->parentSectionSlug;
    }

    /**
     * @param integer $numPerPage Number of event per page.
     * @return EventConfig
     */
    public function setNumPerPage($numPerPage): static
    {
        $this->numPerPage = $numPerPage;

        return $this;
    }

    /**
     * @param boolean $entryCycle Cycle event or not.
     * @return EventConfig
     */
    public function setEntryCycle($entryCycle): static
    {
        $this->entryCycle = $entryCycle;

        return $this;
    }

    /**
     * Accept all DateTime string.
     * @param string $lifespan Event expiry.
     * @return EventConfig
     */
    public function setLifespan($lifespan): static
    {
        $this->lifespan = $lifespan;

        return $this;
    }

    /**
     * @param string $objType Event object type.
     * @return EventConfig
     */
    public function setObjType($objType): static
    {
        $this->objType = $objType;

        return $this;
    }

    /**
     * @param string $category Event category object.
     * @return EventConfig
     */
    public function setCategory($category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Might be overkill.
     * @param string $configFeatIdent Config property containing featured event.
     * @return EventConfig
     */
    public function setConfigFeatIdent($configFeatIdent): static
    {
        $this->configFeatIdent = $configFeatIdent;

        return $this;
    }

    /**
     * resize -> width.
     * @param array $thumbnail Event thumbnail size.
     * @return EventConfig
     */
    public function setThumbnail(array $thumbnail): static
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    /**
     * @param string $parentSectionSlug Event parent section (slug).
     * @return EventConfig
     */
    public function setParentSectionSlug($parentSectionSlug): static
    {
        $this->parentSectionSlug = $parentSectionSlug;

        return $this;
    }
}
