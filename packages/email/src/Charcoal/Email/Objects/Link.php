<?php

declare(strict_types=1);

namespace Charcoal\Email\Objects;

// From 'charcoal/core'
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
     */
    public function setEmail(?string $emailId): static
    {
        $this->email = $emailId;
        return $this;
    }

    public function email(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $url The original (and target) URL.
     */
    public function setUrl(?string $url): static
    {
        $this->url = $url;
        return $this;
    }

    public function url(): ?string
    {
        return $this->url;
    }
}
