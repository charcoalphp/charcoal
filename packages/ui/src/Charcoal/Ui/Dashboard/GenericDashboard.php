<?php

declare(strict_types=1);

namespace Charcoal\Ui\Dashboard;

// From 'charcoal-ui'
use Charcoal\Ui\Dashboard\AbstractDashboard;

/**
 * A Generic Dashboard
 *
 * Concreete implementation of {@see \Charcoal\Ui\Dashboard\DashboardInterface}.
 */
class GenericDashboard extends AbstractDashboard
{
    /**
     * Retrieve the dashboard type.
     */
    #[\Override]
    public function type(): string
    {
        return 'charcoal/ui/dashboard/generic';
    }
}
