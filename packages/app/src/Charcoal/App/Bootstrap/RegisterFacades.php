<?php

namespace Charcoal\App\Bootstrap;

use Charcoal\App\App;
use Charcoal\App\Facade\Facade;

class RegisterFacades
{
    /**
     * Bootstrap the facades with the Charcoal application.
     */
    public function bootstrap(App $app): void
    {
        Facade::clearResolvedFacadeInstances();
        Facade::setFacadeApp($app);
    }
}
