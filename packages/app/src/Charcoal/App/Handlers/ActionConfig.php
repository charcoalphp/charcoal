<?php

declare(strict_types=1);

namespace Charcoal\App\Handlers;

use Charcoal\App\RouteConfig;

class ActionConfig extends RouteConfig
{
    private array $actionData = [];

    public function setActionData(array $actionData)
    {
        $this->actionData = $actionData;
        return $this;
    }

    public function actionData()
    {
        return $this->actionData;
    }
}
