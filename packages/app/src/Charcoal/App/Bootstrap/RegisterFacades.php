<?php

namespace Charcoal\App\Bootstrap;

use Charcoal\App\App;
use Charcoal\App\Facade\Facade;

/**
 * Class RegisterFacades
 */
class RegisterFacades
{
    /**
     * Bootstrap the charcoal application.
     *
     * @param App $app
     * @return void
     */
    public function bootstrap(App $app)
    {
        Facade::clearResolvedContainerServices();
        Facade::setApp($app);
    }
}
