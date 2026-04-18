<?php

declare(strict_types=1);

namespace vhs;

use Sabre\VObject\ITip;
use Sabre\VObject\ITip\Broker;

/**
 * CalDAV Schedule plugin with local delivery disabled.
 *
 * Skips local iTIP delivery so that all invites/updates are sent
 * via email through IMipPlugin, even when the attendee is a local user.
 *
 * Also treats DESCRIPTION changes as significant so they trigger emails.
 */
class SchedulePlugin extends \Sabre\CalDAV\Schedule\Plugin
{
    /**
     * Do not deliver scheduling messages to local users.
     * This ensures IMipPlugin always sends an email instead.
     */
    public function scheduleLocalDelivery(ITip\Message $iTipMessage): void
    {
        // Intentionally left empty to skip local delivery.
    }

    /**
     * Returns an iTip Broker with DESCRIPTION added to significant properties.
     */
    protected function createITipBroker(): Broker
    {
        $broker = parent::createITipBroker();
        $broker->significantChangeProperties[] = 'DESCRIPTION';
        $broker->significantChangeProperties[] = 'SUMMARY';

        return $broker;
    }
}
