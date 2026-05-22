<?php

declare(strict_types=1);

namespace Charcoal\Admin\Object;

use InvalidArgumentException;
use Charcoal\Model\AbstractModel;

/**
 *
 */
class Notification extends AbstractModel
{
    /**
     * The user ids.
     * @var string[]
     */
    private array $users = [];

    /**
     * The types of object to watch, for notifications.
     * @var string[]
     */
    private ?array $targetTypes = null;

    /**
     * Extra emails the report shoul be sent to.
     * @var string[]
     */
    private array $extraEmails = [];

    /**
     * Can be "minute", "hourly", "daily", "weekly" or "monthly".
     */
    private ?string $frequency = null;

    private bool $active = true;

    /**
     * @param array|string|null $users The users of this notifications.
     * @throws InvalidArgumentException If the users are not an array or a comma-separated string.
     * @return Notification Chainable
     */
    public function setUsers($users): static
    {
        if ($users === null) {
            $this->users = [];
            return $this;
        }
        if (is_string($users)) {
            $users = explode(',', $users);
        }
        if (!is_array($users)) {
            throw new InvalidArgumentException(
                'Users must be an array or a comma-separated string.'
            );
        }
        $this->users = array_map(trim(...), $users);
        return $this;
    }

    public function users(): array
    {
        return $this->users;
    }

    /**
     * @param array|string|null $targetTypes The targetTypes of this notifications.
     * @throws InvalidArgumentException If the types are not an array or a comma-separated string.
     * @return Notification Chainable
     */
    public function setTargetTypes($targetTypes): static
    {
        if ($targetTypes === null) {
            $this->targetTypes = null;
            return $this;
        }
        if (is_string($targetTypes)) {
            $targetTypes = explode(',', $targetTypes);
        }
        if (!is_array($targetTypes)) {
            throw new InvalidArgumentException(
                'Object types must be an array or a comma-separated string.'
            );
        }
        $this->targetTypes = array_map(trim(...), $targetTypes);
        return $this;
    }

    public function targetTypes(): ?array
    {
        return $this->targetTypes;
    }

    /**
     * @param array|string|null $extraEmails The targetTypes of this notifications.
     * @throws InvalidArgumentException If the emails are not an array or a comma-separated string.
     * @return Notification Chainable
     */
    public function setExtraEmails($extraEmails): static
    {
        if ($extraEmails === null) {
            $this->extraEmails = [];
            return $this;
        }
        if (is_string($extraEmails)) {
            $extraEmails = explode(',', $extraEmails);
        }
        if (!is_array($extraEmails)) {
            throw new InvalidArgumentException(
                'Extra emails must be an array or a comma-separated string.'
            );
        }
        $this->extraEmails = array_map(trim(...), $extraEmails);
        return $this;
    }

    public function extraEmails(): array
    {
        return $this->extraEmails;
    }

    /**
     * @param string $frequency The frequency mode of this notification.
     * @throws InvalidArgumentException If the frequency is not a valid mode.
     * @return Notification Chainable
     */
    public function setFrequency($frequency): static
    {
        if ($frequency === null) {
            $this->frequency = null;
            return $this;
        }
        $validFrequencies = [
            'minute',
            'hourly',
            'daily',
            'weekly',
            'monthly'
        ];
        if (!in_array($frequency, $validFrequencies)) {
            throw new InvalidArgumentException(
                'Invalid frequency'
            );
        }
        $this->frequency = $frequency;
        return $this;
    }

    /**
     * @return boolean
     */
    public function frequency(): ?string
    {
        return $this->frequency;
    }

    /**
     * @param boolean $active The active flag.
     * @return Notification Chainable
     */
    public function setActive($active): static
    {
        $this->active = (bool)$active;
        return $this;
    }

    public function active(): bool
    {
        return $this->active;
    }
}
