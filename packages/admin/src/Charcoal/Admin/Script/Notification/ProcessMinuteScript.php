<?php

namespace Charcoal\Admin\Script\Notification;

use DateTime;
// From 'charcoal-admin'
use Charcoal\Admin\Object\Notification;
use Charcoal\Admin\Script\Notification\AbstractNotificationScript;

/**
 * Process "minute" notifications
 */
class ProcessMinuteScript extends AbstractNotificationScript
{
    /**
     * Get the frequency type of this script.
     */
    protected function frequency(): string
    {
        return 'minute';
    }

    /**
     * Retrieve the "minimal" date that the revisions should have been made for this script.
     */
    protected function startDate(): \DateTime
    {
        $d = new DateTime('1 minute ago');
        $d->setTime(0, 0, 0);
        return $d;
    }

    /**
     * Retrieve the "maximal" date that the revisions should have been made for this script.
     */
    protected function endDate(): \DateTime
    {
        return new DateTime($this->starDate() . ' +1 minute');
    }

    /**
     * @param Notification $notification The notification object.
     * @param array        $objects      The objects that were modified.
     */
    protected function emailData(Notification $notification, array $objects): array
    {
        unset($notification, $objects);

        return [
            'subject'         => sprintf('Daily Charcoal Notification - %s', $this->startDate()->format('Y-m-d H:i:s')),
            'template_ident'  => 'charcoal/admin/email/notification.minute',
            'template_data'   => [
                'startString' => $this->startDate()->format('Y-m-d H:i:s')
            ]
        ];
    }
}
