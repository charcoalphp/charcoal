<?php

namespace Charcoal\Ui\Dashboard;

use \Charcoal\Ui\Dashboard\AbstractDashboard;

/**
 * Generic, concrete Dashboard implementation.
 */
class GenericDashboard extends AbstractDashboard
{
        /**
         * @return string
         */
    public function type()
    {
        return 'charcoal/ui/dashboard/generic';
    }
}
