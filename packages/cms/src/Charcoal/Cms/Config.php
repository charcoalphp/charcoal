<?php

namespace Charcoal\Cms;

// From 'charcoal-object'
use Charcoal\Object\Content;
// From 'charcoal-property'
use Charcoal\Property\Structure\StructureMetadata;
// From 'charcoal-cms'
use Charcoal\Cms\ConfigInterface;

/**
 * Class Config
 */
class Config extends Content implements
    ConfigInterface
{
    /**
     * @var string
     */
    protected $defaultMetaTitle;

    /**
     * @var string
     */
    protected $defaultMetaDescription;

    /**
     * @var string
     */
    protected $defaultMetaImage;

    /**
     * @var string
     */
    protected $defaultMetaUrl;

    /**
     * @var StructureMetadata|array|mixed
     */
    protected $socialMedias;

    /**
     * @var string
     */
    protected $defaultFromEmail;

    // ==========================================================================
    // INIT
    // ==========================================================================

    /**
     * Section constructor.
     * @param array $data The data.
     */
    public function __construct(?array $data = null)
    {
        parent::__construct($data);

        if (is_callable($this->defaultData(...))) {
            $this->setData($this->defaultData());
        }
    }

    // ==========================================================================
    // SETTERS
    // ==========================================================================
    /**
     * @param mixed $defaultMetaTitle The default meta title.
     */
    public function setDefaultMetaTitle($defaultMetaTitle): static
    {
        $this->defaultMetaTitle = $this->translator()->translation($defaultMetaTitle);

        return $this;
    }

    /**
     * @param mixed $defaultMetaDescription The default meta description.
     */
    public function setDefaultMetaDescription($defaultMetaDescription): static
    {
        $this->defaultMetaDescription = $this->translator()->translation($defaultMetaDescription);

        return $this;
    }

    /**
     * @param mixed $defaultMetaImage The default meta image.
     */
    public function setDefaultMetaImage($defaultMetaImage): static
    {
        $this->defaultMetaImage = $defaultMetaImage;

        return $this;
    }

    /**
     * @param mixed $defaultMetaUrl The default meta url.
     */
    public function setDefaultMetaUrl($defaultMetaUrl): static
    {
        $this->defaultMetaUrl = $this->translator()->translation($defaultMetaUrl);

        return $this;
    }

    /**
     * @param array|StructureMetadata|mixed $socialMedias The social media array.
     */
    public function setSocialMedias($socialMedias): static
    {
        $this->socialMedias = $socialMedias;

        return $this;
    }

    /**
     * @param string $defaultFromEmail The default email to send from.
     */
    public function setDefaultFromEmail($defaultFromEmail): static
    {
        $this->defaultFromEmail = $defaultFromEmail;

        return $this;
    }

    // ==========================================================================
    // GETTERS
    // ==========================================================================

    /**
     * @return mixed
     */
    public function defaultMetaTitle()
    {
        return $this->defaultMetaTitle;
    }

    /**
     * @return mixed
     */
    public function defaultMetaDescription()
    {
        return $this->defaultMetaDescription;
    }

    /**
     * @return mixed
     */
    public function defaultMetaImage()
    {
        return $this->defaultMetaImage;
    }

    /**
     * @return mixed
     */
    public function defaultMetaUrl()
    {
        return $this->defaultMetaUrl;
    }

    /**
     * @return array|StructureMetadata|mixed
     */
    public function socialMedias()
    {
        return $this->socialMedias;
    }

    /**
     * @return string
     */
    public function defaultFromEmail()
    {
        return $this->defaultFromEmail;
    }
}
