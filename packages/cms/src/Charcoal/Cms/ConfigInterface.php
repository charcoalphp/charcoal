<?php

declare(strict_types=1);

namespace Charcoal\Cms;

// From 'charcoal-property'
use Charcoal\Property\Structure\StructureMetadata;

/**
 * ConfigInterface
 */
interface ConfigInterface
{
    /**
     * @return mixed
     */
    public function defaultMetaTitle();

    /**
     * @return mixed
     */
    public function defaultMetaDescription();

    /**
     * @return mixed
     */
    public function defaultMetaImage();

    /**
     * @return mixed
     */
    public function defaultMetaUrl();

    /**
     * @return array|StructureMetadata|mixed
     */
    public function socialMedias();

    /**
     * @return string
     */
    public function defaultFromEmail();
}
