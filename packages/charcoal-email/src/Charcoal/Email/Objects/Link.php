<?php

declare(strict_types=1);

namespace Charcoal\Email\Objects;

// From 'locomotivemtl/charcoal-core'
use Charcoal\Model\AbstractModel;

/**
 * Tracking Link
 */
class Link extends AbstractModel
{
    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $url;

    /**
     * @param string $emailId The email (log) id.
     * @return self
     */
    public function setEmail(?string $emailId)
    {
        $this->email = $emailId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function email(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $url The original (and target) URL.
     * @return self
     */
    public function setUrl(?string $url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string|null
     */
    public function url(): ?string
    {
        return $this->url;
    }
}
