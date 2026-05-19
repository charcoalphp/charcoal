<?php

declare(strict_types=1);

namespace Charcoal\Admin\Script\Notification;

use DateTime;
// From 'charcoal-admin'
use Charcoal\Admin\Object\Notification;
use Charcoal\Admin\Script\Notification\AbstractNotificationScript;

/**
 * Process "daily" notifications
 */
class ProcessDailyScript extends AbstractNotificationScript
{
    /**
     * Get the frequency type of this script.
     */
    protected function frequency(): string
    {
        return 'daily';
    }

    /**
     * Retrieve the "minimal" date that the revisions should have been made for this script.
     */
    protected function startDate(): \DateTime
    {
        $d = new DateTime('yesterday');
        $d->setTime(0, 0, 0);
        return $d;
    }

    /**
     * Retrieve the "maximal" date that the revisions should have been made for this script.
     */
    protected function endDate(): \DateTime
    {
        $d = new DateTime('today');
        $d->setTime(0, 0, 0);
        return $d;
    }

    /**
     * @param Notification $notification The notification object.
     * @param array        $objects      The objects that were modified.
     */
    protected function emailData(Notification $notification, array $objects): array
    {
        unset($notification, $objects);

        return [
            'subject'         => sprintf('Daily Charcoal Notification - %s', $this->startDate()->format('Y-m-d')),
            'template_ident'  => 'charcoal/admin/email/notification.daily',
            'template_data'   => [
                'startString' => $this->startDate()->format('Y-m-d')
            ]
        ];
    }
}
