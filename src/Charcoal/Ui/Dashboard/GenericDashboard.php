<?php

namespace Charcoal\Ui\Dashboard;

// Intra-module (`charcoal-ui`) dependency
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
     *
     * @return string
     */
    public function type()
    {
        return 'charcoal/ui/dashboard/generic';
    }
}
