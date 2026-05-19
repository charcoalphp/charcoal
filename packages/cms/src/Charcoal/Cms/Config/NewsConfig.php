<?php

declare(strict_types=1);

namespace Charcoal\Cms\Config;

// From 'charcoal-config'
use Charcoal\Config\AbstractConfig;

/**
 * News Config
 */
class NewsConfig extends AbstractConfig
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
    private $defaultExpiry;

    /**
     * @var string
     */
    private $median;

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
     * @return string News expiry.
     */
    public function defaultExpiry()
    {
        return $this->defaultExpiry;
    }

    /**
     * @return string Datetime value.
     */
    public function median()
    {
        return $this->median;
    }

    /**
     * @return string News Object type.
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
     * @return string News parent section slug.
     */
    public function parentSectionSlug()
    {
        return $this->parentSectionSlug;
    }

    /**
     * @param integer $numPerPage Number of news per page.
     */
    public function setNumPerPage($numPerPage): static
    {
        $this->numPerPage = $numPerPage;

        return $this;
    }

    /**
     * @param boolean $entryCycle Cycle news or not.
     */
    public function setEntryCycle($entryCycle): static
    {
        $this->entryCycle = $entryCycle;

        return $this;
    }

    /**
     * Accept all DateTime string.
     * @param string $defaultExpiry Expiry.
     */
    public function setDefaultExpiry($defaultExpiry): static
    {
        $this->defaultExpiry = $defaultExpiry;

        return $this;
    }

    /**
     * @param string $median DateTime string.
     */
    public function setMedian($median): static
    {
        $this->median = $median;

        return $this;
    }

    /**
     * @param string $objType News object type.
     */
    public function setObjType($objType): static
    {
        $this->objType = $objType;

        return $this;
    }

    /**
     * @param string $category News category object.
     */
    public function setCategory($category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Might be overkill.
     * @param string $configFeatIdent Config property containing featured news.
     */
    public function setConfigFeatIdent($configFeatIdent): static
    {
        $this->configFeatIdent = $configFeatIdent;

        return $this;
    }

    /**
     * resize -> width.
     * @param array $thumbnail News thumbnail size.
     */
    public function setThumbnail(array $thumbnail): static
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    /**
     * @param string $parentSectionSlug News parent section (slug).
     */
    public function setParentSectionSlug($parentSectionSlug): static
    {
        $this->parentSectionSlug = $parentSectionSlug;

        return $this;
    }
}
